<?php
session_start();
include("conexion.php");

// Solo el administrador lee los mensajes de contacto
if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== 'Administrador') {
    header("Location: index.php");
    exit();
}
$rol = $_SESSION["rol"];

$res = $conexion->query(
    "SELECT id_mensaje, nombre, correo, asunto, mensaje, fecha_envio, estado, respuesta, fecha_respuesta
     FROM mensajes_contacto ORDER BY (estado = 'Nuevo') DESC, fecha_envio DESC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes de contacto - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="breadcrumb"><a href="admin.php">Panel de administracion</a></div>
    <h1>Mensajes de contacto</h1>
    <?php if (!empty($_SESSION['flash_ok'])): ?><div class="aviso aviso-ok"><?php echo htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div><?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?><div class="aviso aviso-error"><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div><?php endif; ?>

    <?php if ($res->num_rows === 0): ?>
        <p class="vacio">No hay mensajes de contacto todavia.</p>
    <?php else: while ($m = $res->fetch_assoc()):
        $clase = $m['estado'] === 'Nuevo' ? 'e-cancelada' : ($m['estado'] === 'Leído' ? 'p-noparticipo' : 'e-finalizada');
    ?>
    <div class="card-item">
        <h3><?php echo htmlspecialchars($m['asunto'] ?: '(Sin asunto)'); ?></h3>
        <p class="meta">De <strong><?php echo htmlspecialchars($m['nombre']); ?></strong>
            (<a href="mailto:<?php echo htmlspecialchars($m['correo']); ?>"><?php echo htmlspecialchars($m['correo']); ?></a>)
            &nbsp;|&nbsp; <?php echo date("d/m/Y H:i", strtotime($m['fecha_envio'])); ?>
            &nbsp;<span class="badge-estado <?php echo $clase; ?>"><?php echo htmlspecialchars($m['estado']); ?></span></p>
        <p><?php echo nl2br(htmlspecialchars($m['mensaje'])); ?></p>
        <?php if (!empty($m['respuesta'])): ?>
            <div class="aviso aviso-ok">
                <strong>Respuesta enviada<?php echo $m['fecha_respuesta'] ? ' el ' . date("d/m/Y H:i", strtotime($m['fecha_respuesta'])) : ''; ?>:</strong><br>
                <?php echo nl2br(htmlspecialchars($m['respuesta'])); ?>
            </div>
        <?php endif; ?>
        <form action="mensajes_acciones.php" method="POST">
            <input type="hidden" name="accion" value="responder">
            <input type="hidden" name="id_mensaje" value="<?php echo (int)$m['id_mensaje']; ?>">
            <label for="resp<?php echo (int)$m['id_mensaje']; ?>">Responder al usuario</label>
            <textarea id="resp<?php echo (int)$m['id_mensaje']; ?>" name="respuesta" rows="3" maxlength="2000" required><?php echo htmlspecialchars($m['respuesta'] ?? ''); ?></textarea>
            <button type="submit"><?php echo empty($m['respuesta']) ? 'Enviar respuesta' : 'Actualizar respuesta'; ?></button>
        </form>
        <div class="acciones-fila">
            <?php foreach (['Leído', 'Respondido', 'Archivado'] as $nuevo): if ($m['estado'] !== $nuevo): ?>
            <form action="mensajes_acciones.php" method="POST" style="display:inline;">
                <input type="hidden" name="accion" value="estado">
                <input type="hidden" name="id_mensaje" value="<?php echo (int)$m['id_mensaje']; ?>">
                <input type="hidden" name="estado" value="<?php echo $nuevo; ?>">
                <button type="submit">Marcar como <?php echo $nuevo === 'Leído' ? 'leido' : ($nuevo === 'Archivado' ? 'archivado' : 'respondido'); ?></button>
            </form>
            <?php endif; endforeach; ?>
            <form action="mensajes_acciones.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este mensaje?');">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_mensaje" value="<?php echo (int)$m['id_mensaje']; ?>">
                <button type="submit" class="btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
    <?php endwhile; endif; ?>
</div>
</body>
</html>
