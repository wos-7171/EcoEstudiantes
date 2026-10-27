<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Muestra un aviso y vuelve al formulario
function aviso_y_volver($mensaje, $tipo = 'error') {
    if ($tipo === 'ok') {
        flash_ok($mensaje);
    } else {
        flash_error($mensaje);
    }
    header('Location: ' . app_url('index.php'));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ' . app_url('index.php'));
    exit();
}

csrf_exigir();

// Honeypot: los bots suelen rellenar campos ocultos.
if (trim((string)($_POST['sitio_web'] ?? '')) !== '') {
    aviso_y_volver('No se pudo completar el registro. Inténtalo de nuevo.');
}

$nombre = trim($_POST["nombre"] ?? '');
$apellido = trim($_POST["apellido"] ?? '');
$correo = trim($_POST["correo"] ?? '');
$contrasena = $_POST["contrasena"] ?? '';
$contrasena_conf = $_POST["contrasena_conf"] ?? $contrasena;

// El autorregistro solo crea estudiantes. El docente lo crea el administrador.
$id_rol = 3;
$id_institucion = institucion_resolver($conexion, $_POST['id_institucion'] ?? 0, $_POST['institucion_nueva'] ?? '');

// Validaciones del lado del servidor (no se confia solo en el JavaScript)
if ($id_institucion === 0) {
    aviso_y_volver('Elige tu institucion de la lista o escribe el nombre si no aparece.');
}
if ($nombre === '' || $apellido === '' || $correo === '') {
    aviso_y_volver('Completa todos los campos.');
}
if (mb_strlen($nombre) > 80 || mb_strlen($apellido) > 80 || mb_strlen($correo) > 120) {
    aviso_y_volver('Algun campo es demasiado largo.');
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    aviso_y_volver('Escribe un correo electronico valido.');
}
if ($contrasena !== $contrasena_conf) {
    aviso_y_volver('Las contraseñas no coinciden.');
}
$errores = password_errores($contrasena);
if (count($errores) > 0) {
    aviso_y_volver('La contraseña debe tener: ' . implode(', ', $errores) . '.');
}

// Hash seguro de la contraseña (nunca se guarda en texto plano)
$hash = password_hash($contrasena, PASSWORD_BCRYPT);

// El correo ya existe?
$check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
$check->bind_param("s", $correo);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    $conexion->close();
    aviso_y_volver('Ese correo ya esta registrado. Inicia sesion en su lugar.');
}
$check->close();

try {
    $stmt = $conexion->prepare(
        "INSERT INTO usuarios (id_rol, id_institucion, nombre, apellido, correo, password)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("iissss", $id_rol, $id_institucion, $nombre, $apellido, $correo, $hash);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    aviso_y_volver('¡Registro exitoso! Ahora puedes iniciar sesión.', 'ok');
} catch (Throwable $e) {
    aviso_y_volver('Error al registrar usuario. Intenta de nuevo.');
}
