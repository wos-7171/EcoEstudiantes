<?php
session_start();
include("conexion.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$rol = $_SESSION["rol"];

// Solo estudiantes suben o eliminan evidencias (validacion del lado del servidor)
if ($rol !== 'Estudiante' || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: proyectos.php");
    exit();
}

$accion = $_POST['accion'] ?? '';
$id_actividad = intval($_POST['id_actividad'] ?? 0);
$volver = "subir_evidencia.php?id_actividad=" . $id_actividad;

// Carpeta fisica de evidencias (se crea sola si no existe, ya protegida)
$carpeta = __DIR__ . "/evidencias/";
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0755, true);
}
if (!file_exists($carpeta . ".htaccess")) {
    file_put_contents($carpeta . ".htaccess",
        "Options -Indexes -ExecCGI\n" .
        "<FilesMatch \"\\.(php|php[0-9]|phtml|phar|pl|py|cgi|sh|exe|html?|js|svg)$\">\n    Require all denied\n</FilesMatch>\n" .
        "<IfModule mod_php.c>\n    php_flag engine off\n</IfModule>\n" .
        "<IfModule mod_php8.c>\n    php_flag engine off\n</IfModule>\n");
}

// Esta inscrito el estudiante en esta actividad? (y la actividad no esta cancelada)
function inscrito($conexion, $id_actividad, $id_usuario) {
    $stmt = $conexion->prepare(
        "SELECT a.estado FROM participantes pt
         INNER JOIN actividades a ON a.id_actividad = pt.id_actividad
         WHERE pt.id_actividad = ? AND pt.id_usuario = ?"
    );
    $stmt->bind_param("ii", $id_actividad, $id_usuario);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $r;
}

// 1. SUBIR EVIDENCIA
if ($accion === 'subir') {
    $insc = inscrito($conexion, $id_actividad, $id_usuario);

    if (!$insc) {
        $_SESSION['flash_error'] = "Debes estar inscrito en la actividad para subir evidencias.";
        header("Location: mis_actividades.php");
        exit();
    }
    if ($insc['estado'] === 'Cancelada') {
        $_SESSION['flash_error'] = "La actividad fue cancelada, no se pueden subir evidencias.";
        header("Location: " . $volver);
        exit();
    }

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        $codigo = $_FILES['archivo']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($codigo === UPLOAD_ERR_INI_SIZE || $codigo === UPLOAD_ERR_FORM_SIZE) {
            $_SESSION['flash_error'] = "El archivo es demasiado grande (maximo 20 MB).";
        } elseif ($codigo === UPLOAD_ERR_NO_FILE) {
            $_SESSION['flash_error'] = "Debes elegir un archivo.";
        } else {
            $_SESSION['flash_error'] = "No se pudo subir el archivo (codigo $codigo). Intenta de nuevo.";
        }
        header("Location: " . $volver);
        exit();
    }

    $archivo = $_FILES['archivo'];
    $max_bytes = 20 * 1024 * 1024;
    if ($archivo['size'] > $max_bytes || $archivo['size'] <= 0) {
        $_SESSION['flash_error'] = "El archivo debe pesar entre 1 byte y 20 MB.";
        header("Location: " . $volver);
        exit();
    }

    // Lista blanca de extensiones -> tipo (segun el ENUM de la tabla evidencias)
    $tipos = [
        'jpg' => 'Imagen', 'jpeg' => 'Imagen', 'png' => 'Imagen', 'gif' => 'Imagen', 'webp' => 'Imagen',
        'mp4' => 'Video', 'webm' => 'Video', 'mov' => 'Video',
        'pdf' => 'Documento', 'doc' => 'Documento', 'docx' => 'Documento', 'ppt' => 'Documento',
        'pptx' => 'Documento', 'xls' => 'Documento', 'xlsx' => 'Documento', 'txt' => 'Documento',
        'zip' => 'Otro',
    ];
    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!isset($tipos[$ext])) {
        $_SESSION['flash_error'] = "Tipo de archivo no permitido (.$ext).";
        header("Location: " . $volver);
        exit();
    }
    $tipo = $tipos[$ext];

    // Rechaza un script disfrazado (por ejemplo un .php renombrado a .jpg)
    $cabecera = file_get_contents($archivo['tmp_name'], false, null, 0, 800);
    if ($cabecera !== false && preg_match('/<\?(php|=)/i', $cabecera)) {
        $_SESSION['flash_error'] = "El archivo parece un script y fue rechazado.";
        header("Location: " . $volver);
        exit();
    }

    // Para imagenes, comprobar ademas que realmente sean imagenes
    if ($tipo === 'Imagen' && @getimagesize($archivo['tmp_name']) === false) {
        $_SESSION['flash_error'] = "El archivo no es una imagen valida.";
        header("Location: " . $volver);
        exit();
    }

    // Nombre aleatorio y unico: nunca se usa el nombre original
    $nombre_guardado = bin2hex(random_bytes(16)) . "." . $ext;
    if (!move_uploaded_file($archivo['tmp_name'], $carpeta . $nombre_guardado)) {
        $_SESSION['flash_error'] = "No se pudo guardar el archivo en el servidor. Revisa los permisos de la carpeta evidencias/.";
        header("Location: " . $volver);
        exit();
    }

    $descripcion = mb_substr(trim($_POST['descripcion'] ?? ''), 0, 500);

    $stmt = $conexion->prepare(
        "INSERT INTO evidencias (id_actividad, id_usuario, tipo, archivo, descripcion)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("iisss", $id_actividad, $id_usuario, $tipo, $nombre_guardado, $descripcion);
    if ($stmt->execute()) {
        $_SESSION['flash_ok'] = "Evidencia subida correctamente.";
    } else {
        @unlink($carpeta . $nombre_guardado);
        $_SESSION['flash_error'] = "No se pudo registrar la evidencia en la base de datos.";
    }
    $stmt->close();
}

// 2. ELIMINAR EVIDENCIA (solo las propias)
if ($accion === 'eliminar') {
    $id_evidencia = intval($_POST['id_evidencia'] ?? 0);

    $stmt = $conexion->prepare("SELECT archivo FROM evidencias WHERE id_evidencia = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_evidencia, $id_usuario);
    $stmt->execute();
    $ev = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($ev) {
        $stmt = $conexion->prepare("DELETE FROM evidencias WHERE id_evidencia = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id_evidencia, $id_usuario);
        $stmt->execute();
        $stmt->close();
        @unlink($carpeta . basename($ev['archivo']));
        $_SESSION['flash_ok'] = "Evidencia eliminada.";
    } else {
        $_SESSION['flash_error'] = "No se encontro esa evidencia.";
    }
}

$conexion->close();
header("Location: " . $volver);
exit();
?>
