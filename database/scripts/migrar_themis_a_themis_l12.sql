-- =============================================================================
-- Migra TODOS los datos de la base `themis` (origen, solo lectura) a
-- `themis_l12` (destino), vaciando antes las tablas de destino.
--
-- USO (desde la raiz del proyecto):
--   1) Backup del destino (obligatorio, el script hace TRUNCATE):
--        mysqldump -uroot --default-character-set=utf8mb4 themis_l12 > themis_l12_backup.sql
--   2) Ejecutar:
--        mysql -uroot --default-character-set=utf8mb4 < database/scripts/migrar_themis_a_themis_l12.sql
--
-- QUE HACE
--   * Copia solo las columnas que existen en ambas bases (cada tabla), asi
--     `tramite_clientes.fecha_vto_directorio` (solo en l12) queda en NULL.
--   * NO toca `migrations` (l12 tiene 71 migraciones y themis 60; copiar
--     romperia `php artisan migrate`).
--   * Copia TODOS los registros, sin descartar ninguno (ni duplicados ni huerfanos):
--       - `estado_requerimientos`: themis tiene nombres duplicados que no caben en
--         el unico `estado_requerimientos_nombre_unique` de l12. Ese indice se
--         reemplaza por uno NO unico (unico cambio de esquema del script).
--       - `observacion_clientes` (344 filas) y `requerimiento_clientes` (49 filas
--         con estado_req_id = 0) quedan con referencias huerfanas, igual que en
--         themis; se copian con foreign_key_checks = 0.
--   * Se conservan los datos propios de l12: permissions 101-104 (`time:*`),
--     su asignacion en role_has_permissions y acciones_controladas id 26 (`Time`).
--   * Al final imprime una tabla de verificacion origen vs destino.
--
-- Los TRUNCATE hacen commit implicito: no hay rollback, por eso el backup.
-- =============================================================================

USE themis_l12;
SET NAMES utf8mb4;
SET SESSION group_concat_max_len = 1000000;
SET SESSION foreign_key_checks = 0;
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- 1) Respaldo de lo que existe solo en l12 ------------------------------------
DROP TABLE IF EXISTS themis_l12._bk_permissions;
DROP TABLE IF EXISTS themis_l12._bk_role_has_permissions;
DROP TABLE IF EXISTS themis_l12._bk_acciones_controladas;

CREATE TABLE themis_l12._bk_permissions AS
  SELECT * FROM themis_l12.permissions
  WHERE name NOT IN (SELECT name FROM themis.permissions);

CREATE TABLE themis_l12._bk_role_has_permissions AS
  SELECT * FROM themis_l12.role_has_permissions
  WHERE permission_id IN (SELECT id FROM themis_l12._bk_permissions);

CREATE TABLE themis_l12._bk_acciones_controladas AS
  SELECT * FROM themis_l12.acciones_controladas
  WHERE id NOT IN (SELECT id FROM themis.acciones_controladas);

-- 1b) Permitir duplicados de nombre en estado_requerimientos -------------------
SET @idx = (SELECT COUNT(*) FROM information_schema.statistics
            WHERE table_schema='themis_l12' AND table_name='estado_requerimientos'
              AND index_name='estado_requerimientos_nombre_unique');
SET @sql = IF(@idx > 0,
  'ALTER TABLE themis_l12.estado_requerimientos DROP INDEX estado_requerimientos_nombre_unique, ADD INDEX estado_requerimientos_nombre_index (nombre)',
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 2) Vaciar y copiar (columnas en comun) ---------------------------------------
DROP PROCEDURE IF EXISTS themis_l12._sp_migrar;
DELIMITER $$
CREATE PROCEDURE themis_l12._sp_migrar()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE t VARCHAR(64);
  DECLARE cur CURSOR FOR
    SELECT table_name FROM information_schema.tables
    WHERE table_schema = 'themis_l12' AND table_type = 'BASE TABLE'
      AND table_name <> 'migrations'
      AND table_name NOT LIKE '\_bk\_%'
            AND table_name IN (SELECT table_name FROM information_schema.tables WHERE table_schema = 'themis')
    ORDER BY table_name;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  OPEN cur;
  loop_tablas: LOOP
    FETCH cur INTO t;
    IF done THEN LEAVE loop_tablas; END IF;

    SET @cols = (
      SELECT GROUP_CONCAT(CONCAT('`', a.column_name, '`') ORDER BY a.ordinal_position)
      FROM information_schema.columns a
      JOIN information_schema.columns b
        ON b.table_schema = 'themis' AND b.table_name = a.table_name AND b.column_name = a.column_name
      WHERE a.table_schema = 'themis_l12' AND a.table_name = t
    );

    SET @sql = CONCAT('TRUNCATE TABLE themis_l12.`', t, '`');
    PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

    SET @sql = CONCAT('INSERT INTO themis_l12.`', t, '` (', @cols, ') SELECT ', @cols, ' FROM themis.`', t, '`');
    PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
  END LOOP;
  CLOSE cur;
