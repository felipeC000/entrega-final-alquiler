-- ============================================================
-- AutoRent - Sistema de Alquiler de Vehículos
-- Base de datos: alquiler_carros
-- Motor: InnoDB | Charset: utf8mb4_general_ci
-- Servidor: MariaDB 10.4.32 (XAMPP)
-- ============================================================

-- Crear y seleccionar la base de datos
CREATE DATABASE IF NOT EXISTS `alquiler_carros`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE `alquiler_carros`;

-- ── TABLA: usuarios ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`               INT(11)      NOT NULL AUTO_INCREMENT,
  `nombre`           VARCHAR(100) NOT NULL,
  `correo`           VARCHAR(100) NOT NULL,
  `contrasena`       VARCHAR(255) NOT NULL COMMENT 'Hash SHA-256',
  `rol`              ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
  `fecha_registro`   TIMESTAMP    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── TABLA: vehiculos ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vehiculos` (
  `id`          INT(11)       NOT NULL AUTO_INCREMENT,
  `placa`       VARCHAR(10)   NOT NULL,
  `marca`       VARCHAR(50)   NOT NULL,
  `modelo`      VARCHAR(50)   NOT NULL,
  `anio`        INT(11)       NOT NULL,
  `color`       VARCHAR(30)   DEFAULT NULL,
  `precio_dia`  DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `placa` (`placa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── TABLA: reservas ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reservas` (
  `usuario_id`   INT(11) NOT NULL,
  `vehiculo_id`  INT(11) NOT NULL,
  `fecha_inicio` DATE    NOT NULL,
  `fecha_fin`    DATE    NOT NULL,
  PRIMARY KEY (`usuario_id`, `vehiculo_id`, `fecha_inicio`),
  CONSTRAINT `fk_reservas_usuario`
    FOREIGN KEY (`usuario_id`)  REFERENCES `usuarios`  (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reservas_vehiculo`
    FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── TABLA: pagos ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pagos` (
  `id`           INT(11)       NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT(11)       NOT NULL,
  `vehiculo_id`  INT(11)       NOT NULL,
  `fecha_inicio` DATE          NOT NULL,
  `monto`        DECIMAL(10,2) NOT NULL,
  `metodo_pago`  ENUM('tarjeta','efectivo','transferencia') NOT NULL,
  `fecha_pago`   TIMESTAMP     NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pagos_reserva`
    FOREIGN KEY (`usuario_id`, `vehiculo_id`, `fecha_inicio`)
    REFERENCES `reservas` (`usuario_id`, `vehiculo_id`, `fecha_inicio`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── DATOS DE PRUEBA ──────────────────────────────────────────
-- Contraseñas: hash SHA-256 de "123456"
INSERT INTO `usuarios` (`nombre`, `correo`, `contrasena`, `rol`) VALUES
  ('Samuel', 'samuel@gmail.com', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'admin'),
  ('Felipe', 'felipe@gmail.com', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'cliente');

INSERT INTO `vehiculos` (`placa`, `marca`, `modelo`, `anio`, `color`, `precio_dia`) VALUES
  ('ABC123', 'Toyota', 'Corolla', 2022, 'Blanco',  150000.00),
  ('ABC124', 'BMW',    'X6',      2024, 'Negro',   350000.00),
  ('ABC125', 'Mazda',  'CX5',     2023, 'Rojo',    200000.00);

INSERT INTO `reservas` (`usuario_id`, `vehiculo_id`, `fecha_inicio`, `fecha_fin`) VALUES
  (1, 1, '2026-04-20', '2026-04-25');

INSERT INTO `pagos` (`usuario_id`, `vehiculo_id`, `fecha_inicio`, `monto`, `metodo_pago`) VALUES
  (1, 1, '2026-04-20', 750000.00, 'tarjeta');
