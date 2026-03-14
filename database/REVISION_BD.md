# Revisión BD – Alineación con lógica actual y SIFEN/Marangatu

**Fecha:** 2026-02-22  
**Origen:** `solver_db_17022026.sql` (estructura exportada desde phpMyAdmin)

---

## 1. Coherencia con el código actual

| Tabla | Uso en código | Estado |
|-------|----------------|--------|
| **usuarios** | Login, registro, sesión (`id`, `nombre_usuario`, `email`, `password`) | ✅ Coincide |
| **pos_categorias** | JOIN en listar_productos para nombre de categoría | ✅ Coincide |
| **pos_productos** | Compras, ventas, inventario (campos usados existen) | ✅ Coincide |
| **pos_proveedores** | Compras: RUC, dv, razon_social, tipo_contribuyente | ✅ Coincide |
| **pos_clientes** | Ventas: documento, dv, razon_social, email; guardar_cliente mapea `nombre` → `razon_social` | ✅ Coincide |
| **pos_compras_cabecera** | guardar_compra: proveedor_id, timbrado, nro_comprobante, fecha_emision, gravadas/IVA, total_factura, estado | ✅ Coincide (tipo_comprobante, condicion por defecto) |
| **pos_compras_detalle** | compra_id, producto_id, cantidad, precio_unitario_costo, subtotal | ✅ Coincide |
| **pos_ventas_cabecera** | guardar_venta: cliente_id, nro_factura, gravadas/IVA, total_venta | ✅ Coincide |
| **pos_ventas_detalle** | venta_id, producto_id, cantidad, precio_unitario_venta, subtotal | ✅ Coincide |

**Conclusión:** La estructura actual está bien alineada con la lógica que usa el PHP. No hay columnas faltantes para el flujo actual.

---

## 2. Lo que falta para factura impresa + SIFEN + Marangatu

### 2.1 `pos_ventas_cabecera` (ventas = facturas de venta)

Para factura “en serio” y SIFEN hace falta asociar cada venta a un comprobante fiscal y, si aplica, al DE electrónico:

| Campo sugerido | Tipo | Uso |
|----------------|------|-----|
| **timbrado** | varchar(8) | Timbrado vigente con el que se emite la factura (obligatorio SET). |
| **punto_emision** | varchar(5) opcional | Si usás formato 001-001-0000001 (punto-sucursal-número). |
| **cdc** | varchar(50) opcional | Código de Control del documento electrónico (SIFEN). |
| **estado_sifen** | tinyint/varchar opcional | Ej: 0=pendiente, 1=enviado, 2=aceptado, 3=rechazado. |
| **xml_path** o **kude_path** | varchar(255) opcional | Ruta al XML/KuDE si lo guardás en servidor. |

Sin **timbrado** en ventas no se puede armar la factura impresa ni el DE según normativa.

### 2.2 Datos del negocio (empresa emisora)

Hoy no hay tabla con datos fiscales del local (razón social, RUC, dirección, timbrado vigente, etc.). Eso se necesita para:

- Encabezado de la factura impresa.
- Enviar a SIFEN (emisor del DE).

**Opción recomendada:** tabla **empresa** (o **config_empresa**) con una fila, por ejemplo:

- razon_social, ruc, dv, direccion, telefono, email  
- timbrado_vigente, fecha_desde_timbrado, fecha_hasta_timbrado  
- (opcional) punto_emision, sucursal  

Así la factura y el DE siempre usan los mismos datos.

### 2.3 Compras y Marangatu

**pos_compras_cabecera** ya tiene **tipo_comprobante** y **condicion**, útiles para reportes y Marangatu.  
Si más adelante recibís comprobantes electrónicos de proveedores, se puede agregar algo como **cdc** (Código de Control) para vincular con el DE del proveedor. No es obligatorio para la primera versión del export Marangatu.

### 2.4 Clientes y productos

- **pos_clientes:** documento, dv, razon_social, email cubren lo que pide SIFEN para el receptor.  
- **pos_productos:** iva_tasa, precios, codigo_barra están bien; no hace falta cambiar nada para factura/SIFEN/Marangatu en esta etapa.

---

## 3. Resumen de cambios sugeridos (orden sugerido)

1. **Tabla `empresa`**  
   Una sola fila con datos del negocio (razón social, RUC, dirección, timbrado vigente, fechas, etc.) para factura impresa y SIFEN.

2. **En `pos_ventas_cabecera`**  
   - Agregar **timbrado** (varchar(8), obligatorio para factura/SET).  
   - Opcional ahora, útil después: **punto_emision**, **cdc**, **estado_sifen** (y si guardás archivos: **xml_path** o **kude_path**).

Con eso la BD queda alineada con la lógica actual y lista para:

- Imprimir facturas con timbrado y datos del negocio.
- Integrar SIFEN (timbrado + CDC + estado).
- Exportar comprobantes para Marangatu (ventas y compras ya tienen los datos necesarios; solo falta el formato de export y, en ventas, completar timbrado).

---

## 4. Sobre el archivo SQL

Recomendación: copiar `solver_db_17022026.sql` desde Descargas a algo como:

- `database/solver_db_17022026.sql`  
o  
- `docs/solver_db_17022026_estructura.sql`

así queda en el proyecto y se puede versionar. El contenido que revisé es el de tu export (solo estructura); sirve para esta revisión y para futuras migraciones o documentación.

---

## 5. Registro de Sincronización
- **Prueba de conexión:** Configuración exitosa entre equipos (PC 2).
