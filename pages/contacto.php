<?php
session_start();
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../index.php");
    exit();
}
$rol = $_SESSION["rol"];
$prefix = "../";

$flash_ok = $_SESSION['flash_ok'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_ok'], $_SESSION['flash_error']);
$nombre_prefill = $_SESSION["nombre"] ?? "";
$correo_prefill = $_SESSION["correo"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - EcoEstudiantes Digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>

<body>
<?php include("../incluir_nav.php"); ?>
<main>
        <div class="container">
            <h2>Contacto</h2>

            <section class="section">
                <div class="grid grid-2">
                    <div>
                        <div class="contact-info">
                            <div class="contact-item">
                                <div>
                                    <h3>Dirección</h3>
                                    <p>6801 Hollywood Blvd, Los Angeles, CA 90028, USA</p>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div>
                                    <h3>Teléfono</h3>
                                    <p>+57 321 639 5343</p>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div>
                                    <h3>Correo electrónico</h3>
                                    <p>ecoestudiantesdigitales@gmail.com</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="map-container">
                            <h3>Ubicación</h3>
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d423283.435550234!2d-118.69191575!3d34.02073034999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80c2c75ddc27da13%3A0xe22fdf6f254608f4!2sLos%20%C3%81ngeles%2C%20California%2C%20EE.%20UU.!5e0!3m2!1ses!2ses!4v1684869686783!5m2!1ses!2ses"
                                width="100%" height="300" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <h3>Envíanos un mensaje</h3>
                <?php if ($flash_ok): ?><div class="aviso aviso-ok"><?php echo htmlspecialchars($flash_ok); ?></div><?php endif; ?>
                <?php if ($flash_error): ?><div class="aviso aviso-error"><?php echo htmlspecialchars($flash_error); ?></div><?php endif; ?>
                <form class="form-contacto" action="contacto_acciones.php" method="POST">
                    <label for="c_nombre">Nombre</label>
                    <input type="text" id="c_nombre" name="nombre" maxlength="100" required value="<?php echo htmlspecialchars($nombre_prefill); ?>">
                    <label for="c_correo">Correo electrónico</label>
                    <input type="email" id="c_correo" name="correo" maxlength="120" required value="<?php echo htmlspecialchars($correo_prefill); ?>">
                    <label for="c_asunto">Asunto</label>
                    <input type="text" id="c_asunto" name="asunto" maxlength="150" required>
                    <label for="c_mensaje">Mensaje</label>
                    <textarea id="c_mensaje" name="mensaje" rows="5" maxlength="2000" required></textarea>
                    <button type="submit">Enviar mensaje</button>
                </form>
            </section>
        </div>
    </main>
</body>

</html>