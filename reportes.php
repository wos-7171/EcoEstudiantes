<?php
require_once __DIR__ . '/includes/bootstrap.php';

$usuario_actual = requiere_usuario($conexion, ['Administrador', 'Docente'], 'mis_actividades.php');

$id_usuario = (int)$_SESSION["id_usuario"];
$rol = $_SESSION["rol"];

// Administrador ve todo; Docente solo lo de los proyectos de los que es responsable
$es_admin = ($rol === 'Administrador');
$filtro = $es_admin ? "1=1" : "p.id_responsable = ?";

// Ejecuta una consulta preparada (con o sin filtro de docente) y devuelve todas las filas.
// Todas las consultas de esta pagina leen el estado ACTUAL de cada tabla, asi que los
// graficos siempre reflejan los cambios que se hayan hecho (proyecto pasado a "Finalizado",
// actividad pasada a "En ejecución", evaluaciones nuevas, etc.) apenas se recarga la pagina.
function consultar($conexion, $sql, $es_admin, $id_usuario) {
    $stmt = $conexion->prepare($sql);
    if (!$es_admin) {
        $n = substr_count($sql, '?');
        $stmt->bind_param(str_repeat("i", $n), ...array_fill(0, $n, $id_usuario));
    }
    $stmt->execute();
    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $filas;
}

// ---------- Indicadores generales ----------
$k_proy = consultar($conexion, "SELECT COUNT(*) AS n FROM proyectos_prae p WHERE $filtro", $es_admin, $id_usuario)[0]['n'];

$k_act = consultar($conexion,
    "SELECT COUNT(*) AS n FROM actividades a
     INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto WHERE $filtro",
    $es_admin, $id_usuario)[0]['n'];

$k_part = consultar($conexion,
    "SELECT COUNT(DISTINCT pt.id_usuario) AS estudiantes,
            COUNT(*) AS inscripciones,
            COALESCE(SUM(pt.estado = 'Participó'), 0) AS participaron,
            AVG(pt.nota) AS promedio
     FROM participantes pt
     INNER JOIN actividades a ON a.id_actividad = pt.id_actividad
     INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
     WHERE $filtro",
    $es_admin, $id_usuario)[0];

$k_evid = consultar($conexion,
    "SELECT COUNT(*) AS n FROM evidencias e
     INNER JOIN actividades a ON a.id_actividad = e.id_actividad
     INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
     WHERE $filtro",
    $es_admin, $id_usuario)[0]['n'];

$pct_part = ((int)$k_part['inscripciones'] > 0)
    ? round(100 * (int)$k_part['participaron'] / (int)$k_part['inscripciones'])
    : 0;

// ---------- 1. Proyectos por estado ----------
$proy_por_estado = consultar($conexion,
    "SELECT p.estado, COUNT(*) AS total FROM proyectos_prae p WHERE $filtro GROUP BY p.estado",
    $es_admin, $id_usuario);
$mapa_proy_estado = [];
foreach ($proy_por_estado as $r) { $mapa_proy_estado[$r['estado']] = (int)$r['total']; }
$proy_estados_orden = ['Planificado', 'En ejecución', 'Finalizado', 'Cancelado'];
$g_proy_estado_lbl = $proy_estados_orden;
$g_proy_estado_val = array_map(fn($e) => $mapa_proy_estado[$e] ?? 0, $proy_estados_orden);

// ---------- 2. Actividades por estado ----------
$por_estado = consultar($conexion,
    "SELECT a.estado, COUNT(*) AS total
     FROM actividades a INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
     WHERE $filtro GROUP BY a.estado",
    $es_admin, $id_usuario);
$mapa_estado = [];
foreach ($por_estado as $r) { $mapa_estado[$r['estado']] = (int)$r['total']; }
$estados_orden = ['Programada', 'En ejecución', 'Finalizada', 'Cancelada'];
$g_estado_lbl = $estados_orden;
$g_estado_val = array_map(fn($e) => $mapa_estado[$e] ?? 0, $estados_orden);

// ---------- 3. Participación por proyecto ----------
$por_proyecto = consultar($conexion,
    "SELECT p.nombre,
            COUNT(pt.id_participante) AS inscritos,
            COALESCE(SUM(pt.estado = 'Participó'), 0) AS participaron
     FROM proyectos_prae p
     LEFT JOIN actividades a ON a.id_proyecto = p.id_proyecto
     LEFT JOIN participantes pt ON pt.id_actividad = a.id_actividad
     WHERE $filtro
     GROUP BY p.id_proyecto, p.nombre
     ORDER BY inscritos DESC",
    $es_admin, $id_usuario);
$g_proy_lbl = array_column($por_proyecto, 'nombre');
$g_proy_ins = array_map('intval', array_column($por_proyecto, 'inscritos'));
$g_proy_par = array_map('intval', array_column($por_proyecto, 'participaron'));

