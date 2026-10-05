<?php
session_start();
include("conexion.php");
include("funciones.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: proyectos.php");
    exit();
}

$id_usuario = (int)$_SESSION["id_usuario"];
$rol = $_SESSION["rol"];
$accion = $_POST['accion'] ?? '';

// Trae una actividad con el responsable de su proyecto
function traer_actividad($conexion, $id_actividad) {
    $stmt = $conexion->prepare(
        "SELECT a.*, p.id_responsable, p.id_institucion FROM actividades a
         INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
         WHERE a.id_actividad = ?"
    );
    $stmt->bind_param("i", $id_actividad);
    $stmt->execute();
    $a = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $a;
}
function puede_administrar($a, $id_usuario, $rol) {
    return $a && ($rol === 'Administrador' || ($rol === 'Docente' && (int)$a['id_responsable'] === (int)$id_usuario));
}

$volver = "proyectos.php";

try {

    // 1. CREAR ACTIVIDAD
    if ($accion === 'crear') {
        $id_proyecto = intval($_POST['id_proyecto'] ?? 0);
        $volver = "actividades.php?id_proyecto=" . $id_proyecto;

        if (!proyecto_gestionable($conexion, $id_proyecto, $id_usuario, $rol)) {
            flash_error("No tienes permiso para crear actividades en este proyecto.");
        } else {
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $fecha_actividad = fecha_permitida($_POST['fecha_actividad'] ?? '');

            if ($nombre === '' || mb_strlen($nombre) > 150) {
                flash_error("El nombre de la actividad es obligatorio (maximo 150 caracteres).");
            } elseif ($fecha_actividad === null) {
                flash_error("La fecha de la actividad es obligatoria y debe tener un ano entre " . fecha_rango_texto() . ".");
            } else {
                $stmt = $conexion->prepare(
                    "INSERT INTO actividades (id_proyecto, nombre, descripcion, fecha_actividad, puntos)
                     VALUES (?, ?, ?, ?, 0)"
                );
                $stmt->bind_param("isss", $id_proyecto, $nombre, $descripcion, $fecha_actividad);
                $stmt->execute();
                $stmt->close();
                flash_ok("Actividad registrada.");
            }
        }
    }

    // 2. EDITAR ACTIVIDAD
    if ($accion === 'editar') {
        $id_actividad = intval($_POST['id_actividad'] ?? 0);
        $a = traer_actividad($conexion, $id_actividad);
        if (!$a) {
            flash_error("La actividad no existe.");
        } elseif (!puede_administrar($a, $id_usuario, $rol)) {
            flash_error("No tienes permiso para editar esta actividad.");
            $volver = "actividades.php?id_proyecto=" . (int)$a['id_proyecto'];
        } else {
            $volver = "actividad_editar.php?id_actividad=" . $id_actividad;
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $fecha_actividad = fecha_permitida($_POST['fecha_actividad'] ?? '');

            if ($nombre === '' || mb_strlen($nombre) > 150) {
                flash_error("El nombre de la actividad es obligatorio (maximo 150 caracteres).");
            } elseif ($fecha_actividad === null) {
                flash_error("La fecha de la actividad es obligatoria y debe tener un ano entre " . fecha_rango_texto() . ".");
            } else {
                $stmt = $conexion->prepare(
                    "UPDATE actividades SET nombre = ?, descripcion = ?, fecha_actividad = ? WHERE id_actividad = ?"
                );
                $stmt->bind_param("sssi", $nombre, $descripcion, $fecha_actividad, $id_actividad);
                $stmt->execute();
                $stmt->close();

                flash_ok("Actividad actualizada.");
                $volver = "actividades.php?id_proyecto=" . (int)$a['id_proyecto'];
            }
        }
    }

    // 3. ELIMINAR ACTIVIDAD
    if ($accion === 'eliminar') {
        $id_actividad = intval($_POST['id_actividad'] ?? 0);
        $a = traer_actividad($conexion, $id_actividad);
        if (!$a) {
            flash_error("La actividad no existe.");
        } else {
            $volver = "actividades.php?id_proyecto=" . (int)$a['id_proyecto'];
            if (puede_administrar($a, $id_usuario, $rol)) {
                eliminar_actividad_completa($conexion, $id_actividad);
                flash_ok("Actividad eliminada junto con sus inscripciones y evidencias.");
            } else {
                flash_error("No tienes permiso para eliminar esta actividad.");
            }
        }
    }

    // 4. CAMBIAR ESTADO DE ACTIVIDAD
    if ($accion === 'cambiar_estado') {
        $id_actividad = intval($_POST['id_actividad'] ?? 0);
        $a = traer_actividad($conexion, $id_actividad);
        if ($a) {
            $volver = "actividades.php?id_proyecto=" . (int)$a['id_proyecto'];
            $estados_validos = ['Programada', 'En ejecución', 'Finalizada', 'Cancelada'];
            $estado = in_array($_POST['estado'] ?? '', $estados_validos, true) ? $_POST['estado'] : 'Programada';
            if (puede_administrar($a, $id_usuario, $rol)) {
                $stmt = $conexion->prepare("UPDATE actividades SET estado = ? WHERE id_actividad = ?");
                $stmt->bind_param("si", $estado, $id_actividad);
                $stmt->execute();
                $stmt->close();
            } else {
                flash_error("No tienes permiso para cambiar el estado de esta actividad.");
            }
        }
    }

    // 5. ESTUDIANTE SE INSCRIBE A UNA ACTIVIDAD
    if ($accion === 'inscribirse') {
        $id_actividad = intval($_POST['id_actividad'] ?? 0);
        $a = traer_actividad($conexion, $id_actividad);
        if ($a) {
            $volver = "actividades.php?id_proyecto=" . (int)$a['id_proyecto'];
            if ($rol !== 'Estudiante') {
                flash_error("Solo los estudiantes pueden inscribirse.");
            } elseif (!in_array($a['estado'], ['Programada', 'En ejecución'], true)) {
                flash_error("Solo puedes inscribirte en actividades programadas o en ejecucion.");
            } else {
                $stmt = $conexion->prepare("SELECT id_institucion FROM usuarios WHERE id_usuario = ?");
                $stmt->bind_param("i", $id_usuario);
                $stmt->execute();
                $yo = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$yo || (int)$yo['id_institucion'] !== (int)$a['id_institucion']) {
                    flash_error("Solo puedes inscribirte en proyectos de tu institucion.");
                } else {
                $check = $conexion->prepare("SELECT id_participante FROM participantes WHERE id_actividad = ? AND id_usuario = ?");
                $check->bind_param("ii", $id_actividad, $id_usuario);
                $check->execute();
                $ya = $check->get_result()->num_rows > 0;
                $check->close();

                if (!$ya) {
                    $stmt = $conexion->prepare(
                        "INSERT INTO participantes (id_actividad, id_usuario, estado, nota) VALUES (?, ?, 'Inscrito', NULL)"
                    );
                    $stmt->bind_param("ii", $id_actividad, $id_usuario);
                    $stmt->execute();
                    $stmt->close();
                    flash_ok("Te inscribiste en la actividad.");
                }
                }
            }
        }
    }

    // 6. ESTUDIANTE CANCELA SU INSCRIPCION (solo si aun no ha sido evaluado ni subio evidencias)
    if ($accion === 'desinscribirse') {
        $id_actividad = intval($_POST['id_actividad'] ?? 0);
        $a = traer_actividad($conexion, $id_actividad);
        if ($a) {
            $volver = "actividades.php?id_proyecto=" . (int)$a['id_proyecto'];
            if ($rol !== 'Estudiante') {
                flash_error("Accion no permitida.");
            } else {
                $stmt = $conexion->prepare("SELECT estado FROM participantes WHERE id_actividad = ? AND id_usuario = ?");
                $stmt->bind_param("ii", $id_actividad, $id_usuario);
                $stmt->execute();
                $pt = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $stmt = $conexion->prepare("SELECT COUNT(*) AS n FROM evidencias WHERE id_actividad = ? AND id_usuario = ?");
                $stmt->bind_param("ii", $id_actividad, $id_usuario);
                $stmt->execute();
                $n_ev = (int)$stmt->get_result()->fetch_assoc()['n'];
                $stmt->close();

                if (!$pt) {
                    flash_error("No estas inscrito en esa actividad.");
                } elseif ($pt['estado'] !== 'Inscrito') {
                    flash_error("Ya fuiste evaluado en esta actividad, no puedes cancelar la inscripcion.");
                } elseif ($n_ev > 0) {
                    flash_error("Ya subiste evidencias; eliminalas primero si quieres cancelar la inscripcion.");
                } else {
                    $stmt = $conexion->prepare("DELETE FROM participantes WHERE id_actividad = ? AND id_usuario = ?");
                    $stmt->bind_param("ii", $id_actividad, $id_usuario);
                    $stmt->execute();
                    $stmt->close();
                    flash_ok("Inscripcion cancelada.");
                }
            }
        }
    }

} catch (Throwable $e) {
    flash_error("Ocurrio un error al procesar la accion. Intenta de nuevo.");
}

$conexion->close();
header("Location: " . $volver);
exit();
