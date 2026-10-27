<?php
/**
 * Genera un hash bcrypt para crear o restablecer una contraseña.
 *
 * Uso:
 *   php scripts/crear_hash.php "NuevaClave123"
 *
 * Copia el hash generado en:
 *   UPDATE usuarios SET password = '<hash>' WHERE correo = 'correo@ejemplo.com';
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este script solo se puede ejecutar por línea de comandos.\n");
}

$clave = $argv[1] ?? '';
if ($clave === '') {
    fwrite(STDERR, "Uso: php scripts/crear_hash.php \"NuevaClave123\"\n");
    exit(1);
}

$errores = [];
if (strlen($clave) < 8) {
    $errores[] = 'mínimo 8 caracteres';
}
if (!preg_match('/[A-Z]/', $clave)) {
    $errores[] = 'al menos una mayúscula';
}
if (!preg_match('/[0-9]/', $clave)) {
    $errores[] = 'al menos un número';
}
if (!empty($errores)) {
    fwrite(STDERR, 'La contraseña debe tener: ' . implode(', ', $errores) . ".\n");
    exit(1);
}

echo password_hash($clave, PASSWORD_BCRYPT) . PHP_EOL;
