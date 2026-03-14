
<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

try {
    $sql = "SELECT p.nombre, SUM(d.cantidad) as total_vendido
            FROM pos_ventas_detalle d
            JOIN pos_productos p ON d.producto_id = p.id
            JOIN pos_ventas_cabecera c ON d.venta_id = c.id
            WHERE c.estado = 1
            GROUP BY p.id
            ORDER BY total_vendido DESC
            LIMIT 5";
            
    $stmt = $pdo->query($sql);
    $top = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'datos' => $top]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}