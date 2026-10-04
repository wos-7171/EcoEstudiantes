/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `ecoestudiantes` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `ecoestudiantes`;
DROP TABLE IF EXISTS `actividades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `actividades` (
  `id_actividad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_proyecto` int(10) unsigned NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_actividad` date NOT NULL,
  `estado` enum('Programada','En ejecución','Finalizada','Cancelada') NOT NULL DEFAULT 'Programada',
  `puntos` int(10) unsigned NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_actividad`),
  KEY `fk_actividad_proyecto` (`id_proyecto`),
  CONSTRAINT `fk_actividad_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos_prae` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `actividades` WRITE;
/*!40000 ALTER TABLE `actividades` DISABLE KEYS */;
INSERT INTO `actividades` VALUES
(5,6,'Taller práctico de compostaje orgánico','Sesión guiada en el patio del colegio para enseñar a los alumnos a separar residuos orgánicos y armar la primera pila de compost para el huerto.','2026-10-05','Programada',15,'2026-09-23 23:12:01'),
(6,7,'adsadsds','dsasddasd','4444-04-23','Programada',10,'2026-09-23 23:15:26'),
(9,10,'Encuesta comunitaria de consumo energético','Recorrer la zona asignada para aplicar un cuestionario a los habitantes. El fin es identificar qué tipo de energía usan actualmente (leña, electricidad deficiente, gas) y cuáles son sus mayores necesidades.','2026-10-08','En ejecución',15,'2026-10-01 21:43:06');
/*!40000 ALTER TABLE `actividades` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `evidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidencias` (
  `id_evidencia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_actividad` int(10) unsigned NOT NULL,
  `id_usuario` int(10) unsigned NOT NULL,
  `tipo` enum('Imagen','Video','Documento','Otro') NOT NULL DEFAULT 'Imagen',
  `archivo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_evidencia`),
  KEY `fk_evidencia_actividad` (`id_actividad`),
  KEY `fk_evidencia_usuario` (`id_usuario`),
  CONSTRAINT `fk_evidencia_actividad` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_evidencia_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `evidencias` WRITE;
/*!40000 ALTER TABLE `evidencias` DISABLE KEYS */;
INSERT INTO `evidencias` VALUES
(6,9,19,'Imagen','565e731c81c02d8925830e038e020d47.jpg','','2026-10-01 21:52:21');
/*!40000 ALTER TABLE `evidencias` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `instituciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `instituciones` (
  `id_institucion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_institucion`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `instituciones` WRITE;
/*!40000 ALTER TABLE `instituciones` DISABLE KEYS */;
INSERT INTO `instituciones` VALUES
(1,'Institución Educativa EcoEstudiantes','Dirección principal','Medellín','Antioquia','0000000000','contacto@ecoestudiantes.com',1),
(2,'IEUR+',NULL,NULL,NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `instituciones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `mensajes_contacto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensajes_contacto` (
  `id_mensaje` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `asunto` varchar(200) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Nuevo','Leído','Respondido','Archivado') NOT NULL DEFAULT 'Nuevo',
  PRIMARY KEY (`id_mensaje`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `mensajes_contacto` WRITE;
/*!40000 ALTER TABLE `mensajes_contacto` DISABLE KEYS */;
INSERT INTO `mensajes_contacto` VALUES
(4,'juan perez','juan@gmail.com','buena app','me parece una app que supera lo pensado','2026-10-01 22:12:37','Respondido');
/*!40000 ALTER TABLE `mensajes_contacto` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `noticias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `noticias` (
  `id_noticia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(10) unsigned NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` text NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_publicacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_noticia`),
  KEY `fk_noticia_usuario` (`id_usuario`),
  CONSTRAINT `fk_noticia_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `noticias` WRITE;
/*!40000 ALTER TABLE `noticias` DISABLE KEYS */;
/*!40000 ALTER TABLE `noticias` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `participantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `participantes` (
  `id_participante` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_actividad` int(10) unsigned NOT NULL,
  `id_usuario` int(10) unsigned NOT NULL,
  `fecha_participacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Inscrito','Participó','No participó') NOT NULL DEFAULT 'Inscrito',
  `puntos_obtenidos` int(10) unsigned NOT NULL DEFAULT 0,
  `nota` decimal(2,1) DEFAULT NULL,
  PRIMARY KEY (`id_participante`),
  UNIQUE KEY `uk_participante_actividad_usuario` (`id_actividad`,`id_usuario`),
  KEY `fk_participante_usuario` (`id_usuario`),
  CONSTRAINT `fk_participante_actividad` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_participante_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `participantes` WRITE;
/*!40000 ALTER TABLE `participantes` DISABLE KEYS */;
INSERT INTO `participantes` VALUES
(10,5,16,'2026-09-23 23:12:28','Participó',15,5.0),
(11,5,17,'2026-09-23 23:19:14','Participó',6,2.0),
(13,5,18,'2026-09-23 23:27:55','Inscrito',0,NULL),
(14,9,19,'2026-10-01 21:52:07','Participó',15,5.0),
(15,9,14,'2026-10-01 22:11:19','Inscrito',0,NULL);
/*!40000 ALTER TABLE `participantes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `proyectos_prae`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `proyectos_prae` (
  `id_proyecto` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_institucion` int(10) unsigned NOT NULL,
  `id_responsable` int(10) unsigned NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `objetivo` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('Planificado','En ejecución','Finalizado','Cancelado') NOT NULL DEFAULT 'Planificado',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_proyecto`),
  KEY `fk_proyecto_institucion` (`id_institucion`),
  KEY `fk_proyecto_responsable` (`id_responsable`),
  CONSTRAINT `fk_proyecto_institucion` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`) ON UPDATE CASCADE,
  CONSTRAINT `fk_proyecto_responsable` FOREIGN KEY (`id_responsable`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `proyectos_prae` WRITE;
/*!40000 ALTER TABLE `proyectos_prae` DISABLE KEYS */;
INSERT INTO `proyectos_prae` VALUES
(6,1,15,'Huerto Escolar Ecológico','Creación y mantenimiento de un huerto orgánico en el patio del colegio con la participación activa de los alumnos de secundaria. Se estudiarán los ciclos de vida de las plantas, el compostaje y la importancia de la agricultura sostenible.','Fomentar la conciencia ambiental y aplicar conocimientos prácticos de biología y ciencias naturales mediante el cultivo y cuidado de hortalizas locales.','2026-09-24','2026-09-30','Finalizado','2026-09-23 23:11:06'),
(7,1,13,'cxzcz','xczxcxz','cxzczx','2222-04-04','0686-05-31','Finalizado','2026-09-23 23:14:48'),
(10,1,20,'Investigación sobre Energías Renovables en Zonas Rurales','Estudio de factibilidad para la implementación de paneles solares comunitarios en poblaciones aisladas económicamente, analizando costos y beneficios a largo plazo','Diseñar un modelo de propuesta sostenible y replicable que pueda ser presentado a ONGs o entidades gubernamentales para su financiamiento.','2026-02-04','2027-02-05','Finalizado','2026-10-01 21:41:45');
/*!40000 ALTER TABLE `proyectos_prae` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'Administrador','Control total de la plataforma'),
(2,'Docente','Gestiona proyectos y actividades PRAE'),
(3,'Estudiante','Participa en actividades ambientales');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_rol` int(10) unsigned NOT NULL,
  `id_institucion` int(10) unsigned DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `documento` varchar(30) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `grado` varchar(30) DEFAULT NULL,
  `grupo` varchar(30) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `documento` (`documento`),
  KEY `fk_usuario_rol` (`id_rol`),
  KEY `fk_usuario_institucion` (`id_institucion`),
  CONSTRAINT `fk_usuario_institucion` FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,1,1,'Administrador','Sistema','0000000000','admin@ecoestudiantes.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC9uGqO2Q6rK3q8e3K6G',NULL,NULL,1,'2026-08-13 16:12:12'),
(2,1,1,'luis','montoya',NULL,'luismontoya200408@gmail.com','$2y$10$JExcCa2.MOU0RI44nMvH.utEvY237UZIyg3MKxwQbUPDWGVqxoSZC',NULL,NULL,1,'2026-09-22 21:43:09'),
(5,2,1,'luis','wos',NULL,'luis@gmail.com','$2y$10$q8sy7SE1XS9pr72rE5mUDeiY.PiZ8qOyhhh4c6FnS2pENshPZxLaC',NULL,NULL,1,'2026-09-23 20:26:56'),
(9,2,1,'ferrrr','adsadsdasads',NULL,'ferr@gmail.com','$2y$10$XTs5VmniSoE2VhloaRknVOEqpgu.LzOihXPq7msUc4sY6EKoE3.Em',NULL,NULL,1,'2026-09-23 20:53:37'),
(11,3,1,'dsds','dsaasddas',NULL,'fdsfsdfd@gmail.com','$2y$10$dEEuSz2h4cYtk7S0SQ5.zuYGeGVk0PpPIlTS7rrULbQNLdUKu4gdK',NULL,NULL,1,'2026-09-23 21:07:27'),
(12,2,1,'luis mintiya','prueba',NULL,'luisito@gmail.com','$2y$10$cE8g7DTB.FbqtDUxR0QCYeRP2NPs5T2Z1jtautp6CaRMC75K5H4MC',NULL,NULL,1,'2026-09-23 21:16:03'),
(13,2,1,'dSDSA','ASSSASA',NULL,'ASSAASAS@gmail.com','$2y$10$KIplsHZdxTKXRhAV1.xWYeoRZQIjmpcsxNzZBaIaCgX6dopiqlZei',NULL,NULL,1,'2026-09-23 22:59:02'),
(14,3,1,'juan','perez',NULL,'juan@gmail.com','$2y$10$.yi2cpHggfv7tKTObaNZneICCPdWNrvZPhLTgPvKdMU3aSYwr8C0O',NULL,NULL,1,'2026-09-23 23:04:12'),
(15,2,1,'wosito','wosito',NULL,'wosito@gmail.com','$2y$10$QdHUCpQk5cQUydemWP6.te9jRfy.1W1f6b4jKF6/lwKack.im4pvK',NULL,NULL,1,'2026-09-23 23:07:51'),
(16,3,1,'duki','duki',NULL,'duki@gmail.com','$2y$10$.Xam2TgmOipSTYQF///c9eQ8tjEMdFYBvxjv5hrNrbFWc/.QO7NPm',NULL,NULL,1,'2026-09-23 23:08:32'),
(17,3,1,'nena','asss',NULL,'nena@gmail.com','$2y$10$8zYug8bB7kIMCf7aB7M8ceyHRVm49WbVOJPYE7G8OyhwXr3Rlh.f6',NULL,NULL,1,'2026-09-23 23:18:55'),
(18,3,1,'nuevo','dsdsadas',NULL,'nuevo@gmail.com','$2y$10$7j4OMBhoXcRGLL.w1SrtwOjgi0/l98oryqMx6kR4VXhFi7K5wvIyC',NULL,NULL,1,'2026-09-23 23:27:43'),
(19,3,1,'luis','prueba',NULL,'ferchito@gmail.com','$2y$10$vzryG/TjfR.Top.3vv1lMe1M6ZPHcKfkDt86p.oMlqZTpT7OKE/.2',NULL,NULL,1,'2026-10-01 21:34:52'),
(20,1,1,'miguel','montoya',NULL,'miguel@gmail.com','$2y$10$hmeecNMTBzS6udETh4tuj.3ZvbfL7au4Bw/WOODFktK5irGDQfuSi',NULL,NULL,1,'2026-10-01 21:39:11'),
(21,3,1,'pepito','perez',NULL,'pepito@gmail.com','$2y$10$Ff7osHBYWn/h7bcfhubsMeXAu8sBJCrxugsVWf5DXF.SzCC98bk6S',NULL,NULL,1,'2026-10-01 22:13:58');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

