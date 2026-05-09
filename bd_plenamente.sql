-- ==========================================================
-- SCRIPT DE CREACIÓN DE BASE DE DATOS: PLENAMENTE - INFRAVENZ
-- Motor: MariaDB / MySQL
-- ==========================================================

-- 1. Crear la base de datos si no existe y usarla
CREATE DATABASE IF NOT EXISTS plenamente_bd
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE plenamente_bd;

-- ==========================================================
-- CREACIÓN DE TABLAS
-- ==========================================================

-- Tabla 1: ROLES
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Tabla 2: USUARIOS (Psicólogos, Directores, Administradores de IT)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (id_rol) 
        REFERENCES roles(id_rol) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla 3: PACIENTES (Estudiantes y Empleados)
CREATE TABLE pacientes (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    nie_dui VARCHAR(20) NOT NULL UNIQUE COMMENT 'NIE para estudiantes, DUI para empleados',
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    tipo_paciente ENUM('Estudiante', 'Empleado') NOT NULL,
    grado_seccion VARCHAR(50) DEFAULT NULL COMMENT 'Nulo si es empleado',
    telefono_contacto VARCHAR(15) DEFAULT NULL,
    correo_paciente VARCHAR(150) DEFAULT NULL,
    nombre_responsable VARCHAR(150) DEFAULT NULL COMMENT 'Importante para menores de edad'
) ENGINE=InnoDB;

-- Tabla 4: EXPEDIENTES (Relación 1 a 1 con Pacientes)
CREATE TABLE expedientes (
    id_expediente INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL UNIQUE, -- UNIQUE garantiza que un paciente solo tenga 1 expediente
    fecha_apertura DATE NOT NULL,
    antecedentes_familiares TEXT,
    antecedentes_medicos TEXT,
    CONSTRAINT fk_expedientes_pacientes FOREIGN KEY (id_paciente) 
        REFERENCES pacientes(id_paciente) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla 5: CITAS (La agenda)
CREATE TABLE citas (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_usuario_psicologo INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    motivo_cita VARCHAR(255) NOT NULL,
    estado_cita ENUM('Programada', 'Completada', 'Cancelada', 'Inasistencia') DEFAULT 'Programada',
    CONSTRAINT fk_citas_pacientes FOREIGN KEY (id_paciente) 
        REFERENCES pacientes(id_paciente) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_citas_usuarios FOREIGN KEY (id_usuario_psicologo) 
        REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla 6: SESIONES CLÍNICAS (Relación 1 a 1 con Citas, y N a 1 con Expedientes)
CREATE TABLE sesiones_clinicas (
    id_sesion INT AUTO_INCREMENT PRIMARY KEY,
    id_cita INT NOT NULL UNIQUE, -- UNIQUE garantiza que una cita solo genere una sesión
    id_expediente INT NOT NULL,
    observaciones_generales TEXT NOT NULL,
    intervencion_realizada TEXT NOT NULL,
    notas_evolucion TEXT NOT NULL,
    fecha_registro DATE NOT NULL,
    CONSTRAINT fk_sesiones_citas FOREIGN KEY (id_cita) 
        REFERENCES citas(id_cita) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_sesiones_expedientes FOREIGN KEY (id_expediente) 
        REFERENCES expedientes(id_expediente) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla 7: REPORTES (Documentos para Dirección)
CREATE TABLE reportes (
    id_reporte INT AUTO_INCREMENT PRIMARY KEY,
    id_expediente INT NOT NULL,
    id_usuario_autor INT NOT NULL,
    fecha_generacion DATE NOT NULL,
    contenido_tecnico TEXT NOT NULL,
    nivel_urgencia ENUM('Bajo', 'Medio', 'Alto') DEFAULT 'Bajo',
    CONSTRAINT fk_reportes_expedientes FOREIGN KEY (id_expediente) 
        REFERENCES expedientes(id_expediente) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reportes_usuarios FOREIGN KEY (id_usuario_autor) 
        REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ==========================================================
-- INSERCIÓN DE DATOS INICIALES (SEMILLAS / SEEDERS)
-- ==========================================================

-- 1. Insertar los 3 roles fundamentales del sistema
INSERT INTO roles (nombre_rol) VALUES 
('Operativo'),       -- ID 1: Psicólogos
('Directivo'),       -- ID 2: Dirección (Dashboard)
('Administrador');   -- ID 3: IT (Soporte Técnico)

-- 2. Insertar el primer usuario Administrador por defecto
INSERT INTO usuarios (id_rol, nombre_completo, correo, password_hash, estado) VALUES 
(3, 'Soporte Técnico IT', 'admin@infravenz.edu.sv', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Activo');