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

// Respuestas de contacto. No borra mensajes ya enviados.
$tabla_msg = $conexion->query("SHOW TABLES LIKE 'mensajes_contacto'");
if ($tabla_msg && $tabla_msg->num_rows > 0) {
    $columnas = [
        "id_usuario" => "ALTER TABLE mensajes_contacto ADD COLUMN id_usuario INT UNSIGNED NULL",
        "respuesta" => "ALTER TABLE mensajes_contacto ADD COLUMN respuesta TEXT NULL",
        "fecha_respuesta" => "ALTER TABLE mensajes_contacto ADD COLUMN fecha_respuesta DATETIME NULL",
        "id_admin" => "ALTER TABLE mensajes_contacto ADD COLUMN id_admin INT UNSIGNED NULL",
    ];
    foreach ($columnas as $nombre => $sql) {
        $col = $conexion->query("SHOW COLUMNS FROM mensajes_contacto LIKE '" . $nombre . "'");
        if ($col && $col->num_rows === 0) {
            $conexion->query($sql);
        }
    }
    $conexion->query(
        "UPDATE mensajes_contacto m
         INNER JOIN usuarios u ON u.correo = m.correo
         SET m.id_usuario = u.id_usuario
         WHERE m.id_usuario IS NULL"
    );
}

$tabla_proy = $conexion->query("SHOW TABLES LIKE 'proyectos_prae'");
if ($tabla_proy && $tabla_proy->num_rows > 0) {
    $col_pei = $conexion->query("SHOW COLUMNS FROM proyectos_prae LIKE 'articulacion_pei'");
    if ($col_pei && $col_pei->num_rows === 0) {
        $conexion->query("ALTER TABLE proyectos_prae ADD COLUMN articulacion_pei VARCHAR(80) NULL");
    }
    $col_impacto = $conexion->query("SHOW COLUMNS FROM proyectos_prae LIKE 'impacto'");
    if ($col_impacto && $col_impacto->num_rows === 0) {
        $conexion->query("ALTER TABLE proyectos_prae ADD COLUMN impacto VARCHAR(20) NULL");
    }
}
$conexion->query(
    "CREATE TABLE IF NOT EXISTS galeria (
        id_foto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(120) NOT NULL,
        archivo VARCHAR(500) NOT NULL,
        fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
$col_archivo = $conexion->query("SHOW COLUMNS FROM galeria LIKE 'archivo'");
if ($col_archivo && ($info = $col_archivo->fetch_assoc()) && strpos($info['Type'], '255') !== false) {
    $conexion->query("ALTER TABLE galeria MODIFY archivo VARCHAR(500) NOT NULL");
}
$fotos_base = [
    ['Estudiantes sembrando un arbol', 'https://www.el19digital.com/files/articulos/251016.jpeg'],
    ['Escuela con paneles solares', 'https://colaborativo.net/wp-content/uploads/2018/07/energia-solar.png'],
    ['Laptop con graficos ambientales', 'https://img.freepik.com/fotos-premium/laptop-grafico_253658-3741.jpg'],
    ['Reciclaje en la escuela', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTbRe-EOvSrxtvN98TK44BTrcP7eGH1YPHZLA&s'],
    ['Rio limpio con peces', 'https://chilebio.cl/wp-content/uploads/2021/08/peces1.jpg'],
    ['Huerta escolar comunitaria', 'https://bogota.gov.co/sites/default/files/styles/1050px/public/h1.jpg'],
];
$stmt_existe = $conexion->prepare("SELECT id_foto FROM galeria WHERE archivo = ?");
$stmt_inserta = $conexion->prepare("INSERT INTO galeria (titulo, archivo) VALUES (?, ?)");
foreach ($fotos_base as $foto) {
    $stmt_existe->bind_param("s", $foto[1]);
    $stmt_existe->execute();
    $ya = $stmt_existe->get_result()->fetch_assoc();
    if (!$ya) {
        $stmt_inserta->bind_param("ss", $foto[0], $foto[1]);
        $stmt_inserta->execute();
    }
}
$stmt_existe->close();
$stmt_inserta->close();
?>