<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$id = $_GET['id'] ?? 0;

try {
    $sql = "SELECT d.*, p.nombre 
            FROM pos_ventas_detalle d
            JOIN pos_productos p ON d.producto_id = p.id
            WHERE d.venta_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'datos' => $items]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}