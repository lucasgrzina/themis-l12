-- =============================================================================
-- Aplica a una base de PRODUCCION restaurada los cambios estructurales de
-- Themis L12. Es IDEMPOTENTE (se puede correr N veces) y NO modifica datos
-- (salvo registrar en `migrations` las migraciones ya reflejadas).
--
-- Se ejecuta sobre la base indicada en la linea de comandos:
--     mysql -uroot --default-character-set=utf8mb4 themis < este_archivo.sql
-- o con database/scripts/actualizar_desde_prod.ps1 (restaura el dump y lo aplica).
--
-- NO se aplican (los datos de prod lo impiden):
--   * UNIQUE estado_requerimientos(nombre): hay nombres duplicados.
--   * FK observacion_clientes.cliente_id -> clientes.id: hay filas huerfanas.
-- Las FK nuevas restantes se agregan solo si no hay huerfanos (si los hay, se
-- avisa al final).
-- =============================================================================
SET NAMES utf8mb4;

DROP PROCEDURE IF EXISTS _run_if;
DELIMITER $$
CREATE PROCEDURE _run_if(IN p_cond BOOLEAN, IN p_sql TEXT)
BEGIN
  IF p_cond THEN
    SET @_ddl = p_sql;
    PREPARE s FROM @_ddl; EXECUTE s; DEALLOCATE PREPARE s;
  END IF;
END$$
DELIMITER ;

-- Columna nueva ---------------------------------------------------------------
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE()
    AND table_name='tramite_clientes' AND column_name='fecha_vto_directorio'),
  'ALTER TABLE tramite_clientes ADD COLUMN fecha_vto_directorio DATE NULL AFTER fecha_remision_vto');

-- Tipos de columna (int unsigned -> bigint unsigned) ---------------------------
CALL _run_if(EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE()
    AND table_name='model_has_permissions' AND column_name='model_id' AND column_type<>'bigint unsigned'),
  'ALTER TABLE model_has_permissions MODIFY model_id BIGINT UNSIGNED NOT NULL');
CALL _run_if(EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE()
    AND table_name='model_has_roles' AND column_name='model_id' AND column_type<>'bigint unsigned'),
  'ALTER TABLE model_has_roles MODIFY model_id BIGINT UNSIGNED NOT NULL');
CALL _run_if(EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE()
    AND table_name='vencimientos' AND column_name='vencible_id' AND column_type<>'bigint unsigned'),
  'ALTER TABLE vencimientos MODIFY vencible_id BIGINT UNSIGNED NOT NULL');

-- Indices: tablas polimorficas (orden type,id) ---------------------------------
CALL _run_if(EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='model_has_permissions' AND index_name='model_has_permissions_model_id_model_type_index'),
  'ALTER TABLE model_has_permissions DROP INDEX model_has_permissions_model_id_model_type_index');
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='model_has_permissions' AND index_name='model_has_permissions_model_type_model_id_index'),
  'ALTER TABLE model_has_permissions ADD INDEX model_has_permissions_model_type_model_id_index (model_type, model_id)');

CALL _run_if(EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='model_has_roles' AND index_name='model_has_roles_model_id_model_type_index'),
  'ALTER TABLE model_has_roles DROP INDEX model_has_roles_model_id_model_type_index');
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='model_has_roles' AND index_name='model_has_roles_model_type_model_id_index'),
  'ALTER TABLE model_has_roles ADD INDEX model_has_roles_model_type_model_id_index (model_type, model_id)');

CALL _run_if(EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='vencimientos' AND index_name='vencimientos_vencible_id_vencible_type_index'),
  'ALTER TABLE vencimientos DROP INDEX vencimientos_vencible_id_vencible_type_index');
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='vencimientos' AND index_name='vencimientos_vencible_type_vencible_id_index'),
  'ALTER TABLE vencimientos ADD INDEX vencimientos_vencible_type_vencible_id_index (vencible_type, vencible_id)');

-- Indices: estado_tramites ------------------------------------------------------
CALL _run_if(EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='estado_tramites' AND index_name='estado_tramites_nombre_unique'),
  'ALTER TABLE estado_tramites DROP INDEX estado_tramites_nombre_unique');
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='estado_tramites' AND index_name='estado_tramites_nombre_area_id_unique'),
  'ALTER TABLE estado_tramites ADD UNIQUE INDEX estado_tramites_nombre_area_id_unique (nombre, area_id)');
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='estado_tramites' AND index_name='estado_tramites_nombre_index'),
  'ALTER TABLE estado_tramites ADD INDEX estado_tramites_nombre_index (nombre)');

-- Indices: time_referencias -------------------------------------------------------
CALL _run_if(EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='time_referencias' AND index_name='NewIndex1'),
  'ALTER TABLE time_referencias DROP INDEX NewIndex1');