// ---------- 4. Ranking de estudiantes por promedio de notas ----------
$ranking = consultar($conexion,
    "SELECT u.nombre, u.apellido, ROUND(AVG(pt.nota), 1) AS promedio,
            COUNT(pt.nota) AS evaluadas,
            SUM(pt.estado = 'Participó') AS participaciones
     FROM participantes pt
     INNER JOIN usuarios u ON u.id_usuario = pt.id_usuario
     INNER JOIN actividades a ON a.id_actividad = pt.id_actividad
     INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
     WHERE $filtro AND pt.nota IS NOT NULL
     GROUP BY u.id_usuario, u.nombre, u.apellido
     HAVING evaluadas > 0
     ORDER BY promedio DESC, evaluadas DESC
     LIMIT 10",
    $es_admin, $id_usuario);
$g_rank_lbl = array_map(fn($r) => $r['nombre'] . ' ' . $r['apellido'], $ranking);
$g_rank_val = array_map(fn($r) => (float)$r['promedio'], $ranking);

// ---------- 5. Evolucion de actividades en el tiempo (por mes) ----------
$anio_max = fecha_anio_maximo();
$evolucion = consultar($conexion,
    "SELECT COALESCE(a.fecha_actividad, DATE(a.fecha_creacion)) AS fecha,
            a.estado
     FROM actividades a INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
     WHERE $filtro",
    $es_admin, $id_usuario);
