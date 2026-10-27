<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ' . app_url('perfil.php'));
    exit();
}
$usuario_actual = requiere_usuario($conexion, null, 'indexprincipal.php');
csrf_exigir();

$id_usuario = (int)$_SESSION["id_usuario"];
$accion = $_POST['accion'] ?? '';

try {

    // 1. ACTUALIZAR NOMBRE, APELLIDO Y CORREO
    if ($accion === 'actualizar_datos') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if ($nombre === '' || $apellido === '' || $correo === '') {
            flash_error("Completa nombre, apellido y correo.");
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            flash_error("El correo electronico no es valido.");
        } else {
            $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario <> ?");
            $stmt->bind_param("si", $correo, $id_usuario);
            $stmt->execute();
            $en_uso = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($en_uso) {
                flash_error("Ese correo ya lo usa otro usuario.");
            } else {
                $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, correo = ? WHERE id_usuario = ?");
                $stmt->bind_param("sssi", $nombre, $apellido, $correo, $id_usuario);
                $stmt->execute();
                $stmt->close();
                $_SESSION['nombre'] = $nombre . ' ' . $apellido;
                $_SESSION['correo'] = $correo;
                flash_ok("Tus datos se actualizaron correctamente.");
            }
        }
    }

    // 2. CAMBIAR CONTRASENA (pide la actual, por seguridad)
    if ($accion === 'cambiar_clave') {
        $actual = $_POST['actual'] ?? '';
        $nueva = $_POST['nueva'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';

        $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$fila || !password_verify($actual, $fila['password'])) {
            flash_error("La contrasena actual no es correcta.");
        } elseif ($nueva !== $confirmar) {
            flash_error("La nueva contrasena y su confirmacion no coinciden.");
        } else {
            $errores = password_errores($nueva);
            if (count($errores) > 0) {
                flash_error("La nueva contrasena debe tener: " . implode(', ', $errores) . ".");
            } else {
                $hash = password_hash($nueva, PASSWORD_BCRYPT);
                $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
                $stmt->bind_param("si", $hash, $id_usuario);
                $stmt->execute();
                $stmt->close();
                flash_ok("Tu contrasena se cambio correctamente.");
            }
        }
    }

} catch (Throwable $e) {
    flash_error("Ocurrio un error al procesar la accion. Intenta de nuevo.");
}

$conexion->close();
header("Location: perfil.php");
exit();
?>
