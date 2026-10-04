<?php
session_start();
include("conexion.php");
include("funciones.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$rol = $_SESSION["rol"];

$id_proyecto = isset($_GET['id_proyecto']) ? intval($_GET['id_proyecto']) : 0;

// Traer el proyecto
$stmt = $conexion->prepare(
    "SELECT p.id_proyecto, p.nombre, p.estado, p.id_responsable,
            u.nombre AS resp_nombre, u.apellido AS resp_apellido
     FROM proyectos_prae p
     LEFT JOIN usuarios u ON u.id_usuario = p.id_responsable
     WHERE p.id_proyecto = ?"
);
$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$proyecto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$proyecto) {
    header("Location: proyectos.php");
    exit();
}

$es_responsable = ((int)$proyecto['id_responsable'] === (int)$id_usuario);
$puede_crear = ($rol === 'Administrador' || ($rol === 'Docente' && $es_responsable));

// Traer actividades de este proyecto, con conteo de inscritos
$stmt = $conexion->prepare(
    "SELECT a.id_actividad, a.nombre, a.descripcion, a.fecha_actividad, a.estado,
            (SELECT COUNT(*) FROM participantes pt WHERE pt.id_actividad = a.id_actividad) AS num_inscritos
     FROM actividades a
     WHERE a.id_proyecto = ?
     ORDER BY a.fecha_creacion DESC"
);
$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$actividades = $stmt->get_result();
$stmt->close();

