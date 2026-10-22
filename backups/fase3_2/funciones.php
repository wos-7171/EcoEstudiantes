<?php
// Funciones compartidas por varios modulos (mensajes, contrasenas, borrados seguros).
// Se incluye con: include("funciones.php");

// ========== SEGURIDAD DE SESIONES Y PROTECCIÓN CSRF (FASE 3.1) ==========

// Detecta si la conexion actual utiliza HTTPS real o un proxy inverso seguro
function sesion_es_https() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

// Inicia la sesion configurando previamente cookies seguras (HttpOnly, SameSite=Lax, Secure condicionado)
function sesion_iniciar() {
    if (session_status() === PHP_SESSION_NONE) {
        $seguro = sesion_es_https();

        session_set_cookie_params([
            'lifetime' => 0,        // Expira al cerrar el navegador
            'path'     => '/',
            'domain'   => '',
            'secure'   => $seguro,  // Solo se activa en conexiones HTTPS reales
            'httponly' => true,     // Impide acceso a la cookie por JavaScript
            'samesite' => 'Lax'     // Mitiga ataques CSRF entre dominios
        ]);

        session_start();
    }

    if (isset($_SESSION['id_usuario'])) {
        sesion_controlar_inactividad(3600);
    }
}

// Destruye por completo la sesion actual y expira la cookie asociada
function sesion_destruir() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $p["path"] ?? '/',
            $p["domain"] ?? '',
            $p["secure"] ?? false,
            $p["httponly"] ?? true
        );
    }
    session_destroy();
}

// Controla la expiracion por inactividad (60 minutos por defecto).
// Requiere que la sesion ya este activa; si no lo esta, no hay nada que controlar.
function sesion_controlar_inactividad($segundos_max = 3600, $redirigir = true) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return true;
    }

    if (!isset($_SESSION['id_usuario'])) {
        return true;
    }

    $ahora = time();

    if (isset($_SESSION['ultimo_acceso']) && ($ahora - (int)$_SESSION['ultimo_acceso'] > $segundos_max)) {
        sesion_destruir();
        if ($redirigir) {
            sesion_iniciar();
            flash_error('Tu sesión ha expirado por inactividad (60 minutos). Por favor, inicia sesión nuevamente.');
            if (!headers_sent()) {
                header('Location: index.php');
            } else {
                echo "<script>window.location.href='index.php';</script>";
            }
            exit();
        }
        return false;
    }

    $_SESSION['ultimo_acceso'] = $ahora;
    return true;
}

// Genera o devuelve el token CSRF activo de la sesion (no se regenera en peticiones GET).
// Requiere sesion activa.
function csrf_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Genera el campo oculto HTML listo para insertar en formularios
function csrf_campo() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// Valida un token CSRF con comparacion resistente a ataques de temporizacion (hash_equals).
// Requiere sesion activa.
function csrf_validar($token = null) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }

    if ($token === null) {
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        } else {
            return false;
        }
    }

    if (!is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

// Verifica en base de datos si el usuario de la sesion sigue existiendo, activo y con el rol adecuado.
// Requiere sesion activa.
function usuario_verificar_activo($conexion, $rol_esperado = null, $auto_redirigir = false) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }

    if (empty($_SESSION['id_usuario'])) {
        if ($auto_redirigir) {
            flash_error('Debes iniciar sesión para acceder.');
            if (!headers_sent()) {
                header('Location: index.php');
            } else {
                echo "<script>window.location.href='index.php';</script>";
            }
            exit();
        }
        return false;
    }

    $id_usuario = (int)$_SESSION['id_usuario'];

    $stmt = $conexion->prepare(
        "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.id_rol, r.nombre AS rol_nombre, u.estado
         FROM usuarios u
         INNER JOIN roles r ON r.id_rol = u.id_rol
         WHERE u.id_usuario = ?
         LIMIT 1"
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();

    // Cuenta no existente o desactivada
    if (!$usuario || (int)$usuario['estado'] !== 1) {
        sesion_destruir();
        if ($auto_redirigir) {
            sesion_iniciar();
            flash_error('Tu cuenta ha sido desactivada. Contacta al administrador.');
            if (!headers_sent()) {
                header('Location: index.php');
            } else {
                echo "<script>window.location.href='index.php';</script>";
            }
            exit();
        }
        return false;
    }

    // Sincronizar datos de rol y sesion con la base de datos
    $_SESSION['id_rol'] = (int)$usuario['id_rol'];
    $_SESSION['rol']    = $usuario['rol_nombre'];
    $_SESSION['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
    $_SESSION['correo'] = $usuario['correo'];

    // Comprobacion de rol si fue solicitado
    if ($rol_esperado !== null) {
        $autorizado = false;
        if (is_array($rol_esperado)) {
            $autorizado = in_array($usuario['rol_nombre'], $rol_esperado, true);
        } else {
            $autorizado = ($usuario['rol_nombre'] === $rol_esperado);
        }

        if (!$autorizado) {
            if ($auto_redirigir) {
                flash_error('No tienes permisos suficientes para acceder a este recurso.');
                $destino = ($usuario['rol_nombre'] === 'Administrador') ? 'admin.php' : 'indexprincipal.php';
                if (!headers_sent()) {
                    header("Location: $destino");
                } else {
                    echo "<script>window.location.href='$destino';</script>";
                }
                exit();
            }
            return false;
        }
    }

    return $usuario;
}

// ---------- Mensajes de aviso (se guardan en la sesion y se muestran una sola vez) ----------
// Estas funciones requieren que la sesion ya este activa (sesion_iniciar ya fue llamado).
function flash_ok($mensaje) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['flash_ok'] = $mensaje;
    }
}
function flash_error($mensaje) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['flash_error'] = $mensaje;
    }
}

