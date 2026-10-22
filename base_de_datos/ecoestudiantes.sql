-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-10-2026 a las 21:27:20
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ecoestudiantes`
--
CREATE DATABASE IF NOT EXISTS `ecoestudiantes` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ecoestudiantes`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id_actividad` int(10) UNSIGNED NOT NULL,
  `id_proyecto` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_actividad` date NOT NULL,
  `estado` enum('Programada','En ejecución','Finalizada','Cancelada') NOT NULL DEFAULT 'Programada',
  `puntos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades`
--

INSERT INTO `actividades` (`id_actividad`, `id_proyecto`, `nombre`, `descripcion`, `fecha_actividad`, `estado`, `puntos`, `fecha_creacion`) VALUES
(6, 7, 'adsadsds', 'dsasddasd', '2026-10-04', 'En ejecución', 10, '2026-09-23 23:15:26'),
(11, 12, 'Taller práctico de separación de residuos.', 'Sesión educativa para enseñar a los participantes a clasificar correctamente los desechos en los contenedores correspondientes', '2026-04-15', 'Cancelada', 0, '2026-10-04 20:50:48'),
(12, 12, 'luis', 'fernando hace mucho', '2027-05-04', 'Finalizada', 0, '2026-10-04 23:56:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evidencias`
--

CREATE TABLE `evidencias` (
  `id_evidencia` int(10) UNSIGNED NOT NULL,
  `id_actividad` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `tipo` enum('Imagen','Video','Documento','Otro') NOT NULL DEFAULT 'Imagen',
  `archivo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evidencias`
--

INSERT INTO `evidencias` (`id_evidencia`, `id_actividad`, `id_usuario`, `tipo`, `archivo`, `descripcion`, `fecha_subida`) VALUES
(9, 11, 26, 'Imagen', 'b7e755df37c508bae5a8b5af6d8d3886.png', '', '2026-10-04 23:51:24'),
(10, 11, 27, 'Imagen', '59335dce52ab7bd1de666ada58c87b04.png', '', '2026-10-04 23:55:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria`
--

CREATE TABLE `galeria` (
  `id_foto` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `archivo` varchar(500) NOT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `galeria`
--

INSERT INTO `galeria` (`id_foto`, `titulo`, `archivo`, `fecha_subida`) VALUES
(2, 'Estudiantes sembrando un arbol', 'https://www.el19digital.com/files/articulos/251016.jpeg', '2026-10-04 17:49:31'),
(3, 'Escuela con paneles solares', 'solar.png', '2026-10-04 17:49:31'),
(5, 'Reciclaje en la escuela', 'reciclaje.jpg', '2026-10-04 17:49:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `id_institucion` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instituciones`
--

INSERT INTO `instituciones` (`id_institucion`, `nombre`, `direccion`, `ciudad`, `departamento`, `telefono`, `correo`, `estado`) VALUES
(1, 'Institución Educativa IERU', 'Dirección principal', 'Medellín', 'Antioquia', '0000000000', 'contacto@ecoestudiantes.com', 1),
(3, 'El Rosal', NULL, NULL, NULL, NULL, NULL, 1),
(4, 'IE La Paz', NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_contacto`
--

CREATE TABLE `mensajes_contacto` (
  `id_mensaje` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `asunto` varchar(200) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Nuevo','Leído','Respondido','Archivado') NOT NULL DEFAULT 'Nuevo',
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  `id_admin` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mensajes_contacto`
--

INSERT INTO `mensajes_contacto` (`id_mensaje`, `nombre`, `correo`, `asunto`, `mensaje`, `fecha_envio`, `estado`, `id_usuario`, `respuesta`, `fecha_respuesta`, `id_admin`) VALUES
(9, 'duki duki', 'duki@gmail.com', 'olee', 'holaaa', '2026-10-04 20:07:52', 'Respondido', 16, 'hola, que pasa', '2026-10-19 19:57:01', 2),
(10, 'gggg gggg', 'gggg@gmail.com', 'ey, está mela la página', 'como está haciendo eso', '2026-10-05 01:02:17', 'Respondido', 29, 'buenas tardes', '2026-10-19 19:57:07', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id_noticia` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` text NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_publicacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id_noticia`, `id_usuario`, `titulo`, `contenido`, `imagen`, `fecha_publicacion`, `estado`) VALUES
(5, 2, 'noticias IERU', 'hoy está la IERU', NULL, '2026-10-19 19:29:58', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias_imagenes`
--

CREATE TABLE `noticias_imagenes` (
  `id_imagen` int(10) UNSIGNED NOT NULL,
  `id_noticia` int(10) UNSIGNED NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `orden` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticias_imagenes`
--

INSERT INTO `noticias_imagenes` (`id_imagen`, `id_noticia`, `archivo`, `orden`, `fecha_subida`) VALUES
(5, 5, 'ab4bcd160e2aadf7246be4324dbe2850.png', 1, '2026-10-19 19:29:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participantes`
--

CREATE TABLE `participantes` (
  `id_participante` int(10) UNSIGNED NOT NULL,
  `id_actividad` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `fecha_participacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Inscrito','Participó','No participó') NOT NULL DEFAULT 'Inscrito',
  `nota` decimal(2,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `participantes`
--

INSERT INTO `participantes` (`id_participante`, `id_actividad`, `id_usuario`, `fecha_participacion`, `estado`, `nota`) VALUES
(18, 11, 26, '2026-10-04 23:50:43', 'Participó', 4.1),
(19, 11, 27, '2026-10-04 23:54:50', 'Participó', 5.0),
(22, 6, 16, '2026-10-22 14:06:36', 'Inscrito', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos_prae`
--

CREATE TABLE `proyectos_prae` (
  `id_proyecto` int(10) UNSIGNED NOT NULL,
  `id_institucion` int(10) UNSIGNED NOT NULL,
  `id_responsable` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `objetivo` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('Planificado','En ejecución','Finalizado','Cancelado') NOT NULL DEFAULT 'Planificado',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `articulacion_pei` varchar(80) DEFAULT NULL,
  `impacto` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyectos_prae`
--

INSERT INTO `proyectos_prae` (`id_proyecto`, `id_institucion`, `id_responsable`, `nombre`, `descripcion`, `objetivo`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_creacion`, `articulacion_pei`, `impacto`) VALUES
(7, 1, 13, 'cxzcz', 'xczxcxz', 'cxzczx', '2026-10-04', '2026-10-04', 'Planificado', '2026-09-23 23:14:48', NULL, NULL),
(12, 3, 25, 'Huerto Escolar Sostenible: Cultivando Conciencia.', 'Este proyecto consiste en la creación y mantenimiento de un huerto orgánico dentro de las instalaciones escolares, gestionado por los propios estudiantes de secundaria para fomentar la soberanía alimentaria.', 'Promover la educación ambiental práctica en los estudiantes mediante el cultivo de hortalizas nativas durante el año escolar.', '2026-02-01', '2026-11-30', 'Finalizado', '2026-10-04 20:49:42', 'Gestion academica', 'Medio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Control total de la plataforma'),
(2, 'Docente', 'Gestiona proyectos y actividades PRAE'),
(3, 'Estudiante', 'Participa en actividades ambientales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_rol` int(10) UNSIGNED NOT NULL,
  `id_institucion` int(10) UNSIGNED DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `documento` varchar(30) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `grado` varchar(30) DEFAULT NULL,
  `grupo` varchar(30) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `id_institucion`, `nombre`, `apellido`, `documento`, `correo`, `password`, `grado`, `grupo`, `estado`, `fecha_registro`) VALUES
(1, 1, 1, 'Administrador', 'Sistema', '0000000000', 'admin@ecoestudiantes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC9uGqO2Q6rK3q8e3K6G', NULL, NULL, 1, '2026-08-13 16:12:12'),
(2, 1, 1, 'luis', 'montoya', NULL, 'luismontoya200408@gmail.com', '$2y$10$JExcCa2.MOU0RI44nMvH.utEvY237UZIyg3MKxwQbUPDWGVqxoSZC', NULL, NULL, 1, '2026-09-22 21:43:09'),
(5, 2, 1, 'luis', 'wos', NULL, 'luis@gmail.com', '$2y$10$q8sy7SE1XS9pr72rE5mUDeiY.PiZ8qOyhhh4c6FnS2pENshPZxLaC', NULL, NULL, 1, '2026-09-23 20:26:56'),
(9, 2, 1, 'ferrrr', 'adsadsdasads', NULL, 'ferr@gmail.com', '$2y$10$XTs5VmniSoE2VhloaRknVOEqpgu.LzOihXPq7msUc4sY6EKoE3.Em', NULL, NULL, 1, '2026-09-23 20:53:37'),
(11, 3, 1, 'dsds', 'dsaasddas', NULL, 'fdsfsdfd@gmail.com', '$2y$10$dEEuSz2h4cYtk7S0SQ5.zuYGeGVk0PpPIlTS7rrULbQNLdUKu4gdK', NULL, NULL, 1, '2026-09-23 21:07:27'),
(12, 2, 1, 'luis mintiya', 'prueba', NULL, 'luisito@gmail.com', '$2y$10$cE8g7DTB.FbqtDUxR0QCYeRP2NPs5T2Z1jtautp6CaRMC75K5H4MC', NULL, NULL, 1, '2026-09-23 21:16:03'),
(13, 2, 1, 'dSDSA', 'ASSSASA', NULL, 'ASSAASAS@gmail.com', '$2y$10$KIplsHZdxTKXRhAV1.xWYeoRZQIjmpcsxNzZBaIaCgX6dopiqlZei', NULL, NULL, 1, '2026-09-23 22:59:02'),
(14, 3, 1, 'juan', 'perez', NULL, 'juan@gmail.com', '$2y$10$.yi2cpHggfv7tKTObaNZneICCPdWNrvZPhLTgPvKdMU3aSYwr8C0O', NULL, NULL, 1, '2026-09-23 23:04:12'),
(15, 2, 1, 'wosito', 'wosito', NULL, 'wosito@gmail.com', '$2y$10$QdHUCpQk5cQUydemWP6.te9jRfy.1W1f6b4jKF6/lwKack.im4pvK', NULL, NULL, 1, '2026-09-23 23:07:51'),
(16, 3, 1, 'duki', 'duki', NULL, 'duki@gmail.com', '$2y$10$.Xam2TgmOipSTYQF///c9eQ8tjEMdFYBvxjv5hrNrbFWc/.QO7NPm', NULL, NULL, 1, '2026-09-23 23:08:32'),
(17, 3, 1, 'nena', 'asss', NULL, 'nena@gmail.com', '$2y$10$8zYug8bB7kIMCf7aB7M8ceyHRVm49WbVOJPYE7G8OyhwXr3Rlh.f6', NULL, NULL, 1, '2026-09-23 23:18:55'),
(18, 3, 1, 'nuevo', 'dsdsadas', NULL, 'nuevo@gmail.com', '$2y$10$7j4OMBhoXcRGLL.w1SrtwOjgi0/l98oryqMx6kR4VXhFi7K5wvIyC', NULL, NULL, 1, '2026-09-23 23:27:43'),
(19, 3, 1, 'luis', 'prueba', NULL, 'ferchito@gmail.com', '$2y$10$vzryG/TjfR.Top.3vv1lMe1M6ZPHcKfkDt86p.oMlqZTpT7OKE/.2', NULL, NULL, 1, '2026-10-01 21:34:52'),
(21, 2, 1, 'pepito', 'perez', NULL, 'pepito@gmail.com', '$2y$10$EsOAZRP3c3hT1UdBl3ALs.ni0brBas3odiU8ylHh.Dcdi4VO5HNDy', NULL, NULL, 1, '2026-10-01 22:13:58'),
(23, 2, 1, 'wefff', 'prueba', NULL, 'doce@gmail.com', '$2y$10$yX7sKGx2GHaKSgch7/asQuXow7.SQpuT875R2tJdUmSG.cMnXjDuC', NULL, NULL, 1, '2026-10-04 18:27:55'),
(24, 3, 3, 'mari', 'montoya', NULL, 'mari@gmail.com', '$2y$10$QVjmPCfz/.tHLqK8W4h8PuIyfJyOn/ml3cZOtlTZkGK4MLdcBomq.', NULL, NULL, 1, '2026-10-04 20:45:19'),
(25, 2, 3, 'vivi', 'montoya', NULL, 'vivi@gmail.com', '$2y$10$tYh9fTZM5QY9Xl2.0iC//uVegR7QqN/yXuMup3EHLCm/z3rql4x/q', NULL, NULL, 1, '2026-10-04 20:47:38'),
(26, 2, 3, 'ffff', 'montoya', NULL, 'ffff@gmail.com', '$2y$10$ujHggrj4d0Cpqd8YfQ/HpOeKLZLPuh69trajCeQmWwFHnXlYjsiqy', NULL, NULL, 1, '2026-10-04 21:16:36'),
(27, 3, 3, 'fernandito', 'montoya', NULL, 'fernandito@gmail.com', '$2y$10$HTR1BuwoK.mT2GgQ759LR.WdMXi2.C22tEg/doRsdAviPtob/CSEW', NULL, NULL, 1, '2026-10-04 23:54:38'),
(28, 2, 4, 'javi', 'perez', NULL, 'javi@gmail.com', '$2y$10$ZBP5qepnmCyY01rBXIzRme6rHg0TJzjxEc94LO6UgXjts2je7npVC', NULL, NULL, 1, '2026-10-05 00:22:20'),
(29, 2, 4, 'gggg', 'gggg', NULL, 'gggg@gmail.com', '$2y$10$JPiC8gh/HCWZhcv8ius1guJ.g4w3rioSyr3oh29jHW7C4cCYYC8/i', NULL, NULL, 1, '2026-10-05 00:37:55'),
(30, 3, 4, 'redad', 'redad', NULL, 'redad@gmail.com', '$2y$10$F01f3.9xUGuuD3459CK1heIgnuWdWoZySI/D5RJYvjDH.rK2qvH/6', NULL, NULL, 1, '2026-10-19 20:40:56');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `fk_actividad_proyecto` (`id_proyecto`);

--
-- Indices de la tabla `evidencias`
--
ALTER TABLE `evidencias`
  ADD PRIMARY KEY (`id_evidencia`),
  ADD KEY `fk_evidencia_actividad` (`id_actividad`),
  ADD KEY `fk_evidencia_usuario` (`id_usuario`);

--
-- Indices de la tabla `galeria`
--
ALTER TABLE `galeria`
  ADD PRIMARY KEY (`id_foto`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`id_institucion`);

--
-- Indices de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  ADD PRIMARY KEY (`id_mensaje`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id_noticia`),
  ADD KEY `fk_noticia_usuario` (`id_usuario`);

--
-- Indices de la tabla `noticias_imagenes`
--
ALTER TABLE `noticias_imagenes`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `fk_ni_not` (`id_noticia`);

--
-- Indices de la tabla `participantes`
--
ALTER TABLE `participantes`
  ADD PRIMARY KEY (`id_participante`),
  ADD UNIQUE KEY `uk_participante_actividad_usuario` (`id_actividad`,`id_usuario`),
  ADD KEY `fk_participante_usuario` (`id_usuario`);

--
-- Indices de la tabla `proyectos_prae`
--
ALTER TABLE `proyectos_prae`
  ADD PRIMARY KEY (`id_proyecto`),
  ADD KEY `fk_proyecto_institucion` (`id_institucion`),
  ADD KEY `fk_proyecto_responsable` (`id_responsable`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `documento` (`documento`),
  ADD KEY `fk_usuario_rol` (`id_rol`),
  ADD KEY `fk_usuario_institucion` (`id_institucion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id_actividad` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `evidencias`
--
ALTER TABLE `evidencias`
  MODIFY `id_evidencia` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `galeria`
--
ALTER TABLE `galeria`
  MODIFY `id_foto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `id_institucion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  MODIFY `id_mensaje` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id_noticia` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `noticias_imagenes`
--
ALTER TABLE `noticias_imagenes`
  MODIFY `id_imagen` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `participantes`
--
ALTER TABLE `participantes`
  MODIFY `id_participante` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `proyectos_prae`
--
ALTER TABLE `proyectos_prae`
  MODIFY `id_proyecto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `fk_actividad_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos_prae` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `evidencias`
--
ALTER TABLE `evidencias`
  ADD CONSTRAINT `fk_evidencia_actividad` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evidencia_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `fk_noticia_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `noticias_imagenes`
--
ALTER TABLE `noticias_imagenes`
  ADD CONSTRAINT `fk_ni_not` FOREIGN KEY (`id_noticia`) REFERENCES `noticias` (`id_noticia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `participantes`
--
ALTER TABLE `participantes`
  ADD CONSTRAINT `fk_participante_actividad` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_participante_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proyectos_prae`
--
ALTER TABLE `proyectos_prae`
  ADD CONSTRAINT `fk_proyecto_institucion` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proyecto_responsable` FOREIGN KEY (`id_responsable`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_institucion` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
