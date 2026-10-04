<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión / Registro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/motion.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
</head>

<body>

    <div class="login-container">
        <div class="login-card">

            <div class="tab-switcher">
                <button class="tab-btn active" id="tabLogin" onclick="mostrarTab('login')">Iniciar Sesión</button>
                <button class="tab-btn" id="tabRegister" onclick="mostrarTab('registro')">Registrarse</button>
            </div>

            <div id="panelLogin">
                <div class="login-header">
                    <h1>Bienvenido</h1>
                    <p>Inicia sesión en tu cuenta</p>
                </div>

                <form id="loginForm" action="login.php" method="POST">

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
                        <a href="#" onclick="mostrarTab('registro'); return false;">Regístrate aquí</a>
                    </p>
                </form>
            </div>

            <div id="panelRegistro" style="display:none;">
                <div class="login-header">
                    <h1>Crear cuenta</h1>
                    <p>Regístrate para comenzar</p>
                </div>

                <form id="registerForm" action="registro_usuario.php" method="POST">

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
                        <label for="regRol">Soy...</label>
                        <select id="regRol" name="rol" required style="width:100%; padding:0.6rem; border-radius:6px; border:1px solid #ccc;">
                            <option value="3">Estudiante</option>
                            <option value="2">Docente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="regPassword">Contraseña</label>
                        <div class="input-wrap">
                            <input type="password" id="regPassword" name="contrasena" placeholder="Mín. 8 caracteres, 1 mayúscula, 1 número" required>
                            <button type="button" id="toggleRegPass">Mostrar</button>
                        </div>
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
                        <a href="#" onclick="mostrarTab('login'); return false;">Inicia sesión</a>
                    </p>
                </form>
            </div>

        </div>

    </div>

    <script src="script.js"></script>
    <script src="js/motion.js"></script>
</body>

</html>
