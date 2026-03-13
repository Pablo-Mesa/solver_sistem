<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

try {
    // Obtenemos ventas de los últimos 7 días
    $sql = "SELECT DATE(fecha_hora) as fecha, SUM(total_venta) as total 
            FROM pos_ventas_cabecera 
            WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            AND estado = 1
            GROUP BY DATE(fecha_hora)
            ORDER BY DATE(fecha_hora) ASC";
            
    $stmt = $pdo->query($sql);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'datos' => $datos]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}