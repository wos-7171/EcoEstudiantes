<?php
// ==========================================================
// INCLUIR_NAV.PHP
// Navegación de EcoEstudiantes Digitales
// ==========================================================

if (!isset($prefix)) {
    $prefix = "";
}

// ==========================================================
// OBTENER EL ROL DEL USUARIO
// ==========================================================

$rol = $_SESSION['rol']
    ?? $_SESSION['nombre_rol']
    ?? $_SESSION['role']
    ?? $_SESSION['id_rol']
    ?? '';

// Si el rol viene como número, lo convertimos a nombre.
if (is_numeric($rol)) {

    switch ((int)$rol) {

        case 1:
            $rol = 'Administrador';
            break;

        case 2:
            $rol = 'Docente';
            break;

        case 3:
            $rol = 'Estudiante';
            break;

        default:
            $rol = '';
            break;
    }
}

$rol = trim((string)$rol);
?>


<!-- ======================================================
     CSS DE LA NAVEGACIÓN
====================================================== -->

<link rel="stylesheet" href="<?php echo htmlspecialchars($prefix); ?>assets/css/motion.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($prefix); ?>assets/css/nav.css">


<!-- ======================================================
     HEADER
====================================================== -->

<header>

    <div class="container header-container">

        <div class="logo-container">

            <img
                src="<?php echo htmlspecialchars($prefix); ?>images/lg.jpeg"
                alt="Logo EcoEstudiantes Digitales"
                class="logo"
            >

            <div class="brand-text">

                <h1>
                    EcoEstudiantes Digitales
                </h1>

                <p class="tagline">
                    Innovación para la educación ambiental
                </p>

            </div>

        </div>


        <div class="header-actions">

            <?php if (!empty($_SESSION['nombre']) || $rol !== ''): ?>
            <div class="sesion-usuario">
                <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?></strong>
                <span><?php echo htmlspecialchars($rol); ?></span>
            </div>
            <?php endif; ?>

            <a
                href="<?php echo htmlspecialchars($prefix); ?>logout.php"
                class="logout-btn"
            >
                Cerrar sesión
            </a>

        </div>

    </div>

</header>


<!-- ======================================================
     NAVEGACIÓN
====================================================== -->

<nav class="sticky-nav">

    <div class="container">

        <ul class="main-nav">

            <!-- INICIO -->

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>indexprincipal.php">
                    Inicio
                </a>
            </li>


            <!-- PROYECTOS -->

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>proyectos.php">
                    Proyectos PRAE
                </a>
            </li>


            <!-- ADMINISTRADOR -->

            <?php if ($rol === 'Administrador'): ?>

                <li>
                    <a href="<?php echo htmlspecialchars($prefix); ?>admin.php">
                        Panel Admin
                    </a>
                </li>

            <?php endif; ?>


            <!-- ADMINISTRADOR / DOCENTE -->

            <?php if ($rol === 'Administrador' || $rol === 'Docente'): ?>

                <li>
                    <a href="<?php echo htmlspecialchars($prefix); ?>reportes.php">
                        Reportes
                    </a>
                </li>

            <?php endif; ?>


            <!-- ESTUDIANTE -->

            <?php if ($rol === 'Estudiante'): ?>

                <li>
                    <a href="<?php echo htmlspecialchars($prefix); ?>mis_actividades.php">
                        Mis Actividades
                    </a>
                </li>

            <?php endif; ?>


            <!-- PERFIL -->

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>perfil.php">
                    Mi Perfil
                </a>
            </li>
            <?php
            $es_admin_nav = ($rol === 'Administrador')
                || ((int)($_SESSION['id_rol'] ?? 0) === 1)
                || (stripos((string)$rol, 'admin') !== false);
            if (!$es_admin_nav):
            ?>
            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>mis_mensajes.php">
                    Mis mensajes
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>manual.php">
                    Manual de usuario
                </a>
            </li>


            <!-- INFORMACIÓN -->

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>pages/recursos.php">
                    Recursos y Normativa
                </a>
            </li>

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>pages/quienes-somos.php">
                    Quiénes Somos
                </a>
            </li>

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>pages/antecedentes.php">
                    Antecedentes
                </a>
            </li>

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>pages/fundamentos.php">
                    Fundamentos
                </a>
            </li>

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>pages/servicios.php">
                    Servicios
                </a>
            </li>

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>pages/noticias.php">
                    Noticias
                </a>
            </li>

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>pages/galeria.php">
                    Galería
                </a>
            </li>

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>pages/contacto.php">
                    Contacto
                </a>
            </li>

            <li>
                <a href="<?php echo htmlspecialchars($prefix); ?>pages/politicas.php">
                    Políticas
                </a>
            </li>

        </ul>

    </div>

</nav>
<script src="<?php echo htmlspecialchars($prefix); ?>assets/js/motion.js"></script>