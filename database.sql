-- ============================================================
-- Sistema de Control y Mantenimiento de CPUs y Laptops
-- Base de Datos: diagnostico_cpus
-- ============================================================

CREATE DATABASE IF NOT EXISTS `diagnostico_cpus` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

USE `diagnostico_cpus`;

-- ------------------------------------------------------------
-- 1. Tabla: usuarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(120) NOT NULL,
  `usuario` VARCHAR(60) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `rol` VARCHAR(30) NOT NULL DEFAULT 'admin',
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  UNIQUE KEY `uk_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Usuario inicial administrador por defecto (Clave: Admin123)
INSERT INTO `usuarios` (`nombre`, `usuario`, `password`, `rol`, `activo`, `created_at`)
VALUES ('Administrador', 'admin', '$2y$10$H2KKDNLvX/CzGkJZhzstlu8yDRBs3CIjNYfOeqc2oy7Gqj61Ja4Za', 'admin', 1, NOW())
ON DUPLICATE KEY UPDATE `activo` = 1;

-- ------------------------------------------------------------
-- 2. Tabla: inventario_general
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inventario_general` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `identificador_1` VARCHAR(100) NULL,
  `identificador_2` VARCHAR(100) NULL,
  `ref_principal` VARCHAR(150) NULL,
  `descripcion` VARCHAR(255) NULL,
  `zona_origen` VARCHAR(100) NULL,
  `ubicacion_origen` VARCHAR(150) NULL,
  `verificado` VARCHAR(50) NULL,
  `observaciones` TEXT NULL,
  `placa_id` VARCHAR(100) NULL,
  `serial` VARCHAR(100) NULL,
  `tipo_equipo` VARCHAR(80) NULL,
  `marca` VARCHAR(100) NULL,
  `modelo` VARCHAR(150) NULL,
  `ubicacion` VARCHAR(150) NULL,
  `estado` VARCHAR(80) NULL,
  `datos_adicionales` TEXT NULL,
  `archivo_origen` VARCHAR(255) NULL,
  `usuario_cargue` VARCHAR(120) NULL,
  `intervenido` TINYINT(1) DEFAULT 0,
  `fecha_intervencion` DATETIME NULL,
  `modulo_intervencion` VARCHAR(50) NULL,
  `analista_intervencion` VARCHAR(120) NULL,
  `created_at` DATETIME NULL,
  KEY `idx_id1` (`identificador_1`),
  KEY `idx_id2` (`identificador_2`),
  KEY `idx_placa` (`placa_id`),
  KEY `idx_serial` (`serial`),
  KEY `idx_intervenido` (`intervenido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 3. Tabla: equipos (Diagnóstico de CPUs / Escritorio)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `equipos` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre_analista` VARCHAR(120) NULL,
  `num_traslado` VARCHAR(100) NULL,
  `placa_id` VARCHAR(100) NULL,
  `tipo_gestion` VARCHAR(100) NULL,
  `energiza` VARCHAR(10) NULL,
  `da_video` VARCHAR(10) NULL,
  `estado_actual` VARCHAR(150) NULL,
  `que_va_intervenir` VARCHAR(150) NULL,
  `origen_pieza` VARCHAR(150) NULL,
  `serial_disco` VARCHAR(120) NULL,
  `descripcion_novedad` TEXT NULL,
  `motivo_baja` VARCHAR(255) NULL,
  `ubicacion_destino` VARCHAR(150) NULL,
  `foto_equipo` TEXT NULL,
  `fecha_creacion` DATETIME NULL,
  KEY `idx_equipos_placa` (`placa_id`),
  KEY `idx_equipos_traslado` (`num_traslado`),
  KEY `idx_equipos_analista` (`nombre_analista`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 4. Tabla: soplado_registros (Mantenimiento y Soplado)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `soplado_registros` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre_analista` VARCHAR(120) NULL,
  `num_traslado` VARCHAR(100) NULL,
  `placa_id` VARCHAR(100) NULL,
  `energiza` VARCHAR(10) NULL,
  `da_video` VARCHAR(10) NULL,
  `detecta_disco` VARCHAR(10) NULL,
  `ingreso_bios` VARCHAR(10) NULL,
  `pasta_termica` VARCHAR(10) NULL,
  `maquina_contenia` VARCHAR(255) NULL,
  `gel_cucarachas` VARCHAR(10) NULL,
  `foto_ruta` TEXT NULL,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_soplado_placa` (`placa_id`),
  KEY `idx_soplado_traslado` (`num_traslado`),
  KEY `idx_soplado_analista` (`nombre_analista`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 5. Tabla: garantias_portatiles (Laptops y Garantías)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `garantias_portatiles` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre_analista` VARCHAR(120) NULL,
  `numero_traslado` VARCHAR(100) NULL,
  `placa_id_equipo` VARCHAR(100) NULL,
  `tipo_gestion` VARCHAR(100) NULL,
  `energiza` VARCHAR(10) NULL,
  `da_video` VARCHAR(10) NULL,
  `realizo_test_lenovo` VARCHAR(10) NULL,
  `estado_actual_equipo` VARCHAR(150) NULL,
  `diagnostico_laptop_intervenido` TEXT NULL,
  `garantia` VARCHAR(20) NULL,
  `porque_solicita_garantia` TEXT NULL,
  `numero_ticket` VARCHAR(100) NULL,
  `estado_final_equipo` VARCHAR(150) NULL,
  `indique_pieza` VARCHAR(150) NULL,
  `indique_fru` VARCHAR(150) NULL,
  `pieza_intervenida` VARCHAR(150) NULL,
  `origen_pieza` VARCHAR(150) NULL,
  `motivo_baja` VARCHAR(255) NULL,
  `serial_disco` VARCHAR(120) NULL,
  `foto_ruta` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_portatiles_placa` (`placa_id_equipo`),
  KEY `idx_portatiles_traslado` (`numero_traslado`),
  KEY `idx_portatiles_ticket` (`numero_ticket`),
  KEY `idx_portatiles_analista` (`nombre_analista`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
