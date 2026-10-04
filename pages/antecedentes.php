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
    <title>Antecedentes - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>
<main>
        <div class="container">
            <h2>Antecedentes: Problema Identificado</h2>

            <section class="section">
                <div class="grid grid-2">
                    <div>
                        <div class="problem-statement">
                            <p><strong>Problema:</strong> En muchas instituciones educativas, los <span class="highlight">Proyectos Ambientales Escolares (PRAE)</span> no se integran de manera efectiva al <span class="highlight">Proyecto Educativo Institucional
                                    (PEI)</span>. Esto limita su <span class="highlight">impacto</span> y <span class="highlight">sostenibilidad</span>, dificultando la formación de una verdadera cultura ambiental.</p>
                        </div>

                        <div class="card">
                            <h3>Consecuencias</h3>
                            <ul>
                                <li>Falta de continuidad en los proyectos ambientales</li>
                                <li>Dificultad para medir el impacto real</li>
                                <li>Poca participación de la comunidad educativa</li>
                                <li>Recursos limitados para implementación</li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <img src="https://previews.123rf.com/images/alfastudio/alfastudio1709/alfastudio170901173/85652941-littering-of-environment-an-empty-of-plastic-drinking-bottle-littering-on-the-road-floor-at-the.jpg" alt="Problemas de integración en educación ambiental"
                            class="section-image">
                        <!-- Imagen importada automáticamente -->
                    </div>
                </div>
            </section>

            <section class="section">
                <h3>Nuestra Solución</h3>
                <div class="grid grid-2">
                    <div class="card">
                        <h4>Integración efectiva</h4>
                        <p>Facilitamos la conexión entre PRAE y PEI mediante herramientas digitales que permiten una planificación coherente y alineada con los objetivos institucionales.</p>
                    </div>
                    <div class="card">
                        <h4>Seguimiento continuo</h4>
                        <p>Ofrecemos sistemas de monitoreo que permiten evaluar el progreso de los proyectos ambientales y su impacto real en la comunidad educativa.</p>
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


</body>

</html>