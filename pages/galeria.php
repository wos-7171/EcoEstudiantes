<?php
session_start();
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit();
}
$rol = $_SESSION["rol"];
$prefix = "../";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:w极@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>
<main>
        <div class="container">
            <h2>Galería / Imágenes</h2>

            <section class="gallery">
                <div class="grid gallery-grid">
                    <figure class="gallery-item">
                        <img src="https://www.el19digital.com/files/articulos/251016.jpeg" alt="Estudiantes sembrando un árbol">
                        <!-- Imagen importada automáticamente -->
                        <figcaption>Estudiantes sembrando un árbol </figcaption>
                    </figure>

                    <figure class="gallery-item">
                        <img src="https://colaborativo.net/wp-content/uploads/2018/07/energia-solar.png" alt="Escuela con paneles solares">
                        <!-- Imagen importada automáticamente -->
                        <figcaption>Escuela con paneles solares </figcaption>
                    </figure>

                    <figure class="gallery-item">
                        <img src="https://img.freepik.com/fotos-premium/laptop-grafico_253658-3741.jpg" alt="Laptop con gráficos ambientales">
                        <!-- Imagen importada automáticamente -->
                        <figcaption>Laptop con gráficos ambientales </figcaption>
                    </figure>

                    <figure class="gallery-item">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTbRe-EOvSrxtvN98TK44BTrcP7eGH1YPHZLA&s" alt="Actividades de reciclaje">
                        <!-- Imagen importada automáticamente -->
                        <figcaption>Reciclaje en la escuela </figcaption>
                    </figure>

                    <figure class="gallery-item">
                        <img src="https://chilebio.cl/wp-content/uploads/2021/08/peces1.jpg
                        " alt="Río limpio con peces">
                        <!-- Imagen importada automáticamente -->
                        <figcaption>Río limpio con peces </figcaption>
                    </figure>

                    <figure class="gallery-item">
                        <img src="https://bogota.gov.co/sites/default/files/styles/1050px/public/h1.jpg" alt="Estudiantes en huerta escolar">
                        <!-- Imagen importada automáticamente -->
                        <figcaption>Huerta escolar comunitaria </figcaption>
                    </figure>
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

    <footer>
        <div class="container">
            <p>&copy; 2026 EcoEstudiantes Digitales – Todos los derechos reservados</p>
            <div class="social-links">
                <a href="https://www.facebook.com/profile.php?id=61580327203316" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/facebook.svg" alt="Facebook" class="icon">
                </a>
                <a href="https://www.instagram.com/ecoestudiantes.digitales/" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/instagram.svg" alt="Instagram" class="icon">
                </a>
                <a href="https://x.com/Ecoestudiantes_" aria-label="Twitter" target="_blank" rel="noopener noreferrer">
                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/x.svg" alt="Twitter" class="icon">
                </a>
                <a href="https://www.youtube.com/channel/UCRxzfKVHC0WeMN8AHlrEd0w" aria-label="YouTube" target="_blank" rel="noopener noreferrer">
                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/youtube.svg" alt="YouTube" class="icon">
                </a>
                <a href="https://www.tiktok.com/@ecoestudiantesdig" aria-label="TikTok" target="_blank" rel="noopener noreferrer">
                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/tiktok.svg" alt="TikTok" class="icon">
                </a>
            </div>
        </div>
    </footer>



</body>

</html>