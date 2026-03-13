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
    
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { background-color: #f4f6f9; font-family: Arial, sans-serif; }
        .container { width: 100%; max-width: 1000px; margin: 0 auto; padding: 16px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .header-reporte { display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 20px; width: 100%;  }
        .filtros form { display: flex; gap: 10px; align-items: center; width: 100%; }
        .filtros select, .filtros button  { padding: 8px; border-radius: 4px; border: 1px solid #ddd; }
        .filtros input[type='number'] { padding: 8px; border-radius: 4px; border: 1px solid #ddd; width: 80px; font-size: 0.9rem; }
        .filtros button { background: #007bff; color: white; border: none; cursor: pointer; width: 100px;}
        
        .resumen-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #ccc; }
        .stat-box.ventas { border-left-color: #28a745; }
        .stat-box.compras { border-left-color: #dc3545; }
        .stat-box h3 { margin: 0 0 10px 0; font-size: 1.1em; color: #555; }
        .stat-box .monto { font-size: 1.5em; font-weight: bold; color: #333; }
        
        .acciones-exportar { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .btn-export { 
            display: inline-flex; align-items: center; gap: 8px;
            text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; transition: 0.2s; 
        }
        .btn-marangatu { background-color: #6f42c1; color: white; } /* Color estilo Marangatu/SET */
        .btn-marangatu:hover { background-color: #5a32a3; }
        
        .info-text { font-size: 0.85em; color: #666; margin-top: 5px; }
        .resumen-iva { background: #343a40; color: white; padding: 20px; margin-top: 20px; border-radius: 8px; text-align: center; }

        .grid-reporte { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card-reporte { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        
        /* --- RESPONSIVE MOBILE --- */
        @media (max-width: 768px) {
            .container { margin: 10px auto; padding: 15px; width: 95%; }
            .header-reporte { flex-direction: column; align-items: flex-start; gap: 15px; }
            
            .filtros { width: 100%; }
            .filtros form { flex-direction: column; width: 100%; gap: 10px; }
            .filtros select, .filtros button { width: 100%; height: 44px; /* Mayor área táctil */ }
            
            .resumen-grid { grid-template-columns: 1fr; gap: 15px; }
            
            .acciones-exportar div { 
                flex-direction: column; 
                width: 100%; 
            }
            .btn-export { 
                width: 100%; 
                justify-content: center; 
                padding: 15px; 
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">

        <div class="header-reporte">
            <h2>📊 Reporte Mensual (IVA)</h2>
            <div style="text-align: center;">
                <a href="../../dashboard.php" style="color: #666; text-decoration: none;">&larr; Volver al Inicio</a>
            </div>            
        </div>

        <div class="header-reporte">
            <div class="filtros">
                <div>
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
                    <button id="btnConsultar">Consultar</button>
                </div>
                                
                
            </div>
        </div>
        
        <div class="resumen-grid">
            <div class="stat-box ventas">
                <h3>📈 Resumen de Ventas <small>(Débito Fiscal)</small></h3>                
                <table id="tablaVentas">
                </table>
            </div>
            <div class="stat-box compras">
                <h3>📉 Resumen de Compras <small>(Crédito Fiscal)</small></h3>
                <table id="tablaCompras">
                </table>
            </div>
        </div>

        <div class="resumen-iva">
            <h3>Resultado de IVA del Mes: <span id="resultado_iva">0</span> Gs.</h3>
            <p id="mensaje_iva"></p>
        </div>

        <div class="acciones-exportar">
            <h3>Exportar para Marangatu / Hechauka</h3>
            <p class="info-text">Genera archivos CSV compatibles con Excel para la importación de libros IVA.</p>
            
            <div style="margin-top: 15px; display: flex; gap: 15px;">
                <button id="btnExportarVentas" class="btn-export btn-marangatu">📄 Descargar Libro Ventas</button>
                <button id="btnExportarCompras" class="btn-export btn-marangatu">📄 Descargar Libro Compras</button>
            </div>            

        </div>
    </div>
</div>

<script src="../../js/pos_reporte_mensual.js"></script>

</body>
</html>