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
                $hash = password_hash($clave, PASSWORD_BCRYPT);
                $stmt = $conexion->prepare(
                    "INSERT INTO usuarios (id_rol, id_institucion, nombre, apellido, correo, password)
                     VALUES (?, 1, ?, ?, ?, ?)"
                );
                $stmt->bind_param("issss", $id_rol, $nombre, $apellido, $correo, $hash);
                $stmt->execute();
                $stmt->close();
                flash_ok("Usuario creado correctamente.");
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
            } elseif ($clave_nueva !== '' && count(password_errores($clave_nueva)) > 0) {
                flash_error("La nueva contraseña debe tener: " . implode(', ', password_errores($clave_nueva)) . ".");
            } else {
                if ($clave_nueva !== '') {
                    $hash = password_hash($clave_nueva, PASSWORD_BCRYPT);
                    $stmt = $conexion->prepare(
                        "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, id_rol = ?, password = ? WHERE id_usuario = ?"
                    );
                    $stmt->bind_param("sssisi", $nombre, $apellido, $correo, $id_rol, $hash, $id);
                } else {
                    $stmt = $conexion->prepare(
                        "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, id_rol = ? WHERE id_usuario = ?"
                    );
                    $stmt->bind_param("sssii", $nombre, $apellido, $correo, $id_rol, $id);
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
        } else {
            $stmt = $conexion->prepare("INSERT INTO noticias (id_usuario, titulo, contenido) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_yo, $titulo, $contenido);
            $stmt->execute();
            $stmt->close();
            flash_ok("Noticia publicada.");
        }
    }

    // 6. ELIMINAR NOTICIA
    if ($accion === 'eliminar_noticia') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conexion->prepare("DELETE FROM noticias WHERE id_noticia = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash_ok("Noticia eliminada.");
    }

} catch (Throwable $e) {
    flash_error("Ocurrio un error al procesar la accion. Intenta de nuevo.");
}

$conexion->close();
header("Location: admin.php");
exit();
