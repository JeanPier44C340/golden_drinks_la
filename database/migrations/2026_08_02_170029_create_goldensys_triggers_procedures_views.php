<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // (8.0) Al crear un producto, garantizar su fila de inventario en 0
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_producto_after_insert`
AFTER INSERT ON `productos`
FOR EACH ROW
BEGIN
  INSERT INTO `inventario` (`producto_id`, `stock_actual`)
  VALUES (NEW.`id`, 0)
  ON DUPLICATE KEY UPDATE `producto_id` = `producto_id`;
END
SQL);

        // (8.1) ENTRADA por descarga confirmada
        DB::unprepared(<<<'SQL'
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

  SELECT d.`recepcion_id`, d.`bodeguero_id`
    INTO v_recepcion, v_bodeguero
    FROM `descargas` d
   WHERE d.`id` = NEW.`descarga_id`;

  SELECT `stock_actual` INTO v_saldo_ant
    FROM `inventario` WHERE `producto_id` = NEW.`producto_id` FOR UPDATE;
  IF v_saldo_ant IS NULL THEN
    INSERT INTO `inventario` (`producto_id`,`stock_actual`) VALUES (NEW.`producto_id`,0);
    SET v_saldo_ant = 0;
  END IF;

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

    SET v_saldo_ant = v_saldo_new;
  END IF;

  IF NEW.`cantidad_danada` > 0 THEN
    INSERT INTO `perdidas`
      (`recepcion_id`,`producto_id`,`bodeguero_id`,`origen`,`cantidad`,`motivo`)
    VALUES
      (v_recepcion, NEW.`producto_id`, v_bodeguero, 'descarga',
       NEW.`cantidad_danada`, COALESCE(NEW.`motivo_dano`,'Producto danado en descarga'));
  END IF;
END
SQL);

        // (8.2) SALIDA por despacho
        DB::unprepared(<<<'SQL'
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
END
SQL);

        // (8.3) PERDIDA registrada
        DB::unprepared(<<<'SQL'
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
    INSERT INTO `movimientos_inventario`
      (`producto_id`,`tipo_movimiento`,`origen_tipo`,`origen_id`,
       `cantidad`,`saldo_anterior`,`saldo_resultante`,`actor_usuario_id`,`nota`)
    VALUES
      (NEW.`producto_id`,'perdida','descarga',NEW.`id`,
       -NEW.`cantidad`, v_saldo_ant, v_saldo_ant, NEW.`bodeguero_id`,
       CONCAT('Perdida en descarga (no ingreso a stock): ', NEW.`motivo`));
  END IF;
END
SQL);

        // (8.4) ALERTAS de stock bajo
        DB::unprepared(<<<'SQL'
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
    IF v_abierta_id IS NULL THEN
      INSERT INTO `alertas_stock` (`producto_id`,`stock_detectado`,`stock_minimo`,`estado`)
      VALUES (NEW.`producto_id`, NEW.`stock_actual`, v_min, 'abierta');
    END IF;
  ELSE
    IF v_abierta_id IS NOT NULL THEN
      UPDATE `alertas_stock`
         SET `estado` = 'cerrada', `cerrada_en` = NOW()
       WHERE `id` = v_abierta_id;
    END IF;
  END IF;
END
SQL);

        // (8.5) Calculo automatico de subtotal
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_detalle_pedido_before_insert`
BEFORE INSERT ON `detalle_pedido`
FOR EACH ROW
BEGIN
  SET NEW.`subtotal` = NEW.`cantidad_solicitada` * NEW.`precio_unitario`;
END
SQL);

        // (8.6) Recalcular valor_total
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_detalle_pedido_after_insert`
AFTER INSERT ON `detalle_pedido`
FOR EACH ROW
BEGIN
  UPDATE `pedidos`
     SET `valor_total` = (SELECT COALESCE(SUM(`subtotal`),0)
                            FROM `detalle_pedido` WHERE `pedido_id` = NEW.`pedido_id`)
   WHERE `id` = NEW.`pedido_id`;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_detalle_pedido_after_update`
