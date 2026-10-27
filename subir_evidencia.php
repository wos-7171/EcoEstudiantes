<?php
require_once __DIR__ . '/includes/bootstrap.php';

$usuario_actual = requiere_usuario($conexion, 'Estudiante', 'proyectos.php');

$id_usuario = (int)$_SESSION["id_usuario"];
$rol = $_SESSION["rol"];

$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 0;

// Traer la actividad + su proyecto
$stmt = $conexion->prepare(
    "SELECT a.id_actividad, a.id_proyecto, a.nombre, a.descripcion, a.estado, a.fecha_actividad,
            p.nombre AS proyecto_nombre
     FROM actividades a
     INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
     WHERE a.id_actividad = ?"
);
$stmt->bind_param("i", $id_actividad);
$stmt->execute();
$actividad = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$actividad) {
    header("Location: mis_actividades.php");
    exit();
}

// El estudiante debe estar inscrito en esta actividad
$stmt = $conexion->prepare("SELECT estado, nota FROM participantes WHERE id_actividad = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_actividad, $id_usuario);
$stmt->execute();
$inscripcion = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$inscripcion) {
    header("Location: actividades.php?id_proyecto=" . (int)$actividad['id_proyecto']);
    exit();
}

// Evidencias que este estudiante ya subio para esta actividad
$stmt = $conexion->prepare(
    "SELECT id_evidencia, tipo, archivo, descripcion, fecha_subida
     FROM evidencias WHERE id_actividad = ? AND id_usuario = ?
     ORDER BY fecha_subida DESC"
);
$stmt->bind_param("ii", $id_actividad, $id_usuario);
$stmt->execute();
$evidencias = $stmt->get_result();
$stmt->close();

$flash_ok = $_SESSION['flash_ok'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

$clase_est = [
    'Programada' => 'e-programada', 'En ejecución' => 'e-ejecucion',
    'Finalizada' => 'e-finalizada', 'Cancelada' => 'e-cancelada',
][$actividad['estado']] ?? 'e-programada';
$clase_part = [
    'Inscrito' => 'p-inscrito', 'Participó' => 'p-participo', 'No participó' => 'p-noparticipo',
][$inscripcion['estado']] ?? 'p-inscrito';
$cancelada = ($actividad['estado'] === 'Cancelada');

$version_styles = (int)@filemtime(__DIR__ . '/assets/css/styles.css');
$version_app = (int)@filemtime(__DIR__ . '/assets/css/app.css');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir evidencia - <?php echo htmlspecialchars($actividad['nombre']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo $version_styles; ?>">
    <link rel="stylesheet" href="assets/css/app.css?v=<?php echo $version_app; ?>">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="breadcrumb">
        <a href="mis_actividades.php">Mis actividades</a> &nbsp;|&nbsp;
        <a href="actividades.php?id_proyecto=<?php echo (int)$actividad['id_proyecto']; ?>">Ver proyecto</a>
    </div>
    <h1>Subir evidencia</h1>
    <h3 style="margin:4px 0;"><?php echo htmlspecialchars($actividad['nombre']); ?></h3>
    <p class="meta">
        Proyecto: <strong><?php echo htmlspecialchars($actividad['proyecto_nombre']); ?></strong>
        &nbsp;|&nbsp; <span class="badge-estado <?php echo $clase_est; ?>"><?php echo htmlspecialchars($actividad['estado']); ?></span>
        &nbsp;|&nbsp; Mi estado: <span class="badge-estado <?php echo $clase_part; ?>"><?php echo htmlspecialchars($inscripcion['estado']); ?></span>
        <?php if ($inscripcion['nota'] !== null): ?>
            &nbsp;|&nbsp; Nota: <?php echo formato_nota($inscripcion['nota']); ?>
        <?php else: ?>
            &nbsp;|&nbsp; Sin evaluar aun
        <?php endif; ?>
    </p>

    <?php if ($flash_ok): ?><div class="aviso aviso-ok"><?php echo htmlspecialchars($flash_ok); ?></div><?php endif; ?>
    <?php if ($flash_error): ?><div class="aviso aviso-error"><?php echo htmlspecialchars($flash_error); ?></div><?php endif; ?>

    <?php if ($cancelada): ?>
        <div class="aviso aviso-error">Esta actividad fue cancelada, ya no se pueden subir evidencias.</div>
    <?php else: ?>
    <h2>Nueva evidencia</h2>
    <form class="crear" action="subir_evidencia_acciones.php" method="POST" enctype="multipart/form-data">
<?php echo csrf_campo(); ?>
        <input type="hidden" name="accion" value="subir">
        <input type="hidden" name="id_actividad" value="<?php echo $id_actividad; ?>">
        <label style="font-size:0.85rem; color:#555;">Archivo (foto, video o documento, maximo <?php echo evidencia_limite_mb(); ?> MB)</label>
        <input type="file" name="archivo" required
               accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.webm,.mov,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip">
        <label style="font-size:0.85rem; color:#555;">Descripcion (que muestra esta evidencia?)</label>
        <textarea name="descripcion" rows="3" maxlength="500" placeholder="Ej: Foto de la jornada de reciclaje en el patio del colegio"></textarea>
        <button type="submit">Subir evidencia</button>
        <p class="meta">Formatos permitidos: imagenes (JPG, PNG, GIF, WEBP), videos (MP4, WEBM, MOV), documentos (PDF, Word, Excel, PowerPoint, TXT) y ZIP.</p>
    </form>
    <?php endif; ?>

    <h2>Mis evidencias de esta actividad</h2>
    <?php if ($evidencias->num_rows === 0): ?>
        <p class="vacio">Todavia no has subido evidencias para esta actividad.</p>
    <?php else: ?>
    <div class="evid-grid">
        <?php while ($e = $evidencias->fetch_assoc()):
            $url = evidencia_url($e['archivo'], $e['tipo']);
            $url_segura = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        ?>
        <div class="evid<?php echo $url === '' ? ' evid-faltante' : ''; ?>">
            <?php if ($url === ''): ?>
                <div class="evid-placeholder">
                    <strong>Archivo no disponible</strong>
                    <small>La evidencia está registrada, pero el archivo no está en el servidor.</small>
                </div>
            <?php elseif ($e['tipo'] === 'Imagen'): ?>
                <a href="<?php echo $url_segura; ?>" target="_blank" rel="noopener"><img src="<?php echo $url_segura; ?>" alt="Evidencia" loading="lazy"></a>
            <?php elseif ($e['tipo'] === 'Video'): ?>
                <video src="<?php echo $url_segura; ?>" controls preload="metadata"></video>
            <?php else: ?>
                <a class="doc" href="<?php echo $url_segura; ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($e['tipo']); ?><br>Abrir / descargar</a>
            <?php endif; ?>
            <p><strong><?php echo htmlspecialchars($e['tipo']); ?></strong> - <?php echo date("d/m/Y H:i", strtotime($e['fecha_subida'])); ?></p>
            <?php if ($e['descripcion']): ?><p><?php echo nl2br(htmlspecialchars($e['descripcion'])); ?></p><?php endif; ?>
            <form action="subir_evidencia_acciones.php" method="POST" onsubmit="return confirm('¿Eliminar esta evidencia?');">
<?php echo csrf_campo(); ?>
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_evidencia" value="<?php echo (int)$e['id_evidencia']; ?>">
                <input type="hidden" name="id_actividad" value="<?php echo $id_actividad; ?>">
                <button type="submit" class="btn-danger" style="padding:4px 10px; font-size:0.8rem;">Eliminar</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>
<?php include("includes/pie.php"); ?>
</body>
</html>
