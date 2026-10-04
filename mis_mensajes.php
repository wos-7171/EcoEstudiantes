<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_SESSION["id_usuario"];
$correo = $_SESSION["correo"] ?? "";
$stmt = $conexion->prepare(
    "SELECT asunto, mensaje, fecha_envio, estado, respuesta, fecha_respuesta
     FROM mensajes_contacto
     WHERE id_usuario = ? OR correo = ?
     ORDER BY fecha_envio DESC"
);
$stmt->bind_param("is", $id, $correo);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis mensajes - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/app.css">
    <link rel="stylesheet" href="css/motion.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <div class="breadcrumb"><a href="pages/contacto.php">Enviar otro mensaje</a></div>
    <h1>Mis mensajes</h1>
    <p>Aqui ves lo que enviaste por contacto y la respuesta del administrador.</p>
    <?php if ($res->num_rows === 0): ?>
        <p class="vacio">Todavia no has enviado mensajes. Puedes escribir uno en Contacto.</p>
    <?php else: while ($m = $res->fetch_assoc()): ?>
    <div class="card-item">
        <h3><?php echo htmlspecialchars($m['asunto'] ?: '(Sin asunto)'); ?></h3>
        <p class="meta"><?php echo date("d/m/Y H:i", strtotime($m['fecha_envio'])); ?>
            &nbsp;<span class="badge-estado <?php echo $m['estado'] === 'Respondido' ? 'e-finalizada' : 'p-noparticipo'; ?>"><?php echo htmlspecialchars($m['estado']); ?></span></p>
        <p><strong>Tu mensaje</strong><br><?php echo nl2br(htmlspecialchars($m['mensaje'])); ?></p>
        <?php if (!empty($m['respuesta'])): ?>
            <div class="aviso aviso-ok">
                <strong>Respuesta del administrador<?php echo $m['fecha_respuesta'] ? ' · ' . date("d/m/Y H:i", strtotime($m['fecha_respuesta'])) : ''; ?></strong><br>
                <?php echo nl2br(htmlspecialchars($m['respuesta'])); ?>
            </div>
        <?php else: ?>
            <p class="meta">El administrador todavia no responde este mensaje.</p>
        <?php endif; ?>
    </div>
    <?php endwhile; endif; ?>
</div>
</body>
</html>
