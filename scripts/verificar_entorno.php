<?php
/**
 * Comprobación del entorno antes de publicar.
 * Uso: php scripts/verificar_entorno.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este script solo se puede ejecutar por línea de comandos.\n");
}

$raiz = dirname(__DIR__);
$errores = [];
$avisos = [];
$ok = [];

function linea($estado, $texto) {
    echo '[' . $estado . '] ' . $texto . PHP_EOL;
}

// PHP y extensiones
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    linea('OK', 'PHP ' . PHP_VERSION);
} else {
    $errores[] = 'Se requiere PHP 7.4 o superior (recomendado 8.0+).';
}
foreach (['mysqli', 'mbstring', 'json', 'openssl'] as $extension) {
    if (extension_loaded($extension)) {
        linea('OK', 'Extensión ' . $extension);
    } else {
        $errores[] = 'Falta la extensión PHP: ' . $extension;
    }
}

// Configuración local
if (is_file($raiz . '/config/config.local.dev.php')) {
    linea('OK', 'config/config.local.dev.php presente (valores de desarrollo)');
} elseif (is_file($raiz . '/config/config.local.php')) {
    linea('OK', 'config/config.local.php presente (valores de producción)');
} else {
    $avisos[] = 'No existe configuración local. Se usarán los valores por defecto de XAMPP.';
}

// Conexión y tablas
require $raiz . '/config/conexion.php';
linea('OK', 'Conexión a MySQL establecida (' . DB_NAME . ' en ' . DB_HOST . ')');

$tablas = ['roles', 'usuarios', 'instituciones', 'proyectos_prae', 'actividades', 'participantes', 'evidencias', 'noticias', 'noticias_imagenes', 'galeria', 'mensajes_contacto', 'intentos_login'];
foreach ($tablas as $tabla) {
    $res = $conexion->query("SHOW TABLES LIKE '" . $conexion->real_escape_string($tabla) . "'");
    if ($res && $res->num_rows === 1) {
        linea('OK', 'Tabla ' . $tabla);
    } else {
        $avisos[] = 'Falta la tabla ' . $tabla . '. Importa base_de_datos/esquema.sql.';
    }
}

// Permisos de escritura
foreach (['uploads/evidencias', 'images/noticias', 'images/galeria', 'evidencias'] as $carpeta) {
    $ruta = $raiz . '/' . $carpeta;
    if (!is_dir($ruta)) {
        @mkdir($ruta, 0755, true);
    }
    if (is_dir($ruta) && is_writable($ruta)) {
        linea('OK', 'Escritura en ' . $carpeta);
    } else {
        $errores[] = 'La carpeta ' . $carpeta . ' no existe o no tiene permisos de escritura.';
    }
}

// Modo depuración
if (defined('APP_DEBUG') && APP_DEBUG) {
    $avisos[] = 'APP_DEBUG está activo. Desactívalo en producción.';
} else {
    linea('OK', 'APP_DEBUG desactivado');
}

// Resultado
echo PHP_EOL;
foreach ($avisos as $aviso) {
    linea('AVISO', $aviso);
}
foreach ($errores as $error) {
    linea('ERROR', $error);
}

if (!empty($errores)) {
    echo PHP_EOL . 'Hay errores que debes corregir antes de publicar.' . PHP_EOL;
    exit(1);
}

echo 'Entorno listo para funcionar.' . PHP_EOL;
exit(0);
