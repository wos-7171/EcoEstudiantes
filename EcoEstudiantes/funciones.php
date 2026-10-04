<?php
// Funciones compartidas por varios modulos (mensajes, contrasenas, borrados seguros).
// Se incluye con: include("funciones.php");

// ---------- Mensajes de aviso (se guardan en la sesion y se muestran una sola vez) ----------
function flash_ok($mensaje) {
    $_SESSION['flash_ok'] = $mensaje;
}
function flash_error($mensaje) {
    $_SESSION['flash_error'] = $mensaje;
}

function flash_mostrar() {
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


// ---------- Archivos de evidencia ----------
function borrar_archivo_evidencia($archivo) {
    $ruta = __DIR__ . "/evidencias/" . basename((string)$archivo);
    if (is_file($ruta)) {
        @unlink($ruta);
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
