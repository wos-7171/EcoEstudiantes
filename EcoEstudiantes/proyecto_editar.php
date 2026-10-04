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

$id_proyecto = isset($_GET['id_proyecto']) ? intval($_GET['id_proyecto']) : 0;
$p = proyecto_gestionable($conexion, $id_proyecto, $id_usuario, $rol);
if (!$p) {
    header("Location: proyectos.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar proyecto - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="breadcrumb"><a href="proyectos.php">Proyectos PRAE</a></div>
    <h1>Editar proyecto</h1>
    <?php flash_mostrar(); ?>
    <form class="crear" action="proyectos_acciones.php" method="POST">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id_proyecto" value="<?php echo (int)$p['id_proyecto']; ?>">
        <label style="font-size:0.85rem; color:#555;">Nombre</label>
        <input type="text" name="nombre" maxlength="150" required value="<?php echo htmlspecialchars($p['nombre']); ?>">
        <label style="font-size:0.85rem; color:#555;">Descripcion</label>
        <textarea name="descripcion" rows="3"><?php echo htmlspecialchars($p['descripcion'] ?? ''); ?></textarea>
        <label style="font-size:0.85rem; color:#555;">Objetivo</label>
        <textarea name="objetivo" rows="2"><?php echo htmlspecialchars($p['objetivo'] ?? ''); ?></textarea>
        <label style="font-size:0.85rem; color:#555;">Fecha de inicio (ano entre <?php echo FECHA_ANIO_MINIMO; ?> y <?php echo fecha_anio_maximo(); ?>)</label>
        <input type="date" name="fecha_inicio" min="<?php echo FECHA_ANIO_MINIMO; ?>-01-01" max="<?php echo fecha_anio_maximo(); ?>-12-31" value="<?php echo htmlspecialchars($p['fecha_inicio'] ?? ''); ?>">
        <label style="font-size:0.85rem; color:#555;">Fecha de fin (estimada)</label>
        <input type="date" name="fecha_fin" min="<?php echo FECHA_ANIO_MINIMO; ?>-01-01" max="<?php echo fecha_anio_maximo(); ?>-12-31" value="<?php echo htmlspecialchars($p['fecha_fin'] ?? ''); ?>">
        <button type="submit">Guardar cambios</button>
        <a class="btn-action" href="proyectos.php" style="background:#607d8b;">Cancelar</a>
    </form>
</div>
</body>
</html>
