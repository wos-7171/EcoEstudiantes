<?php
/**
 * Arranque común de EcoEstudiantes Digitales.
 *
 * Centraliza:
 *   - La carga de la configuración y la conexión a la base de datos.
 *   - El control de errores (sin detalles internos en producción).
 *   - La sesión segura y las cabeceras HTTP de seguridad.
 *   - Las comprobaciones de autenticación, rol y CSRF.
 *
 * Uso recomendado en pantallas y acciones:
 *   require_once __DIR__ . '/includes/bootstrap.php';
 *   $usuario = requiere_usuario($conexion, ['Administrador']);
 *   csrf_exigir();
 */

if (defined('ECO_BOOTSTRAP')) {
    return;
}
define('ECO_BOOTSTRAP', true);

require_once __DIR__ . '/funciones.php';
require_once dirname(__DIR__) . '/config/conexion.php';

// En producción los errores se registran, nunca se muestran al visitante.
$eco_debug = defined('APP_DEBUG') ? (bool) APP_DEBUG : false;
error_reporting(E_ALL);
ini_set('display_errors', $eco_debug ? '1' : '0');
ini_set('log_errors', '1');

// Cabeceras de seguridad. Se envían antes de cualquier salida.
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header(
        "Content-Security-Policy: default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
        "font-src 'self' https://fonts.gstatic.com data:; " .
        "img-src 'self' data: https:; " .
        "connect-src 'self'; " .
        "frame-src https://www.google.com https://maps.google.com; " .
        "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'"
    );
    if (sesion_es_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

sesion_iniciar();

/**
 * Exige una sesión activa y, opcionalmente, uno o varios roles.
 *
 * Devuelve la fila fresca del usuario (rol y estado leídos de la base de datos,
 * no de la sesión) o redirige si no está autorizado.
 *
 * @param mysqli $conexion
 * @param string|string[]|null $roles
 * @param string $destino Ruta relativa a la raíz de la aplicación.
 * @return array
 */
function requiere_usuario($conexion, $roles = null, $destino = 'indexprincipal.php')
{
    $usuario = usuario_verificar_activo($conexion, null, true);

    if ($roles !== null) {
        $roles = is_array($roles) ? $roles : [$roles];
        if (!in_array($usuario['rol_nombre'], $roles, true)) {
            flash_error('No tienes permisos suficientes para realizar esta acción.');
            if (!headers_sent()) {
                header('Location: ' . app_url($destino));
            } else {
                echo '<script>window.location.href=' . json_encode(app_url($destino)) . ';</script>';
            }
            exit();
        }
    }

    return $usuario;
}

/**
 * Rechaza con 403 cualquier POST que no traiga un token CSRF válido.
 * Las peticiones que no son POST no se ven afectadas (las acciones ya las bloquean).
 */
function csrf_exigir()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }

    if (!csrf_validar()) {
        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>403 - Solicitud no válida</title></head><body>';
        echo '<h1>403 - Solicitud no válida</h1>';
        echo '<p>El formulario expiró o no es válido. Vuelve a la página anterior y recárgala.</p>';
        echo '<p><a href="' . htmlspecialchars(app_url('indexprincipal.php'), ENT_QUOTES, 'UTF-8') . '">Volver al inicio</a></p>';
        echo '</body></html>';
        exit();
    }
}
