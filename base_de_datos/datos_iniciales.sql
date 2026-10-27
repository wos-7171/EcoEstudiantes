-- ============================================================
-- EcoEstudiantes Digitales - Datos iniciales
--
-- Importar DESPUÉS de `esquema.sql`, en la misma base de datos.
--
-- Administrador temporal:
--   Correo:     admin@ecoestudiantes.com
--   Contraseña: EcoAdmin2026
--
-- CAMBIA ESTA CONTRASEÑA Y EL CORREO INMEDIATAMENTE después del
-- primer inicio de sesión, desde "Mi Perfil".
-- ============================================================

SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Control total de la plataforma'),
(2, 'Docente', 'Gestiona proyectos y actividades PRAE'),
(3, 'Estudiante', 'Participa en actividades ambientales');

INSERT INTO `instituciones` (`id_institucion`, `nombre`, `direccion`, `ciudad`, `departamento`, `telefono`, `correo`, `estado`) VALUES
(1, 'Institución Educativa Ejemplo', NULL, 'Medellín', 'Antioquia', NULL, NULL, 1);

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `id_institucion`, `nombre`, `apellido`, `correo`, `password`, `estado`) VALUES
(1, 1, 1, 'Administrador', 'Sistema', 'admin@ecoestudiantes.com', '$2y$10$gBlG1.8kqVuxWMB6Bu2y.uY5P7KW2BMG/XU6w6XN7D95doTi/vmQ6', 1);

COMMIT;
