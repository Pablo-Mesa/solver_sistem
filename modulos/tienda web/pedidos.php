<?php 
    require_once '../../includes/auth.php';
?>
<!DOCTYPE html>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solver | Pedidos Web</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body {
            background-color: #f4f7f9;
        }
        .main-container {
            width: 95%;
            max-width: 1400px;
            margin: 20px auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .page-header h1 {
            font-size: 26px;
            color: #333;
        }
        .btn-back {
            text-decoration: none;
            background-color: #6c757d;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 500;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95em;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .status-select {
            padding: 5px 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: white;
        }
        .status-pendiente { background-color: #ffc107; color: #333; }
        .status-procesado { background-color: #28a745; color: white; }
        .status-cancelado { background-color: #dc3545; color: white; }

        .btn-ver {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        /* Estilos del Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 700px;
            border-radius: 8px;
        }
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        #detalle-info-cliente {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>

</head>

<body>

    <div class="main-container">
        <header class="page-header">
            <h1>Pedidos de la Tienda Web</h1>
            <a href="../../dashboard.php" class="btn-back">← Volver al Dashboard</a>
        </header>

        <div class="table-container">
            <table id="tabla-pedidos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Entrega</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-tabla-pedidos">
                    <tr><td colspan="8" style="text-align:center;">Cargando pedidos...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <div id="modal-detalle" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="cerrarModal()">&times;</span>
            <h2>Detalles del Pedido #<span id="detalle-pedido-id"></span></h2>
            <div id="detalle-info-cliente"></div>
            <table id="tabla-detalle-productos">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-tabla-detalle">
                    <!-- Detalles del producto aquí -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/pedidos_web.js"></script>
</body>

</html>