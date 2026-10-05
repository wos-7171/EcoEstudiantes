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

$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 0;
$stmt = $conexion->prepare(
    "SELECT a.*, p.id_responsable, p.nombre AS proyecto_nombre
     FROM actividades a INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
     WHERE a.id_actividad = ?"
);
$stmt->bind_param("i", $id_actividad);
$stmt->execute();
$a = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$a) {
    header("Location: proyectos.php");
    exit();
}
if (!($rol === 'Administrador' || ($rol === 'Docente' && (int)$a['id_responsable'] === $id_usuario))) {
    header("Location: actividades.php?id_proyecto=" . (int)$a['id_proyecto']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar actividad - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="breadcrumb"><a href="actividades.php?id_proyecto=<?php echo (int)$a['id_proyecto']; ?>">Actividades de <?php echo htmlspecialchars($a['proyecto_nombre']); ?></a></div>
    <h1>Editar actividad</h1>
    <?php flash_mostrar(); ?>
    <form class="crear" action="actividades_acciones.php" method="POST">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id_actividad" value="<?php echo (int)$a['id_actividad']; ?>">
        <label style="font-size:0.85rem; color:#555;">Nombre</label>
        <input type="text" name="nombre" maxlength="150" required value="<?php echo htmlspecialchars($a['nombre']); ?>">
        <label style="font-size:0.85rem; color:#555;">Descripcion</label>
        <textarea name="descripcion" rows="3"><?php echo htmlspecialchars($a['descripcion'] ?? ''); ?></textarea>
        <label style="font-size:0.85rem; color:#555;">Fecha de la actividad (ano entre <?php echo FECHA_ANIO_MINIMO; ?> y <?php echo fecha_anio_maximo(); ?>)</label>
        <input type="date" name="fecha_actividad" required min="<?php echo FECHA_ANIO_MINIMO; ?>-01-01" max="<?php echo fecha_anio_maximo(); ?>-12-31" value="<?php echo htmlspecialchars($a['fecha_actividad'] ?? ''); ?>">
        <button type="submit">Guardar cambios</button>
        <a class="btn-action" href="actividades.php?id_proyecto=<?php echo (int)$a['id_proyecto']; ?>" style="background:#607d8b;">Cancelar</a>
    </form>
</div>
<?php include("includes/pie.php"); ?>
</body>
</html>
