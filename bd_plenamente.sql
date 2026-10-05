-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-06-2026 a las 05:41:49
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `plenamente_bd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id_asistencia` int(11) NOT NULL,
  `id_expediente` int(11) NOT NULL,
  `fecha_asistencia` date NOT NULL,
  `estado` enum('Presente','Ausente','Tarde','Justificado') NOT NULL DEFAULT 'Presente',
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id_asistencia`, `id_expediente`, `fecha_asistencia`, `estado`, `observaciones`, `fecha_registro`) VALUES
(1, 1, '2026-06-05', 'Presente', NULL, '2026-06-06 05:29:41'),
(2, 1, '2026-06-07', 'Tarde', 'Retraso por trafico', '2026-06-07 03:34:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_usuario_psicologo` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `motivo_cita` varchar(255) NOT NULL,
  `estado_cita` enum('Programada','Completada','Cancelada','Inasistencia') DEFAULT 'Programada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_paciente`, `id_usuario_psicologo`, `fecha_hora`, `motivo_cita`, `estado_cita`) VALUES
(1, 1, 1, '2026-06-19 08:15:00', 'Control por proceso', 'Completada'),
(2, 1, 1, '2026-06-30 11:45:00', 'Seguimiento', 'Completada'),
(3, 1, 1, '2026-06-06 23:00:00', 'Seguimiento', 'Programada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `expedientes`
--

CREATE TABLE `expedientes` (
  `id_expediente` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `fecha_apertura` date NOT NULL,
  `antecedentes_familiares` text DEFAULT NULL,
  `antecedentes_medicos` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `expedientes`
--

INSERT INTO `expedientes` (`id_expediente`, `id_paciente`, `fecha_apertura`, `antecedentes_familiares`, `antecedentes_medicos`) VALUES
(1, 1, '2026-05-27', NULL, NULL),
(2, 2, '2026-06-07', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id_paciente` int(11) NOT NULL,
  `nie_dui` varchar(20) NOT NULL COMMENT 'NIE para estudiantes, DUI para empleados',
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` varchar(20) NOT NULL,
  `tipo_paciente` enum('Estudiante','Empleado') NOT NULL,
  `grado_seccion` varchar(50) DEFAULT NULL COMMENT 'Nulo si es empleado',
  `telefono_contacto` varchar(15) DEFAULT NULL,
  `correo_paciente` varchar(150) DEFAULT NULL,
  `nombre_responsable` varchar(150) DEFAULT NULL COMMENT 'Importante para menores de edad'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id_paciente`, `nie_dui`, `nombres`, `apellidos`, `fecha_nacimiento`, `genero`, `tipo_paciente`, `grado_seccion`, `telefono_contacto`, `correo_paciente`, `nombre_responsable`) VALUES
(1, '02385519-9', 'Alan Wilner', 'Moris Fermán', '1981-10-31', 'Masculino', 'Empleado', NULL, '7165-6315', 'awmoris@gmail.com', NULL),
(2, '09536252', 'Alan Abdiel', 'Moris Cruz', '2009-12-29', 'Masculino', 'Estudiante', '1DSA', '7165-6315', 'abmoris@gmail.com', 'Alan Wilner Moris Fermán'),
(3, '32658252-6', 'Josefina Sandra', 'López Calderón', '1972-12-12', 'Femenino', 'Empleado', NULL, '2641-5689', 'jslopez@infravenz.edu.sv', NULL),
(4, '23635256', 'María José', 'Mendieta Velásquez', '2009-12-29', 'Femenino', 'Estudiante', NULL, '2641-5689', '23635256@clases.edu.sv', 'Mario Alberto Mendieta Romero');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `id_expediente` int(11) NOT NULL,
  `id_usuario_autor` int(11) NOT NULL,
  `fecha_generacion` date NOT NULL,
  `contenido_tecnico` text NOT NULL,
  `nivel_urgencia` enum('Bajo','Medio','Alto') DEFAULT 'Bajo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Psicologo'),
(2, 'Director'),
(3, 'Administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_clinicas`
--

CREATE TABLE `sesiones_clinicas` (
  `id_sesion` int(11) NOT NULL,
  `id_cita` int(11) DEFAULT NULL,
  `id_expediente` int(11) NOT NULL,
  `tipo_consulta` varchar(50) NOT NULL DEFAULT 'Seguimiento',
  `observaciones_generales` text NOT NULL,
  `intervencion_realizada` text NOT NULL,
  `notas_evolucion` text NOT NULL,
  `fecha_registro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sesiones_clinicas`
--

INSERT INTO `sesiones_clinicas` (`id_sesion`, `id_cita`, `id_expediente`, `tipo_consulta`, `observaciones_generales`, `intervencion_realizada`, `notas_evolucion`, `fecha_registro`) VALUES
(2, NULL, 1, 'Seguimiento', 'sadjkshjksdhjdk shdfkjdsfhjdsfh sfhsjdkfhksjdfh', 'dsfsdfdsfsf fdgfgfdga fgdfg', 'HGHJghj mzsjhsadgjhsadhj kljsaskljdkj', '2026-05-31 10:30:00'),
(3, NULL, 1, 'Seguimiento', 'dfdfasdffssdf', 'dsfsdfdsfsdfsdf', 'asdfdssfsdfdsfdf', '2026-06-03 09:15:00'),
(4, 1, 1, 'Seguimiento', 'asfsfsdsfsdf', 'safsafasfasf', 'asfasfsafasfasff', '2026-06-03 14:20:00'),
(6, 2, 1, 'Seguimiento', 'fdsafdfsdsdfsadf', 'dsfgagdfgfg', 'agdfgdfgdfagfg', '2026-06-03 16:45:00'),
(7, NULL, 1, 'Intervención en Crisis', 'fasdagdfgadfg', 'adfgdfgfgafdgf', 'adfagdfagafdgaf', '2026-06-03 11:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_expedientes`
--

CREATE TABLE `historial_expedientes` (
  `id_historial` int(11) NOT NULL,
  `id_expediente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_cambio` varchar(50) NOT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `fecha_modificacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Estructura de tabla para la tabla `historial_sesiones`
--

CREATE TABLE `historial_sesiones` (
  `id_historial` int(11) NOT NULL,
  `id_sesion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `campo` varchar(50) NOT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `fecha_modificacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre_completo`, `correo`, `password_hash`, `estado`) VALUES
(1, 3, 'Alan Wilner Moris', 'admin@infravenz.edu.sv', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Activo'),
(2, 1, 'Esteban Antonio Bonilla Fuentes', 'eafuentes@clases.edu.sv', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Inactivo'),
(3, 1, 'Blanca Miriam Cruz', 'bmcruz@infravenz.edu.sv', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Activo'),
(4, 2, 'Carmen Antonio Flores García', 'amflores@infravenz.edu.sv', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Activo'),
(6, 2, 'Josefina Sandra López Rubio', 'sjlopez@infravenz.edu.sv', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Inactivo');

--
-- Estructura de tabla para la tabla `recuperaciones_password`
--

CREATE TABLE `recuperaciones_password` (
  `id_recuperacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `codigo_hash` varchar(255) NOT NULL,
  `expira_en` datetime NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 0,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD UNIQUE KEY `unica_asistencia_por_dia` (`id_expediente`,`fecha_asistencia`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `fk_citas_pacientes` (`id_paciente`),
  ADD KEY `fk_citas_usuarios` (`id_usuario_psicologo`);

--
-- Indices de la tabla `expedientes`
--
ALTER TABLE `expedientes`
  ADD PRIMARY KEY (`id_expediente`),
  ADD UNIQUE KEY `id_paciente` (`id_paciente`);

--
-- Indices de la tabla `historial_expedientes`
--
ALTER TABLE `historial_expedientes`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_historial_expedientes` (`id_expediente`),
  ADD KEY `fk_historial_usuarios` (`id_usuario`);

--
-- Indices de la tabla `historial_sesiones`
--
ALTER TABLE `historial_sesiones`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_historial_sesiones` (`id_sesion`),
  ADD KEY `fk_historial_sesiones_usuarios` (`id_usuario`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_paciente`),
  ADD UNIQUE KEY `nie_dui` (`nie_dui`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reportes_expedientes` (`id_expediente`),
  ADD KEY `fk_reportes_usuarios` (`id_usuario_autor`);

--
-- Indices de la tabla `recuperaciones_password`
--
ALTER TABLE `recuperaciones_password`
  ADD PRIMARY KEY (`id_recuperacion`),
  ADD KEY `fk_recuperaciones_usuarios` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `sesiones_clinicas`
--
ALTER TABLE `sesiones_clinicas`
  ADD PRIMARY KEY (`id_sesion`),
  ADD UNIQUE KEY `id_cita` (`id_cita`),
  ADD KEY `fk_sesiones_expedientes` (`id_expediente`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `fk_usuarios_roles` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `expedientes`
--
ALTER TABLE `expedientes`
  MODIFY `id_expediente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `historial_expedientes`
--
ALTER TABLE `historial_expedientes`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `historial_sesiones`
--
ALTER TABLE `historial_sesiones`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recuperaciones_password`
--
ALTER TABLE `recuperaciones_password`
  MODIFY `id_recuperacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sesiones_clinicas`
--
ALTER TABLE `sesiones_clinicas`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `fk_citas_pacientes` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_usuarios` FOREIGN KEY (`id_usuario_psicologo`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `expedientes`
--
ALTER TABLE `expedientes`
  ADD CONSTRAINT `fk_expedientes_pacientes` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_expedientes`
--
ALTER TABLE `historial_expedientes`
  ADD CONSTRAINT `fk_historial_expedientes` FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_sesiones`
--
ALTER TABLE `historial_sesiones`
  ADD CONSTRAINT `fk_historial_sesiones` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_clinicas` (`id_sesion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_sesiones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_reportes_expedientes` FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reportes_usuarios` FOREIGN KEY (`id_usuario_autor`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `recuperaciones_password`
--
ALTER TABLE `recuperaciones_password`
  ADD CONSTRAINT `fk_recuperaciones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones_clinicas`
--
ALTER TABLE `sesiones_clinicas`
  ADD CONSTRAINT `fk_sesiones_citas` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sesiones_expedientes` FOREIGN KEY (`id_expediente`) REFERENCES `expedientes` (`id_expediente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
