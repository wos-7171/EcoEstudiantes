<?php
session_start();
include("conexion.php");
include("funciones.php");

// Verificar sesión y rol de administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] !== 'Administrador') {
    header("Location: index.php");
    exit();
}

// Usuarios + nombre de su rol
$res_usuarios = $conexion->query(
    "SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.id_rol, r.nombre AS rol, u.estado
     FROM usuarios u INNER JOIN roles r ON r.id_rol = u.id_rol
     ORDER BY u.id_usuario DESC"
);

// Roles disponibles (para el <select> de creación)
$res_roles = $conexion->query("SELECT id_rol, nombre FROM roles ORDER BY id_rol");

// Noticias + autor + cantidad de imagenes
$res_noticias = $conexion->query(
    "SELECT n.id_noticia, n.titulo, n.fecha_publicacion, u.nombre AS autor,
            (SELECT COUNT(*) FROM noticias_imagenes ni WHERE ni.id_noticia = n.id_noticia) AS num_imagenes
     FROM noticias n LEFT JOIN usuarios u ON u.id_usuario = n.id_usuario
     ORDER BY n.fecha_publicacion DESC"
);

// Proyectos PRAE (resumen)
$res_proyectos = $conexion->query(
    "SELECT p.id_proyecto, p.nombre, p.estado, u.nombre AS responsable,
            (SELECT COUNT(*) FROM actividades a WHERE a.id_proyecto = p.id_proyecto) AS num_actividades
     FROM proyectos_prae p LEFT JOIN usuarios u ON u.id_usuario = p.id_responsable
     ORDER BY p.fecha_creacion DESC"
);

// Mensajes de contacto pendientes
$res_mensajes = $conexion->query(
    "SELECT id_mensaje, nombre, correo, asunto, fecha_envio, estado
     FROM mensajes_contacto ORDER BY fecha_envio DESC LIMIT 10"
);

