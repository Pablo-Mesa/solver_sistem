<?php 
    require_once '../../includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Historial de Compras</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/styles-for-tool-kit-v001.css">
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 10px; }
        .contenedor { width: 100%; max-width: 1100px; margin: 0 auto; background: white; padding: 10px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tabla-pos { width: 100%; border-collapse: collapse; }
        .tabla-pos thead { display: table; width: 100%; table-layout: fixed; }
        .tabla-pos tbody { display: block; max-height: 400px; overflow-y: auto; }
        .tabla-pos tbody tr { display: table; width: 100%; table-layout: fixed; }
        .tabla-pos th { background: #444; color: white; padding: 12px; font-size: 0.9em; }
        .tabla-pos td { padding: 10px; border: 1px solid #eee; text-align: center; font-size: 0.9em; }
        .btn-ver { background: #17a2b8; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
        .btn-anular { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
        .status-anulado { text-decoration: line-through; color: #999; }

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
    </style>
</head>
<body>

<div class="contenedor">

    <div style="display: flex; flex-direction:row; justify-content: space-between; align-items: center;  margin-bottom: 20px; ">
        <h2>📋 Historial de Compras</h2>
        <a href="../../dashboard.php" style="text-decoration: none; color: #666;">← Volver al Menú</a>
    </div>

    <table class="tabla-pos">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Nro. Factura</th>
                <th>Total (Gs.)</th>
                <th>IVA 10%</th>
                <th>IVA 5%</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="cuerpoHistorial">
            </tbody>
    </table>
</div>

<div id="modalDetalle" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background:white; width:70%; margin: 50px auto; padding:20px; border-radius:8px; max-height:80vh; overflow-y:auto;">
        <h3>Detalle de Factura: <span id="det_nro"></span></h3>
        <p>Proveedor: <strong id="det_proveedor"></strong></p>
        <table class="tabla-pos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Costo Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody id="cuerpoDetalleModal"></tbody>
        </table>
        <div style="text-align:right; margin-top:20px;">
            <button onclick="cerrarModal()" style="padding:10px 20px; cursor:pointer;">Cerrar</button>
        </div>
    </div>
</div>

<script src="../../js/pos_historial_compras.js"></script>
</body>
</html>