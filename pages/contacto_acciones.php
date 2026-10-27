<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ' . app_url('pages/contacto.php'));
    exit();
}
$usuario_actual = requiere_usuario($conexion, null, 'indexprincipal.php');
csrf_exigir();

// Honeypot: los bots suelen rellenar campos ocultos.
if (trim((string)($_POST['sitio_web'] ?? '')) !== '') {
    flash_error('No se pudo enviar el mensaje. Intenta de nuevo.');
    header('Location: ' . app_url('pages/contacto.php'));
    exit();
}

$nombre = mb_substr(trim($_POST['nombre'] ?? ''), 0, 100);
$correo = mb_substr(trim($_POST['correo'] ?? ''), 0, 120);
$asunto = mb_substr(trim($_POST['asunto'] ?? ''), 0, 150);
$mensaje = mb_substr(trim($_POST['mensaje'] ?? ''), 0, 2000);

if ($nombre === '' || $asunto === '' || $mensaje === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    flash_error('Completa todos los campos con un correo válido.');
} else {
    $id_usuario = (int)$_SESSION["id_usuario"];
    $stmt = $conexion->prepare(
        "INSERT INTO mensajes_contacto (id_usuario, nombre, correo, asunto, mensaje) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $id_usuario, $nombre, $correo, $asunto, $mensaje);
    if ($stmt->execute()) {
        flash_ok('Gracias. Tu mensaje fue enviado, te responderemos pronto.');
    } else {
        flash_error('No se pudo enviar el mensaje. Intenta de nuevo.');
    }
    $stmt->close();
}

$conexion->close();
header('Location: ' . app_url('pages/contacto.php'));
exit();
