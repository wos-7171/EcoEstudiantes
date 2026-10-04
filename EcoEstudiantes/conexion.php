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

// Migracion segura: agrega la columna nota si todavia no existe y convierte
// las evaluaciones viejas (puntos) a la escala 1.0-5.0. No borra datos.
$tabla_part = $conexion->query("SHOW TABLES LIKE 'participantes'");
if ($tabla_part && $tabla_part->num_rows > 0) {
    $col_nota = $conexion->query("SHOW COLUMNS FROM participantes LIKE 'nota'");
    if ($col_nota && $col_nota->num_rows === 0) {
        $conexion->query("ALTER TABLE participantes ADD COLUMN nota DECIMAL(2,1) NULL");
    }
    $conexion->query(
        "UPDATE participantes pt
         INNER JOIN actividades a ON a.id_actividad = pt.id_actividad
         SET pt.nota = LEAST(5.0, GREATEST(1.0, ROUND((pt.puntos_obtenidos / a.puntos) * 5, 1)))
         WHERE pt.nota IS NULL
           AND pt.estado = 'Participó'
           AND pt.puntos_obtenidos > 0
           AND a.puntos > 0"
    );
    $conexion->query(
        "UPDATE participantes pt
         INNER JOIN actividades a ON a.id_actividad = pt.id_actividad
         SET pt.nota = 5.0
         WHERE pt.nota IS NULL
           AND pt.estado = 'Participó'
           AND pt.puntos_obtenidos > 0
           AND (a.puntos IS NULL OR a.puntos = 0)"
    );
}
?>