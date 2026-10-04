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

// Imagenes de noticias: hasta 3 por publicacion. No borra noticias existentes.
$tabla_not = $conexion->query("SHOW TABLES LIKE 'noticias'");
if ($tabla_not && $tabla_not->num_rows > 0) {
    $conexion->query(
        "CREATE TABLE IF NOT EXISTS noticias_imagenes (
            id_imagen INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_noticia INT UNSIGNED NOT NULL,
            archivo VARCHAR(255) NOT NULL,
            orden TINYINT UNSIGNED NOT NULL DEFAULT 1,
            fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_ni_not FOREIGN KEY (id_noticia) REFERENCES noticias(id_noticia) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}
?>
