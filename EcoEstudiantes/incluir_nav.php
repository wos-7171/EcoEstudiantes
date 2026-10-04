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

<style>

    /* ==============================
       HEADER
    ============================== */

    header {
        background: #ffffff;
        border-bottom: 1px solid #eeeeee;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .header-container {
        min-height: 75px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
    }

    .logo-container {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .logo {
        width: 62px;
        height: 62px;
        object-fit: cover;
        border-radius: 14px;
    }

    .brand-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .brand-text h1 {
        margin: 0;
        color: #2f9e44;
        font-size: 1.65rem;
        font-weight: 700;
    }

    .tagline {
        margin: 0;
        color: #777777;
        font-size: 0.85rem;
    }


    /* ==============================
       BOTÓN CERRAR SESIÓN
    ============================== */

    .header-actions {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .sesion-usuario {
        text-align: right;
        line-height: 1.25;
    }

    .sesion-usuario strong {
        display: block;
        color: #1b5e20;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .sesion-usuario span {
        display: block;
        color: #8a633c;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .logout-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 10px 20px;

        background: #2f9e44;
        color: #ffffff;

        border-radius: 25px;

        font-size: 0.9rem;
        font-weight: 600;

        text-decoration: none;

        transition: 0.2s ease;
    }

    .logout-btn:hover {
        background: #258638;
        transform: translateY(-1px);
    }


    /* ==============================
       MENÚ
    ============================== */

    .sticky-nav {
        position: sticky;
        top: 0;
        z-index: 1000;

        background: #ffffff;

        border-bottom: 1px solid #eeeeee;

        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
    }

    .main-nav {
        list-style: none;

        display: flex;
        flex-wrap: wrap;

        justify-content: center;
        align-items: center;

        gap: 8px;

        margin: 0;
        padding: 14px 0;
    }

    .main-nav li {
        margin: 0;
        padding: 0;
    }

    .main-nav a {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        min-height: 40px;

        padding: 8px 15px;

        border: 2px solid #a67c52;
        border-radius: 22px;

        background: #ffffff;

        color: #8a633c;

        text-decoration: none;

        font-size: 0.9rem;
        font-weight: 500;

        transition: all 0.2s ease;

        white-space: nowrap;
    }

    .main-nav a:hover {
        background: #a67c52;
        color: #ffffff;

        transform: translateY(-1px);
    }


    /* ==============================
       CONTENEDOR
    ============================== */

    .container {
        width: min(1100px, 92%);
        margin: 0 auto;
    }


    /* ==============================
       RESPONSIVE
    ============================== */

    @media (max-width: 900px) {

        .header-container {
            flex-direction: column;
            padding: 15px 0;
        }

        .logo-container {
            justify-content: center;
        }

        .brand-text {
            text-align: center;
        }

        .header-actions {
            justify-content: center;
        }

        .sesion-usuario {
            text-align: center;
        }

        .main-nav {
            padding: 12px 10px;
        }
    }


    @media (max-width: 600px) {

        .brand-text h1 {
            font-size: 1.3rem;
        }

        .tagline {
            font-size: 0.75rem;
        }

        .logo {
            width: 52px;
            height: 52px;
        }

        .main-nav a {
            font-size: 0.82rem;
            padding: 7px 12px;
        }
    }

</style>


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

                <li>
                    <a href="<?php echo htmlspecialchars($prefix); ?>instituciones.php">
                        Instituciones
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
