<?php 
    require_once '../../includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Compras - Carga</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/styles-for-tool-kit-v001.css">
    <style>
        .form-compras { max-width: 900px; width: 95%; margin: 0px auto; }
        .grid-header { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 5px; }
        .seccion { background: #fff; padding: 10px; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .tabla-productos { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .tabla-productos th, .tabla-productos td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        .total-box { text-align: right; font-size: 1.2em; font-weight: bold; color: #28a745; }
        .badge-iva { font-size: 0.8em; padding: 2px 5px; background: #e9ecef; border-radius: 3px; }
    </style>
</head>
<body>

<div class="form-compras">

    <div style="display: flex; flex-direction:row; justify-content: space-between; align-items: center;  margin-bottom: 20px; ">
        <h2 style="margin: 0px 0;">Nueva Factura de Compra</h2>
        <a href="../../dashboard.php" style="text-decoration: none; color: #666;">← Volver al Menú</a>
    </div>

    <form id="formNuevaCompra">
        <div class="seccion grid-header">
            <div class="form-group">
                <label>RUC Proveedor</label>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="ruc_busqueda" placeholder="Ej: 80012345-6" required style="flex: 1;">
                    <button type="button" onclick="abrirModalProveedor()" style="background: #dc3545; color: white; padding: 0px 0px; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap;">+ Nuevo</button>
                </div>
            </div>
            <div class="form-group">
                <label>Razón Social</label>
                <input type="text" id="razon_social" readonly placeholder="Se autocompletará">
            </div>
            <div class="form-group">
                <label>Nro. Timbrado</label>
                <input type="text" id="timbrado" maxlength="8" required>
            </div>
            <div class="form-group">
                <label>Nro. Factura</label>
                <input type="text" id="nro_factura" placeholder="001-001-0000001" required>
            </div>
            <div class="form-group">
                <label>Fecha Emisión</label>
                <input type="date" id="fecha_emision" required>
            </div>
        </div>

        <div class="seccion">
            <!-- <h4>Agregar Productos</h4> -->
            <div class="grid-header" style="align-items: end;">
                <div class="form-group">
                    <label>Producto</label>
                    <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                        <!-- El select crece para llenar el espacio, pero respeta al botón -->
                        <select id="select_producto" style="flex: 1; min-width: 0; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">Seleccione un producto...</option>
                        </select>

                        <!-- El botón tiene flex-shrink: 0 para que NUNCA se aplaste -->
                        <button type="button" onclick="abrirModalProducto()" 
                                style="flex-shrink: 0; background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; white-space: nowrap;">
                            + Nuevo
                        </button>
                    </div>

                </div>
                <div class="form-group">
                    <label>Cantidad</label>
                    <input type="number" id="cantidad" value="1" min="0.01" step="0.01">
                </div>
                <div class="form-group">
                    <label>Precio Costo (Unitario)</label>
                    <input type="number" id="precio_unitario" placeholder="0">
                </div>
                <button type="button" id="btnAgregar" style="background: #17a2b8; margin-bottom: 15px;">Añadir</button>
            </div>

            <table class="tabla-productos" id="tablaDetalle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>IVA</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>

        <div class="seccion">
            <div class="grid-header">
                <div>
                    <small>Gravada 10%: <span id="lbl_g10">0</span></small><br>
                    <small>Gravada 5%: <span id="lbl_g5">0</span></small><br>
                    <small>Exenta: <span id="lbl_ex">0</span></small>
                </div>
                <div class="total-box">
                    Total Factura: <span id="lbl_total">0</span> Gs.
                </div>
            </div>
            <button 
                type="submit" 
                id="btnGuardarCompra" 
                style="margin-top: 20px; background: #28a745;"
            >
                Guardar Factura Completa
            </button>
        </div>
    </form>
</div>

<div id="modalProveedor" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div class="seccion" style="max-width:500px; margin:10% auto; position:relative;">
        <h3>🚚 Registrar Nuevo Proveedor</h3>
        <hr>
        <form id="formRapidoProveedor">
            <div class="form-group">
                <label>RUC (sin DV)</label>
                <input type="text" id="nuevo_ruc" required>
            </div>
            <div class="form-group">
                <label>DV (Dígito Verificador)</label>
                <input type="text" id="nuevo_dv" maxlength="1" required>
            </div>
            <div class="form-group">
                <label>Razón Social / Nombre</label>
                <input type="text" id="nueva_razon" required>
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select id="nuevo_tipo">
                    <option value="FISICA">Persona Física</option>
                    <option value="JURIDICA">Persona Jurídica (Empresa)</option>
                </select>
            </div>
            <div style="display:flex; gap:10px; margin-top:15px;">
                <button type="submit" style="background:#28a745;">Guardar Proveedor</button>
                <button type="button" onclick="cerrarModal()" style="background:#6c757d;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Nuevo Producto -->
<div id="modalProducto" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1001;">
    <div class="seccion" style="max-width:500px; margin:2% auto; position:relative; max-height: 90vh; overflow-y: auto;">
        <h3>📦 Registrar Nuevo Producto</h3>
        <hr style="margin-bottom: 15px;">
        <form id="formRapidoProducto">
            <div class="form-group">
                <label>Nombre del Producto</label>
                <input type="text" id="nuevo_prod_nombre" required>
            </div>
            <div class="form-group">
                <label>Código de Barras (Opcional)</label>
                <input type="text" id="nuevo_prod_codigo_barra">
            </div>
            <div class="form-group">
                <label>Descripción Corta (Opcional)</label>
                <textarea id="nuevo_prod_descripcion" rows="2" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; font-size: inherit;"></textarea>
            </div>
            <div class="form-group">
                <label>Descripción para Tienda Web (Opcional)</label>
                <textarea id="nuevo_prod_descripcion_web" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; font-size: inherit;"></textarea>
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select id="nuevo_prod_categoria" required>
                    <option value="">Cargando categorías...</option>
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label>Precio Costo</label>
                    <input type="number" id="nuevo_prod_costo" required min="0" step="any">
                </div>
                <div class="form-group">
                    <label>Precio Venta</label>
                    <input type="number" id="nuevo_prod_venta" required min="0" step="any">
                </div>
            </div>
            <div class="form-group">
                <label>Tasa de IVA (%)</label>
                <select id="nuevo_prod_iva" required>
                    <option value="10">10%</option>
                    <option value="5">5%</option>
                    <option value="0">Exenta (0%)</option>
                </select>
            </div>
            <div style="display:flex; gap:10px; margin-top:15px;">
                <button type="submit" style="background:#28a745;">Guardar Producto</button>
                <button type="button" onclick="cerrarModalProducto()" style="background:#6c757d;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script src="../../js/pos_compras.js"></script>
<script src="../../js/tool-kit-v002.js"></script>
</body>
</html>