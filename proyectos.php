<?php
session_start();
include("conexion.php");
include("funciones.php");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit();
}

$rol = $_SESSION["rol"]; // 'Administrador' | 'Docente' | 'Estudiante'
$puede_crear = ($rol === 'Administrador' || $rol === 'Docente');

// Listado de proyectos: Administrador ve todos, Docente ve todos también (para
// poder coordinarse), Estudiante ve todos en modo solo lectura.
$sql = "SELECT p.id_proyecto, p.nombre, p.descripcion, p.objetivo, p.estado,
               p.fecha_inicio, p.fecha_fin, p.id_responsable,
               u.nombre AS resp_nombre, u.apellido AS resp_apellido,
               (SELECT COUNT(*) FROM actividades a WHERE a.id_proyecto = p.id_proyecto) AS num_actividades
        FROM proyectos_prae p
        LEFT JOIN usuarios u ON u.id_usuario = p.id_responsable
        ORDER BY p.fecha_creacion DESC";
$res_proyectos = $conexion->query($sql);
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
    <style>
        .app-section { max-width: 1100px; margin: 30px auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #2e7d32; }
        .topbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .nav-quick a { background: #e8f5e9; color: #1b5e20; padding: 8px 14px; border-radius: 20px; text-decoration: none; font-size: 0.9rem; font-weight: 600; margin-right: 8px; }
        form.crear { background: #f9f9f9; padding: 18px; border-radius: 8px; border: 1px solid #e0e0e0; margin: 20px 0; }
        input[type="text"], input[type="date"], select, textarea {
            width: 100%; padding: 8px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button { background-color: #2e7d32; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-family: inherit; }
        button:hover { background-color: #1b5e20; }
        .grid-proyectos { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-top: 15px; }
        .card-proyecto { border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; background: #fafafa; }
        .card-proyecto h3 { margin: 0 0 8px 0; color: #1b5e20; }
        .estado-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600; color: #fff; }
        .e-planificado { background: #607d8b; }
        .e-ejecucion { background: #1976d2; }
        .e-finalizado { background: #2e7d32; }
        .e-cancelado { background: #d32f2f; }
        .btn-link { display: inline-block; margin-top: 10px; margin-right: 14px; color: #1b5e20; font-weight: 600; text-decoration: none; }
        .meta { font-size: 0.85rem; color: #666; margin: 4px 0; }
        .form-inline { display: inline; margin: 0; }
        button.link-del { background: none; border: none; color: #d32f2f; padding: 0; font-weight: 600; cursor: pointer; font-size: 0.85rem; float: right; font-family: inherit; }
        button.link-del:hover { background: none; text-decoration: underline; }
        .filtros { margin: 14px 0; }
        .filtros a { display: inline-block; padding: 5px 12px; border-radius: 14px; font-size: 0.82rem; text-decoration: none; color: #1b5e20; background: #e8f5e9; margin-right: 6px; }
        .filtros a.activo { background: #2e7d32; color: #fff; }
    </style>
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <h1>Proyectos PRAE</h1>
    <p>Sesión: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong> (<?php echo htmlspecialchars($rol); ?>)</p>
    <?php flash_mostrar(); ?>

    <?php if ($puede_crear): ?>
    <h2>Crear nuevo proyecto</h2>
    <form class="crear" action="proyectos_acciones.php" method="POST">
        <input type="hidden" name="accion" value="crear">
        <input type="text" name="nombre" placeholder="Nombre del proyecto" maxlength="150" required>
        <textarea name="descripcion" placeholder="Descripción" rows="3"></textarea>
        <textarea name="objetivo" placeholder="Objetivo del proyecto" rows="2"></textarea>
        <label style="font-size:0.85rem; color:#555;">Fecha de inicio (ano entre <?php echo FECHA_ANIO_MINIMO; ?> y <?php echo fecha_anio_maximo(); ?>)</label>
        <input type="date" name="fecha_inicio" min="<?php echo FECHA_ANIO_MINIMO; ?>-01-01" max="<?php echo fecha_anio_maximo(); ?>-12-31">
        <label style="font-size:0.85rem; color:#555;">Fecha de fin (estimada)</label>
        <input type="date" name="fecha_fin" min="<?php echo FECHA_ANIO_MINIMO; ?>-01-01" max="<?php echo fecha_anio_maximo(); ?>-12-31">
        <button type="submit">Crear proyecto</button>
    </form>
    <?php endif; ?>

    <h2>Proyectos existentes</h2>
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
<script>
// Filtro de proyectos por estado, sin recargar la página
document.querySelectorAll('.filtros a[data-estado]').forEach(function (enlace) {
    enlace.addEventListener('click', function (ev) {
        ev.preventDefault();
        var estado = enlace.getAttribute('data-estado');
        document.querySelectorAll('.filtros a').forEach(function (a) { a.classList.remove('activo'); });
        enlace.classList.add('activo');
        document.querySelectorAll('#grid-proyectos .card-proyecto').forEach(function (tarjeta) {
            tarjeta.style.display = (tarjeta.getAttribute('data-estado') === estado) ? '' : 'none';
        });
    });
});
document.getElementById('f-todos').addEventListener('click', function (ev) {
    ev.preventDefault();
    document.querySelectorAll('.filtros a').forEach(function (a) { a.classList.remove('activo'); });
    document.getElementById('f-todos').classList.add('activo');
    document.querySelectorAll('#grid-proyectos .card-proyecto').forEach(function (tarjeta) { tarjeta.style.display = ''; });
});
</script>
</body>
</html>