// Si es estudiante: saber en cuáles ya está inscrito (para no mostrar 2 veces el botón)
$mis_inscripciones = [];
if ($rol === 'Estudiante') {
    $stmt = $conexion->prepare(
        "SELECT pt.id_actividad, pt.estado, pt.nota
         FROM participantes pt
         INNER JOIN actividades a ON a.id_actividad = pt.id_actividad
         WHERE a.id_proyecto = ? AND pt.id_usuario = ?"
    );
    $stmt->bind_param("ii", $id_proyecto, $id_usuario);
    $stmt->execute();
    $res_insc = $stmt->get_result();
    while ($row = $res_insc->fetch_assoc()) {
        $mis_inscripciones[$row['id_actividad']] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividades - <?php echo htmlspecialchars($proyecto['nombre']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/app.css">
    <style>
        .app-section { max-width: 1100px; margin: 30px auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #2e7d32; margin-bottom: 4px; }
        .breadcrumb { font-size: 0.9rem; margin-bottom: 15px; }
        .breadcrumb a { color: #1b5e20; text-decoration: none; }
        form.crear { background: #f9f9f9; padding: 18px; border-radius: 8px; border: 1px solid #e0e0e0; margin: 20px 0; }
        input[type="text"], input[type="date"], input[type="number"], textarea {
            width: 100%; padding: 8px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button, .btn-action { background-color: #2e7d32; color: white; padding: 8px 14px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.9rem; font-family: inherit; }
        button:hover, .btn-action:hover { background-color: #1b5e20; }
        .card-actividad { border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; background: #fafafa; margin-bottom: 14px; }
        .card-actividad h3 { margin: 0 0 6px 0; color: #1b5e20; }
        .estado-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600; color: #fff; }
        .e-programada { background: #607d8b; }
        .e-ejecucion { background: #1976d2; }
        .e-finalizada { background: #2e7d32; }
        .e-cancelada { background: #d32f2f; }
        .estado-part { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600; color: #fff; margin-left: 6px; }
        .p-inscrito { background: #f9a825; }
        .p-participo { background: #2e7d32; }
        .p-noparticipo { background: #9e9e9e; }
        .meta { font-size: 0.85rem; color: #666; margin: 4px 0; }
        .form-inline { display: inline; margin: 0; }
        button.link-del { background: none; border: none; color: #d32f2f; padding: 0; font-weight: 600; cursor: pointer; font-size: 0.85rem; float: right; font-family: inherit; }
        button.link-del:hover { background: none; text-decoration: underline; }
        .acciones-fila { margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    </style>
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="breadcrumb"><a href="proyectos.php">Proyectos PRAE</a></div>
    <h1><?php echo htmlspecialchars($proyecto['nombre']); ?></h1>
    <p class="meta">Responsable: <?php echo htmlspecialchars(trim(($proyecto['resp_nombre'] ?? '(sin responsable)') . ' ' . ($proyecto['resp_apellido'] ?? ''))); ?>
        &nbsp;|&nbsp; Estado del proyecto: <strong><?php echo htmlspecialchars($proyecto['estado']); ?></strong></p>
    <?php flash_mostrar(); ?>

    <?php if ($puede_crear): ?>
    <h2>Registrar nueva actividad</h2>
    <form class="crear" action="actividades_acciones.php" method="POST">
        <input type="hidden" name="accion" value="crear">
        <input type="hidden" name="id_proyecto" value="<?php echo $id_proyecto; ?>">
        <input type="text" name="nombre" placeholder="Nombre de la actividad (ej: Jornada de reciclaje)" maxlength="150" required>
        <textarea name="descripcion" placeholder="Descripción de la actividad" rows="3"></textarea>
        <label style="font-size:0.85rem; color:#555;">Fecha de la actividad (ano entre <?php echo FECHA_ANIO_MINIMO; ?> y <?php echo fecha_anio_maximo(); ?>)</label>
        <input type="date" name="fecha_actividad" required min="<?php echo FECHA_ANIO_MINIMO; ?>-01-01" max="<?php echo fecha_anio_maximo(); ?>-12-31">
        <button type="submit">Registrar actividad</button>
    </form>
    <?php endif; ?>

    <h2>Actividades de este proyecto</h2>
    <?php if ($actividades->num_rows === 0): ?>
        <p style="color:#666;">Todavía no hay actividades registradas en este proyecto.</p>
    <?php else: while ($a = $actividades->fetch_assoc()):
        $clase_estado = [
            'Programada'    => 'e-programada',
            'En ejecución'  => 'e-ejecucion',
            'Finalizada'    => 'e-finalizada',
            'Cancelada'     => 'e-cancelada',
        ][$a['estado']] ?? 'e-programada';
        $mi_insc = $mis_inscripciones[$a['id_actividad']] ?? null;
    ?>
    <div class="card-actividad">
        <?php if ($puede_crear): ?>
            <form action="actividades_acciones.php" method="POST" class="form-inline"
                  onsubmit="return confirm('¿Eliminar esta actividad? También se borrarán sus inscripciones y evidencias.')">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_actividad" value="<?php echo (int)$a['id_actividad']; ?>">
                <button type="submit" class="link-del" title="Eliminar actividad">Eliminar</button>
            </form>
        <?php endif; ?>
        <h3><?php echo htmlspecialchars($a['nombre']); ?></h3>
        <span class="estado-badge <?php echo $clase_estado; ?>"><?php echo htmlspecialchars($a['estado']); ?></span>
        <span class="meta"><?php echo $a['num_inscritos']; ?> inscritos
            <?php if ($a['fecha_actividad']) echo ' &nbsp;|&nbsp; ' . date("d/m/Y", strtotime($a['fecha_actividad'])); ?></span>
        <?php if ($a['descripcion']): ?><p><?php echo nl2br(htmlspecialchars($a['descripcion'])); ?></p><?php endif; ?>

        <div class="acciones-fila">
            <?php if ($puede_crear): ?>
                <form action="actividades_acciones.php" method="POST" style="display:inline;">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <input type="hidden" name="id_actividad" value="<?php echo $a['id_actividad']; ?>">
                    <input type="hidden" name="id_proyecto" value="<?php echo $id_proyecto; ?>">
                    <select name="estado" onchange="this.form.submit()">
                        <?php foreach (['Programada','En ejecución','Finalizada','Cancelada'] as $op): ?>
                            <option value="<?php echo $op; ?>" <?php echo $op === $a['estado'] ? 'selected' : ''; ?>><?php echo $op; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <a class="btn-action" href="participantes.php?id_actividad=<?php echo $a['id_actividad']; ?>">Ver / evaluar participantes</a>
                <a class="btn-action" style="background:#607d8b;" href="actividad_editar.php?id_actividad=<?php echo $a['id_actividad']; ?>">Editar</a>
            <?php endif; ?>

            <?php if ($rol === 'Estudiante'): ?>
                <?php if ($mi_insc === null): ?>
                    <?php if (in_array($a['estado'], ['Programada', 'En ejecución'], true)): ?>
                    <form action="actividades_acciones.php" method="POST" style="display:inline;">
                        <input type="hidden" name="accion" value="inscribirse">
                        <input type="hidden" name="id_actividad" value="<?php echo $a['id_actividad']; ?>">
                        <input type="hidden" name="id_proyecto" value="<?php echo $id_proyecto; ?>">
                        <button type="submit">Inscribirme</button>
                    </form>
                    <?php else: ?>
                        <span class="meta">Inscripciones cerradas (actividad <?php echo htmlspecialchars(mb_strtolower($a['estado'])); ?>)</span>
                    <?php endif; ?>
                <?php else:
                    $clase_p = [
                        'Inscrito'      => 'p-inscrito',
                        'Participó'     => 'p-participo',
                        'No participó'  => 'p-noparticipo',
                    ][$mi_insc['estado']] ?? 'p-inscrito';
                ?>
                    <span class="estado-part <?php echo $clase_p; ?>">Mi estado: <?php echo htmlspecialchars($mi_insc['estado']); ?></span>
                    <span class="meta"><?php echo $mi_insc['nota'] === null ? 'Sin evaluar aun' : 'Nota: ' . formato_nota($mi_insc['nota']); ?></span>
                    <a class="btn-action" href="subir_evidencia.php?id_actividad=<?php echo $a['id_actividad']; ?>">Subir evidencia</a>
                    <?php if ($mi_insc['estado'] === 'Inscrito'): ?>
                    <form action="actividades_acciones.php" method="POST" style="display:inline;"
                          onsubmit="return confirm('¿Cancelar tu inscripción en esta actividad?')">
                        <input type="hidden" name="accion" value="desinscribirse">
                        <input type="hidden" name="id_actividad" value="<?php echo $a['id_actividad']; ?>">
                        <button type="submit" style="background:#9e9e9e;">Cancelar inscripción</button>
                    </form>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; endif; ?>
</div>
</body>
</html>
