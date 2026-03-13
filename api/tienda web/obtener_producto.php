<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'ID de producto no proporcionado.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nombre,
            descripcion_web,
            precio_venta,
            stock_actual,
            imagen_url,
            categoria_id
        FROM 
            pos_productos 
        WHERE 
            id = ?
    ");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        echo json_encode(['status' => 'ok', 'datos' => $producto]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'mensaje' => 'Producto no encontrado.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>