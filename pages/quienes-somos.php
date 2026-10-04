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
    <title>Quiénes Somos - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>
<main>
        <div class="container">
            <h2>Quiénes Somos</h2>

            <section class="section">
                <div class="grid grid-2">
                    <div>
                        <h3>Misión </h3>
                        <p>Desarrollar herramientas digitales que fortalezcan la educación ambiental escolar, facilitando la gestión, seguimiento y evaluación de los Proyectos Ambientales Escolares (PRAE).
                        </p>

                        <h3>Visión </h3>
                        <p>Para el año 2030, ser la plataforma líder en Colombia y Latinoamérica para la gestión y evaluación de proyectos ambientales escolares, promoviendo comunidades educativas sostenibles.
                        </p>
                    </div>
                    <div>
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Equipo de trabajo colaborativo" class="section-image">
                        <!-- Imagen importada automáticamente -->
                    </div>
                </div>
            </section>

            <section class="section">
                <h3>Valores</h3>
                <div class="grid grid-2">
                    <div class="card">
                        <h4>Responsabilidad ambiental </h4>
                        <p>Promovemos el cuidado del medio ambiente en todas nuestras acciones.</p>
                    </div>
                    <div class="card">
                        <h4>Innovación </h4>
                        <p>Buscamos constantemente nuevas soluciones tecnológicas para la educación ambiental.</p>
                    </div>
                    <div class="card">
                        <h4>Colaboración </h4>
                        <p>Trabajamos en equipo con instituciones educativas, estudiantes y comunidades.</p>
                    </div>
                    <div class="card">
                        <h4>Inclusión </h4>
                        <p>Garantizamos que nuestras herramientas sean accesibles para todos.</p>
                    </div>
                </div>
            </section>

            <section class="section">
                <h3>Políticas de calidad </h3>
                <ul class="custom-list">
                    <li>Garantizar herramientas confiables y fáciles de usar.</li>
                    <li>Proteger los datos de los usuarios.</li>
                    <li>Promover la mejora continua.</li>
                    <li>Asegurar accesibilidad e inclusión.</li>
                </ul>
            </section>

            <section class="section">
                <h3>Alcance </h3>
                <p>Dirigido a instituciones educativas colombianas de básica y media, con proyección a nivel regional.
                </p>
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