// Instituciones (para el resumen)
$res_instituciones = $conexion->query("SELECT COUNT(*) AS n FROM instituciones");
$n_instituciones = (int)$res_instituciones->fetch_assoc()['n'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - EcoEstudiantes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/motion.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<div class="container">
    <h1>Panel de Administración - EcoEstudiantes</h1>
    <p>Sesión activa: <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong> (<?php echo htmlspecialchars($_SESSION["rol"]); ?>)
        | <a href="logout.php" style="color: #d32f2f;">Cerrar Sesión</a></p>

    <?php flash_mostrar(); ?>

    <div class="nav-quick">
        <a href="proyectos.php">Proyectos PRAE</a>
        <a href="reportes.php">Reportes</a>
        <a href="mensajes.php">Mensajes</a>
        <a href="instituciones.php">Instituciones</a>
        <a href="perfil.php">Mi perfil</a>
        <a href="indexprincipal.php">Ver sitio</a>
    </div>

    <div class="kpis">
        <div class="kpi"><span class="num"><?php echo $res_usuarios->num_rows; ?></span><span class="lbl">Usuarios</span></div>
        <div class="kpi"><span class="num"><?php echo $res_proyectos->num_rows; ?></span><span class="lbl">Proyectos PRAE</span></div>
        <div class="kpi"><span class="num"><?php echo $n_instituciones; ?></span><span class="lbl">Instituciones</span></div>
        <div class="kpi"><span class="num"><?php echo $res_noticias->num_rows; ?></span><span class="lbl">Noticias</span></div>
    </div>

    <!-- SECCIÓN USUARIOS -->
    <h2>Gestión de Usuarios</h2>

    <h3>Crear Nuevo Usuario</h3>
    <form action="admin_acciones.php" method="POST">
        <input type="hidden" name="accion" value="crear_usuario">
        <input type="text" name="nombre" placeholder="Nombres" maxlength="80" required>
        <input type="text" name="apellido" placeholder="Apellidos" maxlength="80" required>
        <input type="email" name="correo" placeholder="Correo electrónico" maxlength="120" required>
        <input type="password" name="contrasena" placeholder="Contraseña (mín. 8, 1 mayúscula, 1 número)" required>
        <select name="id_rol" required>
            <?php while ($rol_op = $res_roles->fetch_assoc()): ?>
                <option value="<?php echo $rol_op['id_rol']; ?>"><?php echo htmlspecialchars($rol_op['nombre']); ?></option>
            <?php endwhile; $res_roles->data_seek(0); ?>
        </select>
        <button type="submit">Crear Usuario</button>
    </form>

    <h3>Usuarios Registrados</h3>
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php while ($u = $res_usuarios->fetch_assoc()): ?>
            <tr>
                <td><?php echo $u['id_usuario']; ?></td>
                <td><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></td>
                <td><?php echo htmlspecialchars($u['correo']); ?></td>
                <td><strong><?php echo htmlspecialchars($u['rol']); ?></strong></td>
                <td><?php echo $u['estado'] ? 'Activo' : 'Inactivo'; ?></td>
                <td>
                    <?php if ((int)$u['id_usuario'] === (int)$_SESSION['id_usuario']): ?>
                        <em style="color:#666;">(tu cuenta)</em>
                        <a class="link" style="color:#1976d2;" href="perfil.php">Editar</a>
                    <?php else: ?>
                    <a class="link link-edit" href="admin_usuario_editar.php?id=<?php echo (int)$u['id_usuario']; ?>">Editar</a>
                    <form action="admin_acciones.php" method="POST">
                        <input type="hidden" name="accion" value="cambiar_estado_usuario">
                        <input type="hidden" name="id" value="<?php echo (int)$u['id_usuario']; ?>">
                        <button type="submit" class="link link-est"><?php echo $u['estado'] ? 'Desactivar' : 'Activar'; ?></button>
                    </form>
                    <form action="admin_acciones.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario? Se borrarán también sus inscripciones y evidencias.')">
                        <input type="hidden" name="accion" value="eliminar_usuario">
                        <input type="hidden" name="id" value="<?php echo (int)$u['id_usuario']; ?>">
                        <button type="submit" class="link link-del">Eliminar</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- SECCIÓN NOTICIAS -->
    <h2>Gestión de Noticias y Contenido</h2>

    <h3>Publicar Nueva Noticia</h3>
    <form action="admin_acciones.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="crear_noticia">
        <input type="text" name="titulo" placeholder="Título de la noticia" maxlength="200" required>
        <textarea name="contenido" placeholder="Escribe aquí el contenido de la noticia..." rows="5" required></textarea>
        <label for="imagenes">Imagenes de la noticia (opcional, maximo 3)</label>
        <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
        <p class="ayuda">JPG, PNG, WEBP o GIF. Hasta 3 imagenes, de 5 MB cada una. Se muestran completas, sin estirarlas.</p>
        <button type="submit">Publicar Noticia</button>
    </form>
<script src="assets/js/admin.js"></script>

    <h3>Noticias Publicadas</h3>
    <table>
        <thead>
            <tr><th>ID</th><th>Título</th><th>Autor</th><th>Fecha</th><th>Imagenes</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php if ($res_noticias->num_rows === 0): ?>
            <tr><td colspan="6" style="text-align:center; color:#666;">Todavía no hay noticias publicadas.</td></tr>
            <?php else: while ($n = $res_noticias->fetch_assoc()): ?>
            <tr>
                <td><?php echo $n['id_noticia']; ?></td>
                <td><?php echo htmlspecialchars($n['titulo']); ?></td>
                <td><?php echo htmlspecialchars($n['autor'] ?? 'EcoEstudiantes'); ?></td>
                <td><?php echo date("d/m/Y", strtotime($n['fecha_publicacion'])); ?></td>
                <td><?php echo (int)$n['num_imagenes']; ?></td>
                <td>
                    <form action="admin_acciones.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta noticia?')">
                        <input type="hidden" name="accion" value="eliminar_noticia">
                        <input type="hidden" name="id" value="<?php echo (int)$n['id_noticia']; ?>">
                        <button type="submit" class="link link-del">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>

    <!-- SECCIÓN PROYECTOS PRAE -->
    <h2>Proyectos PRAE (resumen)</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Responsable</th><th>Estado</th><th># Actividades</th></tr>
        </thead>
        <tbody>
            <?php if ($res_proyectos->num_rows > 0): while ($p = $res_proyectos->fetch_assoc()): ?>
            <tr>
                <td><?php echo $p['id_proyecto']; ?></td>
                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                <td><?php echo htmlspecialchars($p['responsable'] ?? '(sin responsable)'); ?></td>
                <td><?php echo htmlspecialchars($p['estado']); ?></td>
                <td><?php echo $p['num_actividades']; ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="5" style="text-align:center; color:#666;">Aún no hay proyectos PRAE registrados. <a class="btn-link" href="proyectos.php">Crear el primero</a></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- SECCIÓN MENSAJES DE CONTACTO -->
    <h2>Últimos Mensajes de Contacto <a class="btn-link" href="mensajes.php" style="font-size:0.9rem;">Ver todos</a></h2>
    <table>
        <thead>
            <tr><th>Nombre</th><th>Correo</th><th>Asunto</th><th>Fecha</th><th>Estado</th></tr>
        </thead>
        <tbody>
            <?php if ($res_mensajes->num_rows > 0): while ($m = $res_mensajes->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($m['nombre']); ?></td>
                <td><?php echo htmlspecialchars($m['correo']); ?></td>
                <td><?php echo htmlspecialchars($m['asunto'] ?? '(sin asunto)'); ?></td>
                <td><?php echo date("d/m/Y", strtotime($m['fecha_envio'])); ?></td>
                <td>
                    <?php
                    $clase = $m['estado'] === 'Nuevo' ? 'badge-nuevo' : ($m['estado'] === 'Leído' ? 'badge-leido' : 'badge-respondido');
                    ?>
                    <span class="badge <?php echo $clase; ?>"><?php echo htmlspecialchars($m['estado']); ?></span>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="5" style="text-align:center; color:#666;">No hay mensajes de contacto todavía.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="assets/js/motion.js"></script>
</body>
</html>
