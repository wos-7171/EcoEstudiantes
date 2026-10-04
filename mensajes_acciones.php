<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== 'Administrador' || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$accion = $_POST['accion'] ?? '';
$id_mensaje = intval($_POST['id_mensaje'] ?? 0);

if ($accion === 'estado') {
    $validos = ['Nuevo', 'Leído', 'Respondido', 'Archivado'];
    $estado = in_array($_POST['estado'] ?? '', $validos, true) ? $_POST['estado'] : 'Leído';
    $stmt = $conexion->prepare("UPDATE mensajes_contacto SET estado = ? WHERE id_mensaje = ?");
    $stmt->bind_param("si", $estado, $id_mensaje);
    $stmt->execute();
    $stmt->close();
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
?>
