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

// Solo Administrador o Docente (y el docente solo en sus proyectos)
if ($rol !== 'Administrador' && $rol !== 'Docente') {
    header("Location: proyectos.php");
    exit();
}

$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 0;

$stmt = $conexion->prepare(
    "SELECT a.id_actividad, a.id_proyecto, a.nombre, a.estado, a.fecha_actividad,
            p.nombre AS proyecto_nombre, p.id_responsable
     FROM actividades a
     INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
     WHERE a.id_actividad = ?"
);
$stmt->bind_param("i", $id_actividad);
$stmt->execute();
$actividad = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$actividad) {
    header("Location: proyectos.php");
    exit();
}

// Permiso: Admin siempre; Docente solo si es el responsable del proyecto
if ($rol === 'Docente' && (int)$actividad['id_responsable'] !== (int)$id_usuario) {
    header("Location: actividades.php?id_proyecto=" . (int)$actividad['id_proyecto']);
    exit();
}

// Estudiantes inscritos
$stmt = $conexion->prepare(
    "SELECT pt.id_participante, pt.id_usuario, pt.estado, pt.nota, pt.fecha_participacion,
            u.nombre, u.apellido, u.correo
     FROM participantes pt
     INNER JOIN usuarios u ON u.id_usuario = pt.id_usuario
     WHERE pt.id_actividad = ?
     ORDER BY u.apellido, u.nombre"
);
$stmt->bind_param("i", $id_actividad);
$stmt->execute();
$participantes = $stmt->get_result();
$stmt->close();

// Evidencias de la actividad, agrupadas por estudiante
$stmt = $conexion->prepare(
    "SELECT id_evidencia, id_usuario, tipo, archivo, descripcion, fecha_subida
     FROM evidencias WHERE id_actividad = ? ORDER BY fecha_subida DESC"
);
$stmt->bind_param("i", $id_actividad);
$stmt->execute();
$res_ev = $stmt->get_result();
$stmt->close();
$evid_por_usuario = [];
while ($e = $res_ev->fetch_assoc()) {
    $evid_por_usuario[$e['id_usuario']][] = $e;
}

$flash_ok = $_SESSION['flash_ok'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_ok'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participantes - <?php echo htmlspecialchars($actividad['nombre']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="breadcrumb">
        <a href="actividades.php?id_proyecto=<?php echo (int)$actividad['id_proyecto']; ?>">Actividades del proyecto</a>
    </div>
    <h1>Participantes y evaluacion</h1>
    <h3 style="margin:4px 0;"><?php echo htmlspecialchars($actividad['nombre']); ?></h3>
    <p class="meta">
        Proyecto: <strong><?php echo htmlspecialchars($actividad['proyecto_nombre']); ?></strong>
        &nbsp;|&nbsp; Estado: <strong><?php echo htmlspecialchars($actividad['estado']); ?></strong>
        &nbsp;|&nbsp; Escala de nota: <strong>1.0 a 5.0</strong>
        <?php if ($actividad['fecha_actividad']) echo ' &nbsp;|&nbsp; ' . date("d/m/Y", strtotime($actividad['fecha_actividad'])); ?>
    </p>

    <?php if ($flash_ok): ?><div class="aviso aviso-ok"><?php echo htmlspecialchars($flash_ok); ?></div><?php endif; ?>
    <?php if ($flash_error): ?><div class="aviso aviso-error"><?php echo htmlspecialchars($flash_error); ?></div><?php endif; ?>

    <?php if ($participantes->num_rows === 0): ?>
        <p class="vacio">Todavia no hay estudiantes inscritos en esta actividad.</p>
    <?php else: while ($p = $participantes->fetch_assoc()):
        $clase_part = [
            'Inscrito' => 'p-inscrito', 'Participó' => 'p-participo', 'No participó' => 'p-noparticipo',
        ][$p['estado']] ?? 'p-inscrito';
        $evs = $evid_por_usuario[$p['id_usuario']] ?? [];
    ?>
    <div class="card-item">
        <h3><?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido']); ?></h3>
        <p class="meta"><?php echo htmlspecialchars($p['correo']); ?>
            &nbsp;|&nbsp; Inscrito el <?php echo date("d/m/Y", strtotime($p['fecha_participacion'])); ?>
            &nbsp;|&nbsp; <span class="badge-estado <?php echo $clase_part; ?>"><?php echo htmlspecialchars($p['estado']); ?></span>
            &nbsp;|&nbsp; <?php echo $p['nota'] === null ? 'Sin evaluar aun' : 'Nota: ' . formato_nota($p['nota']); ?></p>

        <p style="margin:10px 0 0;"><strong>Evidencias (<?php echo count($evs); ?>)</strong></p>
        <?php if (count($evs) === 0): ?>
            <p class="meta">Este estudiante todavia no ha subido evidencias.</p>
        <?php else: ?>
        <div class="evid-grid">
            <?php foreach ($evs as $e): $url = "evidencias/" . rawurlencode($e['archivo']); ?>
            <div class="evid">
                <?php if ($e['tipo'] === 'Imagen'): ?>
                    <a href="<?php echo $url; ?>" target="_blank"><img src="<?php echo $url; ?>" alt="Evidencia"></a>
                <?php elseif ($e['tipo'] === 'Video'): ?>
                    <video src="<?php echo $url; ?>" controls preload="metadata"></video>
                <?php else: ?>
                    <a class="doc" href="<?php echo $url; ?>" target="_blank"><?php echo htmlspecialchars($e['tipo']); ?><br>Abrir / descargar</a>
                <?php endif; ?>
                <p><strong><?php echo htmlspecialchars($e['tipo']); ?></strong> - <?php echo date("d/m/Y H:i", strtotime($e['fecha_subida'])); ?></p>
                <?php if ($e['descripcion']): ?><p><?php echo nl2br(htmlspecialchars($e['descripcion'])); ?></p><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form action="participantes_acciones.php" method="POST" class="acciones-fila" style="margin-top:14px;">
            <input type="hidden" name="accion" value="evaluar">
            <input type="hidden" name="id_participante" value="<?php echo (int)$p['id_participante']; ?>">
            <input type="hidden" name="id_actividad" value="<?php echo $id_actividad; ?>">
            <label class="meta">Resultado:</label>
            <select name="estado" style="width:auto; margin:0;" onchange="ajustarNota(this)">
                <?php foreach (['Inscrito', 'Participó', 'No participó'] as $op): ?>
                    <option value="<?php echo $op; ?>" <?php echo $op === $p['estado'] ? 'selected' : ''; ?>><?php echo $op; ?></option>
                <?php endforeach; ?>
            </select>
            <label class="meta">Nota (1.0 a 5.0):</label>
            <input type="number" name="nota" min="1" max="5" step="0.1"
                   value="<?php echo $p['nota'] === null ? '' : formato_nota($p['nota']); ?>"
                   placeholder="Ej: 4.5" style="width:110px; margin:0;"
                   <?php echo $p['estado'] === 'Participó' ? 'required' : ''; ?>>
            <button type="submit">Guardar evaluacion</button>
        </form>
    </div>
    <?php endwhile; endif; ?>
</div>
<script>
// Si no participo o sigue inscrito, la nota queda vacia. Solo se exige al marcar Participo.
function ajustarNota(sel) {
    var campo = sel.form.querySelector('input[name="nota"]');
    if (sel.value === 'Participó') {
        campo.required = true;
    } else {
        campo.required = false;
        campo.value = '';
    }
}
</script>
</body>
</html>
ml>
