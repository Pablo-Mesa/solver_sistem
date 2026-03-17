<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'mensaje' => 'ID no proporcionado']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            p.nombre, 
            d.cantidad, 
            d.precio_unitario AS precio_unitario_venta, 
            d.subtotal 
        FROM pos_ventas_detalle d
        JOIN pos_productos p ON d.producto_id = p.id
        WHERE d.venta_id = ?
    ");
    $stmt->execute([$id]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'datos' => $datos]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}
?>