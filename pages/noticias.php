<?php
session_start();
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit();
}
$rol = $_SESSION["rol"];
$prefix = "../";

// Conexión a la base de datos subiendo un nivel desde la carpeta 'pages'
include("../conexion.php");

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
    <style>
        .noticia-fotos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 8px;
            margin: 0 0 12px;
        }
        .noticia-fotos figure {
            margin: 0;
            background: #f4f6f4;
            border: 1px solid #e4e4e4;
            border-radius: 8px;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .noticia-fotos img {
            max-width: 100%;
            max-height: 180px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
    </style>
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