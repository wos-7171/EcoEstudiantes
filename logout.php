<?php
/**
 * Cierre de sesión.
 * Solo acepta POST con token CSRF para evitar cierres de sesión inducidos por terceros.
 */

require_once __DIR__ . '/includes/funciones.php';
sesion_iniciar();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !csrf_validar()) {
    header('Location: ' . app_url('index.php'));
    exit();
}

sesion_destruir();
header('Location: ' . app_url('index.php'));
exit();
