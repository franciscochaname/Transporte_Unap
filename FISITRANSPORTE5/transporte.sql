-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 15-07-2024 a las 21:29:45
-- Versión del servidor: 8.2.0
-- Versión de PHP: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `transporte`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

DROP TABLE IF EXISTS `horarios`;
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `nombre`, `hora_inicio`, `hora_fin`) VALUES
(2, 'Horario tarde', '14:18:00', '19:19:00'),
(3, 'hoaria alterno', '06:15:00', '08:15:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

DROP TABLE IF EXISTS `personal`;
CREATE TABLE IF NOT EXISTS `personal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `dni` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `rol` enum('mecanico','chofer') COLLATE utf8mb4_spanish_ci NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `vehiculo_id` int DEFAULT NULL,
  `horario_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`),
  KEY `vehiculo_id` (`vehiculo_id`),
  KEY `horario_id` (`horario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id`, `nombre`, `apellido`, `dni`, `rol`, `usuario`, `password`, `vehiculo_id`, `horario_id`) VALUES
(7, 'gerardo', 'pinedo', '4845496', 'chofer', NULL, NULL, 4, 2),
(8, 'francisco', 'chaname', '78787878', 'mecanico', 'francisco', 'francisco', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `ruc` varchar(11) COLLATE utf8mb4_spanish_ci NOT NULL,
  `razon_social` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `ruc`, `razon_social`, `telefono`, `direccion`) VALUES
(1, 'GRUPO JOA', '20585685485', 'JOA SAC', '965896589', 'Jr Samanecio Ocam´p'),
(2, 'GRUPO FELIX', '20589895665', 'FELIX SOCIEDAD ANONIMA', '98965652', 'Calle Navarro cauper 345');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte`
--

DROP TABLE IF EXISTS `reporte`;
CREATE TABLE IF NOT EXISTS `reporte` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mecanico_id` int DEFAULT NULL,
  `fecha_mantenimiento` date DEFAULT NULL,
  `tipo_servicio` int DEFAULT NULL,
  `vehiculo_id` int DEFAULT NULL,
  `repuestos` text COLLATE utf8mb4_spanish_ci,
  `sugerencias` text COLLATE utf8mb4_spanish_ci,
  `fecha_ingreso` date DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `nombre_mecanico` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mecanico_id` (`mecanico_id`),
  KEY `tipo_servicio` (`tipo_servicio`),
  KEY `vehiculo_id` (`vehiculo_id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `reporte`
--

INSERT INTO `reporte` (`id`, `mecanico_id`, `fecha_mantenimiento`, `tipo_servicio`, `vehiculo_id`, `repuestos`, `sugerencias`, `fecha_ingreso`, `estado`, `nombre_mecanico`) VALUES
(31, 3, '2024-07-13', 2, 7, '4, 6', '', '2024-07-17', 'Operativo', 'francisco chaname'),
(33, 2, '2024-07-25', 2, 8, '3, 6', '', '2024-07-18', 'Inoperativo', 'francisco chaname');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuestos`
--

DROP TABLE IF EXISTS `repuestos`;
CREATE TABLE IF NOT EXISTS `repuestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proveedor_id` int NOT NULL,
  `nombre_pieza` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `cantidad` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `proveedor_id` (`proveedor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `repuestos`
--

INSERT INTO `repuestos` (`id`, `proveedor_id`, `nombre_pieza`, `costo`, `cantidad`) VALUES
(3, 1, 'Bujia', 30.00, 2),
(4, 1, 'llanta', 120.00, 8),
(5, 1, 'filtro', 60.00, 5),
(6, 1, 'pastilla', 50.00, 73),
(8, 1, 'bobina', 20.00, 0),
(9, 1, 'elio', 20.00, 2),
(10, 1, 'camara', 50.00, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

DROP TABLE IF EXISTS `servicios`;
CREATE TABLE IF NOT EXISTS `servicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`) VALUES
(1, 'Cambio de aceite'),
(2, 'Bajada de motor'),
(3, 'Cambio de aceite'),
(4, 'Bajada de motor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_alquiler`
--

DROP TABLE IF EXISTS `solicitudes_alquiler`;
CREATE TABLE IF NOT EXISTS `solicitudes_alquiler` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruc` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `razon_social_o_nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `tiempo_alquiler` int NOT NULL,
  `visto` tinyint(1) DEFAULT '0',
  `fecha` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `solicitudes_alquiler`
--

INSERT INTO `solicitudes_alquiler` (`id`, `ruc`, `razon_social_o_nombre`, `direccion`, `telefono`, `tiempo_alquiler`, `visto`, `fecha`) VALUES
(5, '7817812', 'urtq2r', 'uefghiweug', '786786', 5, 1, '2000-06-21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transporte`
--

DROP TABLE IF EXISTS `transporte`;
CREATE TABLE IF NOT EXISTS `transporte` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `conductor` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `placa` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `rol` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `personal_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_personal` (`personal_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `rol`, `personal_id`) VALUES
(1, 'admin', '$2y$10$Wbg7bp.iuLB9RbfvAKa5Ue218gzTdRctm4cYeneeX7d8lT9EwWbvu', 'administrador', NULL),
(2, 'admin3', '$2y$10$4Ogpd/5veMElIqZabbeyfusiwNPlwBwbq0sGs47CuRUNxuw1UWasy', 'mecanico', 3),
(3, 'jorge', '$2y$10$RonNGWSAHBfeCON8gNjPiuQUZsnXsYtdXEGdzuwvc3t/WupxOE8VK', 'mecanico', 6),
(4, 'admin', '$2y$10$GLYVieRrhmONkyyhCSF1gub7jZl0zz02C8Jk6ePGN1J7.qaJ8.vpS', 'administrador', NULL),
(5, 'admin', '$2y$10$ZwCCRcxau6W5HC0ficcM2e9MB1WXOoY75vIsYxbuL2zEV9dShbVt2', 'administrador', NULL),
(6, 'Chaname', '$2y$10$U.zGwXw7D1ZnIdDcKLHTJeY8/5rGgjsvCthIJexIFkdBA/r//vEwO', 'administrador', NULL),
(7, 'Chaname', '$2y$10$KJ2LRYkGRZsBrWuePMzBSe8T.b00zokmafSNoDagouDexrBVJmIXy', 'administrador', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

DROP TABLE IF EXISTS `vehiculos`;
CREATE TABLE IF NOT EXISTS `vehiculos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marca` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `placa` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `ruta` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `horario` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id`, `marca`, `placa`, `ruta`, `horario`) VALUES
(8, 'Mercedes Benz', 'FT-123', 'Ruta especial', 'Horario tarde'),
(7, 'Hyundai', 'RM-321', 'Plaza Ponce-Rectorado', 'Horario tarde'),
(11, 'Hyundai', 'FRM-444', 'Plaza Ponce-Rectorado', 'hoaria alterno');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
