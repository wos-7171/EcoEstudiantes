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
    <title>Servicios - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>
<main>
        <div class="container">
            <h2>Servicios o Productos</h2>

            <section class="section">
                <div class="grid grid-2">
                    <div>
                        <div class="service-card">
                            <h3>1. Plataforma Digital PRAE</h3>
                            <p>Un sistema web donde docentes y estudiantes pueden <span class="highlight">registrar</span>, <span class="highlight">monitorear</span> y
                                <span class="highlight">evaluar</span> actividades ambientales.</p>
                            <div class="service-icon"> </div>
                        </div>

                        <div class="service-features">
                            <h4>Características principales:</h4>
                            <ul class="custom-list">
                                <li>Registro de actividades ambientales</li>
                                <li>Seguimiento del estado del proyecto al consultar la pagina</li>
                                <li>Mensajes de contacto con respuesta del administrador</li>
                                <li>Almacenamiento de proyectos, notas y evidencias</li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <img src="https://images.unsplash.com/photo-1542744094-3a31f272c490?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Plataforma Digital PRAE" class="section-image">
                        <!-- Imagen importada automáticamente -->
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="grid grid-2 reverse-on-mobile">
                    <div>
                        <h3>modulos de reportes interactivos</h3>
                        <img src="https://www.expertos-oracle-apex.com/wp-content/uploads/2022/10/ir-improve.jpg" alt="Módulo de Reportes Interactivos" class="section-image">
                        <!-- Imagen importada automáticamente -->
                   
                        <div class="service-card">
                            <h3>2. Módulo de Reportes Interactivos</h3>
                            <p>Genera <span class="highlight">informes</span> graficos para ver el avance, la participacion y el promedio de los proyectos ambientales escolares.
                            </p>
                        
                        <div class="service-features">
                            <h4>Características principales:</h4>
                            <ul class="custom-list">
                                <li>Reportes de proyectos, actividades y promedios</li>
                                <li>Graficos de estado, participacion y evolucion</li>
                                <li>Promedio de notas de 1.0 a 5.0</li>
                                <li>Consulta segun el rol del usuario</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <h3>Beneficios de nuestros servicios</h3>
                <div class="grid grid-3">
                    <div class="card">
                        <h4>Eficiencia</h4>
                        <p>Automatización de procesos de seguimiento y evaluación de proyectos ambientales.</p>
                    </div>
                    <div class="card">
                        <h4>Transparencia</h4>
                        <p>Acceso a información clara y verificable sobre el impacto de las iniciativas ambientales.</p>
                    </div>
                    <div class="card">
                        <h4>Colaboración</h4>
                        <p>Espacios de trabajo colaborativo entre docentes, estudiantes y comunidad.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>

</html>