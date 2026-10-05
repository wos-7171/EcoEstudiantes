<?php
session_start();
include("conexion.php");
include("funciones.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit();
}

$rol = $_SESSION["rol"]; // 'Administrador' | 'Docente' | 'Estudiante'
$id_usuario = (int)$_SESSION["id_usuario"];
$puede_crear = ($rol === 'Administrador' || $rol === 'Docente');
$buscar = trim($_GET['q'] ?? '');
$like = '%' . $buscar . '%';

// El administrador ve todo. Docente y estudiante solo ven proyectos de su institucion.
// El docente no se inscribe: solo consulta los de otros docentes de su colegio.
if ($rol === 'Administrador') {
    $sql = "SELECT p.id_proyecto, p.nombre, p.descripcion, p.objetivo, p.estado, p.articulacion_pei, p.impacto,
                   p.fecha_inicio, p.fecha_fin, p.id_responsable, p.id_institucion,
                   u.nombre AS resp_nombre, u.apellido AS resp_apellido,
                   (SELECT COUNT(*) FROM actividades a WHERE a.id_proyecto = p.id_proyecto) AS num_actividades
            FROM proyectos_prae p
            LEFT JOIN usuarios u ON u.id_usuario = p.id_responsable
            WHERE (? = '' OR p.nombre LIKE ? OR p.descripcion LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ?)
            ORDER BY p.fecha_creacion DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssss", $buscar, $like, $like, $like, $like);
    $stmt->execute();
    $res_proyectos = $stmt->get_result();
} else {
    $sql = "SELECT p.id_proyecto, p.nombre, p.descripcion, p.objetivo, p.estado, p.articulacion_pei, p.impacto,
                   p.fecha_inicio, p.fecha_fin, p.id_responsable, p.id_institucion,
                   u.nombre AS resp_nombre, u.apellido AS resp_apellido,
                   (SELECT COUNT(*) FROM actividades a WHERE a.id_proyecto = p.id_proyecto) AS num_actividades
            FROM proyectos_prae p
            LEFT JOIN usuarios u ON u.id_usuario = p.id_responsable
            WHERE p.id_institucion = (SELECT id_institucion FROM usuarios WHERE id_usuario = ?)
              AND (? = '' OR p.nombre LIKE ? OR p.descripcion LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ?)
            ORDER BY p.fecha_creacion DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("isssss", $id_usuario, $buscar, $like, $like, $like, $like);
    $stmt->execute();
    $res_proyectos = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos PRAE - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/proyectos.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <h1>Proyectos PRAE</h1>
    <p>Sesión: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong> (<?php echo htmlspecialchars($rol); ?>)</p>
    <?php if ($rol !== 'Administrador'): ?>
    <p class="meta">Solo ves los proyectos de tu institucion. El docente no se inscribe: gestiona los suyos y puede consultar los de otros docentes del mismo colegio.</p>
    <?php endif; ?>
    <?php flash_mostrar(); ?>

    <?php if ($puede_crear): ?>
    <h2>Crear nuevo proyecto</h2>
    <form class="crear" action="proyectos_acciones.php" method="POST">
        <input type="hidden" name="accion" value="crear">
        <input type="text" name="nombre" placeholder="Nombre del proyecto" maxlength="150" required>
        <textarea name="descripcion" placeholder="Descripción" rows="3"></textarea>
        <textarea name="objetivo" placeholder="Objetivo del proyecto" rows="2"></textarea>
        <label style="font-size:0.85rem; color:#555;">Articulacion con el PEI</label>
        <select name="articulacion_pei" required>
            <option value="">Selecciona el componente</option>
            <option value="Gestion academica">Gestion academica</option>
            <option value="Gestion de comunidad">Gestion de comunidad</option>
            <option value="Gestion administrativa">Gestion administrativa</option>
            <option value="Proyecto transversal">Proyecto transversal</option>
        </select>
        <label style="font-size:0.85rem; color:#555;">Impacto ambiental esperado</label>
        <select name="impacto" required>
            <option value="">Selecciona el impacto</option>
            <option value="Bajo">Bajo</option>
            <option value="Medio">Medio</option>
            <option value="Alto">Alto</option>
        </select>
        <label style="font-size:0.85rem; color:#555;">Fecha de inicio (ano entre <?php echo FECHA_ANIO_MINIMO; ?> y <?php echo fecha_anio_maximo(); ?>)</label>
        <input type="date" name="fecha_inicio" min="<?php echo FECHA_ANIO_MINIMO; ?>-01-01" max="<?php echo fecha_anio_maximo(); ?>-12-31">
        <label style="font-size:0.85rem; color:#555;">Fecha de fin (estimada)</label>
        <input type="date" name="fecha_fin" min="<?php echo FECHA_ANIO_MINIMO; ?>-01-01" max="<?php echo fecha_anio_maximo(); ?>-12-31">
        <button type="submit">Crear proyecto</button>
    </form>
    <?php endif; ?>

    <h2>Proyectos existentes</h2>
    <form method="GET" action="proyectos.php" class="crear" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <input type="text" name="q" maxlength="80" value="<?php echo htmlspecialchars($buscar); ?>" placeholder="Buscar por nombre, descripcion o docente" style="margin:0; max-width:360px;">
        <button type="submit">Buscar</button>
        <?php if ($buscar !== ''): ?>
        <a href="proyectos.php">Quitar busqueda</a>
        <?php endif; ?>
    </form>
    <div class="filtros">
        <a href="proyectos.php" class="activo" id="f-todos">Todos</a>
        <a href="#" data-estado="Planificado">Planificado</a>
        <a href="#" data-estado="En ejecución">En ejecución</a>
        <a href="#" data-estado="Finalizado">Finalizado</a>
        <a href="#" data-estado="Cancelado">Cancelado</a>
    </div>
    <div class="grid-proyectos" id="grid-proyectos">
        <?php if ($res_proyectos->num_rows === 0): ?>
            <p style="color:#666;">Todavía no hay proyectos PRAE registrados.</p>
        <?php else: while ($p = $res_proyectos->fetch_assoc()):
            $clase_estado = [
                'Planificado'   => 'e-planificado',
                'En ejecución'  => 'e-ejecucion',
                'Finalizado'    => 'e-finalizado',
                'Cancelado'     => 'e-cancelado',
            ][$p['estado']] ?? 'e-planificado';
            $es_dueno = ($p['id_responsable'] == $_SESSION['id_usuario']);
        ?>
        <div class="card-proyecto" data-estado="<?php echo htmlspecialchars($p['estado']); ?>">
            <?php if ($rol === 'Administrador' || $es_dueno): ?>
                <form action="proyectos_acciones.php" method="POST" class="form-inline"
                      onsubmit="return confirm('¿Eliminar este proyecto? También se borrarán sus actividades, inscripciones y evidencias.')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?php echo (int)$p['id_proyecto']; ?>">
                    <button type="submit" class="link-del" title="Eliminar proyecto">Eliminar</button>
                </form>
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
            <span class="estado-badge <?php echo $clase_estado; ?>"><?php echo htmlspecialchars($p['estado']); ?></span>
            <p class="meta">Responsable: <?php echo htmlspecialchars(trim(($p['resp_nombre'] ?? '(sin responsable)') . ' ' . ($p['resp_apellido'] ?? ''))); ?></p>
            <p class="meta">PEI: <?php echo htmlspecialchars($p['articulacion_pei'] ?: 'Sin articular'); ?></p>
            <p class="meta">Impacto: <?php echo htmlspecialchars($p['impacto'] ?: 'Sin registrar'); ?></p>
            <p class="meta">Actividades: <?php echo $p['num_actividades']; ?></p>
            <?php if ($p['fecha_inicio']): ?>
                <p class="meta"><?php echo date("d/m/Y", strtotime($p['fecha_inicio'])); ?>
                    <?php if ($p['fecha_fin']) echo ' al ' . date("d/m/Y", strtotime($p['fecha_fin'])); ?></p>
            <?php endif; ?>
            <?php if ($p['descripcion']): ?><p><?php echo nl2br(htmlspecialchars($p['descripcion'])); ?></p><?php endif; ?>

            <?php if ($rol === 'Administrador' || $es_dueno): ?>
            <form action="proyectos_acciones.php" method="POST" style="margin-top:10px;">
                <input type="hidden" name="accion" value="cambiar_estado">
                <input type="hidden" name="id_proyecto" value="<?php echo $p['id_proyecto']; ?>">
                <select name="estado" onchange="this.form.submit()">
                    <?php foreach (['Planificado','En ejecución','Finalizado','Cancelado'] as $op): ?>
                        <option value="<?php echo $op; ?>" <?php echo $op === $p['estado'] ? 'selected' : ''; ?>><?php echo $op; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php endif; ?>

            <a class="btn-link" href="actividades.php?id_proyecto=<?php echo $p['id_proyecto']; ?>">Ver actividades</a>
            <?php if ($rol === 'Administrador' || $es_dueno): ?>
                <a class="btn-link" href="proyecto_editar.php?id_proyecto=<?php echo $p['id_proyecto']; ?>">Editar</a>
            <?php endif; ?>
        </div>
        <?php endwhile; endif; ?>
    </div>
</div>
<script src="assets/js/proyectos.js"></script>
<?php include("includes/pie.php"); ?>
</body>
</html>
