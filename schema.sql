-- =============================================================================
-- Base de Datos: alumnos
-- Esquema relacional según ER.xlsx
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `alumnos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `alumnos`;

-- 1. Tabla de Usuarios del Sistema
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `correo` VARCHAR(30) NOT NULL,
  `nombre` VARCHAR(35) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuario_correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Catálogo de Cursos
CREATE TABLE IF NOT EXISTS `cursos` (
  `id_curso` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_curso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla Principal de Alumnos
CREATE TABLE IF NOT EXISTS `alumno` (
  `id_alumno` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `nombres` VARCHAR(50) NOT NULL,
  `apellidos` VARCHAR(50) NOT NULL,
  `correo` VARCHAR(30) NOT NULL,
  `aficiones` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_alumno`),
  KEY `fk_alumno_usuario_idx` (`id_usuario`),
  CONSTRAINT `fk_alumno_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla Intermedia Alumno - Cursos
CREATE TABLE IF NOT EXISTS `alumno_cursos` (
  `id_alumno` INT NOT NULL,
  `id_curso` INT NOT NULL,
  `anio` INT NOT NULL,
  PRIMARY KEY (`id_alumno`, `id_curso`, `anio`),
  KEY `fk_ac_curso_idx` (`id_curso`),
  CONSTRAINT `fk_ac_alumno` FOREIGN KEY (`id_alumno`) REFERENCES `alumno` (`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ac_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- Datos de prueba iniciales
-- =============================================================================

-- Usuario por defecto (Credenciales: admin@correo.com / 12345)
INSERT INTO `usuario` (`id_usuario`, `correo`, `nombre`, `password`) VALUES
(1, 'admin@correo.com', 'Administrador General', '12345')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- Catálogo inicial de cursos IT
INSERT INTO `cursos` (`id_curso`, `nombre`) VALUES
(1, 'PHP y MariaDB'),
(2, 'JavaScript Moderno'),
(3, 'Desarrollo con Bootstrap 5'),
(4, 'Arquitectura de Software'),
(5, 'Seguridad Web y Ciberseguridad')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- Alumno de prueba inicial
INSERT INTO `alumno` (`id_alumno`, `id_usuario`, `nombres`, `apellidos`, `correo`, `aficiones`) VALUES
(1, 1, 'Carlos Andrés', 'Mendoza Soto', 'carlos.m@correo.com', 'Programación, Ajedrez, Robótica')
ON DUPLICATE KEY UPDATE `correo` = VALUES(`correo`);

-- Cursos asociados al alumno inicial
INSERT INTO `alumno_cursos` (`id_alumno`, `id_curso`, `anio`) VALUES
(1, 1, 2023),
(1, 2, 2024)
ON DUPLICATE KEY UPDATE `anio` = VALUES(`anio`);
