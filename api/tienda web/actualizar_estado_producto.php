<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

// Solo aceptamos peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

// Obtenemos los datos del cuerpo de la petición
$idProducto = $_POST['id'] ?? null;
$publicado = $_POST['publicado'] ?? null;

// Validaciones básicas
if ($idProducto === null || $publicado === null) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'mensaje' => 'Faltan parámetros requeridos (id, publicado).']);
    exit;
}

$estadoFinal = ($publicado === '1') ? 1 : 0;

try {
    // Preparamos la consulta para actualizar el estado
    $sql = "UPDATE pos_productos SET publicado_web = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([$estadoFinal, $idProducto]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'ok', 'mensaje' => 'Estado del producto actualizado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'El producto no fue encontrado o no hubo cambios.']);
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al actualizar en la base de datos: ' . $e->getMessage()]);
}
?>