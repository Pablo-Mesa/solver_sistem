<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$mes = $_GET['mes'] ?? date('m');
$anio = $_GET['anio'] ?? date('Y');

try {
    // Reporte de Ventas
    $sqlVentas = "SELECT 
                    COALESCE(SUM(gravada_10), 0) as g10, 
                    COALESCE(SUM(iva_10), 0) as i10, 
                    COALESCE(SUM(gravada_5), 0) as g5, 
                    COALESCE(SUM(iva_5), 0) as i5, 
                    COALESCE(SUM(exenta), 0) as ex, 
                    COALESCE(SUM(total_venta), 0) as total 
                  FROM pos_ventas_cabecera 
                  WHERE MONTH(fecha_hora) = ? AND YEAR(fecha_hora) = ? AND estado = 1";
    
    $stmtV = $pdo->prepare($sqlVentas);
    $stmtV->execute([$mes, $anio]);
    $ventas = $stmtV->fetch(PDO::FETCH_ASSOC);

    // Reporte de Compras
    $sqlCompras = "SELECT 
                    COALESCE(SUM(gravada_10), 0) as g10, 
                    COALESCE(SUM(iva_10), 0) as i10, 
                    COALESCE(SUM(gravada_5), 0) as g5, 
                    COALESCE(SUM(iva_5), 0) as i5, 
                    COALESCE(SUM(exenta), 0) as ex, 
                    COALESCE(SUM(total_factura), 0) as total 
                   FROM pos_compras_cabecera 
                   WHERE MONTH(fecha_emision) = ? AND YEAR(fecha_emision) = ? AND estado = 1";
    
    $stmtC = $pdo->prepare($sqlCompras);
    $stmtC->execute([$mes, $anio]);
    $compras = $stmtC->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'ok',
        'ventas' => $ventas,
        'compras' => $compras
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}