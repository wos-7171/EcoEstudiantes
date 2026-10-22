<?php
// Cierra la sesion de verdad: borra los datos, la cookie y destruye la sesion.
require_once __DIR__ . '/includes/funciones.php';
sesion_destruir();
header("Location: index.php");
exit();
?>
