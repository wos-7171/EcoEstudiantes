<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Si ya hay una sesión activa, no tiene sentido mostrar el formulario de ingreso.
if (!empty($_SESSION['id_usuario'])) {
    $usuario_sesion = usuario_verificar_activo($conexion, null, false);
    if ($usuario_sesion) {
        $destino = (($usuario_sesion['rol_nombre'] ?? '') === 'Administrador') ? 'admin.php' : 'indexprincipal.php';
        header('Location: ' . app_url($destino));
        exit();
    }
    // Si la cuenta fue desactivada, la sesión se destruyó: se reinicia para el formulario.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        sesion_iniciar();
    }
}

// Fuerza la actualizacion de CSS/JS cuando cambian los archivos.
$version_style  = (int)@filemtime(__DIR__ . '/assets/css/style.css');
$version_motion = (int)@filemtime(__DIR__ . '/assets/css/motion.css');
$version_script = (int)@filemtime(__DIR__ . '/assets/js/script.js');
$version_js_motion = (int)@filemtime(__DIR__ . '/assets/js/motion.js');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión / Registro</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo $version_style; ?>">
    <link rel="stylesheet" href="assets/css/motion.css?v=<?php echo $version_motion; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
</head>

<body>
<?php
$lista_instituciones = $conexion->query("SELECT id_institucion, nombre FROM instituciones ORDER BY nombre");
?>

    <div class="login-container">
        <div class="login-card">

            <?php flash_mostrar(); ?>

            <!-- Pestañas para alternar entre Login y Registro -->
            <div class="tab-switcher">
                <button class="tab-btn active" id="tabLogin" onclick="mostrarTab('login')">Iniciar Sesión</button>
                <button class="tab-btn" id="tabRegister" onclick="mostrarTab('registro')">Registrarse</button>
            </div>

            <!-- ══════════════════════════════
           FORMULARIO DE INICIO DE SESIÓN
      ══════════════════════════════ -->
            <div id="panelLogin">
                <div class="login-header">
                    <h1>Bienvenido</h1>
                    <p>Inicia sesión en tu cuenta</p>
                </div>

                <form id="loginForm" action="login.php" method="POST">
<?php echo csrf_campo(); ?>

                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="correo" placeholder="tu@correo.com" required>
                        <span class="error-msg" id="emailError"></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrap">
                            <input type="password" id="password" name="contrasena" placeholder="••••••••" required>
                            <button type="button" id="togglePass">Mostrar</button>
                        </div>
                        <span class="error-msg" id="passError"></span>
                    </div>

                    <div class="alert" id="loginAlert"></div>

                    <button type="submit" id="btnLogin">Ingresar</button>

                    <p class="switch-hint">
                        ¿No tienes cuenta?
                        <a href="#" onclick="mostrarTab('registro')">Regístrate aquí</a>
                    </p>
                </form>
            </div>

            <!-- ══════════════════════════════
           FORMULARIO DE REGISTRO
      ══════════════════════════════ -->
            <div id="panelRegistro" style="display:none;">
                <div class="login-header">
                    <h1>Crear cuenta</h1>
                    <p>Regístrate para comenzar</p>
                </div>

                <form id="registerForm" action="registro_usuario.php" method="POST">
<?php echo csrf_campo(); ?>
                    <div class="hp-field" aria-hidden="true" style="position:absolute!important;left:-10000px!important;top:auto!important;width:1px!important;height:1px!important;overflow:hidden!important;">
                        <label for="regSitioWeb">No rellenar</label>
                        <input type="text" id="regSitioWeb" name="sitio_web" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="regNombre">Nombres</label>
                        <input type="text" id="regNombre" name="nombre" placeholder="Juan" required>
                        <span class="error-msg" id="nombreError"></span>
                    </div>

                    <div class="form-group">
                        <label for="regApellido">Apellidos</label>
                        <input type="text" id="regApellido" name="apellido" placeholder="Pérez" required>
                        <span class="error-msg" id="apellidoError"></span>
                    </div>

                    <div class="form-group">
                        <label for="regEmail">Correo electrónico</label>
                        <input type="email" id="regEmail" name="correo" placeholder="tu@correo.com" required>
                        <span class="error-msg" id="regEmailError"></span>
                    </div>

                    <div class="form-group">
                        <label for="regInstitucion">Institucion educativa</label>
                        <select id="regInstitucion" name="id_institucion" class="form-select">
                            <option value="">Elige una de la lista</option>
                            <?php if ($lista_instituciones): while ($inst = $lista_instituciones->fetch_assoc()): ?>
                            <option value="<?php echo (int)$inst['id_institucion']; ?>"><?php echo htmlspecialchars($inst['nombre']); ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                        <input type="text" name="institucion_nueva" maxlength="150" placeholder="Si no esta, escribe el nombre aqui">
                    </div>
                    <div class="form-group">
                        <label for="regRol">Rol</label>
                        <input type="hidden" name="rol" value="3">
                        <input id="regRol" type="text" value="Estudiante" readonly>
                    </div>

                    <div class="form-group">
                        <label for="regPassword">Contraseña</label>
                        <div class="input-wrap">
                            <input type="password" id="regPassword" name="contrasena" placeholder="Mín. 8 caracteres, 1 mayúscula, 1 número" required>
                            <button type="button" id="toggleRegPass">Mostrar</button>
                        </div>
                        <!-- Medidor de fortaleza -->
                        <div id="fortaleza">
                            <div id="barraFuerza"></div>
                        </div>
                        <span id="textoFuerza"></span>
                        <span class="error-msg" id="regPassError"></span>
                    </div>

                    <div class="form-group">
                        <label for="regPasswordConf">Confirmar contraseña</label>
                        <div class="input-wrap">
                            <input type="password" id="regPasswordConf" name="contrasena_conf" placeholder="Repite tu contraseña" required>
                            <button type="button" id="toggleRegPassConf">Mostrar</button>
                        </div>
                        <span class="error-msg" id="regPassConfError"></span>
                    </div>

                    <div class="alert" id="registerAlert"></div>

                    <button type="submit" id="btnRegistro">Crear cuenta</button>

                    <p class="switch-hint">
                        ¿Ya tienes cuenta?
                        <a href="#" onclick="mostrarTab('login')">Inicia sesión</a>
                    </p>
                </form>
            </div>

        </div>

    </div>

    <script src="assets/js/script.js?v=<?php echo $version_script; ?>"></script>
    <script src="assets/js/motion.js?v=<?php echo $version_js_motion; ?>"></script>
</body>

</html>