AFTER UPDATE ON `detalle_pedido`
FOR EACH ROW
BEGIN
  UPDATE `pedidos`
     SET `valor_total` = (SELECT COALESCE(SUM(`subtotal`),0)
                            FROM `detalle_pedido` WHERE `pedido_id` = NEW.`pedido_id`)
   WHERE `id` = NEW.`pedido_id`;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_detalle_pedido_after_delete`
AFTER DELETE ON `detalle_pedido`
FOR EACH ROW
BEGIN
  UPDATE `pedidos`
     SET `valor_total` = (SELECT COALESCE(SUM(`subtotal`),0)
                            FROM `detalle_pedido` WHERE `pedido_id` = OLD.`pedido_id`)
   WHERE `id` = OLD.`pedido_id`;
END
SQL);

        // (8.7) RN-15: no aprobar sin pago verificado
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_pedido_before_update`
BEFORE UPDATE ON `pedidos`
FOR EACH ROW
BEGIN
  IF NEW.`estado` IN ('aprobado','despachado','entregado')
     AND OLD.`estado` = 'en_revision'
     AND NEW.`pago_estado` <> 'verificado' THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'No se puede aprobar/despachar el pedido sin comprobante de pago verificado (RN-15)';
  END IF;

  IF NEW.`pago_estado` = 'verificado' AND OLD.`pago_estado` <> 'verificado' THEN
    SET NEW.`pago_verificado_en` = NOW();
  END IF;
END
SQL);

        // (8.8) ENTREGA con evidencia
        DB::unprepared(<<<'SQL'
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
END
SQL);

        // (8.9) SALIDA de vehiculo
        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_recepcion_before_update`
BEFORE UPDATE ON `recepciones`
FOR EACH ROW
BEGIN
  IF NEW.`hora_salida` IS NOT NULL AND OLD.`hora_salida` IS NULL THEN
    SET NEW.`estado` = 'salida';
  END IF;
END
SQL);

        // (8B) Procedimiento de sincronizacion de alertas
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE `sp_sincronizar_alertas_stock`()
BEGIN
  INSERT INTO `alertas_stock` (`producto_id`,`stock_detectado`,`stock_minimo`,`estado`)
  SELECT p.`id`, i.`stock_actual`, p.`stock_minimo`, 'abierta'
    FROM `productos` p
    JOIN `inventario` i ON i.`producto_id` = p.`id`
   WHERE p.`activo` = TRUE
     AND i.`stock_actual` < p.`stock_minimo`
     AND NOT EXISTS (
       SELECT 1 FROM `alertas_stock` a
        WHERE a.`producto_id` = p.`id` AND a.`estado` = 'abierta');

  UPDATE `alertas_stock` a
    JOIN `inventario` i ON i.`producto_id` = a.`producto_id`
    JOIN `productos`  p ON p.`id` = a.`producto_id`
     SET a.`estado` = 'cerrada', a.`cerrada_en` = NOW()
   WHERE a.`estado` = 'abierta'
     AND i.`stock_actual` >= p.`stock_minimo`;
END
SQL);

        // Vistas
        DB::unprepared(<<<'SQL'
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
  (SELECT COUNT(*) FROM `reclamos` WHERE `estado` <> 'resuelto')  AS reclamos_abiertos
SQL);

        DB::unprepared(<<<'SQL'
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
WHERE p.`activo` = TRUE
SQL);

        DB::unprepared(<<<'SQL'
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
ORDER BY unidades_despachadas DESC
SQL);

        DB::unprepared(<<<'SQL'
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
JOIN `proveedores` pr ON pr.`id` = r.`proveedor_id`
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS `vw_ciclo_vehiculos`');
        DB::unprepared('DROP VIEW IF EXISTS `vw_producto_estrella_mes`');
        DB::unprepared('DROP VIEW IF EXISTS `vw_inventario_estado`');
        DB::unprepared('DROP VIEW IF EXISTS `vw_dashboard_operativo`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `sp_sincronizar_alertas_stock`');

        DB::unprepared('DROP TRIGGER IF EXISTS `trg_recepcion_before_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_entrega_after_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_pedido_before_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_detalle_pedido_after_delete`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_detalle_pedido_after_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_detalle_pedido_after_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_detalle_pedido_before_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_inventario_after_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_perdida_after_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_detalle_despacho_after_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_detalle_descarga_after_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_producto_after_insert`');
    }
};
