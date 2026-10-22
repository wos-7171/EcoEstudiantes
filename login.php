<?php
require_once __DIR__ . '/includes/funciones.php';
sesion_iniciar();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : ''; // no se recorta: el registro tampoco la recorta

// Consulta preparada: trae al usuario junto con el nombre de su rol
$sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.password,
               u.id_rol, r.nombre AS rol_nombre, u.estado
        FROM usuarios u
        INNER JOIN roles r ON r.id_rol = u.id_rol
        WHERE u.correo = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();

    if ((int)$usuario["estado"] !== 1) {
        $stmt->close();
        $conexion->close();
        echo "<script>alert('Tu cuenta esta desactivada. Contacta al administrador.'); window.location.href='index.php';</script>";
        exit();
    }

    // Verifica la contraseña contra el hash guardado (bcrypt)
    if (password_verify($contrasena, $usuario["password"])) {

        session_regenerate_id(true); // evita fijacion de sesion
        $_SESSION["id_usuario"]    = (int)$usuario["id_usuario"];
        $_SESSION["nombre"]        = $usuario["nombre"] . " " . $usuario["apellido"];
        $_SESSION["correo"]        = $usuario["correo"];
        $_SESSION["id_rol"]        = (int)$usuario["id_rol"];
        $_SESSION["rol"]           = $usuario["rol_nombre"]; // 'Administrador' | 'Docente' | 'Estudiante'
        $_SESSION["ultimo_acceso"] = time();
        $_SESSION["csrf_token"]    = bin2hex(random_bytes(32));

        $stmt->close();
        $conexion->close();

        if ($usuario["rol_nombre"] === "Administrador") {
            header("Location: admin.php");
        } else {
            header("Location: indexprincipal.php");
        }
        exit();
    } else {
        echo "<script>alert('Correo o contraseña incorrectos'); window.location.href='index.php';</script>";
    }
} else {
    echo "<script>alert('Correo o contraseña incorrectos'); window.location.href='index.php';</script>";
}

$stmt->close();
$conexion->close();
?>
