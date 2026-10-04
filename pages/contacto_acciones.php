<?php
session_start();
include("../conexion.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = mb_substr(trim($_POST['nombre'] ?? ''), 0, 100);
    $correo = mb_substr(trim($_POST['correo'] ?? ''), 0, 120);
    $asunto = mb_substr(trim($_POST['asunto'] ?? ''), 0, 150);
    $mensaje = mb_substr(trim($_POST['mensaje'] ?? ''), 0, 2000);

    if ($nombre === '' || $asunto === '' || $mensaje === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = "Completa todos los campos con un correo valido.";
    } else {
        $stmt = $conexion->prepare(
            "INSERT INTO mensajes_contacto (nombre, correo, asunto, mensaje) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $nombre, $correo, $asunto, $mensaje);
        if ($stmt->execute()) {
            $_SESSION['flash_ok'] = "Gracias. Tu mensaje fue enviado, te responderemos pronto.";
        } else {
            $_SESSION['flash_error'] = "No se pudo enviar el mensaje. Intenta de nuevo.";
        }
        $stmt->close();
    }
}

$conexion->close();
header("Location: contacto.php");
exit();
?>
