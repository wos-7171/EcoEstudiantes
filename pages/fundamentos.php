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
    <title>Fundamentos - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>
<main>
        <div class="container">
            <h2>Fundamentos: Marco Teórico</h2>

            <section class="section">
                <div class="grid grid-2">
                    <div>
                        <div class="card">
                            <h3>Educación ambiental = herramienta fundamental para el desarrollo sostenible</h3>
                            <p>La educación ambiental es un proceso permanente que busca desarrollar una población consciente y preocupada por el medio ambiente y sus problemas, con conocimientos, actitudes, motivaciones y compromisos para trabajar individual
                                y colectivamente en la búsqueda de soluciones.</p>
                        </div>

                        <div class="card">
                            <h3>Datos relevantes</h3>
                            <ul class="custom-list">
                                <li>En 2023, mas de 250.000 estudiantes participaron en PRAE</li>
                                <li>62% de instituciones reportan dificultades para integrar PRAE al PEI</li>
                                <li>Solo 18% de PRAE tienen indicadores digitales de seguimiento</li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <img src="https://www.educaciontrespuntocero.com/wp-content/uploads/2023/02/benficios-huerto-1.jpg">
                        <!-- Imagen importada automáticamente -->
                    </div>
                </div>
            </section>

            <section class="section">
                <h3>Ejemplos internacionales</h3>
                <div class="grid grid-3">
                    <div class="card">
                        <h4>EcoApp (Colombia) </h4>
                        <p>Plataforma digital para gestión de proyectos ambientales escolares con enfoque en comunidades educativas rurales.</p>
                    </div>
                    <div class="card">
                        <h4>EcoEscuela (Argentina) </h4>
                        <p>Programa de certificación ambiental para centros educativos que integra curriculum y gestión ambiental.
                        </p>
                    </div>
                    <div class="card">
                        <h4>EduAmbiental (México) </h4>
                        <p>Sistema de seguimiento y evaluación de proyectos ambientales con indicadores de impacto comunitario.
                        </p>
                    </div>
                </div>
            </section>

            <section class="section">
                <h3>Bases pedagógicas</h3>
                <div class="grid grid-2">
                    <div class="card">
                        <h4>Aprendizaje significativo</h4>
                        <p>Nuestra metodología se basa en conectar los conocimientos ambientales con las experiencias previas de los estudiantes, facilitando la construcción de nuevos aprendizajes.</p>
                    </div>
                    <div class="card">
                        <h4>Pedagogía crítica</h4>
                        <p>Fomentamos la reflexión sobre las problemáticas ambientales y la búsqueda de soluciones colectivas desde una perspectiva crítica y transformadora.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>
<?php include("../includes/pie.php"); ?>
</body>

</html>