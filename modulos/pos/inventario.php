<?php 
    require_once '../../includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Inventario</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/styles-for-tool-kit-v001.css">    
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 10px 20px; }
        .contenedor { width: 100%; max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        /*h2 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }*/
        .tabla-pos { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tabla-pos th, .tabla-pos td { padding: 12px; border: 1px solid #eee; text-align: left; }
        .tabla-pos th { background-color: #f8f9fa; color: #666; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; background: #e8f5e9; color: #2e7d32; }
        .stock-bajo { color: #d32f2f; font-weight: bold; }

        @media (max-width: 768px) {
            .tabla-pos {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>

<div class="contenedor">
    <div class="cabecera">

        <div style="display: flex; flex-direction:row; justify-content: space-between; align-items: center;  margin-bottom: 20px; ">
            <h2>📦 Control de Inventario</h2>
            <a href="../../dashboard.php" style="text-decoration: none; color: #666;">← Volver al Menú</a>
        </div>
        <p>Listado de existencias reales en base de datos.</p>
    </div>

    <table class="tabla-pos">
        <thead>
            <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>IVA</th>
                <th>Stock Actual</th>
                <th>Precio Venta</th>
            </tr>
        </thead>
        <tbody id="cuerpoInventario">
            </tbody>
    </table>
</div>

<script src="../../js/pos_inventario.js"></script>
</body>
</html>