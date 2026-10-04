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
    <title>Recursos y normativa - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/app.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>
<main>
    <div class="container">
        <h2>Recursos y normativa PRAE</h2>

        <section class="section">
            <h3>Que es un PRAE?</h3>
            <p>Los Proyectos Ambientales Escolares (PRAE) integran la dimension ambiental en los procesos pedagogicos de las instituciones educativas, mediante estrategias orientadas al analisis y a la solucion de problematicas del entorno.</p>
        </section>

        <section class="section">
            <h3>Marco normativo</h3>
            <div class="card">
                <h3>Ley 1549 de 2012</h3>
                <p>Fortalece la institucionalizacion de la Politica Nacional de Educacion Ambiental y su incorporacion efectiva en el desarrollo territorial. Es el respaldo legal principal de los PRAE.</p>
                <a href="https://www.funcionpublica.gov.co/eva/gestornormativo/norma.php?i=48262" target="_blank" rel="noopener noreferrer" class="btn primary">Consultar la ley</a>
            </div>
        </section>

        <section class="section">
            <h3>Articulacion PRAE - PEI</h3>
            <p>Los PRAE deben articularse con el Proyecto Educativo Institucional (PEI), de modo que las acciones ambientales formen parte de la planeacion y del curriculo escolar y no se desarrollen de forma aislada. EcoEstudiantes Digitales apoya esa articulacion al permitir registrar, hacer seguimiento y evaluar cada actividad, y al generar reportes de avance e impacto que la institucion puede usar para tomar decisiones.</p>
        </section>

        <section class="section">
            <h3>Como te ayuda la plataforma en cada etapa</h3>
            <div class="grid grid-3">
                <div class="card">
                    <h3>Planificacion</h3>
                    <p>Docentes y administradores crean proyectos PRAE y sus actividades, con fechas de inicio y de realizacion.</p>
                </div>
                <div class="card">
                    <h3>Ejecucion y seguimiento</h3>
                    <p>Los estudiantes se inscriben, participan y suben evidencias (fotos, videos y documentos).</p>
                </div>
                <div class="card">
                    <h3>Evaluacion</h3>
                    <p>El docente revisa las evidencias, evalua la participacion y asigna una nota de 1.0 a 5.0. Los reportes muestran el impacto.</p>
                </div>
            </div>
        </section>

        <section class="section">
            <h3>Recursos de consulta</h3>
            <div class="grid grid-2">
                <div class="card">
                    <h3>Ministerio de Ambiente y Desarrollo Sostenible</h3>
                    <p>Colombia avanza en materia de educacion ambiental: una politica renovada y transformadora (2025).</p>
                    <a href="https://www.minambiente.gov.co/colombia-avanza-en-materia-de-educacion-ambiental-una-politica-renovada-y-transformadora/" target="_blank" rel="noopener noreferrer" class="btn primary">Leer</a>
                </div>
                <div class="card">
                    <h3>EduAmbiental - Alcaldia de Medellin</h3>
                    <p>La educacion ambiental, un proceso para la formacion de ciudadania (Secretaria de Medio Ambiente de Medellin).</p>
                    <a href="https://eduambientalmed.wixsite.com/eduambiental" target="_blank" rel="noopener noreferrer" class="btn primary">Visitar</a>
                </div>
                <div class="card">
                    <h3>Observatorio Mesoamericano de Educacion Ambiental</h3>
                    <p>Iniciativa creada en 2025 para fortalecer la educacion ambiental (Proyecto Mesoamerica).</p>
                    <a href="https://proyectomesoamerica.org/index.php/noticias/20-noticiaspm/articulos-noticias/1063-crean-observatorio-mesoamericano-de-educacion-ambiental" target="_blank" rel="noopener noreferrer" class="btn primary">Leer</a>
                </div>
            </div>
        </section>
    </div>
</main>

<footer>
    <div class="container">
        <p>&copy; 2026 EcoEstudiantes Digitales - Todos los derechos reservados</p>
    </div>
</footer>
</body>

</html>
