<?php
date_default_timezone_set("America/Bogota");
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$basedatos = "ecoestudiantes";

$conexion = new mysqli($servidor, $usuario, $contrasena, $basedatos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>