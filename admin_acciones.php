<?php
session_start();
include("conexion.php");
include("funciones.php");

// Verificacion de seguridad: solo el Administrador, y solo por POST
if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== 'Administrador') {
    header("Location: index.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin.php");
    exit();
}

$id_yo = (int)$_SESSION["id_usuario"];
$accion = $_POST['accion'] ?? '';

try {

    // 1. CREAR USUARIO
    if ($accion === 'crear_usuario') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $clave = $_POST['contrasena'] ?? '';
        $id_rol = intval($_POST['id_rol'] ?? 0);

        $stmt = $conexion->prepare("SELECT id_rol FROM roles WHERE id_rol = ?");
        $stmt->bind_param("i", $id_rol);
        $stmt->execute();
        $rol_ok = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($nombre === '' || $apellido === '' || $correo === '') {
            flash_error("Completa nombre, apellido y correo.");
        } elseif (mb_strlen($nombre) > 80 || mb_strlen($apellido) > 80) {
            flash_error("El nombre o el apellido es demasiado largo.");
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            flash_error("El correo electronico no es valido.");
        } elseif (!$rol_ok) {
            flash_error("El rol elegido no existe.");
        } elseif (count(password_errores($clave)) > 0) {
            flash_error("La contraseña debe tener: " . implode(', ', password_errores($clave)) . ".");
        } else {
            $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $existe = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($existe) {
                flash_error("Ya existe un usuario con el correo $correo.");
            } else {
                $id_institucion = institucion_resolver($conexion, $_POST['id_institucion'] ?? 0, $_POST['institucion_nueva'] ?? '');
                if ($id_institucion === 0) {
                    flash_error("Elige la institucion o escribe el nombre si no aparece.");
                } else {
                $hash = password_hash($clave, PASSWORD_BCRYPT);
                $stmt = $conexion->prepare(
                    "INSERT INTO usuarios (id_rol, id_institucion, nombre, apellido, correo, password)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("iissss", $id_rol, $id_institucion, $nombre, $apellido, $correo, $hash);
                $stmt->execute();
                $stmt->close();
                flash_ok("Usuario creado correctamente.");
                }
            }
        }
    }

    // 2. EDITAR USUARIO (datos y, opcionalmente, nueva contraseña)
    if ($accion === 'editar_usuario') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $id_rol = intval($_POST['id_rol'] ?? 0);
        $clave_nueva = $_POST['contrasena'] ?? '';

        $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $existe_usuario = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        $stmt = $conexion->prepare("SELECT id_rol FROM roles WHERE id_rol = ?");
        $stmt->bind_param("i", $id_rol);
        $stmt->execute();
        $rol_ok = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$existe_usuario) {
            flash_error("Ese usuario no existe.");
        } elseif ($nombre === '' || $apellido === '' || $correo === '') {
            flash_error("Completa nombre, apellido y correo.");
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            flash_error("El correo electronico no es valido.");
        } elseif (!$rol_ok) {
            flash_error("El rol elegido no existe.");
        } elseif ($id === $id_yo && $id_rol !== (int)$_SESSION['id_rol']) {
            flash_error("No puedes cambiar tu propio rol mientras estas en sesion.");
        } else {
            $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario <> ?");
            $stmt->bind_param("si", $correo, $id);
            $stmt->execute();
            $correo_en_uso = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($correo_en_uso) {
                flash_error("Ese correo ya lo usa otro usuario.");
            } else {
                $id_institucion = institucion_resolver($conexion, $_POST['id_institucion'] ?? 0, $_POST['institucion_nueva'] ?? '');
                if ($id_institucion === 0) {
                    flash_error("Elige la institucion o escribe el nombre si no aparece.");
                } elseif ($clave_nueva !== '' && count(password_errores($clave_nueva)) > 0) {
                    flash_error("La nueva contraseña debe tener: " . implode(', ', password_errores($clave_nueva)) . ".");
                } else {
                if ($clave_nueva !== '') {
                    $hash = password_hash($clave_nueva, PASSWORD_BCRYPT);
                    $stmt = $conexion->prepare(
                        "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, id_rol = ?, id_institucion = ?, password = ? WHERE id_usuario = ?"
                    );
                    $stmt->bind_param("sssissi", $nombre, $apellido, $correo, $id_rol, $id_institucion, $hash, $id);
                } else {
                    $stmt = $conexion->prepare(
                        "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, id_rol = ?, id_institucion = ? WHERE id_usuario = ?"
                    );
                    $stmt->bind_param("sssiii", $nombre, $apellido, $correo, $id_rol, $id_institucion, $id);
                }
                $stmt->execute();
                $stmt->close();

                // Si el usuario editado es el que esta en sesion, refresca sus datos de sesion
                if ($id === $id_yo) {
                    $_SESSION['nombre'] = $nombre . ' ' . $apellido;
                    $_SESSION['correo'] = $correo;
                }
                flash_ok("Usuario actualizado correctamente.");
                }
            }
        }
    }

    // 3. ELIMINAR USUARIO
    if ($accion === 'eliminar_usuario') {
        $id = intval($_POST['id'] ?? 0);

        if ($id === $id_yo) {
            flash_error("No puedes eliminar tu propia cuenta mientras estas en sesion.");
        } else {
            // Si es responsable de proyectos, no se elimina (quedarian proyectos sin dueño)
            $stmt = $conexion->prepare("SELECT COUNT(*) AS n FROM proyectos_prae WHERE id_responsable = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $n_proy = (int)$stmt->get_result()->fetch_assoc()['n'];
            $stmt->close();

            if ($n_proy > 0) {
                flash_error("Ese usuario es responsable de $n_proy proyecto(s). Elimina esos proyectos primero, o desactiva al usuario en lugar de eliminarlo.");
            } else {
                $archivos = [];
                $stmt = $conexion->prepare("SELECT archivo FROM evidencias WHERE id_usuario = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $r = $stmt->get_result();
                while ($f = $r->fetch_assoc()) {
                    $archivos[] = $f['archivo'];
                }
                $stmt->close();

                $conexion->begin_transaction();
                try {
                    foreach ([
                        "DELETE FROM evidencias WHERE id_usuario = ?",
                        "DELETE FROM participantes WHERE id_usuario = ?",
                        "DELETE FROM noticias WHERE id_usuario = ?",
                        "DELETE FROM usuarios WHERE id_usuario = ?",
                    ] as $sql) {
                        $stmt = $conexion->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $stmt->close();
                    }
                    $conexion->commit();
                    foreach ($archivos as $a) {
                        borrar_archivo_evidencia($a);
                    }
                    flash_ok("Usuario eliminado.");
                } catch (Throwable $e) {
                    $conexion->rollback();
                    flash_error("No se pudo eliminar: el usuario tiene datos asociados. Mejor desactivalo.");
                }
            }
        }
    }

    // 4. ACTIVAR / DESACTIVAR USUARIO
    if ($accion === 'cambiar_estado_usuario') {
        $id = intval($_POST['id'] ?? 0);
        if ($id === $id_yo) {
            flash_error("No puedes desactivar tu propia cuenta.");
        } else {
            $stmt = $conexion->prepare("UPDATE usuarios SET estado = IF(estado = 1, 0, 1) WHERE id_usuario = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash_ok("Estado del usuario actualizado.");
        }
    }

    // 5. CREAR NOTICIA
    if ($accion === 'crear_noticia') {
        $titulo = trim($_POST['titulo'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        if ($titulo === '' || $contenido === '') {
            flash_error("La noticia necesita titulo y contenido.");
        } elseif (mb_strlen($titulo) > 200) {
            flash_error("El titulo no puede pasar de 200 caracteres.");
        } else {
            $stmt = $conexion->prepare("INSERT INTO noticias (id_usuario, titulo, contenido) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_yo, $titulo, $contenido);
            $stmt->execute();
            $id_noticia = (int)$stmt->insert_id;
            $stmt->close();

            $aviso_img = guardar_imagenes_noticia($conexion, $id_noticia);
            if ($aviso_img === '') {
                flash_ok("Noticia publicada.");
            } else {
                flash_ok("Noticia publicada. " . $aviso_img);
            }
        }
    }

    // 6. ELIMINAR NOTICIA
    if ($accion === 'eliminar_noticia') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conexion->prepare("SELECT archivo FROM noticias_imagenes WHERE id_noticia = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $imgs = $stmt->get_result();
        $carpeta = __DIR__ . "/images/noticias/";
        while ($img = $imgs->fetch_assoc()) {
            $ruta = $carpeta . basename($img['archivo']);
            if (is_file($ruta)) {
                unlink($ruta);
            }
        }
        $stmt->close();
        $stmt = $conexion->prepare("DELETE FROM noticias WHERE id_noticia = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash_ok("Noticia eliminada.");
    }

    if ($accion === 'subir_galeria') {
        $titulo = mb_substr(trim($_POST['titulo'] ?? ''), 0, 120);
        $archivo = $_FILES['foto'] ?? null;
        if ($titulo === '' || !$archivo || ($archivo['error'] ?? 1) !== UPLOAD_ERR_OK) {
            flash_error("Escribe un titulo y elige una imagen.");
        } else {
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
            $info = @getimagesize($archivo['tmp_name']);
            if (!isset($permitidas[$ext]) || $info === false || $archivo['size'] > 5 * 1024 * 1024) {
                flash_error("La foto debe ser JPG, PNG, WEBP o GIF, de maximo 5 MB.");
            } else {
                $carpeta = __DIR__ . "/images/galeria/";
                if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);
                $nombre = bin2hex(random_bytes(16)) . "." . $ext;
                if (move_uploaded_file($archivo['tmp_name'], $carpeta . $nombre)) {
                    $stmt = $conexion->prepare("INSERT INTO galeria (titulo, archivo) VALUES (?, ?)");
                    $stmt->bind_param("ss", $titulo, $nombre);
                    $stmt->execute();
                    $stmt->close();
                    flash_ok("Foto publicada en la galeria.");
                } else {
                    flash_error("No se pudo guardar la foto.");
                }
            }
        }
    }

    if ($accion === 'editar_galeria') {
        $id = intval($_POST['id'] ?? 0);
        $titulo = mb_substr(trim($_POST['titulo'] ?? ''), 0, 120);
        if ($id <= 0 || $titulo === '') {
            flash_error("Escribe el titulo de la foto.");
        } else {
            $stmt = $conexion->prepare("UPDATE galeria SET titulo = ? WHERE id_foto = ?");
            $stmt->bind_param("si", $titulo, $id);
            $stmt->execute();
            $stmt->close();
            $archivo = $_FILES['foto'] ?? null;
            if ($archivo && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
                $info = @getimagesize($archivo['tmp_name']);
                if (!isset($permitidas[$ext]) || $info === false || $archivo['size'] > 5 * 1024 * 1024) {
                    flash_error("El titulo se guardo, pero la nueva foto no es valida.");
                } else {
                    $carpeta = __DIR__ . "/images/galeria/";
                    if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);
                    $nombre = bin2hex(random_bytes(16)) . "." . $ext;
                    if (move_uploaded_file($archivo['tmp_name'], $carpeta . $nombre)) {
                        $stmt = $conexion->prepare("SELECT archivo FROM galeria WHERE id_foto = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $vieja = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                        if ($vieja) {
                            $ruta = $carpeta . basename($vieja['archivo']);
                            if (is_file($ruta)) @unlink($ruta);
                        }
                        $stmt = $conexion->prepare("UPDATE galeria SET archivo = ? WHERE id_foto = ?");
                        $stmt->bind_param("si", $nombre, $id);
                        $stmt->execute();
                        $stmt->close();
                        flash_ok("Foto de la galeria actualizada.");
                    }
                }
            } else {
                flash_ok("Titulo de la foto actualizado.");
            }
        }
    }

    if ($accion === 'eliminar_galeria') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conexion->prepare("SELECT archivo FROM galeria WHERE id_foto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($fila) {
            $ruta = __DIR__ . "/images/galeria/" . basename($fila['archivo']);
            if (!preg_match('#^https?://#', $fila['archivo']) && is_file($ruta)) @unlink($ruta);
            $stmt = $conexion->prepare("DELETE FROM galeria WHERE id_foto = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash_ok("Foto eliminada de la galeria.");
        }
    }

} catch (Throwable $e) {
    flash_error("Ocurrio un error al procesar la accion. Intenta de nuevo.");
}

$conexion->close();
header("Location: admin.php");
exit();

// Guarda como maximo 3 imagenes reales. Devuelve un aviso si alguna se rechazo.
function guardar_imagenes_noticia($conexion, $id_noticia) {
    if ($id_noticia <= 0 || !isset($_FILES['imagenes']) || !is_array($_FILES['imagenes']['name'])) {
        return '';
    }

    $carpeta = __DIR__ . "/images/noticias/";
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
    if (!file_exists($carpeta . "index.html")) {
        file_put_contents($carpeta . "index.html", "");
    }

    $permitidas = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
    $max_bytes = 5 * 1024 * 1024;
    $nombres = $_FILES['imagenes']['name'];
    $total = count($nombres);
    $guardadas = 0;
    $rechazadas = 0;
    $de_mas = 0;

    $stmt = $conexion->prepare("INSERT INTO noticias_imagenes (id_noticia, archivo, orden) VALUES (?, ?, ?)");

    for ($i = 0; $i < $total; $i++) {
        if (($_FILES['imagenes']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($guardadas >= 3) {
            $de_mas++;
            continue;
        }
        $error = $_FILES['imagenes']['error'][$i];
        $tmp = $_FILES['imagenes']['tmp_name'][$i];
        $ext = strtolower(pathinfo($nombres[$i], PATHINFO_EXTENSION));
        $tam = (int)($_FILES['imagenes']['size'][$i] ?? 0);

        if ($error !== UPLOAD_ERR_OK || $tam <= 0 || $tam > $max_bytes || !isset($permitidas[$ext])) {
            $rechazadas++;
            continue;
        }
        $cabecera = file_get_contents($tmp, false, null, 0, 800);
        if ($cabecera !== false && preg_match('/<\?(php|=)/i', $cabecera)) {
            $rechazadas++;
            continue;
        }
        $info = @getimagesize($tmp);
        if ($info === false || ($info['mime'] ?? '') !== $permitidas[$ext]) {
            $rechazadas++;
            continue;
        }

        $nombre_guardado = bin2hex(random_bytes(16)) . "." . $ext;
        if (!move_uploaded_file($tmp, $carpeta . $nombre_guardado)) {
            $rechazadas++;
            continue;
        }
        $orden = $guardadas + 1;
        $stmt->bind_param("isi", $id_noticia, $nombre_guardado, $orden);
        $stmt->execute();
        $guardadas++;
    }
    $stmt->close();

    $partes = [];
    if ($guardadas > 0) {
        $partes[] = $guardadas . " imagen" . ($guardadas === 1 ? "" : "es") . " anexada" . ($guardadas === 1 ? "" : "s");
    }
    if ($de_mas > 0) {
        $partes[] = "solo se permiten 3, las demas no se guardaron";
    }
    if ($rechazadas > 0) {
        $partes[] = $rechazadas . " archivo" . ($rechazadas === 1 ? "" : "s") . " no era una imagen valida o pesaba mas de 5 MB";
    }
    return implode(". ", $partes);
}
