<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php'; 

try {
    // Seleccionamos solo lo necesario para el selector de compras
    // Traemos el nombre de la categoría haciendo un JOIN para que sea más amigable
    $sql = "SELECT p.id, p.nombre, p.iva_tasa, p.precio_costo, c.nombre as categoria, p.precio_venta, p.stock_actual 
            FROM pos_productos p
            LEFT JOIN pos_categorias c ON p.categoria_id = c.id
            WHERE p.estado = 1 
            ORDER BY p.nombre ASC";

    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'ok',
        'datos' => $productos
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error al listar productos: ' . $e->getMessage()
    ]);
}