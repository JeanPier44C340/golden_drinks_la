-- ============================================================================
-- GOLDENSYS / GOLDENDRINKS
-- Base de datos integral para la gestion digital de bodega
-- Sistema:  GoldenSys
-- Cliente:  GoldenDrinks  (Campoalegre, Huila, Colombia)
-- Autor:    Jean Pier Andres Calderon Rico
-- Version:  4.0  -- Sincronizada con SRS/CU/HU (RF-01..RF-25, RN-01..RN-16)
-- Motor:    MySQL 8.x / MariaDB 10.4+
-- ----------------------------------------------------------------------------
-- Cobertura funcional:
--   * Recepcion de vehiculos con registro de LLEGADA y SALIDA (RF-01, RF-23)
--   * Vinculacion de proveedor (RF-02)
--   * Descarga con buenas/danadas + perdidas (RF-03, RF-04)
--   * Inventario en tiempo real via triggers (RF-05, RF-06)
--   * Despachos con descuento a la salida (RF-07)
--   * Alertas de stock bajo automaticas (RF-08)
--   * Reportes / Dashboard (RF-09, RF-11, RF-12)
--   * Seguridad y roles + auditoria de sesiones (RF-10)
--   * Portal proveedor: ordenes, estados, danos, facturacion (RF-13..RF-17)
--   * Portal vendedor: catalogo, pedidos, comprobante de pago,
--     seguimiento, reclamos, historial (RF-18..RF-22, RF-24)
--   * Confirmacion de entrega con evidencia fotografica (RF-25)
--   * Trazabilidad total via movimientos_inventario + auditoria (RN-08, RNF-09)
-- ============================================================================

DROP DATABASE IF EXISTS `goldensys_db`;
CREATE DATABASE `goldensys_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `goldensys_db`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_ALL_TABLES,NO_ENGINE_SUBSTITUTION';

-- ============================================================================
-- SECCION 1 — SEGURIDAD Y ACTORES
-- ============================================================================

-- Roles del sistema (RF-10) ---------------------------------------------------
CREATE TABLE `roles` (
  `id`          INT PRIMARY KEY AUTO_INCREMENT,
  `nombre`      VARCHAR(50)  NOT NULL UNIQUE,
  `descripcion` VARCHAR(180) NOT NULL,
  `activo`      BOOLEAN      NOT NULL DEFAULT TRUE,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Roles operativos internos: administrador, celador, bodeguero, repartidor';

-- Usuarios internos: celador, bodeguero, repartidor, administrador (RF-10) -----
CREATE TABLE `usuarios` (
  `id`               INT PRIMARY KEY AUTO_INCREMENT,
  `nombre_completo`  VARCHAR(120) NOT NULL,
  `correo`           VARCHAR(150) NOT NULL UNIQUE,
  `password_hash`    VARCHAR(255) NOT NULL,                 -- bcrypt (RNF-02)
  `telefono`         VARCHAR(30),
  `rol_id`           INT          NOT NULL,
  `estado`           ENUM('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  `ultimo_acceso`    DATETIME     NULL,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_usuarios_roles`
    FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Cuentas internas del sistema (HU-SEC-024, HU-ADM-011)';

