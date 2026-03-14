<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

try {
    // Traemos las ventas ordenadas por la más reciente
    $sql = "SELECT id, nro_factura, fecha_hora, total_venta, estado 
            FROM pos_ventas_cabecera 
            ORDER BY fecha_hora DESC";
            
    $stmt = $pdo->query($sql);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'datos' => $ventas]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}