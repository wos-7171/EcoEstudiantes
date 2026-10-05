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
    <title>Políticas - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>
<main>
        <div class="container">
            <h2>Políticas</h2>

            <section class="section">
                <div class="grid grid-2">
                    <div>
                        <div class="policy-card">
      
                            <h3>Seguridad</h3>
                            <p>Proteger datos y garantizar confianza. Implementamos medidas de seguridad robustas para asegurar que la información de nuestros usuarios esté protegida contra accesos no autorizados.
                            </p>
                            <ul class="custom-list">
                                <li>Encriptación de datos sensibles</li>
                                <li>Acceso restringido según roles</li>
                                <li>Copias de seguridad regulares</li>
                                <li>Protocolos de respuesta a incidentes</li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8f极x8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Seguridad y protección de datos" class="section-image">
                        <!-- Imagen importada automáticamente -->
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="grid grid-2 reverse-on-mobile">
                    <div>
                        <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Responsabilidad ambiental" class="section-image">
                        <!-- Imagen importada automáticamente -->
                    </div>
                    <div>
                        <div class="policy-card">
                            <h3>Responsabilidad Ambiental</h3>
                            <p>Uso de tecnología sostenible y educación ecológica. Nuestras operaciones se diseñan para minimizar el impacto ambiental y promover prácticas sostenibles.</p>
                            <ul class="custom-list">
                                <li>Servidores con eficiencia energética</li>
                                <li>Política de papel cero</li>
                                <li>Promoción de reciclaje y reutilización</li>
                                <li>Compensación de huella de carbono</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="grid grid-2">
                    <div>
                        <div class="policy-card">
                            <h3>Inclusión</h3>
                            <p>Asegurar acceso a todos los estudiantes sin discriminación. Diseñamos nuestras herramientas pensando en la diversidad de usuarios y sus diferentes necesidades.</p>
                            <ul class="custom-list">
                                <li>Interfaces accesibles</li>
                                <li>Compatibilidad con tecnologías asistivas</li>
                                <li>Contenido multiformato</li>
                                <li>Diseño universal para el aprendizaje</li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <img src="https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Inclusión y accesibilidad" class="section-image">
                        <!-- Imagen importada automáticamente -->
                    </div>
                </div>
            </section>

            <section class="section">
                <h3>Compromisos adicionales</h3>
                <div class="grid grid-3">
                    <div class="card">
                        <h4>Privacidad</h4>
                        <p>Respetamos y protegemos la privacidad de nuestros usuarios, cumpliendo con las regulaciones aplicables de protección de datos.</p>
                    </div>
                    <div class="card">
                        <h4>Transparencia</h4>
                        <p>Mantenemos comunicación clara sobre cómo utilizamos los datos y recursos dentro de nuestra plataforma.
                        </p>
                    </div>
                    <div class="card">
                        <h4>Mejora continua</h4>
                        <p>Nos comprometemos a evaluar y mejorar constantemente nuestras políticas y prácticas.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>

</html>