-- Proveedores: portal externo de abastecimiento (RF-13..RF-17) -----------------
CREATE TABLE `proveedores` (
  `id`            INT PRIMARY KEY AUTO_INCREMENT,
  `nit`           VARCHAR(30)  NOT NULL UNIQUE,
  `nombre`        VARCHAR(150) NOT NULL,
  `correo`        VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `telefono`      VARCHAR(30),
  `direccion`     VARCHAR(180),
  `estado`        ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Proveedores que entregan mercancia a la bodega';

-- Vendedores externos: portal comercial (RF-18..RF-22, RF-24) ------------------
CREATE TABLE `vendedores` (
  `id`              INT PRIMARY KEY AUTO_INCREMENT,
  `empresa`         VARCHAR(150) NOT NULL,
  `nombre_contacto` VARCHAR(120) NOT NULL,
  `correo`          VARCHAR(150) NOT NULL UNIQUE,
  `password_hash`   VARCHAR(255) NOT NULL,
  `telefono`        VARCHAR(30),
  `estado`          ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Vendedores externos que realizan pedidos comerciales';

-- ============================================================================
-- SECCION 2 — CATALOGOS E INVENTARIO
-- ============================================================================

-- Categorias de producto ------------------------------------------------------
CREATE TABLE `categorias_producto` (
  `id`          INT PRIMARY KEY AUTO_INCREMENT,
  `nombre`      VARCHAR(80)  NOT NULL UNIQUE,
  `descripcion` VARCHAR(180)
) ENGINE=InnoDB COMMENT='Categorias de licores: vino, ron, aguardiente, etc.';

-- Productos (RF-18) -----------------------------------------------------------
CREATE TABLE `productos` (
  `id`                  INT PRIMARY KEY AUTO_INCREMENT,
  `codigo`              VARCHAR(40)  NOT NULL UNIQUE,
  `nombre`              VARCHAR(150) NOT NULL,
  `categoria_id`        INT          NOT NULL,
  `unidad_medida`       VARCHAR(20)  NOT NULL DEFAULT 'botella',
  `precio_compra`       DECIMAL(12,2) NOT NULL DEFAULT 0 CHECK (`precio_compra` >= 0),
  `precio_distribucion` DECIMAL(12,2) NOT NULL DEFAULT 0 CHECK (`precio_distribucion` >= 0),
  `stock_minimo`        INT          NOT NULL DEFAULT 10 CHECK (`stock_minimo` >= 0),
  `activo`              BOOLEAN      NOT NULL DEFAULT TRUE,
  `created_at`          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_productos_categoria`
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias_producto`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Catalogo maestro de productos';

-- Inventario en tiempo real (RF-05, RF-06) ------------------------------------
-- Una fila por producto; el stock lo mantienen los triggers automaticamente.
CREATE TABLE `inventario` (
  `id`                   INT PRIMARY KEY AUTO_INCREMENT,
  `producto_id`          INT       NOT NULL UNIQUE,
  `stock_actual`         INT       NOT NULL DEFAULT 0 CHECK (`stock_actual` >= 0),
  `ultima_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_inventario_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Existencias actuales por producto (espejo en tiempo real)';

-- ============================================================================
-- SECCION 3 — RECEPCION Y ABASTECIMIENTO
-- ============================================================================

-- Vehiculos (RF-01) -----------------------------------------------------------
CREATE TABLE `vehiculos` (
  `id`                      INT PRIMARY KEY AUTO_INCREMENT,
  `placa`                   VARCHAR(20)  NOT NULL UNIQUE,
  `conductor`               VARCHAR(120) NOT NULL,
  `tipo_vehiculo`           VARCHAR(50),
  `capacidad_cajas`         INT NULL CHECK (`capacidad_cajas` IS NULL OR `capacidad_cajas` >= 0),
  `estado`                  ENUM('disponible','en_mantenimiento','inactivo') NOT NULL DEFAULT 'disponible',
  `observaciones`           VARCHAR(180),
  `registrado_por_admin_id` INT NULL,
  `created_at`              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_vehiculos_admin`
    FOREIGN KEY (`registrado_por_admin_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Vehiculos que ingresan a la bodega';

-- Ordenes de entrega programadas por el proveedor (RF-15, RF-16) --------------
CREATE TABLE `ordenes_entrega` (
  `id`             INT PRIMARY KEY AUTO_INCREMENT,
  `proveedor_id`   INT          NOT NULL,
  `codigo_orden`   VARCHAR(40)  NOT NULL UNIQUE,
  `fecha_estimada` DATETIME     NOT NULL,
  `estado`         ENUM('programada','en_proceso','recibida','cancelada') NOT NULL DEFAULT 'programada',
  `observaciones`  TEXT,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ordenes_entrega_proveedor`
    FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Programacion anticipada de entregas (RN-10: 24h)';

CREATE TABLE `detalle_orden_entrega` (
  `id`                  INT PRIMARY KEY AUTO_INCREMENT,
  `orden_entrega_id`    INT NOT NULL,
  `producto_id`         INT NOT NULL,
  `cantidad_programada` INT NOT NULL CHECK (`cantidad_programada` > 0),
  CONSTRAINT `fk_detalle_orden_orden`
    FOREIGN KEY (`orden_entrega_id`) REFERENCES `ordenes_entrega`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_orden_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `uq_orden_producto` UNIQUE (`orden_entrega_id`,`producto_id`)
) ENGINE=InnoDB COMMENT='Items programados de cada orden de entrega';

-- Recepciones: LLEGADA y SALIDA de vehiculos (RF-01, RF-02, RF-23) ------------
-- Incluye los campos de salida del audio (RF-23 / RN-14).
CREATE TABLE `recepciones` (
  `id`               INT PRIMARY KEY AUTO_INCREMENT,
  `orden_entrega_id` INT NULL,
  `vehiculo_id`      INT NOT NULL,
  `proveedor_id`     INT NOT NULL,
  `celador_id`       INT NOT NULL,                          -- registra la llegada
  `codigo_recepcion` VARCHAR(40)  NOT NULL UNIQUE,
  `hora_llegada`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- Registro de salida (RF-23): se diligencia cuando el vehiculo abandona la bodega
  `hora_salida`            DATETIME NULL,
  `celador_salida_id`      INT NULL,                         -- registra la salida
  `salida_observaciones`   VARCHAR(180) NULL,
  `valor_flete`      DECIMAL(12,2) NOT NULL DEFAULT 0 CHECK (`valor_flete` >= 0),
  `observaciones`    TEXT,
  `estado`           ENUM('pendiente','descargada','salida','cancelada') NOT NULL DEFAULT 'pendiente',
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_recepcion_orden`
    FOREIGN KEY (`orden_entrega_id`) REFERENCES `ordenes_entrega`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_recepcion_vehiculo`
    FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_recepcion_proveedor`
    FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_recepcion_celador`
    FOREIGN KEY (`celador_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_recepcion_celador_salida`
    FOREIGN KEY (`celador_salida_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Recepcion con ciclo completo llegada->descarga->salida';

-- Descargas confirmadas por el bodeguero (RF-03) ------------------------------
CREATE TABLE `descargas` (
  `id`            INT PRIMARY KEY AUTO_INCREMENT,
  `recepcion_id`  INT NOT NULL UNIQUE,
  `bodeguero_id`  INT NOT NULL,
  `confirmada_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observaciones` TEXT,
  CONSTRAINT `fk_descarga_recepcion`
    FOREIGN KEY (`recepcion_id`) REFERENCES `recepciones`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_descarga_bodeguero`
    FOREIGN KEY (`bodeguero_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Confirmacion de descarga por recepcion (HU-BOD-003)';

CREATE TABLE `detalle_descarga` (
  `id`                INT PRIMARY KEY AUTO_INCREMENT,
  `descarga_id`       INT NOT NULL,
  `producto_id`       INT NOT NULL,
  `cantidad_recibida` INT NOT NULL DEFAULT 0 CHECK (`cantidad_recibida` >= 0),
  `cantidad_danada`   INT NOT NULL DEFAULT 0 CHECK (`cantidad_danada`   >= 0),
  `motivo_dano`       VARCHAR(180),
  CONSTRAINT `fk_detalle_descarga_descarga`
    FOREIGN KEY (`descarga_id`) REFERENCES `descargas`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_descarga_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  -- RN-04: lo danado nunca supera lo recibido
  CONSTRAINT `chk_danado_no_supera` CHECK (`cantidad_danada` <= `cantidad_recibida`),
  CONSTRAINT `uq_descarga_producto` UNIQUE (`descarga_id`,`producto_id`)
) ENGINE=InnoDB COMMENT='Cantidades buenas/danadas por producto en la descarga';

-- Perdidas por productos danados (RF-04) --------------------------------------
CREATE TABLE `perdidas` (
  `id`            INT PRIMARY KEY AUTO_INCREMENT,
  `recepcion_id`  INT NULL,
  `producto_id`   INT NOT NULL,
  `bodeguero_id`  INT NULL,
  `origen`        ENUM('descarga','bodega') NOT NULL DEFAULT 'descarga',
  `cantidad`      INT NOT NULL CHECK (`cantidad` > 0),
  `motivo`        VARCHAR(180) NOT NULL,
  `evidencia_url` VARCHAR(255),                              -- foto del dano (HU-BOD-027)
  `registrada_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_perdida_recepcion`
    FOREIGN KEY (`recepcion_id`) REFERENCES `recepciones`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_perdida_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_perdida_bodeguero`
    FOREIGN KEY (`bodeguero_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Registro trazable de productos danados (descarga o bodega)';

-- ============================================================================
-- SECCION 4 — TRAZABILIDAD Y ALERTAS (transversal)
-- ============================================================================

-- Libro mayor de inventario: TODO movimiento queda aqui (RN-08, RNF-09) -------
CREATE TABLE `movimientos_inventario` (
  `id`               INT PRIMARY KEY AUTO_INCREMENT,
  `producto_id`      INT NOT NULL,
  `tipo_movimiento`  ENUM('entrada','salida','perdida','ajuste') NOT NULL,
  `origen_tipo`      ENUM('descarga','despacho','perdida','entrega','manual') NOT NULL,
  `origen_id`        INT NOT NULL,
  `cantidad`         INT NOT NULL CHECK (`cantidad` <> 0),
  `saldo_anterior`   INT NOT NULL,
  `saldo_resultante` INT NOT NULL,
  `actor_usuario_id` INT NULL,
  `nota`             VARCHAR(180),
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_mov_inventario_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_mov_inventario_actor`
    FOREIGN KEY (`actor_usuario_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Kardex inmutable: cada entrada/salida/perdida con saldo';

-- Alertas de stock bajo automaticas (RF-08) -----------------------------------
CREATE TABLE `alertas_stock` (
  `id`              INT PRIMARY KEY AUTO_INCREMENT,
  `producto_id`     INT NOT NULL,
  `stock_detectado` INT NOT NULL,
  `stock_minimo`    INT NOT NULL,
  `estado`          ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  `abierta_en`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cerrada_en`      DATETIME NULL,
  CONSTRAINT `fk_alerta_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Alertas generadas/cerradas automaticamente por triggers';

-- ============================================================================
-- SECCION 5 — COMERCIAL: PEDIDOS, PAGO, DESPACHO, ENTREGA, RECLAMOS
-- ============================================================================

-- Pedidos de vendedores externos (RF-19, RF-24) -------------------------------
-- pago_estado controla la regla RN-15 (no se aprueba/despacha sin pago verificado)
CREATE TABLE `pedidos` (
  `id`                  INT PRIMARY KEY AUTO_INCREMENT,
  `vendedor_id`         INT NOT NULL,
  `codigo_pedido`       VARCHAR(40) NOT NULL UNIQUE,
  `fecha_pedido`        DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado`              ENUM('en_revision','aprobado','rechazado','despachado','entregado') NOT NULL DEFAULT 'en_revision',
  -- Comprobante de pago (RF-24 / RN-15)
  `pago_estado`         ENUM('pendiente','verificado','rechazado') NOT NULL DEFAULT 'pendiente',
  `pago_verificado_por` INT NULL,                            -- admin que verifica
  `pago_verificado_en`  DATETIME NULL,
  `motivo_rechazo`      VARCHAR(255),
  `admin_aprobador_id`  INT NULL,
  `repartidor_id`       INT NULL,
  `valor_total`         DECIMAL(12,2) NOT NULL DEFAULT 0 CHECK (`valor_total` >= 0),
  `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pedido_vendedor`
    FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_pedido_admin`
    FOREIGN KEY (`admin_aprobador_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_pedido_pago_verif`
    FOREIGN KEY (`pago_verificado_por`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_pedido_repartidor`
    FOREIGN KEY (`repartidor_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Pedidos comerciales con control de pago (RF-19, RF-24)';

CREATE TABLE `detalle_pedido` (
  `id`                  INT PRIMARY KEY AUTO_INCREMENT,
  `pedido_id`           INT NOT NULL,
  `producto_id`         INT NOT NULL,
  `cantidad_solicitada` INT NOT NULL CHECK (`cantidad_solicitada` > 0),
  `precio_unitario`     DECIMAL(12,2) NOT NULL CHECK (`precio_unitario` >= 0),
  `subtotal`            DECIMAL(12,2) NOT NULL CHECK (`subtotal` >= 0),
  CONSTRAINT `fk_detalle_pedido_pedido`
    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_pedido_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `uq_pedido_producto` UNIQUE (`pedido_id`,`producto_id`)
) ENGINE=InnoDB COMMENT='Items de cada pedido comercial';

-- Comprobantes de pago del pedido (RF-24 / nueva tabla) -----------------------
CREATE TABLE `pedido_pago_archivos` (
  `id`            INT PRIMARY KEY AUTO_INCREMENT,
  `pedido_id`     INT NOT NULL,
  `archivo_url`   VARCHAR(255) NOT NULL,
  `tipo_archivo`  VARCHAR(50)  NOT NULL DEFAULT 'comprobante_pago',
  `monto`         DECIMAL(12,2) NULL CHECK (`monto` IS NULL OR `monto` >= 0),
  `referencia`    VARCHAR(120) NULL,                         -- nro de transaccion / consignacion
  `subido_en`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pago_pedido`
    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Evidencias de pago adjuntadas por el vendedor (HU-VEN-031)';

-- Despachos de mercancia a repartidores (RF-07) -------------------------------
CREATE TABLE `despachos` (
  `id`              INT PRIMARY KEY AUTO_INCREMENT,
  `pedido_id`       INT NULL,
  `admin_id`        INT NOT NULL,
  `repartidor_id`   INT NOT NULL,
  `codigo_despacho` VARCHAR(40) NOT NULL UNIQUE,
  `estado`          ENUM('creado','en_camino','entregado','cancelado') NOT NULL DEFAULT 'creado',
  `despachado_en`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `entregado_en`    DATETIME NULL,
  `motivo_cancelacion` VARCHAR(180) NULL,
  `observaciones`   TEXT,
  CONSTRAINT `fk_despacho_pedido`
    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_despacho_admin`
    FOREIGN KEY (`admin_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_despacho_repartidor`
    FOREIGN KEY (`repartidor_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Salida de mercancia asignada a un repartidor (HU-ADM-028)';

CREATE TABLE `detalle_despacho` (
  `id`           INT PRIMARY KEY AUTO_INCREMENT,
  `despacho_id`  INT NOT NULL,
  `producto_id`  INT NOT NULL,
  `cantidad`     INT NOT NULL CHECK (`cantidad` > 0),
  CONSTRAINT `fk_detalle_despacho_despacho`
    FOREIGN KEY (`despacho_id`) REFERENCES `despachos`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_despacho_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `uq_despacho_producto` UNIQUE (`despacho_id`,`producto_id`)
) ENGINE=InnoDB COMMENT='Items despachados; descuentan inventario via trigger';

-- Evidencia fotografica de entrega (RF-25 / nueva tabla) ----------------------
CREATE TABLE `despacho_entrega_archivos` (
  `id`            INT PRIMARY KEY AUTO_INCREMENT,
  `despacho_id`   INT NOT NULL,
  `repartidor_id` INT NOT NULL,
  `archivo_url`   VARCHAR(255) NOT NULL,
  `tipo_archivo`  VARCHAR(50)  NOT NULL DEFAULT 'foto_entrega',
  `latitud`       DECIMAL(10,7) NULL,
  `longitud`      DECIMAL(10,7) NULL,
  `entregado_en`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_entrega_despacho`
    FOREIGN KEY (`despacho_id`) REFERENCES `despachos`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_entrega_repartidor`
    FOREIGN KEY (`repartidor_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Foto que confirma la entrega del pedido (HU-DES-032)';

-- Reclamos sobre pedidos (RF-21) ----------------------------------------------
CREATE TABLE `reclamos` (
  `id`                INT PRIMARY KEY AUTO_INCREMENT,
  `pedido_id`         INT NOT NULL,
  `vendedor_id`       INT NOT NULL,
  `descripcion`       TEXT NOT NULL,
  `cantidad_afectada` INT NOT NULL DEFAULT 0 CHECK (`cantidad_afectada` >= 0),
  `estado`            ENUM('abierto','en_revision','resuelto') NOT NULL DEFAULT 'abierto',
  `respuesta_admin`   TEXT,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reclamo_pedido`
    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_reclamo_vendedor`
    FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores`(`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Reclamos del vendedor sobre pedidos despachados (HU-VEN-022)';

CREATE TABLE `pedido_reclamo_archivos` (
  `id`           INT PRIMARY KEY AUTO_INCREMENT,
  `reclamo_id`   INT NOT NULL,
  `archivo_url`  VARCHAR(255) NOT NULL,
  `tipo_archivo` VARCHAR(50)  NOT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reclamo_archivo`
    FOREIGN KEY (`reclamo_id`) REFERENCES `reclamos`(`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Evidencias adjuntas a un reclamo';

-- ============================================================================
-- SECCION 6 — REPORTES, NOTIFICACIONES Y AUDITORIA
-- ============================================================================

-- Reportes PDF generados (RF-09, RF-17, RF-22) --------------------------------
CREATE TABLE `reportes_generados` (
  `id`                   INT PRIMARY KEY AUTO_INCREMENT,
  `tipo_reporte`         ENUM('informe_mensual','facturacion_proveedor','historial_vendedor','inventario') NOT NULL,
  `periodo_desde`        DATE NOT NULL,
  `periodo_hasta`        DATE NOT NULL,
  `usuario_generador_id` INT NULL,
  `proveedor_id`         INT NULL,
  `vendedor_id`          INT NULL,
  `ruta_archivo`         VARCHAR(255) NOT NULL,
  `created_at`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reporte_usuario`
    FOREIGN KEY (`usuario_generador_id`) REFERENCES `usuarios`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_reporte_proveedor`
    FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_reporte_vendedor`
    FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores`(`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `chk_periodo` CHECK (`periodo_hasta` >= `periodo_desde`)
) ENGINE=InnoDB COMMENT='Reportes PDF almacenados para descarga (RN-13)';

-- Notificaciones a usuarios, proveedores y vendedores (RF-14, RF-16, etc.) ----
CREATE TABLE `notificaciones` (
  `id`                INT PRIMARY KEY AUTO_INCREMENT,
  `destinatario_tipo` ENUM('usuario','proveedor','vendedor') NOT NULL,
  `destinatario_id`   INT NOT NULL,
  `canal`             ENUM('portal','correo') NOT NULL DEFAULT 'portal',
  `tipo_evento`       VARCHAR(60)  NOT NULL,
  `titulo`            VARCHAR(150) NOT NULL,
  `mensaje`           TEXT NOT NULL,
  `referencia_tipo`   VARCHAR(40) NULL,                      -- p.ej. 'recepcion','pedido','despacho'
  `referencia_id`     INT NULL,
  `leida`             BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Bandeja de notificaciones por actor';

-- Auditoria de sesiones / accesos (RF-10, RNF-09) -----------------------------
CREATE TABLE `sesiones_auditoria` (
  `id`          INT PRIMARY KEY AUTO_INCREMENT,
  `usuario_tipo`  ENUM('usuario','proveedor','vendedor') NOT NULL,
  `usuario_id`  INT NOT NULL,
  `accion`      ENUM('login','logout') NOT NULL DEFAULT 'login',
  `ip_origen`   VARCHAR(45) NOT NULL,
  `navegador`   VARCHAR(180),
  `resultado`   ENUM('exitoso','fallido') NOT NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Trazabilidad de inicios y cierres de sesion';

SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================================
-- SECCION 7 — INDICES DE RENDIMIENTO (RNF-03)
-- ============================================================================
CREATE INDEX `idx_usuario_estado`            ON `usuarios` (`estado`);
CREATE INDEX `idx_producto_categoria`        ON `productos` (`categoria_id`);
CREATE INDEX `idx_producto_activo`           ON `productos` (`activo`);
CREATE INDEX `idx_vehiculo_estado`           ON `vehiculos` (`estado`);
CREATE INDEX `idx_vehiculo_admin`            ON `vehiculos` (`registrado_por_admin_id`);
CREATE INDEX `idx_recepcion_fecha`           ON `recepciones` (`hora_llegada`);
CREATE INDEX `idx_recepcion_salida`          ON `recepciones` (`hora_salida`);
CREATE INDEX `idx_recepcion_estado`          ON `recepciones` (`estado`);
CREATE INDEX `idx_recepcion_proveedor`       ON `recepciones` (`proveedor_id`);
CREATE INDEX `idx_orden_proveedor_fecha`     ON `ordenes_entrega` (`proveedor_id`,`fecha_estimada`);
CREATE INDEX `idx_movimiento_producto_fecha` ON `movimientos_inventario` (`producto_id`,`created_at`);
CREATE INDEX `idx_movimiento_origen`         ON `movimientos_inventario` (`origen_tipo`,`origen_id`);
CREATE INDEX `idx_alerta_estado`             ON `alertas_stock` (`estado`);
CREATE INDEX `idx_alerta_producto`           ON `alertas_stock` (`producto_id`,`estado`);
CREATE INDEX `idx_pedido_estado`             ON `pedidos` (`estado`);
CREATE INDEX `idx_pedido_pago`               ON `pedidos` (`pago_estado`);
CREATE INDEX `idx_pedido_vendedor`           ON `pedidos` (`vendedor_id`);
CREATE INDEX `idx_despacho_estado`           ON `despachos` (`estado`);
CREATE INDEX `idx_despacho_repartidor`       ON `despachos` (`repartidor_id`);
CREATE INDEX `idx_reclamo_estado`            ON `reclamos` (`estado`);
CREATE INDEX `idx_perdida_producto`          ON `perdidas` (`producto_id`,`registrada_en`);
CREATE INDEX `idx_notif_destinatario`        ON `notificaciones` (`destinatario_tipo`,`destinatario_id`,`leida`);
CREATE INDEX `idx_sesion_usuario`            ON `sesiones_auditoria` (`usuario_tipo`,`usuario_id`,`created_at`);

-- ============================================================================
-- SECCION 8 — TRIGGERS: INVENTARIO Y TRAZABILIDAD AUTOMATICA
-- Hacen que el inventario "funcione solo" y que todo quede registrado.
-- ============================================================================
DELIMITER $$

-- (8.0) Al crear un producto, garantizar su fila de inventario en 0 -----------
CREATE TRIGGER `trg_producto_after_insert`
AFTER INSERT ON `productos`
FOR EACH ROW
BEGIN
  INSERT INTO `inventario` (`producto_id`, `stock_actual`)
  VALUES (NEW.`id`, 0)
  ON DUPLICATE KEY UPDATE `producto_id` = `producto_id`;
END$$

-- ----------------------------------------------------------------------------
-- (8.1) ENTRADA por descarga confirmada (RF-05 / RN-05)
--   Suma cantidad buena (recibida - danada) al inventario y deja kardex.
--   Si hay danadas, registra automaticamente la perdida (RF-04).
-- ----------------------------------------------------------------------------
CREATE TRIGGER `trg_detalle_descarga_after_insert`
AFTER INSERT ON `detalle_descarga`
FOR EACH ROW
BEGIN
  DECLARE v_buenas      INT;
  DECLARE v_saldo_ant   INT;
  DECLARE v_saldo_new   INT;
  DECLARE v_recepcion   INT;
  DECLARE v_bodeguero   INT;

  SET v_buenas = NEW.`cantidad_recibida` - NEW.`cantidad_danada`;

  -- datos de contexto (recepcion y bodeguero) desde la descarga
  SELECT d.`recepcion_id`, d.`bodeguero_id`
    INTO v_recepcion, v_bodeguero
    FROM `descargas` d
   WHERE d.`id` = NEW.`descarga_id`;

  -- saldo actual del producto
  SELECT `stock_actual` INTO v_saldo_ant
    FROM `inventario` WHERE `producto_id` = NEW.`producto_id` FOR UPDATE;
  IF v_saldo_ant IS NULL THEN
    INSERT INTO `inventario` (`producto_id`,`stock_actual`) VALUES (NEW.`producto_id`,0);
    SET v_saldo_ant = 0;
  END IF;

  -- 1) ENTRADA de unidades buenas
  IF v_buenas > 0 THEN
    SET v_saldo_new = v_saldo_ant + v_buenas;
    UPDATE `inventario` SET `stock_actual` = v_saldo_new
     WHERE `producto_id` = NEW.`producto_id`;

    INSERT INTO `movimientos_inventario`
      (`producto_id`,`tipo_movimiento`,`origen_tipo`,`origen_id`,
       `cantidad`,`saldo_anterior`,`saldo_resultante`,`actor_usuario_id`,`nota`)
    VALUES
      (NEW.`producto_id`,'entrada','descarga',NEW.`descarga_id`,
       v_buenas, v_saldo_ant, v_saldo_new, v_bodeguero, 'Entrada por descarga confirmada');

    SET v_saldo_ant = v_saldo_new;  -- por si tambien hay perdida luego
  END IF;

  -- 2) PERDIDA automatica por unidades danadas (RF-04)
  IF NEW.`cantidad_danada` > 0 THEN
    INSERT INTO `perdidas`
      (`recepcion_id`,`producto_id`,`bodeguero_id`,`origen`,`cantidad`,`motivo`)
    VALUES
      (v_recepcion, NEW.`producto_id`, v_bodeguero, 'descarga',
       NEW.`cantidad_danada`, COALESCE(NEW.`motivo_dano`,'Producto danado en descarga'));
    -- El kardex de la perdida lo registra trg_perdida_after_insert (no afecta stock
    -- porque las danadas nunca entraron al inventario; se deja como movimiento 'perdida' informativo de saldo 0).
  END IF;
END$$

-- ----------------------------------------------------------------------------
-- (8.2) SALIDA por despacho (RF-07 / RN-05 / RN-06)
--   Descuenta del inventario cuando la mercancia sale de la bodega.
--   Valida stock suficiente (RN-06) antes de descontar.
-- ----------------------------------------------------------------------------
CREATE TRIGGER `trg_detalle_despacho_after_insert`
AFTER INSERT ON `detalle_despacho`
FOR EACH ROW
BEGIN
  DECLARE v_saldo_ant INT;
  DECLARE v_saldo_new INT;
  DECLARE v_admin     INT;
  DECLARE v_msg       VARCHAR(160);

  SELECT `stock_actual` INTO v_saldo_ant
    FROM `inventario` WHERE `producto_id` = NEW.`producto_id` FOR UPDATE;

  IF v_saldo_ant IS NULL THEN SET v_saldo_ant = 0; END IF;

  -- RN-06: no se puede despachar mas de lo disponible
  IF NEW.`cantidad` > v_saldo_ant THEN
    SET v_msg = CONCAT('Stock insuficiente para producto ', NEW.`producto_id`,
                       ': disponible ', v_saldo_ant, ', solicitado ', NEW.`cantidad`);
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
  END IF;

  SELECT `admin_id` INTO v_admin FROM `despachos` WHERE `id` = NEW.`despacho_id`;

  SET v_saldo_new = v_saldo_ant - NEW.`cantidad`;
  UPDATE `inventario` SET `stock_actual` = v_saldo_new
   WHERE `producto_id` = NEW.`producto_id`;

  INSERT INTO `movimientos_inventario`
    (`producto_id`,`tipo_movimiento`,`origen_tipo`,`origen_id`,
     `cantidad`,`saldo_anterior`,`saldo_resultante`,`actor_usuario_id`,`nota`)
  VALUES
    (NEW.`producto_id`,'salida','despacho',NEW.`despacho_id`,
     -NEW.`cantidad`, v_saldo_ant, v_saldo_new, v_admin, 'Salida por despacho a repartidor');
END$$

-- ----------------------------------------------------------------------------
-- (8.3) PERDIDA registrada en BODEGA (RF-04 / HU-BOD-027)
--   Cuando el origen es 'bodega', descuenta del inventario (el producto si estaba).
--   Cuando el origen es 'descarga', solo deja kardex informativo (no estaba en stock).
-- ----------------------------------------------------------------------------
CREATE TRIGGER `trg_perdida_after_insert`
AFTER INSERT ON `perdidas`
FOR EACH ROW
BEGIN
  DECLARE v_saldo_ant INT;
  DECLARE v_saldo_new INT;

  SELECT `stock_actual` INTO v_saldo_ant
    FROM `inventario` WHERE `producto_id` = NEW.`producto_id` FOR UPDATE;
  IF v_saldo_ant IS NULL THEN SET v_saldo_ant = 0; END IF;

  IF NEW.`origen` = 'bodega' THEN
    -- descuenta del inventario existente
    SET v_saldo_new = GREATEST(v_saldo_ant - NEW.`cantidad`, 0);
    UPDATE `inventario` SET `stock_actual` = v_saldo_new
     WHERE `producto_id` = NEW.`producto_id`;

    INSERT INTO `movimientos_inventario`
      (`producto_id`,`tipo_movimiento`,`origen_tipo`,`origen_id`,
       `cantidad`,`saldo_anterior`,`saldo_resultante`,`actor_usuario_id`,`nota`)
    VALUES
      (NEW.`producto_id`,'perdida','perdida',NEW.`id`,
       -NEW.`cantidad`, v_saldo_ant, v_saldo_new, NEW.`bodeguero_id`,
       CONCAT('Perdida en bodega: ', NEW.`motivo`));
  ELSE
    -- origen 'descarga': las danadas nunca ingresaron; kardex informativo
    INSERT INTO `movimientos_inventario`
      (`producto_id`,`tipo_movimiento`,`origen_tipo`,`origen_id`,
       `cantidad`,`saldo_anterior`,`saldo_resultante`,`actor_usuario_id`,`nota`)
    VALUES
      (NEW.`producto_id`,'perdida','descarga',NEW.`id`,
       -NEW.`cantidad`, v_saldo_ant, v_saldo_ant, NEW.`bodeguero_id`,
       CONCAT('Perdida en descarga (no ingreso a stock): ', NEW.`motivo`));
  END IF;
END$$

-- ----------------------------------------------------------------------------
-- (8.4) ALERTAS de stock bajo automaticas (RF-08 / RN-09)
--   Tras cualquier cambio de inventario abre o cierra la alerta del producto.
-- ----------------------------------------------------------------------------
CREATE TRIGGER `trg_inventario_after_update`
AFTER UPDATE ON `inventario`
FOR EACH ROW
BEGIN
  DECLARE v_min        INT;
  DECLARE v_abierta_id INT;

  SELECT `stock_minimo` INTO v_min FROM `productos` WHERE `id` = NEW.`producto_id`;
  IF v_min IS NULL THEN SET v_min = 0; END IF;

  SELECT `id` INTO v_abierta_id
    FROM `alertas_stock`
   WHERE `producto_id` = NEW.`producto_id` AND `estado` = 'abierta'
   LIMIT 1;

  IF NEW.`stock_actual` < v_min THEN
    -- abrir alerta si no hay una abierta
    IF v_abierta_id IS NULL THEN
      INSERT INTO `alertas_stock` (`producto_id`,`stock_detectado`,`stock_minimo`,`estado`)
      VALUES (NEW.`producto_id`, NEW.`stock_actual`, v_min, 'abierta');
    END IF;
  ELSE
    -- cerrar alerta si el stock se normalizo
    IF v_abierta_id IS NOT NULL THEN
      UPDATE `alertas_stock`
         SET `estado` = 'cerrada', `cerrada_en` = NOW()
       WHERE `id` = v_abierta_id;
    END IF;
  END IF;
END$$

-- ----------------------------------------------------------------------------
-- (8.5) Calculo automatico de subtotal del detalle de pedido -----------------
CREATE TRIGGER `trg_detalle_pedido_before_insert`
BEFORE INSERT ON `detalle_pedido`
FOR EACH ROW
BEGIN
  SET NEW.`subtotal` = NEW.`cantidad_solicitada` * NEW.`precio_unitario`;
END$$

-- (8.6) Recalcular valor_total del pedido al insertar/actualizar/borrar item --
CREATE TRIGGER `trg_detalle_pedido_after_insert`
AFTER INSERT ON `detalle_pedido`
FOR EACH ROW
BEGIN
  UPDATE `pedidos`
     SET `valor_total` = (SELECT COALESCE(SUM(`subtotal`),0)
                            FROM `detalle_pedido` WHERE `pedido_id` = NEW.`pedido_id`)
   WHERE `id` = NEW.`pedido_id`;
END$$

CREATE TRIGGER `trg_detalle_pedido_after_update`
AFTER UPDATE ON `detalle_pedido`
FOR EACH ROW
BEGIN
  UPDATE `pedidos`
     SET `valor_total` = (SELECT COALESCE(SUM(`subtotal`),0)
                            FROM `detalle_pedido` WHERE `pedido_id` = NEW.`pedido_id`)
   WHERE `id` = NEW.`pedido_id`;
END$$

CREATE TRIGGER `trg_detalle_pedido_after_delete`
AFTER DELETE ON `detalle_pedido`
FOR EACH ROW
BEGIN
  UPDATE `pedidos`
     SET `valor_total` = (SELECT COALESCE(SUM(`subtotal`),0)
                            FROM `detalle_pedido` WHERE `pedido_id` = OLD.`pedido_id`)
   WHERE `id` = OLD.`pedido_id`;
END$$

-- ----------------------------------------------------------------------------
-- (8.7) REGLA RN-15: no aprobar pedido sin pago verificado (RF-24) -----------
CREATE TRIGGER `trg_pedido_before_update`
BEFORE UPDATE ON `pedidos`
FOR EACH ROW
BEGIN
  -- Bloquear aprobar/despachar/entregar si el pago no esta verificado
  IF NEW.`estado` IN ('aprobado','despachado','entregado')
     AND OLD.`estado` = 'en_revision'
     AND NEW.`pago_estado` <> 'verificado' THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'No se puede aprobar/despachar el pedido sin comprobante de pago verificado (RN-15)';
  END IF;

  -- Sellar fecha de verificacion de pago automaticamente
  IF NEW.`pago_estado` = 'verificado' AND OLD.`pago_estado` <> 'verificado' THEN
    SET NEW.`pago_verificado_en` = NOW();
  END IF;
END$$

-- ----------------------------------------------------------------------------
-- (8.8) ENTREGA con evidencia (RF-25 / RN-16) --------------------------------
--   Al subir la foto de entrega, marca el despacho como entregado y, si tiene
--   pedido asociado, pasa el pedido a 'entregado'.
-- ----------------------------------------------------------------------------
CREATE TRIGGER `trg_entrega_after_insert`
AFTER INSERT ON `despacho_entrega_archivos`
FOR EACH ROW
BEGIN
  DECLARE v_pedido INT;

  UPDATE `despachos`
     SET `estado` = 'entregado', `entregado_en` = NOW()
   WHERE `id` = NEW.`despacho_id`;

  SELECT `pedido_id` INTO v_pedido FROM `despachos` WHERE `id` = NEW.`despacho_id`;
  IF v_pedido IS NOT NULL THEN
    UPDATE `pedidos` SET `estado` = 'entregado' WHERE `id` = v_pedido;
  END IF;
END$$

-- ----------------------------------------------------------------------------
-- (8.9) SALIDA de vehiculo (RF-23 / RN-14) -----------------------------------
--   Cuando se diligencia hora_salida, el estado de la recepcion pasa a 'salida'.
-- ----------------------------------------------------------------------------
CREATE TRIGGER `trg_recepcion_before_update`
BEFORE UPDATE ON `recepciones`
FOR EACH ROW
BEGIN
  IF NEW.`hora_salida` IS NOT NULL AND OLD.`hora_salida` IS NULL THEN
    SET NEW.`estado` = 'salida';
  END IF;
END$$

DELIMITER ;

-- ============================================================================
-- SECCION 8B — PROCEDIMIENTO DE SINCRONIZACION DE ALERTAS (RF-08)
-- Recalcula todas las alertas segun el stock real. Util al cargar datos
-- iniciales o como tarea de mantenimiento; el backend puede invocarlo.
-- ============================================================================
DELIMITER $$
CREATE PROCEDURE `sp_sincronizar_alertas_stock`()
BEGIN
  -- Abrir alertas faltantes para productos bajo el minimo
  INSERT INTO `alertas_stock` (`producto_id`,`stock_detectado`,`stock_minimo`,`estado`)
  SELECT p.`id`, i.`stock_actual`, p.`stock_minimo`, 'abierta'
    FROM `productos` p
    JOIN `inventario` i ON i.`producto_id` = p.`id`
   WHERE p.`activo` = TRUE
     AND i.`stock_actual` < p.`stock_minimo`
     AND NOT EXISTS (
       SELECT 1 FROM `alertas_stock` a
        WHERE a.`producto_id` = p.`id` AND a.`estado` = 'abierta');

  -- Cerrar alertas abiertas cuyo stock ya se normalizo
  UPDATE `alertas_stock` a
    JOIN `inventario` i ON i.`producto_id` = a.`producto_id`
    JOIN `productos`  p ON p.`id` = a.`producto_id`
     SET a.`estado` = 'cerrada', a.`cerrada_en` = NOW()
   WHERE a.`estado` = 'abierta'
     AND i.`stock_actual` >= p.`stock_minimo`;
END$$
DELIMITER ;

-- Dashboard operativo del administrador (RF-12) -------------------------------
CREATE OR REPLACE VIEW `vw_dashboard_operativo` AS
SELECT
  (SELECT COUNT(*) FROM `productos` WHERE `activo` = TRUE) AS total_productos_activos,
  (SELECT COALESCE(SUM(`stock_actual`),0) FROM `inventario`) AS stock_total,
  (SELECT COUNT(*) FROM `recepciones`
     WHERE MONTH(`hora_llegada`) = MONTH(CURRENT_DATE())
       AND YEAR(`hora_llegada`)  = YEAR(CURRENT_DATE())) AS recepciones_mes,
  (SELECT COUNT(*) FROM `recepciones`
     WHERE `hora_salida` IS NOT NULL
       AND MONTH(`hora_salida`) = MONTH(CURRENT_DATE())
       AND YEAR(`hora_salida`)  = YEAR(CURRENT_DATE())) AS salidas_vehiculos_mes,
  (SELECT COUNT(*) FROM `despachos`
     WHERE MONTH(`despachado_en`) = MONTH(CURRENT_DATE())
       AND YEAR(`despachado_en`)  = YEAR(CURRENT_DATE())) AS despachos_mes,
  (SELECT COALESCE(SUM(`cantidad`),0) FROM `perdidas`
     WHERE MONTH(`registrada_en`) = MONTH(CURRENT_DATE())
       AND YEAR(`registrada_en`)  = YEAR(CURRENT_DATE())) AS perdidas_mes,
  (SELECT COUNT(*) FROM `alertas_stock` WHERE `estado` = 'abierta') AS alertas_activas,
  (SELECT COUNT(*) FROM `pedidos` WHERE `estado` = 'en_revision') AS pedidos_en_revision,
  (SELECT COUNT(*) FROM `reclamos` WHERE `estado` <> 'resuelto')  AS reclamos_abiertos;

-- Inventario con estado visual (RF-06) ----------------------------------------
CREATE OR REPLACE VIEW `vw_inventario_estado` AS
SELECT
  p.`id`            AS producto_id,
  p.`codigo`,
  p.`nombre`,
  c.`nombre`        AS categoria,
  i.`stock_actual`,
  p.`stock_minimo`,
  CASE
    WHEN i.`stock_actual` = 0                     THEN 'Agotado'
    WHEN i.`stock_actual` < p.`stock_minimo`      THEN 'Stock Bajo'
    ELSE 'Disponible'
  END               AS estado_visual,
  i.`ultima_actualizacion`
FROM `productos` p
JOIN `inventario` i ON i.`producto_id` = p.`id`
JOIN `categorias_producto` c ON c.`id` = p.`categoria_id`
WHERE p.`activo` = TRUE;

-- Producto estrella del mes (mayor volumen despachado) (RF-09, RF-12) ---------
CREATE OR REPLACE VIEW `vw_producto_estrella_mes` AS
SELECT
  p.`id` AS producto_id, p.`nombre`,
  SUM(dd.`cantidad`) AS unidades_despachadas
FROM `detalle_despacho` dd
JOIN `despachos` d ON d.`id` = dd.`despacho_id`
JOIN `productos`  p ON p.`id` = dd.`producto_id`
WHERE MONTH(d.`despachado_en`) = MONTH(CURRENT_DATE())
  AND YEAR(d.`despachado_en`)  = YEAR(CURRENT_DATE())
GROUP BY p.`id`, p.`nombre`
ORDER BY unidades_despachadas DESC;

-- Ciclo de permanencia de vehiculos: llegada vs salida (RF-23) ----------------
CREATE OR REPLACE VIEW `vw_ciclo_vehiculos` AS
SELECT
  r.`id` AS recepcion_id,
  r.`codigo_recepcion`,
  v.`placa`,
  v.`conductor`,
  pr.`nombre` AS proveedor,
  r.`hora_llegada`,
  r.`hora_salida`,
  CASE WHEN r.`hora_salida` IS NULL THEN 'En bodega' ELSE 'Salio' END AS situacion,
  TIMESTAMPDIFF(MINUTE, r.`hora_llegada`, r.`hora_salida`) AS minutos_en_bodega,
  r.`estado`
FROM `recepciones` r
JOIN `vehiculos`   v  ON v.`id`  = r.`vehiculo_id`
JOIN `proveedores` pr ON pr.`id` = r.`proveedor_id`;
-- ============================================================================
-- SECCION 10 — DATOS SEMILLA
-- Conjunto minimo coherente para pruebas y sustentacion academica.
-- ============================================================================

-- 10.1 Roles ------------------------------------------------------------------
INSERT INTO `roles` (`id`,`nombre`,`descripcion`,`activo`) VALUES
  (1,'administrador','Control total del sistema y de la operacion', TRUE),
  (2,'celador','Registra llegada y salida de vehiculos y alertas de ingreso', TRUE),
  (3,'bodeguero','Confirma descargas y registra danos de mercancia', TRUE),
  (4,'repartidor','Consulta inventario, despachos asignados y confirma entregas', TRUE);

-- 10.2 Usuarios internos (password_hash = bcrypt demo) ------------------------
INSERT INTO `usuarios` (`id`,`nombre_completo`,`correo`,`password_hash`,`telefono`,`rol_id`,`estado`) VALUES
  (1,'Administrador GoldenSys','admin@goldendrinks.local','$2y$10$demo.hash.admin','3000000000',1,'activo'),
  (2,'Celador Principal','celador@goldendrinks.local','$2y$10$demo.hash.celador','3000000001',2,'activo'),
  (3,'Bodeguero Principal','bodeguero@goldendrinks.local','$2y$10$demo.hash.bodeguero','3000000002',3,'activo'),
  (4,'Carlos Perez (Repartidor)','repartidor@goldendrinks.local','$2y$10$demo.hash.repartidor','3000000003',4,'activo');

-- 10.3 Proveedores ------------------------------------------------------------
INSERT INTO `proveedores` (`id`,`nit`,`nombre`,`correo`,`password_hash`,`telefono`,`direccion`,`estado`) VALUES
  (1,'900111222-1','Licores del Valle S.A.','contacto@licoresdelvalle.co','$2y$10$demo.hash.prov1','3101111111','Cali, Valle','activo'),
  (2,'901333444-2','Distribuidora Andina Ltda.','ventas@andina.co','$2y$10$demo.hash.prov2','3102222222','Bogota, Cund.','activo');

-- 10.4 Vendedores externos ----------------------------------------------------
INSERT INTO `vendedores` (`id`,`empresa`,`nombre_contacto`,`correo`,`password_hash`,`telefono`,`estado`) VALUES
  (1,'Tienda La Esquina','Marta Gomez','marta@laesquina.co','$2y$10$demo.hash.vend1','3201111111','activo'),
  (2,'Bar El Encuentro','Luis Rojas','luis@elencuentro.co','$2y$10$demo.hash.vend2','3202222222','activo');

-- 10.5 Categorias y productos -------------------------------------------------
INSERT INTO `categorias_producto` (`id`,`nombre`,`descripcion`) VALUES
  (1,'Vino','Vinos tintos, blancos y rosados'),
  (2,'Ron','Rones nacionales e importados'),
  (3,'Aguardiente','Aguardientes anisados'),
  (4,'Whisky','Whisky escoces y americano');

INSERT INTO `productos`
  (`id`,`codigo`,`nombre`,`categoria_id`,`unidad_medida`,`precio_compra`,`precio_distribucion`,`stock_minimo`,`activo`) VALUES
  (1,'VIN-001','Vino Tinto Reserva 750ml',1,'botella',18000,26000,10,TRUE),
  (2,'RON-001','Ron Medellin Anejo 750ml',2,'botella',32000,45000,12,TRUE),
  (3,'AGU-001','Aguardiente Antioqueno 750ml',3,'botella',25000,35000,15,TRUE),
  (4,'WHI-001','Whisky Buchanans 750ml',4,'botella',95000,135000,8,TRUE);
-- (El trigger trg_producto_after_insert crea la fila de inventario en 0)

-- 10.6 Vehiculos --------------------------------------------------------------
INSERT INTO `vehiculos` (`id`,`placa`,`conductor`,`tipo_vehiculo`,`capacidad_cajas`,`estado`,`registrado_por_admin_id`) VALUES
  (1,'ABC-123','Pedro Ramirez','Camion NHR',120,'disponible',1),
  (2,'XYZ-789','Jorge Salas','Turbo',200,'disponible',1);

-- ============================================================================
-- SECCION 11 — FLUJO DE DEMOSTRACION (trazabilidad de punta a punta)
-- Simula la operacion descrita en el audio para validar los triggers.
-- ============================================================================

-- 11.1 Proveedor programa una orden de entrega (RF-15) ------------------------
INSERT INTO `ordenes_entrega` (`id`,`proveedor_id`,`codigo_orden`,`fecha_estimada`,`estado`) VALUES
  (1,1,'OE-0001', NOW() + INTERVAL 1 DAY, 'programada');
INSERT INTO `detalle_orden_entrega` (`orden_entrega_id`,`producto_id`,`cantidad_programada`) VALUES
  (1,1,50),(1,2,30);

-- 11.2 Celador registra LLEGADA del vehiculo (RF-01, RF-02) -------------------
INSERT INTO `recepciones`
  (`id`,`orden_entrega_id`,`vehiculo_id`,`proveedor_id`,`celador_id`,`codigo_recepcion`,`valor_flete`,`estado`) VALUES
  (1,1,1,1,2,'REC-0001',80000,'pendiente');

-- 11.3 Bodeguero confirma DESCARGA: 50 vinos (2 danados) + 30 rones (RF-03/04/05)
INSERT INTO `descargas` (`id`,`recepcion_id`,`bodeguero_id`) VALUES (1,1,3);
INSERT INTO `detalle_descarga` (`descarga_id`,`producto_id`,`cantidad_recibida`,`cantidad_danada`,`motivo_dano`) VALUES
  (1,1,50,2,'Botellas rotas en transporte'),   -- => inventario vino +48, perdida 2
  (1,2,30,0,NULL);                              -- => inventario ron  +30
UPDATE `recepciones` SET `estado` = 'descargada' WHERE `id` = 1;

-- 11.4 Celador registra SALIDA del vehiculo (RF-23) ---------------------------
UPDATE `recepciones`
   SET `hora_salida` = NOW(), `celador_salida_id` = 2, `salida_observaciones` = 'Sale tras descargar'
 WHERE `id` = 1;   -- el trigger pone estado='salida'

-- 11.5 Vendedor crea PEDIDO y adjunta COMPROBANTE DE PAGO (RF-19, RF-24) -------
INSERT INTO `pedidos` (`id`,`vendedor_id`,`codigo_pedido`,`estado`,`pago_estado`) VALUES
  (1,1,'PED-0001','en_revision','pendiente');
INSERT INTO `detalle_pedido` (`pedido_id`,`producto_id`,`cantidad_solicitada`,`precio_unitario`,`subtotal`) VALUES
  (1,1,20,26000,0),    -- subtotal lo calcula el trigger
  (1,2,10,45000,0);    -- valor_total se recalcula solo
INSERT INTO `pedido_pago_archivos` (`pedido_id`,`archivo_url`,`tipo_archivo`,`monto`,`referencia`) VALUES
  (1,'/uploads/pagos/ped-0001.jpg','comprobante_pago',970000,'TRX-558211');

-- 11.6 Administrador VERIFICA el pago y APRUEBA el pedido (RF-24 / RN-15) ------
UPDATE `pedidos`
   SET `pago_estado` = 'verificado', `pago_verificado_por` = 1
 WHERE `id` = 1;                       -- el trigger sella pago_verificado_en
UPDATE `pedidos`
   SET `estado` = 'aprobado', `admin_aprobador_id` = 1
 WHERE `id` = 1;                       -- permitido porque el pago esta verificado

-- 11.7 Administrador crea DESPACHO -> sale de bodega -> descuenta stock (RF-07)
INSERT INTO `despachos` (`id`,`pedido_id`,`admin_id`,`repartidor_id`,`codigo_despacho`,`estado`) VALUES
  (1,1,1,4,'DESP-0001','creado');
INSERT INTO `detalle_despacho` (`despacho_id`,`producto_id`,`cantidad`) VALUES
  (1,1,20),   -- vino: inventario -20
  (1,2,10);   -- ron : inventario -10
UPDATE `pedidos`   SET `estado` = 'despachado' WHERE `id` = 1;
UPDATE `despachos` SET `estado` = 'en_camino'  WHERE `id` = 1;

-- 11.8 Repartidor confirma ENTREGA con FOTO (RF-25 / RN-16) --------------------
INSERT INTO `despacho_entrega_archivos`
  (`despacho_id`,`repartidor_id`,`archivo_url`,`tipo_archivo`,`latitud`,`longitud`) VALUES
  (1,4,'/uploads/entregas/desp-0001.jpg','foto_entrega',2.9273,-75.2819);
-- el trigger marca despacho='entregado' y pedido='entregado'

-- 11.9 Notificacion de ejemplo al proveedor por los danos (RF-14) -------------
INSERT INTO `notificaciones`
  (`destinatario_tipo`,`destinatario_id`,`canal`,`tipo_evento`,`titulo`,`mensaje`,`referencia_tipo`,`referencia_id`) VALUES
  ('proveedor',1,'portal','danos_descarga','Daños detectados en su entrega',
   'Se registraron 2 unidades dañadas de Vino Tinto Reserva en la recepción REC-0001.','recepcion',1);

-- 11.10 Sincronizar alertas de stock con el estado real (RF-08) ---------------
-- Genera alertas para productos que esten por debajo del minimo (p.ej. agotados).
CALL `sp_sincronizar_alertas_stock`();

-- ============================================================================
-- SECCION 12 — VERIFICACION RAPIDA (descomentar para revisar tras importar)
-- ============================================================================
-- SELECT * FROM vw_inventario_estado;          -- vino=28, ron=20, agua=0, whisky=0
-- SELECT * FROM vw_ciclo_vehiculos;            -- REC-0001 con llegada y salida
-- SELECT * FROM vw_dashboard_operativo;        -- KPIs del mes
-- SELECT producto_id, tipo_movimiento, origen_tipo, cantidad, saldo_resultante
--   FROM movimientos_inventario ORDER BY id;   -- kardex completo
-- SELECT codigo_pedido, estado, pago_estado, valor_total FROM pedidos;  -- entregado / verificado
-- SELECT * FROM alertas_stock;                 -- alertas de agua/whisky (stock 0 < minimo)

-- ============================================================================
-- FIN DEL SCRIPT — goldensys_db v4.0
-- ============================================================================
