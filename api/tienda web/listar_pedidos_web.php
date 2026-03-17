<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';

try {
    // Consulta para obtener los pedidos web, ordenados por los más nuevos primero
    $stmt = $pdo->query("
        SELECT 
            id,
            nombre_contacto,
            telefono_contacto,
            tipo_entrega,
            total_estimado,
            estado,
            fecha_creacion
        FROM 
            pos_pedidos_web
        ORDER BY 
            fecha_creacion DESC
    ");

    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'datos' => $pedidos]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al consultar pedidos: ' . $e->getMessage()]);
}
?>