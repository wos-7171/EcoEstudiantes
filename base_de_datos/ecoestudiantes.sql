-- Esquema de referencia EcoEstudiantes (instalacion nueva o reparacion).
-- Solo CREATE TABLE IF NOT EXISTS e INSERT IGNORE. No hace DROP.
-- Si la base ya existe con datos, usa migracion_notas.sql.

CREATE DATABASE IF NOT EXISTS ecoestudiantes CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ecoestudiantes;

CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO roles (id_rol, nombre, descripcion) VALUES
(1, 'Administrador', 'Gestiona la plataforma'),
(2, 'Docente', 'Responsable de proyectos PRAE'),
(3, 'Estudiante', 'Participa en actividades');

CREATE TABLE IF NOT EXISTS instituciones (
    id_institucion INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    direccion VARCHAR(200) NULL,
    ciudad VARCHAR(80) NULL,
    departamento VARCHAR(80) NULL,
    telefono VARCHAR(30) NULL,
    correo VARCHAR(120) NULL,
    estado TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO instituciones (id_institucion, nombre) VALUES (1, 'Institucion principal');

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    id_institucion INT NULL,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    documento VARCHAR(30) NULL,
    correo VARCHAR(120) NOT NULL,
    password VARCHAR(255) NOT NULL,
    grado VARCHAR(20) NULL,
    grupo VARCHAR(20) NULL,
    estado TINYINT NOT NULL DEFAULT 1,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_correo (correo),
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
    CONSTRAINT fk_usuarios_institucion FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS proyectos_prae (
    id_proyecto INT AUTO_INCREMENT PRIMARY KEY,
    id_institucion INT NOT NULL,
    id_responsable INT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    objetivo TEXT NULL,
    estado ENUM('Planificado','En ejecución','Finalizado','Cancelado') NOT NULL DEFAULT 'Planificado',
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_proy_inst FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion),
    CONSTRAINT fk_proy_resp FOREIGN KEY (id_responsable) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS actividades (
    id_actividad INT AUTO_INCREMENT PRIMARY KEY,
    id_proyecto INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    fecha_actividad DATE NOT NULL,
    estado ENUM('Programada','En ejecución','Finalizada','Cancelada') NOT NULL DEFAULT 'Programada',
    puntos INT NULL DEFAULT 0,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_act_proy FOREIGN KEY (id_proyecto) REFERENCES proyectos_prae(id_proyecto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS participantes (
    id_participante INT AUTO_INCREMENT PRIMARY KEY,
    id_actividad INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_participacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Inscrito','Participó','No participó') NOT NULL DEFAULT 'Inscrito',
    puntos_obtenidos INT NOT NULL DEFAULT 0,
    nota DECIMAL(2,1) NULL,
    UNIQUE KEY uq_act_usuario (id_actividad, id_usuario),
    CONSTRAINT fk_part_act FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad),
    CONSTRAINT fk_part_user FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evidencias (
    id_evidencia INT AUTO_INCREMENT PRIMARY KEY,
    id_actividad INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo ENUM('Imagen','Video','Documento','Otro') NOT NULL,
    archivo VARCHAR(255) NOT NULL,
    descripcion VARCHAR(500) NULL,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ev_act FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad),
    CONSTRAINT fk_ev_user FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS noticias (
    id_noticia INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255) NULL,
    fecha_publicacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado TINYINT NOT NULL DEFAULT 1,
    CONSTRAINT fk_not_user FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mensajes_contacto (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Nuevo','Leído','Respondido','Archivado') NOT NULL DEFAULT 'Nuevo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
