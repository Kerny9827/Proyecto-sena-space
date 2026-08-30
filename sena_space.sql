-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-05-2026 a las 02:56:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sena_space`
--
CREATE DATABASE IF NOT EXISTS `sena_space` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sena_space`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acceso`
--

CREATE TABLE `acceso` (
  `id_acceso` int(11) NOT NULL,
  `cedula` int(11) DEFAULT NULL,
  `rol` varchar(50) DEFAULT NULL,
  `contraseña` varchar(100) DEFAULT NULL,
  `fecha_ingreso_ambiente` date DEFAULT NULL,
  `fecha_ingreso_complejo` date DEFAULT NULL,
  `fecha_salida_complejo` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Aprendiz'),
(2, 'Instructor'),
(3, 'Seguridad'),
(4, 'Cafetería'),
(5, 'Visitante');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `cedula` int(11) NOT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `tipo_usuario` varchar(50) DEFAULT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL,
  `contraseña` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `admin`
--

INSERT INTO `admin` (`cedula`, `correo`, `nombre`, `tipo_usuario`, `rol_id`, `tipo_documento`, `contraseña`) VALUES
(12345698, 'kd@gmail.com', 'Kevin Jaramillo', 'Aprendiz', 1, 'Cedula', '0'),
(12398745, 'iv@gmail.com', 'Ivan cepeda', 'Visitante', 5, 'Cedula', '0'),
(123456989, 'tbmz@gmail.com', 'Sebas bedoya', 'Aprendiz', 1, 'Cedula', '0'),
(135832168, 'dd@gmail.com', 'Dani D', 'Varios', NULL, 'Cedula', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ambiente`
--

