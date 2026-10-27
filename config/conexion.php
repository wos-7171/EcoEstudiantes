<?php
/**
 * Configuración y conexión a la base de datos.
 *
 * Prioridad de configuración:
 *   1. config/config.local.dev.php (solo desarrollo; tiene prioridad si existe)
 *   2. config/config.local.php     (producción; ignorado por Git)
 *   3. Variables de entorno        (DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, APP_DEBUG)
 *   4. Valores por defecto de XAMPP (localhost / root / sin contraseña)
 *
 * Para InfinityFree crea config/config.local.php con los datos del panel:
 *
 *   <?php
 *   return [
 *       'DB_HOST' => 'sql123.infinityfree.com',
 *       'DB_USER' => 'if0_00000000',
 *       'DB_PASS' => 'tu_contraseña',
 *       'DB_NAME' => 'if0_00000000_ecoestudiantes',
 *       'APP_DEBUG' => false,
 *   ];
 *
 * En el PC de desarrollo, config/config.local.dev.php (si existe) permite
 * seguir usando XAMPP sin tocar las credenciales de producción. Ese archivo
 * nunca debe subirse al hosting.
 */

date_default_timezone_set('America/Bogota');

if (!defined('ECO_ROOT')) {
    define('ECO_ROOT', dirname(__DIR__));
}

$eco_config = [];
// Se cargan en orden: producción primero y desarrollo después, de modo que
// config.local.dev.php tiene la última palabra cuando existe.
foreach ([__DIR__ . '/config.local.php', __DIR__ . '/config.local.dev.php'] as $eco_config_file) {
    if (!is_file($eco_config_file)) {
        continue;
    }
    $eco_config_cargada = require $eco_config_file;
    if (is_array($eco_config_cargada)) {
        $eco_config = array_merge($eco_config, $eco_config_cargada);
    }
}

$eco_config_valor = static function ($clave, $por_defecto = null) use ($eco_config) {
    if (array_key_exists($clave, $eco_config) && $eco_config[$clave] !== '') {
        return $eco_config[$clave];
    }
    $entorno = getenv($clave);
    if ($entorno !== false && $entorno !== '') {
        return $entorno;
    }
    return $por_defecto;
};

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', filter_var($eco_config_valor('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN));
}
if (!defined('DB_HOST')) {
    define('DB_HOST', (string) $eco_config_valor('DB_HOST', 'localhost'));
}
if (!defined('DB_USER')) {
    define('DB_USER', (string) $eco_config_valor('DB_USER', 'root'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', (string) $eco_config_valor('DB_PASS', ''));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', (string) $eco_config_valor('DB_NAME', 'ecoestudiantes'));
}
if (!defined('DB_PORT')) {
    define('DB_PORT', (int) $eco_config_valor('DB_PORT', 3306));
}

// Los errores nunca se muestran al visitante; solo se registran.
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// Sin excepciones automáticas de mysqli: el fallo se maneja de forma controlada.
mysqli_report(MYSQLI_REPORT_OFF);

$conexion = null;
try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
} catch (Throwable $eco_error_conexion) {
    $conexion = null;
    error_log('EcoEstudiantes: no se pudo crear la conexión MySQL: ' . $eco_error_conexion->getMessage());
}

if (!$conexion || $conexion->connect_error) {
    $detalle = $conexion instanceof mysqli ? $conexion->connect_error : 'conexión no disponible';
    error_log('EcoEstudiantes: error de conexión a la base de datos: ' . $detalle);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    exit('No se pudo conectar con la base de datos. Inténtalo de nuevo más tarde.');
}

if (!$conexion->set_charset('utf8mb4')) {
    error_log('EcoEstudiantes: no se pudo establecer utf8mb4 en la conexión.');
}
