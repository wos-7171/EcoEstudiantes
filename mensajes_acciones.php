<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== 'Administrador' || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$accion = $_POST['accion'] ?? '';
$id_mensaje = intval($_POST['id_mensaje'] ?? 0);
$id_yo = (int)$_SESSION["id_usuario"];

if ($accion === 'estado') {
    $validos = ['Nuevo', 'Leído', 'Respondido', 'Archivado'];
    $estado = in_array($_POST['estado'] ?? '', $validos, true) ? $_POST['estado'] : 'Leído';
    $stmt = $conexion->prepare("UPDATE mensajes_contacto SET estado = ? WHERE id_mensaje = ?");
    $stmt->bind_param("si", $estado, $id_mensaje);
    $stmt->execute();
    $stmt->close();
}

if ($accion === 'responder') {
    $respuesta = trim($_POST['respuesta'] ?? '');
    if ($respuesta === '') {
        $_SESSION['flash_error'] = "Escribe la respuesta antes de enviarla.";
    } elseif (mb_strlen($respuesta) > 2000) {
        $_SESSION['flash_error'] = "La respuesta no puede pasar de 2000 caracteres.";
    } else {
        $estado = 'Respondido';
        $stmt = $conexion->prepare(
            "UPDATE mensajes_contacto
             SET respuesta = ?, fecha_respuesta = NOW(), id_admin = ?, estado = ?
             WHERE id_mensaje = ?"
        );
        $stmt->bind_param("sisi", $respuesta, $id_yo, $estado, $id_mensaje);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash_ok'] = "Respuesta enviada. El usuario la vera en Mis mensajes.";
    }
}

if ($accion === 'eliminar') {
    $stmt = $conexion->prepare("DELETE FROM mensajes_contacto WHERE id_mensaje = ?");
    $stmt->bind_param("i", $id_mensaje);
    $stmt->execute();
    $stmt->close();
}

$conexion->close();
header("Location: mensajes.php");
exit();