function flash_mostrar() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    if (!empty($_SESSION['flash_ok'])) {
        echo '<div class="aviso aviso-ok">' . htmlspecialchars($_SESSION['flash_ok']) . '</div>';
    }
    if (!empty($_SESSION['flash_error'])) {
        echo '<div class="aviso aviso-error">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
    }
    unset($_SESSION['flash_ok'], $_SESSION['flash_error']);
}

// ---------- Contrasenas: mismas reglas que el formulario de registro ----------
function password_errores($p) {
    $errores = [];
    if (strlen($p) < 8) {
        $errores[] = 'minimo 8 caracteres';
    }
    if (!preg_match('/[A-Z]/', $p)) {
        $errores[] = 'al menos una mayuscula';
    }
    if (!preg_match('/[0-9]/', $p)) {
        $errores[] = 'al menos un numero';
    }
    return $errores;
}

// ---------- Fechas ----------
// Ano minimo aceptado en actividades y proyectos. El maximo es el ano actual + 10.
const FECHA_ANIO_MINIMO = 2015;

function fecha_anio_maximo() {
    return (int)date('Y') + 10;
}

// Devuelve la fecha si tiene formato AAAA-MM-DD valido, null si esta vacia o es invalida
function fecha_valida($f) {
    $f = trim((string)$f);
    if ($f === '') {
        return null;
    }
    $d = DateTime::createFromFormat('Y-m-d', $f);
    return ($d && $d->format('Y-m-d') === $f) ? $f : null;
}

// Igual que fecha_valida, pero ademas exige un ano real (2015 hasta ano actual + 10).
// Devuelve null si esta vacia, mal escrita o fuera de rango.
function fecha_permitida($f) {
    $f = fecha_valida($f);
    if ($f === null) {
        return null;
    }
    $anio = (int)substr($f, 0, 4);
    if ($anio < FECHA_ANIO_MINIMO || $anio > fecha_anio_maximo()) {
        return null;
    }
    return $f;
}

function fecha_rango_texto() {
    return FECHA_ANIO_MINIMO . ' y ' . fecha_anio_maximo();
}

// ---------- Notas (escala colombiana 1.0 a 5.0) ----------
// Devuelve la nota redondeada a 1 decimal, null si viene vacia, false si esta fuera de rango.
function nota_valida($valor) {
    if ($valor === null) {
        return null;
    }
    $valor = trim((string)$valor);
    if ($valor === '') {
        return null;
    }
    $valor = str_replace(',', '.', $valor);
    if (!is_numeric($valor)) {
        return false;
    }
    $nota = round((float)$valor, 1);
    if ($nota < 1.0 || $nota > 5.0) {
        return false;
    }
    return $nota;
}

function formato_nota($nota) {
    if ($nota === null || $nota === '') {
        return 'Sin evaluar aun';
    }
    return number_format((float)$nota, 1, '.', '');
}

