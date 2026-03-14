-- =================================================================
-- ATENCIÓN: SCRIPT DE REINICIO DE DATOS TRANSACCIONALES
-- Base de datos: solver_db_17022026
--
-- ¡¡¡CUIDADO!!! Este script BORRARÁ PERMANENTEMENTE todos los datos de
-- las tablas de ventas, compras, clientes y productos.
--
-- ÚSALO SOLO SI QUIERES EMPEZAR DE CERO con datos reales.
--
-- >> RECOMENDACIÓN FUERTE: HAZ UNA COPIA DE SEGURIDAD (BACKUP)
-- >> DE TU BASE DE DATOS ANTES DE EJECUTAR ESTO.
-- =================================================================

-- Desactivamos temporalmente la verificación de claves foráneas para evitar errores
SET FOREIGN_KEY_CHECKS = 0;

-- Vaciamos las tablas. TRUNCATE es más rápido que DELETE y reinicia los contadores ID.
TRUNCATE TABLE `pos_ventas_cabecera`;
TRUNCATE TABLE `pos_ventas_detalle`;
TRUNCATE TABLE `pos_compras_cabecera`;
TRUNCATE TABLE `pos_compras_detalle`;
TRUNCATE TABLE `pos_clientes`;
TRUNCATE TABLE `pos_productos`;

-- Reactivamos la verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- A partir de aquí, el sistema está limpio para empezar a cargar datos reales.
-- Las tablas como 'usuarios', 'empresa' y 'proveedores' no se han tocado.