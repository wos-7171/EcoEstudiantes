<?php
session_start();
include("conexion.php");
include("funciones.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== 'Administrador') {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conexion->prepare(
    "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.id_rol, u.estado
     FROM usuarios u WHERE u.id_usuario = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$u) {
    header("Location: admin.php");
    exit();
}

$res_roles = $conexion->query("SELECT id_rol, nombre FROM roles ORDER BY id_rol");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="breadcrumb"><a href="admin.php">Panel de administracion</a></div>
    <h1>Editar usuario</h1>
    <?php flash_mostrar(); ?>
    <form class="crear" action="admin_acciones.php" method="POST">
        <input type="hidden" name="accion" value="editar_usuario">
        <input type="hidden" name="id" value="<?php echo (int)$u['id_usuario']; ?>">
        <label style="font-size:0.85rem; color:#555;">Nombres</label>
        <input type="text" name="nombre" maxlength="80" required value="<?php echo htmlspecialchars($u['nombre']); ?>">
        <label style="font-size:0.85rem; color:#555;">Apellidos</label>
        <input type="text" name="apellido" maxlength="80" required value="<?php echo htmlspecialchars($u['apellido']); ?>">
        <label style="font-size:0.85rem; color:#555;">Correo electronico</label>
        <input type="email" name="correo" maxlength="120" required value="<?php echo htmlspecialchars($u['correo']); ?>">
        <label style="font-size:0.85rem; color:#555;">Rol</label>
        <select name="id_rol" required>
            <?php while ($r = $res_roles->fetch_assoc()): ?>
                <option value="<?php echo $r['id_rol']; ?>" <?php echo (int)$r['id_rol'] === (int)$u['id_rol'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($r['nombre']); ?></option>
            <?php endwhile; ?>
        </select>
        <label style="font-size:0.85rem; color:#555;">Nueva contrasena (opcional, dejar en blanco para no cambiarla)</label>
        <input type="password" name="contrasena" placeholder="Minimo 8, 1 mayuscula, 1 numero">
        <button type="submit">Guardar cambios</button>
        <a class="btn-action" href="admin.php" style="background:#607d8b;">Cancelar</a>
    </form>
</div>
<?php include("includes/pie.php"); ?>
</body>
</html>
