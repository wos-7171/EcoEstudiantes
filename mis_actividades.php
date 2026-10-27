<?php
require_once __DIR__ . '/includes/bootstrap.php';

$usuario_actual = usuario_verificar_activo($conexion, null, true);

$id_usuario = (int)$_SESSION["id_usuario"];
$rol = $_SESSION["rol"];

// Esta pagina es solo para estudiantes
if ($rol !== 'Estudiante') {
    header("Location: proyectos.php");
    exit();
}

// Todas las actividades en las que esta inscrito, de cualquier proyecto
$stmt = $conexion->prepare(
    "SELECT pt.id_participante, pt.estado AS estado_part, pt.nota, pt.fecha_participacion,
            a.id_actividad, a.id_proyecto, a.nombre AS actividad, a.estado AS estado_act,
            a.fecha_actividad,
            p.nombre AS proyecto,
            (SELECT COUNT(*) FROM evidencias e WHERE e.id_actividad = a.id_actividad AND e.id_usuario = pt.id_usuario) AS num_evidencias
     FROM participantes pt
     INNER JOIN actividades a ON a.id_actividad = pt.id_actividad
     INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
     WHERE pt.id_usuario = ?
     ORDER BY pt.fecha_participacion DESC"
);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

$filas = [];
$suma_notas = 0;
$n_notas = 0;
$n_participo = 0;
$n_inscrito = 0;
$total_evidencias = 0;
while ($f = $res->fetch_assoc()) {
    $filas[] = $f;
    if ($f['nota'] !== null) {
        $suma_notas += (float)$f['nota'];
        $n_notas++;
    }
    $total_evidencias += (int)$f['num_evidencias'];
    if ($f['estado_part'] === 'Participó') $n_participo++;
    if ($f['estado_part'] === 'Inscrito') $n_inscrito++;
}
$promedio = $n_notas > 0 ? number_format($suma_notas / $n_notas, 1, '.', '') : '-';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis actividades - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <h1>Mis actividades</h1>
    <p class="meta">Sesion: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong> (Estudiante)</p>

    <div class="kpis">
        <div class="kpi"><span class="num"><?php echo count($filas); ?></span><span class="lbl">Actividades inscritas</span></div>
        <div class="kpi"><span class="num"><?php echo $n_participo; ?></span><span class="lbl">Donde participe</span></div>
        <div class="kpi"><span class="num"><?php echo $n_inscrito; ?></span><span class="lbl">Pendientes de evaluar</span></div>
        <div class="kpi"><span class="num"><?php echo $promedio; ?></span><span class="lbl">Promedio de notas</span></div>
        <div class="kpi"><span class="num"><?php echo $total_evidencias; ?></span><span class="lbl">Evidencias subidas</span></div>
    </div>

    <?php if (count($filas) === 0): ?>
        <p class="vacio">Todavia no te has inscrito a ninguna actividad.
            Entra a <a href="proyectos.php" style="color:#1b5e20; font-weight:600;">Proyectos PRAE</a>,
            abre un proyecto y pulsa "Inscribirme" en la actividad que quieras.</p>
    <?php else: foreach ($filas as $f):
        $clase_part = [
            'Inscrito' => 'p-inscrito', 'Participó' => 'p-participo', 'No participó' => 'p-noparticipo',
        ][$f['estado_part']] ?? 'p-inscrito';
        $clase_act = [
            'Programada' => 'e-programada', 'En ejecución' => 'e-ejecucion',
            'Finalizada' => 'e-finalizada', 'Cancelada' => 'e-cancelada',
        ][$f['estado_act']] ?? 'e-programada';
    ?>
    <div class="card-item">
        <h3><?php echo htmlspecialchars($f['actividad']); ?></h3>
        <p class="meta">Proyecto:
            <a href="actividades.php?id_proyecto=<?php echo (int)$f['id_proyecto']; ?>" style="color:#1b5e20;"><?php echo htmlspecialchars($f['proyecto']); ?></a>
            <?php if ($f['fecha_actividad']) echo ' &nbsp;|&nbsp; ' . date("d/m/Y", strtotime($f['fecha_actividad'])); ?>
        </p>
        <p>
            <span class="badge-estado <?php echo $clase_act; ?>">Actividad: <?php echo htmlspecialchars($f['estado_act']); ?></span>
            <span class="badge-estado <?php echo $clase_part; ?>">Mi estado: <?php echo htmlspecialchars($f['estado_part']); ?></span>
            <span class="meta">&nbsp;<?php echo $f['nota'] === null ? 'Sin evaluar aun' : 'Nota: ' . formato_nota($f['nota']); ?>
                &nbsp;|&nbsp; <?php echo (int)$f['num_evidencias']; ?> evidencia(s)</span>
        </p>
        <div class="acciones-fila">
            <?php if ($f['estado_act'] !== 'Cancelada'): ?>
                <a class="btn-action" href="subir_evidencia.php?id_actividad=<?php echo (int)$f['id_actividad']; ?>">Subir / ver evidencias</a>
            <?php else: ?>
                <span class="meta">Actividad cancelada</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>
<?php include("includes/pie.php"); ?>
</body>
</html>
