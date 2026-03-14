<?php 
    require_once '../../includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" /> 
    <title>Solver | Ventas - caja</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/styles-for-tool-kit-v001.css">
    <style>
        /* Reutilizamos el estilo que ya definimos para que todo se vea igual */
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 0px 0px; }
        .contenedor { width: 100%; max-width: 1100px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .seccion-factura { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 6px; background: #e9ecef; padding: 6px 15px; border-radius: 5px; }
        .tabla-pos { width: 100%; border-collapse: collapse; margin-top: 0px; }
        .tabla-pos th { background: #333; color: white; padding: 10px; }
        .tabla-pos td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        .totales-container { margin-top: 20px; text-align: right; font-size: 1.2em; font-weight: bold; }
        input, select { padding: 8px; width: 100%; box-sizing: border-box; }
        .btn-cobrar { background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em; }
        /* estilo para las secciones (utilizado en el modal) */
        .seccion { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        @media (max-width: 768px) {
            .tabla-pos {
                display: block;
                overflow-x: auto;
                white-space: nowrap; /* Evita que el contenido de las celdas se parta en varias líneas */
            }
        }
    </style>
</head>
<body>

<div class="contenedor">
        
    <div style="display: flex; flex-direction:row; justify-content: space-between; align-items: center;  margin-bottom: 20px; ">
        <h2>🛒 Nueva Venta</h2>
        <a href="../../dashboard.php" style="text-decoration: none; color: #666;">← Volver al Menú</a>
    </div>

    <div style="background: #e9ecef; width: 245px; padding: 6px 15px; border-radius: 8px; margin-bottom: 6px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-start;">
        <div style="flex: 1; min-width:220px;">
            <label>Nro. Factura</label>
            <input type="text" id="nro_factura" class="form-control" placeholder="001-001-0000001" style="width: auto;">
            <input type="hidden" id="timbrado_venta" value="">
            <input type="hidden" id="punto_emision_venta" value="">
            <small id="timbrado_leyenda" style="color:#666; display:block; margin-top:4px;"></small>
        </div>
    </div>

    <div style="background: #e9ecef; padding: 6px 15px; border-radius: 8px; margin-bottom: 6px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
    
        <div style="flex: 1; min-width:220px;">
            <label>RUC / Cédula:</label>
            <div style="display: flex; gap: 5px;">
                <input type="text" id="ruc_cliente" placeholder="Escriba y presione Enter" class="form-control" onkeypress="if(event.key === 'Enter') buscarCliente()">
                <button type="button" onclick="buscarCliente()" title="Buscar Cliente por RUC/CI" style="background: #007bff; color: white; border: none; padding: 5px 10px; cursor: pointer;">🔍</button>
                <button type="button" onclick="abrirModalListarClientes()" title="Ver listado de clientes" style="background: #6c757d; color: white; border: none; padding: 5px 10px; cursor: pointer; margin-left:4px;">👥</button>
                <button type="button" onclick="abrirModalCliente()" title="Agregar Nuevo Cliente" style="background: #28a745; color: white; border: none; padding: 5px 10px; cursor: pointer; margin-left:4px;">➕</button>
            </div>
        </div>

        <div style="flex: 2; min-width:260px;">
            <label>Nombre / Razón Social:</label>
            <input type="text" id="nombre_cliente" class="form-control" readonly placeholder="Cliente Ocasional">
        </div>

        <div style="flex: 1; min-width:220px;">
            <label>Email (SIFEN):</label>
            <input type="text" id="email_cliente" class="form-control" readonly>
        </div>

        <input type="hidden" id="id_cliente_seleccionado" value="">
    </div>

    <div class="seccion-factura" style="grid-template-columns: 2fr 1fr 1fr auto;">
        <div>
            <label>Producto:</label>
            <select id="select_producto">
                <option value="">Seleccione un producto...</option>
                </select>
        </div>
        <div>
            <label>Precio:</label>
            <input type="number" id="precio_venta" readonly>
        </div>
        <div>
            <label>Cantidad:</label>
            <input type="number" id="cantidad_venta" value="1">
        </div>
        <div style="align-self: end;">
            <button id="btnAgregarVenta" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">+</button>
        </div>
    </div>

    <table class="tabla-pos">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Precio</th>
                <th>Subtotal</th>
                <th>IVA</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="cuerpoVenta"></tbody>
    </table>

    <div class="totales-container">
        <div>Total Factura: <span id="total_factura">0</span> Gs.</div>
        <button id="btnFinalizarVenta" class="btn-cobrar" style="margin-top: 15px;">FINALIZAR VENTA (F12)</button>
    </div>
</div>

<!-- Modal para registrar cliente desde la pantalla de ventas -->
<div id="modalCliente" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div class="seccion" style="max-width:500px; margin:10% auto; position:relative;">
        <h3>👤 Registrar Cliente</h3>
        <hr>
        <form id="formModalCliente">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label>Tipo Documento:</label>
                    <select id="m_tipo_doc" class="form-control" onchange="gestionarCambioRUC(document.getElementById('m_doc').value, 'm_')">
                        <option value="1">Cédula Paraguaya</option>
                        <option value="11">RUC</option>
                        <option value="3">Pasaporte</option>
                    </select>
                </div>
                <div>
                    <label>Nro. Documento:</label>
                    <div style="display: flex; gap: 5px;">
                        <input type="text" id="m_doc" placeholder="Ej: 4444440" oninput="gestionarCambioRUC(this.value, 'm_')" required style="flex: 3;">
                        <input type="text" id="m_dv" placeholder="DV" readonly style="flex: 1; background: #eee; text-align: center;">
                    </div>
                </div>

                <div style="grid-column: span 2;">
                    <label>Razón Social / Nombre:</label>
                    <input type="text" id="m_nombre" placeholder="Nombre completo o Empresa" required style="width: 100%;">
                </div>

                <div style="grid-column: span 2;">
                    <label>Correo Electrónico (Para envío de XML):</label>
                    <input type="email" id="m_email" placeholder="cliente@correo.com" required style="width: 100%;">
                </div>

                <div>
                    <label>Tipo Contribuyente:</label>
                    <select id="m_tipo_cont">
                        <option value="1">Persona Física</option>
                        <option value="2">Persona Jurídica</option>
                    </select>
                </div>

                <div>
                    <label>Teléfono:</label>
                    <input type="text" id="m_tel" placeholder="09xx ...">
                </div>
            </div>
        </form>
        <div style="display:flex; gap:10px; margin-top:15px;">
            <button type="button" onclick="guardarClienteModal()" style="background:#28a745; color:white; padding:8px 16px; border:none; border-radius:4px;cursor:pointer;">💾 Guardar</button>
            <button type="button" onclick="cerrarModalCliente()" style="background:#dc3545; color:white; padding:8px 16px; border:none; border-radius:4px;cursor:pointer;">✖ Cerrar</button>
        </div>                
    </div>
</div>

<!-- Modal para LISTAR clientes -->
<div id="modalListarClientes" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1010;">
    <div class="seccion" style="max-width:800px; margin:5% auto; position:relative; max-height: 80vh; display: flex; flex-direction: column;">
        <h3>👥 Listado de Clientes</h3>
        <hr>
        <input type="text" id="buscadorClientes" onkeyup="filtrarClientes()" placeholder="Buscar por nombre, RUC o cédula..." style="margin-bottom: 15px; padding: 10px;">
        <div style="overflow-y: auto;">
            <table class="tabla-pos">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre / Razón Social</th>
                        <th>Email</th>
                        <th style="width: 100px;">Acción</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaClientes"></tbody>
            </table>
        </div>
        <div style="text-align:right; margin-top:15px;">
            <button type="button" onclick="cerrarModalListarClientes()" style="background:#dc3545; color:white; padding:8px 16px; border:none; border-radius:4px;cursor:pointer; width: auto;">✖ Cerrar</button>
        </div>
    </div>
</div>

<script src="../../js/pos_ruc_util.js"></script>
<script src="../../js/pos_ventas.js"></script>
</body>
</html>