$mapa_tot = [];
$mapa_fin = [];
$mes_actual = date('Y-m');
foreach ($evolucion as $fila) {
    $fecha = (string)$fila['fecha'];
    $anio = (int)substr($fecha, 0, 4);
    $mes = ($anio >= 2015 && $anio <= $anio_max) ? substr($fecha, 0, 7) : $mes_actual;
    $mapa_tot[$mes] = ($mapa_tot[$mes] ?? 0) + 1;
    if ($fila['estado'] === 'Finalizada') {
        $mapa_fin[$mes] = ($mapa_fin[$mes] ?? 0) + 1;
    }
}
$nombres_mes = [1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic'];
$inicio = date('Y-m', strtotime('-5 months'));
if ($mapa_tot && min(array_keys($mapa_tot)) < $inicio) {
    $inicio = min(array_keys($mapa_tot));
}
$fin = max($mapa_tot ? max(array_keys($mapa_tot)) : date('Y-m'), date('Y-m'));
$cursor = DateTime::createFromFormat('Y-m-d', $inicio . '-01');
$limite = DateTime::createFromFormat('Y-m-d', $fin . '-01');
$g_evo_lbl = [];
$g_evo_tot = [];
$g_evo_fin = [];
$acum_tot = 0;
$acum_fin = 0;
while ($cursor <= $limite) {
    $mes = $cursor->format('Y-m');
    $acum_tot += $mapa_tot[$mes] ?? 0;
    $acum_fin += $mapa_fin[$mes] ?? 0;
    $g_evo_lbl[] = $nombres_mes[(int)$cursor->format('n')] . ' ' . $cursor->format('Y');
    $g_evo_tot[] = $acum_tot;
    $g_evo_fin[] = $acum_fin;
    $cursor->modify('+1 month');
}

$impactos = consultar($conexion,
    "SELECT COALESCE(p.impacto, 'Sin registrar') AS impacto, COUNT(*) AS total
     FROM proyectos_prae p WHERE $filtro GROUP BY impacto",
    $es_admin, $id_usuario);

$opts = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de impacto - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="topbar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <div>
            <h1>Reportes e indicadores de impacto</h1>
            <p class="meta">
                <?php echo $es_admin ? "Vista general de toda la plataforma" : "Resumen de tus proyectos PRAE"; ?>
                &nbsp;|&nbsp; Generado el <?php echo date("d/m/Y H:i"); ?>
            </p>
        </div>
        <div class="no-print" style="display:flex; gap:8px;">
            <button type="button" onclick="window.location.reload()">Actualizar datos</button>
            <button type="button" onclick="window.print()">Imprimir / guardar PDF</button>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi"><span class="num"><?php echo (int)$k_proy; ?></span><span class="lbl">Proyectos PRAE</span></div>
        <div class="kpi"><span class="num"><?php echo (int)$k_act; ?></span><span class="lbl">Actividades</span></div>
        <div class="kpi"><span class="num"><?php echo (int)$k_part['estudiantes']; ?></span><span class="lbl">Estudiantes participantes</span></div>
        <div class="kpi"><span class="num"><?php echo (int)$k_part['inscripciones']; ?></span><span class="lbl">Inscripciones</span></div>
        <div class="kpi"><span class="num"><?php echo $pct_part; ?>%</span><span class="lbl">Tasa de participacion</span></div>
        <div class="kpi"><span class="num"><?php echo $k_part['promedio'] === null ? '-' : number_format((float)$k_part['promedio'], 1, '.', ''); ?></span><span class="lbl">Promedio general de notas</span></div>
        <div class="kpi"><span class="num"><?php echo (int)$k_evid; ?></span><span class="lbl">Evidencias subidas</span></div>
    </div>

    <?php if ((int)$k_act === 0 && (int)$k_proy === 0): ?>
        <p class="vacio">Todavia no hay proyectos ni actividades registradas, por eso los graficos estan vacios. Crea proyectos y actividades para ver los indicadores.</p>
    <?php endif; ?>

    <div class="charts">
        <div class="chart-box">
            <h3>Proyectos por estado</h3>
            <div class="chart-wrap"><canvas id="gProyEstado"></canvas></div>
        </div>
        <div class="chart-box">
            <h3>Actividades por estado</h3>
            <div class="chart-wrap"><canvas id="gEstado"></canvas></div>
        </div>
        <div class="chart-box">
            <h3>Participacion por proyecto</h3>
            <div class="chart-wrap"><canvas id="gProyecto"></canvas></div>
        </div>
        <div class="chart-box">
            <h3>Ranking de estudiantes (promedio de notas)</h3>
            <div class="chart-wrap"><canvas id="gRanking"></canvas></div>
        </div>
        <div class="chart-box" style="grid-column: 1 / -1;">
            <h3>Evolucion de actividades en el tiempo</h3>
            <p class="meta">La linea azul es el total de actividades hasta ese mes. La verde es cuantas ya estan finalizadas. Si una fecha esta mal escrita, esa actividad se cuenta en el mes actual para que no desaparezca.</p>
            <div class="chart-wrap"><canvas id="gEvolucion"></canvas></div>
        </div>
    </div>

    <h2>Impacto ambiental de los proyectos</h2>
    <div class="kpis">
        <?php foreach (['Alto', 'Medio', 'Bajo', 'Sin registrar'] as $nivel):
            $n = 0;
            foreach ($impactos as $fila) { if ($fila['impacto'] === $nivel) $n = (int)$fila['total']; }
        ?>
        <div class="kpi"><span class="num"><?php echo $n; ?></span><span class="lbl"><?php echo $nivel; ?></span></div>
        <?php endforeach; ?>
    </div>

    <h2>Top de estudiantes</h2>
    <?php if (count($ranking) === 0): ?>
        <p class="vacio">Aun no hay estudiantes con notas asignadas.</p>
    <?php else: ?>
    <table class="tabla">
        <thead><tr><th>#</th><th>Estudiante</th><th>Actividades evaluadas</th><th>Promedio</th></tr></thead>
        <tbody>
        <?php foreach ($ranking as $i => $r): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($r['nombre'] . ' ' . $r['apellido']); ?></td>
                <td><?php echo (int)$r['evaluadas']; ?></td>
                <td><strong><?php echo number_format((float)$r['promedio'], 1, '.', ''); ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script>
(function () {
    if (typeof Chart === 'undefined') {
        document.querySelectorAll('.chart-wrap').forEach(function (d) {
            d.innerHTML = '<p class="vacio">No se pudo cargar la libreria de graficos (se necesita conexion a internet).</p>';
        });
        return;
    }
    var verde = '#2e7d32', verdeClaro = '#81c784', azul = '#1976d2', gris = '#607d8b', rojo = '#d32f2f', ambar = '#f9a825';
    var entero = { beginAtZero: true, ticks: { precision: 0 } };

    new Chart(document.getElementById('gProyEstado'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($g_proy_estado_lbl, $opts); ?>,
            datasets: [{ data: <?php echo json_encode($g_proy_estado_val); ?>, backgroundColor: [gris, azul, verde, rojo] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('gEstado'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($g_estado_lbl, $opts); ?>,
            datasets: [{ data: <?php echo json_encode($g_estado_val); ?>, backgroundColor: [gris, azul, verde, rojo] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('gProyecto'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($g_proy_lbl, $opts); ?>,
            datasets: [
                { label: 'Inscritos', data: <?php echo json_encode($g_proy_ins); ?>, backgroundColor: ambar },
                { label: 'Participaron', data: <?php echo json_encode($g_proy_par); ?>, backgroundColor: verde }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: entero } }
    });

    new Chart(document.getElementById('gRanking'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($g_rank_lbl, $opts); ?>,
            datasets: [{ label: 'Promedio', data: <?php echo json_encode($g_rank_val); ?>, backgroundColor: verdeClaro, borderColor: verde, borderWidth: 1 }]
        },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                   scales: { x: { min: 0, max: 5, ticks: { stepSize: 0.5 } } }, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('gEvolucion'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($g_evo_lbl, $opts); ?>,
            datasets: [
                { label: 'Total acumulado', data: <?php echo json_encode($g_evo_tot); ?>, borderColor: azul, backgroundColor: 'rgba(25,118,210,0.15)', tension: 0.2, borderWidth: 3, pointRadius: 4, fill: true },
                { label: 'Finalizadas acumuladas', data: <?php echo json_encode($g_evo_fin); ?>, borderColor: verde, backgroundColor: 'rgba(46,125,50,0.12)', tension: 0.2, borderWidth: 3, pointRadius: 4, fill: true }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: entero }, plugins: { legend: { position: 'bottom' } } }
    });
})();
</script>
<?php include("includes/pie.php"); ?>
</body>
</html>
