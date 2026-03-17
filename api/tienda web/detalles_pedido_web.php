<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'ID de pedido no proporcionado.']);
    exit;
}

try {
    // Obtener la cabecera del pedido
    $stmt_cab = $pdo->prepare("SELECT * FROM pos_pedidos_web WHERE id = ?");
    $stmt_cab->execute([$id]);
    $cabecera = $stmt_cab->fetch(PDO::FETCH_ASSOC);

    if (!$cabecera) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'mensaje' => 'Pedido no encontrado.']);
        exit;
    }

    // Obtener los detalles (productos) del pedido
    $stmt_det = $pdo->prepare("
        SELECT 
            d.cantidad, 
            d.precio_unitario, 
            d.subtotal, 
            p.nombre AS producto_nombre
        FROM pos_pedidos_web_detalle d
        JOIN pos_productos p ON d.producto_id = p.id
        WHERE d.pedido_id = ?
    ");
    $stmt_det->execute([$id]);
    $detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);

    // Devolver ambos (cabecera y detalles)
    echo json_encode(['status' => 'ok', 'datos' => ['cabecera' => $cabecera, 'detalles' => $detalles]]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al consultar detalles: ' . $e->getMessage()]);
}
?>