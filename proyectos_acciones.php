<?php
session_start();
include("conexion.php");
include("funciones.php");

if (!isset($_SESSION["id_usuario"]) || !in_array($_SESSION["rol"], ["Administrador", "Docente"])) {
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
$volver = "proyectos.php";

try {

    // 1. CREAR PROYECTO
    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $objetivo = trim($_POST['objetivo'] ?? '');
        $pei = trim($_POST['articulacion_pei'] ?? '');
        $impacto = trim($_POST['impacto'] ?? '');
        $fecha_inicio = fecha_permitida($_POST['fecha_inicio'] ?? '');
        $fecha_fin = fecha_permitida($_POST['fecha_fin'] ?? '');
        $id_institucion = 1; // unica institucion registrada por ahora

        if ($nombre === '' || mb_strlen($nombre) > 150) {
            flash_error("El nombre del proyecto es obligatorio (maximo 150 caracteres).");
        } elseif (trim((string)($_POST['fecha_inicio'] ?? '')) !== '' && $fecha_inicio === null) {
            flash_error("La fecha de inicio no es valida. Usa un ano entre " . fecha_rango_texto() . ".");
        } elseif (trim((string)($_POST['fecha_fin'] ?? '')) !== '' && $fecha_fin === null) {
            flash_error("La fecha de fin no es valida. Usa un ano entre " . fecha_rango_texto() . ".");
        } elseif ($fecha_inicio && $fecha_fin && $fecha_fin < $fecha_inicio) {
            flash_error("La fecha de fin no puede ser anterior a la fecha de inicio.");
        } else {
            $inicio_sql = $fecha_inicio ?? '';
            $fin_sql = $fecha_fin ?? '';
            $stmt = $conexion->prepare(
                "INSERT INTO proyectos_prae (id_institucion, id_responsable, nombre, descripcion, objetivo, articulacion_pei, impacto, fecha_inicio, fecha_fin)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''))"
            );
            $stmt->bind_param("iisssssss", $id_institucion, $id_usuario, $nombre, $descripcion, $objetivo, $pei, $impacto, $inicio_sql, $fin_sql);
            $stmt->execute();
            $stmt->close();
            flash_ok("Proyecto creado.");
        }
    }

    // 2. EDITAR PROYECTO (solo el responsable o un administrador)
    if ($accion === 'editar') {
        $id = intval($_POST['id_proyecto'] ?? 0);
        $volver = "proyecto_editar.php?id_proyecto=" . $id;

        if (!proyecto_gestionable($conexion, $id, $id_usuario, $rol)) {
            flash_error("No tienes permiso para editar este proyecto.");
            $volver = "proyectos.php";
        } else {
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $objetivo = trim($_POST['objetivo'] ?? '');
            $pei = trim($_POST['articulacion_pei'] ?? '');
            $impacto = trim($_POST['impacto'] ?? '');
            $fecha_inicio = fecha_permitida($_POST['fecha_inicio'] ?? '');
            $fecha_fin = fecha_permitida($_POST['fecha_fin'] ?? '');

            if ($nombre === '' || mb_strlen($nombre) > 150) {
                flash_error("El nombre del proyecto es obligatorio (maximo 150 caracteres).");
            } elseif (trim((string)($_POST['fecha_inicio'] ?? '')) !== '' && $fecha_inicio === null) {
                flash_error("La fecha de inicio no es valida. Usa un ano entre " . fecha_rango_texto() . ".");
            } elseif (trim((string)($_POST['fecha_fin'] ?? '')) !== '' && $fecha_fin === null) {
                flash_error("La fecha de fin no es valida. Usa un ano entre " . fecha_rango_texto() . ".");
            } elseif ($fecha_inicio && $fecha_fin && $fecha_fin < $fecha_inicio) {
                flash_error("La fecha de fin no puede ser anterior a la fecha de inicio.");
            } else {
                $inicio_sql = $fecha_inicio ?? '';
                $fin_sql = $fecha_fin ?? '';
                $stmt = $conexion->prepare(
                    "UPDATE proyectos_prae SET nombre = ?, descripcion = ?, objetivo = ?, articulacion_pei = ?, impacto = ?, fecha_inicio = NULLIF(?, ''), fecha_fin = NULLIF(?, '')
                     WHERE id_proyecto = ?"
                );
                $stmt->bind_param("sssssssi", $nombre, $descripcion, $objetivo, $pei, $impacto, $inicio_sql, $fin_sql, $id);
                $stmt->execute();
                $stmt->close();
                flash_ok("Proyecto actualizado.");
                $volver = "proyectos.php";
            }
        }
    }

    // 3. ELIMINAR PROYECTO (solo el responsable o un administrador)
    if ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        if (proyecto_gestionable($conexion, $id, $id_usuario, $rol)) {
            eliminar_proyecto_completo($conexion, $id);
            flash_ok("Proyecto eliminado junto con sus actividades, inscripciones y evidencias.");
        } else {
            flash_error("No tienes permiso para eliminar este proyecto.");
        }
    }

    // 4. CAMBIAR ESTADO
    if ($accion === 'cambiar_estado') {
        $id = intval($_POST['id_proyecto'] ?? 0);
        $estados_validos = ['Planificado', 'En ejecución', 'Finalizado', 'Cancelado'];
        $estado = in_array($_POST['estado'] ?? '', $estados_validos, true) ? $_POST['estado'] : 'Planificado';

        if (proyecto_gestionable($conexion, $id, $id_usuario, $rol)) {
            $stmt = $conexion->prepare("UPDATE proyectos_prae SET estado = ? WHERE id_proyecto = ?");
            $stmt->bind_param("si", $estado, $id);
            $stmt->execute();
            $stmt->close();
            if ($estado === 'Finalizado') {
                $stmt = $conexion->prepare(
                    "UPDATE actividades SET estado = 'Finalizada'
                     WHERE id_proyecto = ? AND estado <> 'Cancelada'"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            flash_error("No tienes permiso para cambiar el estado de este proyecto.");
        }
    }

} catch (Throwable $e) {
    flash_error("Ocurrio un error al procesar la accion. Intenta de nuevo.");
}

$conexion->close();
header("Location: " . $volver);
exit();
