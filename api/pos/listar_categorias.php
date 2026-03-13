<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

try {
    $stmt = $pdo->query("SELECT id, nombre FROM pos_categorias ORDER BY nombre ASC");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'ok', 'datos' => $categorias]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al consultar categorías.']);
}
?>