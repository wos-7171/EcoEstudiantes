<?php
date_default_timezone_set("America/Bogota");

$servidor   = "sql310.infinityfree.com";
$usuario    = "if0_43247887"; // Tu usuario de InfinityFree
$contrasena = "HZYa6t7Vrni2"; // La contraseña de tu cuenta de InfinityFree
$basedatos  = "if0_43247887_ecoestudiantes";

$conexion = new mysqli($servidor, $usuario, $contrasena, $basedatos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>