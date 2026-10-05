<?php
session_start();
include("../conexion.php");
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit();
}
$rol = $_SESSION["rol"];
$prefix = "../";
$fotos = $conexion->query("SELECT titulo, archivo FROM galeria ORDER BY fecha_subida DESC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>
<main>
        <div class="container">
            <h2>Galería / Imágenes</h2>

            <section class="gallery">
                <div class="grid gallery-grid">
                    <?php if ($fotos && $fotos->num_rows > 0): while ($foto = $fotos->fetch_assoc()):
                        $src = preg_match('#^https?://#', $foto['archivo']) ? $foto['archivo'] : '../images/galeria/' . rawurlencode($foto['archivo']);
                    ?>
                    <figure class="gallery-item">
                        <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($foto['titulo']); ?>">
                        <figcaption><?php echo htmlspecialchars($foto['titulo']); ?></figcaption>
                    </figure>
                    <?php endwhile; else: ?>
                    <p>Todavia no hay fotos en la galeria.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="section">
                <h3>Nuestras actividades en imágenes</h3>
                <p>La galería muestra algunas de las actividades que realizan las instituciones educativas que utilizan nuestra plataforma. Desde siembra de árboles hasta implementación de tecnologías sostenibles, cada imagen representa el compromiso con
                    la educación ambiental.</p>

                <div class="grid grid-3">
                    <div class="card">
                        <h4>Educación práctica</h4>
                        <p>Los estudiantes aprenden haciendo, con actividades prácticas que refuerzan los conceptos ambientales.</p>
                    </div>
                    <div class="card">
                        <h4>Tecnología aplicada</h4>
                        <p>Utilización de herramientas digitales para registrar y monitorear las actividades ambientales.</p>
                    </div>
                    <div class="card">
                        <h4>Impacto comunitario</h4>
                        <p>Los proyectos trascienden las aulas y generan beneficios para toda la comunidad.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>
<?php include("../includes/pie.php"); ?>
</body>

</html>