-- ============================================================
-- Migración: Factura impresa + SIFEN + Marangatu
-- Base de datos: solver_db_17022026
-- Ejecutar en phpMyAdmin o consola MySQL (una sola vez).
-- ============================================================

USE solver_db_17022026;

-- ------------------------------------------------------------
-- 1. Tabla empresa (datos del negocio emisor)
-- Una sola fila. Para factura impresa y envío a SIFEN.
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `empresa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) NOT NULL,
  `ruc` varchar(15) NOT NULL,
  `dv` char(1) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `timbrado_vigente` varchar(8) DEFAULT NULL,
  `fecha_desde_timbrado` date DEFAULT NULL,
  `fecha_hasta_timbrado` date DEFAULT NULL,
  `punto_emision` varchar(5) DEFAULT '001',
  `sucursal` varchar(5) DEFAULT '001',
  `actividad_economica` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `actualizado_el` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Fila inicial (podés editarla después con tus datos reales)
INSERT INTO `empresa` (
  `id`, `razon_social`, `ruc`, `dv`, `direccion`, `telefono`, `email`,
  `timbrado_vigente`, `fecha_desde_timbrado`, `fecha_hasta_timbrado`,
  `punto_emision`, `sucursal`, `estado`
) VALUES (
  1,
  'Mi Negocio S.A.',
  '80012345',
  '6',
  'Av. Ejemplo 123, Asunción',
  '',
  '',
  NULL,
  NULL,
  NULL,
  '001',
  '001',
  1
) ON DUPLICATE KEY UPDATE `id` = `id`;

-- ------------------------------------------------------------
-- 2. Alter table pos_ventas_cabecera (timbrado + SIFEN)
-- ------------------------------------------------------------

ALTER TABLE `pos_ventas_cabecera`
  ADD COLUMN `timbrado` varchar(8) DEFAULT NULL COMMENT 'Timbrado con el que se emitió la factura (SET)' AFTER `nro_factura`,
  ADD COLUMN `punto_emision` varchar(5) DEFAULT NULL COMMENT 'Ej: 001 en formato 001-001-0000001' AFTER `timbrado`,
  ADD COLUMN `cdc` varchar(50) DEFAULT NULL COMMENT 'Código de Control SIFEN (documento electrónico)' AFTER `punto_emision`,
  ADD COLUMN `estado_sifen` tinyint(1) DEFAULT 0 COMMENT '0=sin enviar, 1=enviado, 2=aceptado, 3=rechazado' AFTER `cdc`,
  ADD COLUMN `kude_path` varchar(255) DEFAULT NULL COMMENT 'Ruta al archivo KuDE si se guarda en servidor' AFTER `estado_sifen`;

-- Las ventas ya existentes quedarán con timbrado NULL hasta que las uses
-- solo para consulta; las nuevas ventas deberán enviar timbrado desde el POS.

-- ============================================================
-- Fin de la migración
-- ============================================================
