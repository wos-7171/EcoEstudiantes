<?php
session_start();
include("conexion.php");
include("funciones.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== 'Administrador') {
    header("Location: index.php");
    exit();
}
$rol = $_SESSION["rol"];

$res = $conexion->query(
    "SELECT i.id_institucion, i.nombre,
            (SELECT COUNT(*) FROM usuarios u WHERE u.id_institucion = i.id_institucion) AS num_usuarios,
            (SELECT COUNT(*) FROM proyectos_prae p WHERE p.id_institucion = i.id_institucion) AS num_proyectos
     FROM instituciones i ORDER BY i.id_institucion"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instituciones - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="breadcrumb"><a href="admin.php">Panel de administracion</a></div>
    <h1>Instituciones educativas</h1>
    <p class="meta">La lista crece cuando un estudiante se registra o cuando el administrador crea un docente o un estudiante y escribe una institucion nueva.</p>
    <?php flash_mostrar(); ?>

    <h2>Instituciones registradas</h2>
    <table class="tabla">
        <thead><tr><th>ID</th><th>Nombre</th><th>Usuarios</th><th>Proyectos PRAE</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if ($res->num_rows === 0): ?>
            <tr><td colspan="5" style="text-align:center; color:#666;">No hay instituciones registradas.</td></tr>
        <?php else: while ($i = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo (int)$i['id_institucion']; ?></td>
                <td>
                    <form action="instituciones_acciones.php" method="POST" style="display:flex; gap:6px; margin:0;">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id" value="<?php echo (int)$i['id_institucion']; ?>">
                        <input type="text" name="nombre" maxlength="150" required value="<?php echo htmlspecialchars($i['nombre']); ?>" style="margin:0;">
                        <button type="submit" style="white-space:nowrap;">Guardar</button>
                    </form>
                </td>
                <td><?php echo (int)$i['num_usuarios']; ?></td>
                <td><?php echo (int)$i['num_proyectos']; ?></td>
                <td>
                    <?php if ((int)$i['num_usuarios'] === 0 && (int)$i['num_proyectos'] === 0 && (int)$i['id_institucion'] !== 1): ?>
                    <form action="instituciones_acciones.php" method="POST" onsubmit="return confirm('¿Eliminar esta institucion?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo (int)$i['id_institucion']; ?>">
                        <button type="submit" class="link-del">Eliminar</button>
                    </form>
                    <?php else: ?>
                        <span class="meta">En uso</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>
<?php include("includes/pie.php"); ?>
</body>
</html>
