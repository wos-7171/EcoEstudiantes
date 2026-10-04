<?php
session_start();
include("conexion.php");
include("funciones.php");

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== 'Administrador' || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$accion = $_POST['accion'] ?? '';

try {

    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '' || mb_strlen($nombre) > 150) {
            flash_error("El nombre de la institucion es obligatorio (maximo 150 caracteres).");
        } else {
            $stmt = $conexion->prepare("INSERT INTO instituciones (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
            $stmt->execute();
            $stmt->close();
            flash_ok("Institucion creada.");
        }
    }

    if ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '' || mb_strlen($nombre) > 150) {
            flash_error("El nombre de la institucion es obligatorio (maximo 150 caracteres).");
        } else {
            $stmt = $conexion->prepare("UPDATE instituciones SET nombre = ? WHERE id_institucion = ?");
            $stmt->bind_param("si", $nombre, $id);
            $stmt->execute();
            $stmt->close();
            flash_ok("Institucion actualizada.");
        }
    }

    if ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        if ($id === 1) {
            flash_error("No se puede eliminar la institucion principal.");
        } else {
            $stmt = $conexion->prepare("SELECT COUNT(*) AS n FROM usuarios WHERE id_institucion = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $n_usuarios = (int)$stmt->get_result()->fetch_assoc()['n'];
            $stmt->close();

            $stmt = $conexion->prepare("SELECT COUNT(*) AS n FROM proyectos_prae WHERE id_institucion = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $n_proyectos = (int)$stmt->get_result()->fetch_assoc()['n'];
            $stmt->close();

            if ($n_usuarios > 0 || $n_proyectos > 0) {
                flash_error("No se puede eliminar: la institucion tiene usuarios o proyectos asociados.");
            } else {
                $stmt = $conexion->prepare("DELETE FROM instituciones WHERE id_institucion = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                flash_ok("Institucion eliminada.");
            }
        }
    }

} catch (Throwable $e) {
    flash_error("Ocurrio un error al procesar la accion. Intenta de nuevo.");
}

$conexion->close();
header("Location: instituciones.php");
exit();
?>