END$$
DELIMITER ;

CALL themis_l12._sp_migrar();
DROP PROCEDURE themis_l12._sp_migrar;

-- 3) Restaurar lo propio de l12 ------------------------------------------------
INSERT INTO themis_l12.permissions          SELECT * FROM themis_l12._bk_permissions;
INSERT INTO themis_l12.role_has_permissions SELECT * FROM themis_l12._bk_role_has_permissions;
INSERT INTO themis_l12.acciones_controladas SELECT * FROM themis_l12._bk_acciones_controladas;

-- 3b) Time: acciones/permisos que existen solo en l12 (idempotente) ------------
INSERT IGNORE INTO themis_l12.acciones_controladas (id, nombre, created_at, updated_at, nombre_permiso)
VALUES (26, 'Time', '2026-08-21 15:02:15', '2026-08-21 15:02:15', 'time');

INSERT IGNORE INTO themis_l12.permissions (id, name, guard_name, created_at, updated_at, display_name) VALUES
  (101, 'time:C', 'web', '2026-08-21 15:02:15', '2026-08-21 15:02:15', 'C Time'),
  (102, 'time:R', 'web', '2026-08-21 15:02:15', '2026-08-21 15:02:15', 'R Time'),
  (103, 'time:U', 'web', '2026-08-21 15:02:15', '2026-08-21 15:02:15', 'U Time'),
  (104, 'time:D', 'web', '2026-08-21 15:02:15', '2026-08-21 15:02:15', 'D Time');

INSERT IGNORE INTO themis_l12.role_has_permissions (permission_id, role_id)
SELECT p.id, 1 FROM themis_l12.permissions p WHERE p.name LIKE 'time:%';

DROP TABLE themis_l12._bk_permissions;
DROP TABLE themis_l12._bk_role_has_permissions;
DROP TABLE themis_l12._bk_acciones_controladas;

SET SESSION foreign_key_checks = 1;

-- 4) Verificacion: origen vs destino -------------------------------------------
-- Unica diferencia esperada: migrations (no se toca). Todo lo demas debe ser OK.
DROP TEMPORARY TABLE IF EXISTS _verif;
CREATE TEMPORARY TABLE _verif (tabla VARCHAR(64), origen BIGINT, destino BIGINT);
DELIMITER $$
CREATE PROCEDURE themis_l12._sp_verif()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE t VARCHAR(64);
  DECLARE cur CURSOR FOR
    SELECT table_name FROM information_schema.tables
    WHERE table_schema = 'themis_l12' AND table_type = 'BASE TABLE'
      AND table_name NOT LIKE '\_bk\_%'       AND table_name IN (SELECT table_name FROM information_schema.tables WHERE table_schema = 'themis');
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
  OPEN cur;
  l: LOOP
    FETCH cur INTO t;
    IF done THEN LEAVE l; END IF;
    SET @sql = CONCAT('INSERT INTO _verif SELECT ''', t, ''', (SELECT COUNT(*) FROM themis.`', t,
                      '`), (SELECT COUNT(*) FROM themis_l12.`', t, '`)');
    PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
  END LOOP;
  CLOSE cur;
END$$
DELIMITER ;
CALL themis_l12._sp_verif();
DROP PROCEDURE themis_l12._sp_verif;

SELECT tabla, origen, destino, destino - origen AS diferencia,
       IF(origen = destino, 'OK', 'REVISAR') AS estado
FROM _verif ORDER BY (origen = destino), tabla;
