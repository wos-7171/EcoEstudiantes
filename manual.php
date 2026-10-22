<?php
require_once __DIR__ . '/includes/funciones.php';
sesion_iniciar();
require_once __DIR__ . '/conexion.php';

$usuario_actual = usuario_verificar_activo($conexion, null, true);
$rol = $_SESSION["rol"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de usuario - EcoEstudiantes</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<?php include("incluir_nav.php"); ?>
<div class="app-section">
    <h1>Manual de usuario</h1>
    <p>Guia corta de lo que puede hacer tu rol: <strong><?php echo htmlspecialchars($rol); ?></strong>.</p>

    <div class="card-item">
        <h3>Para todos</h3>
        <p>Entra en Iniciar sesion con tu correo y contrasena. En Mi perfil puedes actualizar tus datos. En Contacto escribes al administrador y en Mis mensajes lees la respuesta. Cerrar sesion esta arriba a la derecha.</p>
    </div>

    <?php if ($rol === "Estudiante"): ?>
    <div class="card-item">
        <h3>Estudiante</h3>
        <p>1. Abre Proyectos PRAE y entra al proyecto de tu curso.</p>
        <p>2. En la actividad, inscribete.</p>
        <p>3. En Mis actividades abre Subir evidencias y adjunta la foto o el archivo.</p>
        <p>4. El docente evalua. La nota va de 1.0 a 5.0 y la ves en la misma actividad.</p>
    </div>
    <?php elseif ($rol === "Docente"): ?>
    <div class="card-item">
        <h3>Docente</h3>
        <p>1. En Proyectos PRAE crea el proyecto y escribe objetivo, fechas y descripcion.</p>
        <p>2. Entra al proyecto y registra las actividades.</p>
        <p>3. En participantes inscribe al grupo o revisa quien ya se inscribio.</p>
        <p>4. Marca si participo y guarda la nota de 1.0 a 5.0. Revisa las evidencias antes de evaluar.</p>
        <p>5. En Reportes mira el avance, la participacion y el promedio.</p>
    </div>
    <?php elseif ($rol === "Administrador"): ?>
    <div class="card-item">
        <h3>Administrador</h3>
        <p>1. En Panel Admin crea usuarios, publica noticias y revisa el resumen.</p>
        <p>2. En Instituciones registra el colegio.</p>
        <p>3. En Mensajes respondes los mensajes de contacto. El usuario los ve en Mis mensajes.</p>
        <p>4. Puedes crear proyectos, actividades y ver Reportes de toda la institucion.</p>
    </div>
    <?php endif; ?>
</div>
<?php include("includes/pie.php"); ?>
</body>
</html>
