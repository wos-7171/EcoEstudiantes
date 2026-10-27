<?php
/**
 * Inicio de sesión.
 *
 * - Solo acepta POST con token CSRF válido.
 * - Limita los intentos fallidos por IP + correo (5 intentos / 15 minutos).
 * - No revela si el correo existe o está desactivado.
 * - Regenera el ID de sesión y el token CSRF al autenticar.
 */

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('index.php'));
    exit();
}

csrf_exigir();

$correo = trim((string)($_POST['correo'] ?? ''));
$contrasena = (string)($_POST['contrasena'] ?? '');

if ($correo === '' || $contrasena === '') {
    flash_error('Escribe tu correo y tu contraseña.');
    header('Location: ' . app_url('index.php'));
    exit();
}

// Bloqueo temporal tras varios intentos fallidos (por correo y por IP).
$bloqueo = login_intentos_estado($conexion, $correo);
$bloqueo_ip = login_intentos_estado($conexion, '__ip__');
if ($bloqueo['bloqueado'] || $bloqueo_ip['bloqueado']) {
    $minutos = max((int)$bloqueo['minutos'], (int)$bloqueo_ip['minutos'], 1);
    flash_error('Demasiados intentos fallidos. Espera ' . $minutos . ' minuto(s) e inténtalo de nuevo.');
    header('Location: ' . app_url('index.php'));
    exit();
}

// Consulta preparada: trae al usuario junto con el nombre de su rol.
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
$usuario = ($resultado && $resultado->num_rows > 0) ? $resultado->fetch_assoc() : null;
$stmt->close();

// Se verifica siempre un hash (real o de relleno) para no filtrar por tiempos.
$hash = $usuario['password'] ?? '$2y$10$LVw6WprbsmY2qCTHv4XBoesJg0ZTqNXpmZ9bgFQV8gs23ednpaEXS';
$clave_correcta = password_verify($contrasena, $hash);

if (!$usuario || !$clave_correcta) {
    login_intentos_fallo($conexion, $correo);
    login_intentos_fallo($conexion, '__ip__', 20, 15);
    flash_error('Correo o contraseña incorrectos.');
    header('Location: ' . app_url('index.php'));
    exit();
}

// La cuenta existe y la contraseña es correcta, pero está desactivada.
if ((int)$usuario['estado'] !== 1) {
    flash_error('Tu cuenta está desactivada. Contacta al administrador.');
    header('Location: ' . app_url('index.php'));
    exit();
}

login_intentos_limpiar($conexion, $correo);

// Actualiza el hash si el algoritmo por defecto cambia en el futuro.
if (password_needs_rehash($usuario['password'], PASSWORD_BCRYPT)) {
    $nuevo_hash = password_hash($contrasena, PASSWORD_BCRYPT);
    if ($nuevo_hash !== false) {
        $upd = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
        if ($upd) {
            $id = (int)$usuario['id_usuario'];
            $upd->bind_param("si", $nuevo_hash, $id);
            $upd->execute();
            $upd->close();
        }
    }
}

// Evita fijación de sesión y renueva el token CSRF.
session_regenerate_id(true);
$_SESSION['id_usuario'] = (int)$usuario['id_usuario'];
$_SESSION['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
$_SESSION['correo'] = $usuario['correo'];
$_SESSION['id_rol'] = (int)$usuario['id_rol'];
$_SESSION['rol'] = $usuario['rol_nombre'];
$_SESSION['ultimo_acceso'] = time();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$destino = ($usuario['rol_nombre'] === 'Administrador') ? 'admin.php' : 'indexprincipal.php';
header('Location: ' . app_url($destino));
exit();
