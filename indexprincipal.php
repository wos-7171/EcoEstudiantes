<?php
require_once __DIR__ . '/includes/bootstrap.php';

$usuario_actual = usuario_verificar_activo($conexion, null, true);
$rol = trim((string)($_SESSION["rol"] ?? '')); // 'Administrador' | 'Docente' | 'Estudiante'

$nombre_sesion = trim((string)($usuario_actual['nombre'] ?? $_SESSION['nombre'] ?? ''));
$partes_nombre = $nombre_sesion !== '' ? preg_split('/\s+/', $nombre_sesion) : [];
$primer_nombre = $partes_nombre[0] ?? '';

$version_styles = (int)@filemtime(__DIR__ . '/assets/css/styles.css');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo $version_styles; ?>">
</head>

<body>
<?php include("incluir_nav.php"); ?>

<main>
    <section class="bienvenida" aria-label="Mensaje de bienvenida">
        <div class="container">
            <div class="bienvenida-card">
                <h1>¡Bienvenido(a), <?php echo htmlspecialchars($primer_nombre !== '' ? $primer_nombre : 'estudiante'); ?>!</h1>
                <p>Nos alegra tenerte de vuelta. Explora los proyectos, actividades y recursos disponibles para tu institución.</p>
                <span class="bienvenida-rol">Sesión iniciada como <?php echo htmlspecialchars($rol !== '' ? $rol : 'usuario'); ?></span>
            </div>
        </div>
    </section>

    <section class="hero">
        <div class="container">
            <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80" alt="Estudiantes trabajando en proyecto ecológico" class="hero-image">
            <div class="hero-content">
                <h2>Bienvenidos a EcoEstudiantes digitales</h2>
                <p>Somos una plataforma dedicada a fortalecer la <span class="highlight">educación ambiental</span> en instituciones educativas mediante herramientas <span class="highlight">tecnológicas</span> innovadoras que facilitan la gestión, seguimiento
                    y evaluación de los Proyectos Ambientales Escolares (PRAE).</p>
                <p>Nuestro enfoque combina <span class="highlight">naturaleza</span>, <span class="highlight">tecnología</span> y <span class="highlight">educación ambiental</span> para crear comunidades educativas sostenibles.</p>
                <a href="pages/servicios.php" class="btn primary">Conoce nuestros servicios</a>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2>Nuestros Pilares Fundamentales</h2>
            <div class="grid grid-3">
                <div class="card">
                    <h3>Sostenibilidad</h3>
                    <p>Promovemos prácticas ambientales responsables en las instituciones educativas.</p>
                </div>
                <div class="card">
                    <h3>Tecnología</h3>
                    <p>Utilizamos herramientas digitales para facilitar la gestión ambiental escolar.</p>
                </div>
                <div class="card">
                    <h3>Educación</h3>
                    <p>Fortalecer la cultura ambiental es el corazón de nuestro proyecto.</p>
                </div>
            </div>
        </div>
    </section>
</main>



<?php include("includes/pie.php"); ?>
</body>

</html>