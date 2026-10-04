<?php
session_start();
include("conexion.php");
include("funciones.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit();
}

$id_usuario = (int)$_SESSION["id_usuario"];
$rol = $_SESSION["rol"];

$stmt = $conexion->prepare(
    "SELECT u.nombre, u.apellido, u.correo, u.fecha_registro, r.nombre AS rol, i.nombre AS institucion
     FROM usuarios u
     INNER JOIN roles r ON r.id_rol = u.id_rol
     LEFT JOIN instituciones i ON i.id_institucion = u.id_institucion
     WHERE u.id_usuario = ?"
);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Algunas estadisticas segun el rol, solo informativas
$stats = [];
if ($rol === 'Estudiante') {
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) AS inscripciones,
                AVG(nota) AS promedio,
                SUM(nota IS NOT NULL) AS evaluadas
         FROM participantes WHERE id_usuario = ?"
    );
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} elseif ($rol === 'Docente') {
    $stmt = $conexion->prepare("SELECT COUNT(*) AS proyectos FROM proyectos_prae WHERE id_responsable = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <h1>Mi perfil</h1>
    <?php flash_mostrar(); ?>

    <div class="kpis">
        <?php if ($rol === 'Estudiante'): ?>
            <div class="kpi"><span class="num"><?php echo (int)($stats['inscripciones'] ?? 0); ?></span><span class="lbl">Inscripciones</span></div>
            <div class="kpi"><span class="num"><?php echo $stats['promedio'] === null ? '-' : number_format((float)$stats['promedio'], 1, '.', ''); ?></span><span class="lbl">Promedio de notas</span></div>
        <?php elseif ($rol === 'Docente'): ?>
            <div class="kpi"><span class="num"><?php echo (int)($stats['proyectos'] ?? 0); ?></span><span class="lbl">Proyectos a cargo</span></div>
        <?php endif; ?>
        <div class="kpi"><span class="num"><?php echo htmlspecialchars($u['rol']); ?></span><span class="lbl">Rol</span></div>
    </div>

    <h2>Datos personales</h2>
    <form class="crear" action="perfil_acciones.php" method="POST">
        <input type="hidden" name="accion" value="actualizar_datos">
        <label style="font-size:0.85rem; color:#555;">Nombres</label>
        <input type="text" name="nombre" maxlength="80" required value="<?php echo htmlspecialchars($u['nombre']); ?>">
        <label style="font-size:0.85rem; color:#555;">Apellidos</label>
        <input type="text" name="apellido" maxlength="80" required value="<?php echo htmlspecialchars($u['apellido']); ?>">
        <label style="font-size:0.85rem; color:#555;">Correo electronico</label>
        <input type="email" name="correo" maxlength="120" required value="<?php echo htmlspecialchars($u['correo']); ?>">
        <p class="meta">Rol: <strong><?php echo htmlspecialchars($u['rol']); ?></strong>
            &nbsp;|&nbsp; Institucion: <strong><?php echo htmlspecialchars($u['institucion'] ?? '-'); ?></strong>
            &nbsp;|&nbsp; Miembro desde: <?php echo date("d/m/Y", strtotime($u['fecha_registro'])); ?></p>
        <button type="submit">Guardar datos</button>
    </form>

    <h2>Cambiar contrasena</h2>
    <form class="crear" action="perfil_acciones.php" method="POST">
        <input type="hidden" name="accion" value="cambiar_clave">
        <label style="font-size:0.85rem; color:#555;">Contrasena actual</label>
        <input type="password" name="actual" required>
        <label style="font-size:0.85rem; color:#555;">Contrasena nueva (minimo 8, 1 mayuscula, 1 numero)</label>
        <input type="password" name="nueva" required>
        <label style="font-size:0.85rem; color:#555;">Confirmar contrasena nueva</label>
        <input type="password" name="confirmar" required>
        <button type="submit">Cambiar contrasena</button>
    </form>
</div>
</body>
</html>
