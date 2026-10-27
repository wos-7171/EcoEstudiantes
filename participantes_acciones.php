<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ' . app_url('proyectos.php'));
    exit();
}
$usuario_actual = requiere_usuario($conexion, ['Administrador', 'Docente'], 'proyectos.php');
csrf_exigir();

$id_usuario = (int)$_SESSION["id_usuario"];
$rol = $_SESSION["rol"];

$accion = $_POST['accion'] ?? '';
$id_actividad = intval($_POST['id_actividad'] ?? 0);
$volver = "participantes.php?id_actividad=" . $id_actividad;

// EVALUAR PARTICIPANTE (nota de 1.0 a 5.0, solo si participo)
if ($accion === 'evaluar') {
    $id_participante = intval($_POST['id_participante'] ?? 0);

    $stmt = $conexion->prepare(
        "SELECT pt.id_participante, a.id_actividad, p.id_responsable
         FROM participantes pt
         INNER JOIN actividades a ON a.id_actividad = pt.id_actividad
         INNER JOIN proyectos_prae p ON p.id_proyecto = a.id_proyecto
         WHERE pt.id_participante = ?"
    );
    $stmt->bind_param("i", $id_participante);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$info) {
        $_SESSION['flash_error'] = "No se encontro al participante.";
    } elseif ($rol === 'Docente' && (int)$info['id_responsable'] !== (int)$id_usuario) {
        $_SESSION['flash_error'] = "No tienes permiso para evaluar en este proyecto.";
    } else {
        $estados_validos = ['Inscrito', 'Participó', 'No participó'];
        $estado = in_array($_POST['estado'] ?? '', $estados_validos, true) ? $_POST['estado'] : 'Inscrito';
        $nota = null;

        if ($estado === 'Participó') {
            $nota = nota_valida($_POST['nota'] ?? '');
            if ($nota === null || $nota === false) {
                $_SESSION['flash_error'] = "Si el estudiante participo, la nota debe estar entre 1.0 y 5.0.";
                $conexion->close();
                header("Location: participantes.php?id_actividad=" . (int)$info['id_actividad']);
                exit();
            }
        }

        if ($nota === null) {
            $stmt = $conexion->prepare("UPDATE participantes SET estado = ?, nota = NULL WHERE id_participante = ?");
            $stmt->bind_param("si", $estado, $id_participante);
        } else {
            $stmt = $conexion->prepare("UPDATE participantes SET estado = ?, nota = ? WHERE id_participante = ?");
            $stmt->bind_param("sdi", $estado, $nota, $id_participante);
        }
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash_ok'] = "Evaluacion guardada.";
        $volver = "participantes.php?id_actividad=" . (int)$info['id_actividad'];
    }
}

$conexion->close();
header("Location: " . $volver);
exit();
?>
