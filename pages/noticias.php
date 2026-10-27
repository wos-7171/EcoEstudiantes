<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$usuario_actual = requiere_usuario($conexion, null, 'indexprincipal.php');
$rol = $_SESSION["rol"];
$prefix = "../";

// Consultar todas las noticias desde la base de datos
// (fecha_publicacion es el nombre real de la columna en la tabla actual)
$sql_noticias = "SELECT * FROM noticias ORDER BY fecha_publicacion DESC";
$resultado_noticias = $conexion->query($sql_noticias);
$imagenes_por_noticia = [];
$res_imgs = $conexion->query("SELECT id_noticia, archivo FROM noticias_imagenes ORDER BY orden ASC, id_imagen ASC");
if ($res_imgs) {
    while ($img = $res_imgs->fetch_assoc()) {
        $imagenes_por_noticia[(int)$img['id_noticia']][] = $img['archivo'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/noticias.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>

    <main>
        <div class="container">
            <h2>Noticias o Novedades</h2>

            <!-- NOTICIA DESTACADA ESTÁTICA -->
            <section class="section">
                <div class="news-article">
                    <div class="news-badge">Reconocimiento 2025</div>
                    <p>"Nuestro proyecto fue reconocido en <span class="highlight">2025</span> como propuesta <span class="highlight">innovadora</span> en el Programa Nacional de <span class="highlight">Educación Ambiental</span>."</p>
                    <p>"Este logro demuestra el compromiso de EcoEstudiantes Digitales con la <span class="highlight">sostenibilidad</span> escolar."</p>
                </div>
            </section>

            <!-- SECCIÓN DINÁMICA: NOTICIAS DESDE LA BASE DE DATOS -->
            <section class="section">
                <h3>Últimas Publicaciones</h3>
                
                <?php if ($resultado_noticias && $resultado_noticias->num_rows > 0): ?>
                    <div class="grid grid-2">
                        <?php while ($noticia = $resultado_noticias->fetch_assoc()): ?>
                            <div class="card">
                                <h4><?php echo htmlspecialchars($noticia['titulo']); ?></h4>
                                <small style="display: block; color: #666; margin-bottom: 10px;">
                                    <?php echo date("d/m/Y", strtotime($noticia['fecha_publicacion'])); ?>
                                </small>
                                <?php $fotos = $imagenes_por_noticia[(int)$noticia['id_noticia']] ?? []; ?>
                                <?php if (count($fotos) > 0): ?>
                                <div class="noticia-fotos">
                                    <?php foreach ($fotos as $foto): ?>
                                    <figure>
                                        <img src="<?php echo htmlspecialchars('../images/noticias/' . basename($foto)); ?>" alt="<?php echo htmlspecialchars($noticia['titulo']); ?>">
                                    </figure>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <p><?php echo nl2br(htmlspecialchars($noticia['contenido'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <p style="text-align: center; color: #666;">No hay publicaciones disponibles en este momento.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
<?php include("../includes/pie.php"); ?>
</body>

</html>