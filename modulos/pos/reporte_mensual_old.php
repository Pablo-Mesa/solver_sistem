
<?php
require_once '../../includes/auth.php';
$mes_actual = (int) date('n');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Cierre de Mes</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <style>
        *{
            margin: 0px;
            padding: 0px;
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;            
        }
        
        body { font-family: sans-serif; background: #f4f7f6; padding: 10px 20px; }
        .contenedor { width: 100%; max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .grid-reporte { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .total-row { font-weight: bold; background: #f9f9f9; }
        .resumen-iva { background: #343a40; color: white; padding: 20px; margin-top: 20px; border-radius: 8px; text-align: center; }
    </style>
</head>
<body>
    
<div class="contenedor">

    <div class="cabecera">
        <div style="display: flex; flex-direction:row; justify-content: space-between; align-items: center;  margin-bottom: 20px; ">
        <h2>📊 Cierre de Mes / Liquidación de IVA</h2>
        <a href="../../dashboard.php" style="text-decoration: none; color: #666;">← Volver al Menú</a>
    </div>

    <div style="margin-bottom: 20px;">
        <select id="mes">
            <option value="1"<?php echo $mes_actual === 1 ? ' selected' : ''; ?>>Enero</option>
            <option value="2"<?php echo $mes_actual === 2 ? ' selected' : ''; ?>>Febrero</option>
            <option value="3"<?php echo $mes_actual === 3 ? ' selected' : ''; ?>>Marzo</option>
            <option value="4"<?php echo $mes_actual === 4 ? ' selected' : ''; ?>>Abril</option>
            <option value="5"<?php echo $mes_actual === 5 ? ' selected' : ''; ?>>Mayo</option>
            <option value="6"<?php echo $mes_actual === 6 ? ' selected' : ''; ?>>Junio</option>
            <option value="7"<?php echo $mes_actual === 7 ? ' selected' : ''; ?>>Julio</option>
            <option value="8"<?php echo $mes_actual === 8 ? ' selected' : ''; ?>>Agosto</option>
            <option value="9"<?php echo $mes_actual === 9 ? ' selected' : ''; ?>>Septiembre</option>
            <option value="10"<?php echo $mes_actual === 10 ? ' selected' : ''; ?>>Octubre</option>
            <option value="11"<?php echo $mes_actual === 11 ? ' selected' : ''; ?>>Noviembre</option>
            <option value="12"<?php echo $mes_actual === 12 ? ' selected' : ''; ?>>Diciembre</option>
        </select>
        <input type="number" id="anio" value="<?php echo date('Y'); ?>">
        <button onclick="generarReporte()">Consultar</button>
    </div>

    <div class="grid-reporte">
        <div class="card">
            <h3>📈 Resumen de Ventas (Débito Fiscal)</h3>
            <table id="tablaVentas">
            </table>
        </div>
        <div class="card">
            <h3>📉 Resumen de Compras (Crédito Fiscal)</h3>
            <table id="tablaCompras">
            </table>
        </div>
    </div>

    <div class="resumen-iva">
        <h3>Resultado de IVA del Mes: <span id="resultado_iva">0</span> Gs.</h3>
        <p id="mensaje_iva"></p>
    </div>

    <script src="../../js/pos_reporte_mensual.js"></script>
</body>
</html>