// Usa la institucion elegida, o crea una nueva si escribieron un nombre que no existe.
function institucion_resolver($conexion, $id_elegido, $nombre_nuevo) {
    $nombre_nuevo = trim((string)$nombre_nuevo);
    if ($nombre_nuevo !== '') {
        if (mb_strlen($nombre_nuevo) > 150) {
            return 0;
        }
        $stmt = $conexion->prepare("SELECT id_institucion FROM instituciones WHERE nombre = ?");
        $stmt->bind_param("s", $nombre_nuevo);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($fila) {
            return (int)$fila['id_institucion'];
        }
        $stmt = $conexion->prepare("INSERT INTO instituciones (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombre_nuevo);
        $stmt->execute();
        $nuevo = (int)$stmt->insert_id;
        $stmt->close();
        return $nuevo;
    }
    $id_elegido = (int)$id_elegido;
    if ($id_elegido > 0) {
        $stmt = $conexion->prepare("SELECT id_institucion FROM instituciones WHERE id_institucion = ?");
        $stmt->bind_param("i", $id_elegido);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($fila) {
            return (int)$fila['id_institucion'];
        }
    }
    return 0;
}


// ---------- Archivos de evidencia ----------
function borrar_archivo_evidencia($archivo) {
    $nombre = basename((string)$archivo);
    foreach (["/uploads/evidencias/", "/evidencias/"] as $carpeta) {
        $ruta = dirname(__DIR__) . $carpeta . $nombre;
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }
}

// Devuelve la fila del proyecto si el usuario puede gestionarlo (admin o docente responsable)
function proyecto_gestionable($conexion, $id_proyecto, $id_usuario, $rol) {
    $stmt = $conexion->prepare("SELECT * FROM proyectos_prae WHERE id_proyecto = ?");
    $stmt->bind_param("i", $id_proyecto);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$p) {
        return false;
    }
    if ($rol === 'Administrador' || ($rol === 'Docente' && (int)$p['id_responsable'] === (int)$id_usuario)) {
        return $p;
    }
    return false;
}

// ---------- Borrados completos (no dependen de que la base tenga ON DELETE CASCADE) ----------
function eliminar_actividad_completa($conexion, $id_actividad) {
    $archivos = [];
    $stmt = $conexion->prepare("SELECT archivo FROM evidencias WHERE id_actividad = ?");
    $stmt->bind_param("i", $id_actividad);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($f = $r->fetch_assoc()) {
        $archivos[] = $f['archivo'];
    }
    $stmt->close();

    $conexion->begin_transaction();
    try {
        foreach ([
            "DELETE FROM evidencias WHERE id_actividad = ?",
            "DELETE FROM participantes WHERE id_actividad = ?",
            "DELETE FROM actividades WHERE id_actividad = ?",
        ] as $sql) {
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id_actividad);
            $stmt->execute();
            $stmt->close();
        }
        $conexion->commit();
    } catch (Throwable $e) {
        $conexion->rollback();
        throw $e;
    }
    foreach ($archivos as $a) {
        borrar_archivo_evidencia($a);
    }
}

function eliminar_proyecto_completo($conexion, $id_proyecto) {
    $archivos = [];
    $stmt = $conexion->prepare(
        "SELECT e.archivo FROM evidencias e
         INNER JOIN actividades a ON a.id_actividad = e.id_actividad
         WHERE a.id_proyecto = ?"
    );
    $stmt->bind_param("i", $id_proyecto);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($f = $r->fetch_assoc()) {
        $archivos[] = $f['archivo'];
    }
    $stmt->close();

    $conexion->begin_transaction();
    try {
        foreach ([
            "DELETE FROM evidencias WHERE id_actividad IN (SELECT id_actividad FROM actividades WHERE id_proyecto = ?)",
            "DELETE FROM participantes WHERE id_actividad IN (SELECT id_actividad FROM actividades WHERE id_proyecto = ?)",
            "DELETE FROM actividades WHERE id_proyecto = ?",
            "DELETE FROM proyectos_prae WHERE id_proyecto = ?",
        ] as $sql) {
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id_proyecto);
            $stmt->execute();
            $stmt->close();
        }
        $conexion->commit();
    } catch (Throwable $e) {
        $conexion->rollback();
        throw $e;
    }
    foreach ($archivos as $a) {
        borrar_archivo_evidencia($a);
    }
}
