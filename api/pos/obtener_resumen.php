<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

try {
    // 1. Ventas del día (Estado 1 = Activo)
    $sqlVentas = "SELECT SUM(total_venta) as total FROM pos_ventas_cabecera 
                  WHERE DATE(fecha_hora) = CURDATE() AND estado = 1";
    $resVentas = $pdo->query($sqlVentas)->fetch();
    $totalVentasDia = $resVentas['total'] ?? 0;

    // 2. Alerta de Stock Bajo (menos de 5 unidades, por ejemplo)
    $sqlStock = "SELECT COUNT(*) as bajo FROM pos_productos WHERE stock_actual < 5 AND estado = 1";
    $resStock = $pdo->query($sqlStock)->fetch();
    $alertasStock = $resStock['bajo'] ?? 0;

    // 3. Cantidad de ventas del día
    $sqlCant = "SELECT COUNT(*) as cant FROM pos_ventas_cabecera 
                WHERE DATE(fecha_hora) = CURDATE() AND estado = 1";
    $resCant = $pdo->query($sqlCant)->fetch();
    $cantVentas = $resCant['cant'] ?? 0;

    echo json_encode([
        'status' => 'ok',
        'ventas_hoy' => $totalVentasDia,
        'alertas_stock' => $alertasStock,
        'conteo_ventas' => $cantVentas
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}