CREATE TABLE `ambiente` (
  `id_ambiente` int(11) NOT NULL,
  `id_acceso` int(11) DEFAULT NULL,
  `cedula` int(11) DEFAULT NULL,
  `piso` int(11) DEFAULT NULL,
  `bloque` varchar(50) DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `Hora_notificacion` date DEFAULT NULL,
  `cedula` int(11) DEFAULT NULL,
  `descripcion` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ranking`
--

CREATE TABLE `ranking` (
  `id_ranking` int(11) NOT NULL,
  `cedula` int(11) DEFAULT NULL,
  `calculo` float DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rap`
--

CREATE TABLE `rap` (
  `rap` varchar(100) DEFAULT NULL,
  `cedula` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_usuario`
--

CREATE TABLE `tipo_usuario` (
  `id_tipo_usuario` int(11) NOT NULL,
  `rol` varchar(50) DEFAULT NULL,
  `cedula` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acceso`
--
ALTER TABLE `acceso`
  ADD PRIMARY KEY (`id_acceso`),
  ADD KEY `cedula` (`cedula`);

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`cedula`),
  ADD KEY `idx_admin_rol_id` (`rol_id`);

--
-- Indices de la tabla `roles`
--

ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_roles_nombre` (`nombre`);

--
-- Indices de la tabla `ambiente`
--
ALTER TABLE `ambiente`
  ADD PRIMARY KEY (`id_ambiente`),
  ADD KEY `id_acceso` (`id_acceso`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_notificacion_admin` (`cedula`);

--
-- Indices de la tabla `ranking`
--
ALTER TABLE `ranking`
  ADD PRIMARY KEY (`id_ranking`),
  ADD KEY `cedula` (`cedula`);

--
-- Indices de la tabla `rap`
--
ALTER TABLE `rap`
  ADD KEY `cedula` (`cedula`);

--
-- Indices de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`id_tipo_usuario`),
  ADD KEY `cedula` (`cedula`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acceso`
--
ALTER TABLE `acceso`
  MODIFY `id_acceso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ambiente`
--
ALTER TABLE `ambiente`
  MODIFY `id_ambiente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `admin`
--

ALTER TABLE `admin`
  ADD CONSTRAINT `fk_admin_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT;

--
-- Filtros para la tabla `acceso`
--
ALTER TABLE `acceso`
  ADD CONSTRAINT `acceso_ibfk_1` FOREIGN KEY (`cedula`) REFERENCES `admin` (`cedula`);

--
-- Filtros para la tabla `ambiente`
--
ALTER TABLE `ambiente`
  ADD CONSTRAINT `ambiente_ibfk_1` FOREIGN KEY (`id_acceso`) REFERENCES `acceso` (`id_acceso`);

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `fk_notificacion_admin` FOREIGN KEY (`cedula`) REFERENCES `admin` (`cedula`);

--
-- Filtros para la tabla `ranking`
--
ALTER TABLE `ranking`
  ADD CONSTRAINT `ranking_ibfk_1` FOREIGN KEY (`cedula`) REFERENCES `rap` (`cedula`);

--
-- Filtros para la tabla `rap`
--
ALTER TABLE `rap`
  ADD CONSTRAINT `rap_ibfk_1` FOREIGN KEY (`cedula`) REFERENCES `admin` (`cedula`);

--
-- Filtros para la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD CONSTRAINT `tipo_usuario_ibfk_1` FOREIGN KEY (`cedula`) REFERENCES `rap` (`cedula`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================================
-- SISTEMA NUMÉRICO DE ROLES
-- ============================================================
-- Los roles disponibles quedan centralizados en la tabla `roles`.
--
-- 1 = Aprendiz
-- 2 = Instructor
-- 3 = Seguridad
-- 4 = Cafetería
-- 5 = Visitante
--
-- La tabla `admin`.`rol_id` referencia `roles`.`id`.
-- `tipo_usuario` se conserva para mantener compatibilidad con el
-- código existente del proyecto.
-- ============================================================

-- ============================================================
-- MÓDULO INTEGRADO DE RECURSOS / PRÉSTAMOS
-- Añadido al proyecto LOG-IN
-- ============================================================
USE sena_space;
ALTER TABLE admin ADD COLUMN IF NOT EXISTS rol_sistema VARCHAR(30) NOT NULL DEFAULT 'Usuario';
UPDATE admin SET rol_sistema='Administrador' WHERE correo='kd@gmail.com';

CREATE TABLE IF NOT EXISTS recursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  categoria VARCHAR(50) NOT NULL,
  stock_total INT NOT NULL DEFAULT 1,
  estado ENUM('Disponible','Mantenimiento','Baja') NOT NULL DEFAULT 'Disponible',
  stock_minimo INT NOT NULL DEFAULT 1,
  tipo ENUM('Objeto','Servicio') NOT NULL DEFAULT 'Objeto',
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_recursos_estado (estado), INDEX idx_recursos_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS solicitudes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  recurso_id INT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_devolucion DATE NOT NULL,
  cantidad INT NOT NULL DEFAULT 1,
  estado ENUM('Pendiente','Aprobado','Rechazado','Devuelto') NOT NULL DEFAULT 'Pendiente',
  observacion TEXT,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_aprobacion DATETIME NULL,
  fecha_devolucion_real DATETIME NULL,
  devolucion_solicitada TINYINT(1) NOT NULL DEFAULT 0,
  INDEX idx_solicitudes_usuario (usuario_id), INDEX idx_solicitudes_recurso (recurso_id), INDEX idx_solicitudes_estado (estado),
  CONSTRAINT fk_solicitudes_usuario FOREIGN KEY (usuario_id) REFERENCES admin(cedula) ON UPDATE CASCADE,
  CONSTRAINT fk_solicitudes_recurso FOREIGN KEY (recurso_id) REFERENCES recursos(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reposiciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recurso_id INT NOT NULL,
  cantidad_solicitada INT NOT NULL,
  estado ENUM('Solicitada','Atendida','Cancelada') NOT NULL DEFAULT 'Solicitada',
  observacion TEXT,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reposiciones_recurso FOREIGN KEY (recurso_id) REFERENCES recursos(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS historial_recursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  accion VARCHAR(80) NOT NULL,
  detalle TEXT,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_historial_fecha (fecha),
  CONSTRAINT fk_historial_usuario FOREIGN KEY (usuario_id) REFERENCES admin(cedula) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO recursos (nombre,categoria,stock_total,estado,stock_minimo,tipo)
SELECT 'Proyector Epson EB-X05','Audiovisual',3,'Disponible',1,'Objeto' WHERE NOT EXISTS (SELECT 1 FROM recursos WHERE nombre='Proyector Epson EB-X05');
INSERT INTO recursos (nombre,categoria,stock_total,estado,stock_minimo,tipo)
SELECT 'Laptop Dell Latitude','Cómputo',5,'Disponible',2,'Objeto' WHERE NOT EXISTS (SELECT 1 FROM recursos WHERE nombre='Laptop Dell Latitude');
INSERT INTO recursos (nombre,categoria,stock_total,estado,stock_minimo,tipo)
SELECT 'Cámara Canon EOS','Fotografía',2,'Disponible',1,'Objeto' WHERE NOT EXISTS (SELECT 1 FROM recursos WHERE nombre='Cámara Canon EOS');
INSERT INTO recursos (nombre,categoria,stock_total,estado,stock_minimo,tipo)
SELECT 'Cables HDMI','Accesorios',8,'Disponible',3,'Objeto' WHERE NOT EXISTS (SELECT 1 FROM recursos WHERE nombre='Cables HDMI');
INSERT INTO recursos (nombre,categoria,stock_total,estado,stock_minimo,tipo)
SELECT 'Servicio de soporte audiovisual','Servicios',1,'Disponible',1,'Servicio' WHERE NOT EXISTS (SELECT 1 FROM recursos WHERE nombre='Servicio de soporte audiovisual');