CALL _run_if(EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='time_referencias' AND index_name='time_referencias_nombre_unique'),
  'ALTER TABLE time_referencias DROP INDEX time_referencias_nombre_unique');
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='time_referencias' AND index_name='time_referencias_cliente_id_nombre_unique'),
  'ALTER TABLE time_referencias ADD UNIQUE INDEX time_referencias_cliente_id_nombre_unique (cliente_id, nombre)');
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='time_referencias' AND index_name='time_referencias_nombre_index'),
  'ALTER TABLE time_referencias ADD INDEX time_referencias_nombre_index (nombre)');

-- Indices: reparticion_origen --------------------------------------------------------
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='reparticion_origen' AND index_name='reparticion_origen_nombre_index'),
  'ALTER TABLE reparticion_origen ADD INDEX reparticion_origen_nombre_index (nombre)');

-- Indices: tramite_clientes (el heredado de prod apunta a fecha_vto) ---------------
CALL _run_if(EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='tramite_clientes' AND index_name='tramite_clientes_fecha_vto_directorio_index'
    AND column_name='fecha_vto'),
  'ALTER TABLE tramite_clientes DROP INDEX tramite_clientes_fecha_vto_directorio_index');
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='tramite_clientes' AND index_name='tramite_clientes_fecha_vto_directorio_index'),
  'ALTER TABLE tramite_clientes ADD INDEX tramite_clientes_fecha_vto_directorio_index (fecha_vto_directorio)');
CALL _run_if(NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE()
    AND table_name='tramite_clientes' AND index_name='tramite_clientes_fecha_vto_index'),
  'ALTER TABLE tramite_clientes ADD INDEX tramite_clientes_fecha_vto_index (fecha_vto)');

-- Claves foraneas (solo si no hay huerfanos) ------------------------------------------
CALL _run_if(
  NOT EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE table_schema=DATABASE()
    AND table_name='documento_clientes' AND constraint_name='documento_clientes_cliente_id_foreign')
  AND NOT EXISTS (SELECT 1 FROM documento_clientes d LEFT JOIN clientes c ON c.id=d.cliente_id WHERE c.id IS NULL),
  'ALTER TABLE documento_clientes ADD CONSTRAINT documento_clientes_cliente_id_foreign FOREIGN KEY (cliente_id) REFERENCES clientes (id)');

CALL _run_if(
  NOT EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE table_schema=DATABASE()
    AND table_name='observacion_clientes' AND constraint_name='observacion_clientes_user_id_foreign')
  AND NOT EXISTS (SELECT 1 FROM observacion_clientes o LEFT JOIN users u ON u.id=o.user_id WHERE u.id IS NULL),
  'ALTER TABLE observacion_clientes ADD CONSTRAINT observacion_clientes_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)');

-- Migraciones ya reflejadas en la estructura -------------------------------------------
SET @b = (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations);
INSERT INTO migrations (migration, batch)
SELECT v.m, @b FROM (
  SELECT '2018_07_12_162705_add_visible_to_users_table' m UNION ALL
  SELECT '2018_08_08_163357_create_sessions_table' UNION ALL
  SELECT '2018_10_02_131409_add_inicia_tramite_to_tipo_tramites_table' UNION ALL
  SELECT '2018_10_02_143225_add_monto_beneficio_to_tramite_clientes_table' UNION ALL
  SELECT '2018_10_02_145415_add_monto_conciliacion_to_tramite_expedientes_table' UNION ALL
  SELECT '2018_10_04_213037_add_nro_correlativo_to_clientes_table' UNION ALL
  SELECT '2018_11_27_165437_create_time_gestions_table' UNION ALL
  SELECT '2018_11_29_093138_create_time_referencias_table' UNION ALL
  SELECT '2018_11_29_171025_create_time_horas_table' UNION ALL
  SELECT '2018_12_03_130532_add_time_level_to_users_table' UNION ALL
  SELECT '2026_08_22_000000_add_columnas_faltantes_de_produccion'
) v
WHERE v.m NOT IN (SELECT migration FROM migrations);

DROP PROCEDURE _run_if;

-- Avisos: FK que no se pudieron agregar por datos huerfanos ------------------------------
SELECT 'AVISO: FK pendiente por huerfanos' AS aviso, 'documento_clientes.cliente_id' AS fk
WHERE NOT EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE table_schema=DATABASE()
  AND constraint_name='documento_clientes_cliente_id_foreign')
UNION ALL
SELECT 'AVISO: FK pendiente por huerfanos', 'observacion_clientes.user_id'
WHERE NOT EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE table_schema=DATABASE()
  AND constraint_name='observacion_clientes_user_id_foreign');
