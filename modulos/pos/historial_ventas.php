<?php require_once '../../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Historial de Ventas</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/styles-for-tool-kit-v001.css">
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 10px; }
        .contenedor { width: 100%; max-width: 1100px; margin: 0 auto; background: white; padding: 10px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tabla-pos { width: 100%; border-collapse: collapse;}
        .tabla-pos thead { display: table; width: 100%; table-layout: fixed; }
        .tabla-pos tbody { display: block; max-height: 400px; overflow-y: auto; }
        .tabla-pos tbody tr { display: table; width: 100%; table-layout: fixed; }
        .tabla-pos th { background: #28a745; color: white; padding: 12px; }
        .tabla-pos td { padding: 10px; border: 1px solid #eee; text-align: center; }
        .anulado { text-decoration: line-through; color: #b22222; opacity: 0.6; }
        .btn-anular { background: #dc3545; color: white; border: none; padding: 5px; cursor: pointer; border-radius: 3px; }

        @media (max-width: 768px) {
            .tabla-pos {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            /* Se resetean los estilos del 'tbody' para que el scroll horizontal funcione correctamente */
            .tabla-pos thead, .tabla-pos tbody, .tabla-pos tbody tr {
                display: revert;
            }
        }
        /* Estilo para el número de factura editable (Pedidos Web) */
        .nro-factura-editable {
            cursor: pointer;
            text-decoration: underline;
            text-decoration-style: dotted;
            color: #0d6efd;
            font-weight: bold;
        }
        .nro-factura-editable:hover {
            color: #0a58ca;
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>

<div class="contenedor">
    

    <div style="display: flex; flex-direction:row; justify-content: space-between; align-items: center;  margin-bottom: 20px; ">
        <h2>📋 Historial de Ventas</h2>
        <a href="../../dashboard.php" style="text-decoration: none; color: #666;">← Volver al Menú</a>
    </div>
    
    <table class="tabla-pos">
        <thead>
            <tr>
                <th>Fecha/Hora</th>
                <th>Nro. Factura</th>
                <th>Total Venta</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="cuerpoHistorialVentas">
            </tbody>
    </table>
</div>

<div id="modalDetalleVenta" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background:white; width:70%; margin: 50px auto; padding:20px; border-radius:8px; max-height:80vh; overflow-y:auto;">
        <h3>Detalle de Venta: <span id="det_nro_venta"></span></h3>
        <p>Fecha/Hora: <strong id="det_fecha_venta"></strong></p>
        <table class="tabla-pos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody id="cuerpoDetalleVentaModal"></tbody>
        </table>
        <div style="text-align:right; margin-top:20px;">
            <button onclick="cerrarModalVenta()" style="padding:10px 20px; cursor:pointer;">Cerrar</button>
        </div>
    </div>
</div>

<script src="../../js/pos_historial_ventas.js"></script>
</body>
</html>