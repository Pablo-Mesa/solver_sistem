<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$input = json_decode(file_get_contents('php://input'), true);

$venta_id = $input['id'] ?? null;
$nuevo_nro = trim($input['nro_factura'] ?? '');

if (!$venta_id || empty($nuevo_nro)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'ID de venta y nuevo número de factura son requeridos.']);
    exit;
}

try {
    $sql = "UPDATE pos_ventas_cabecera SET nro_factura = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nuevo_nro, $venta_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'ok', 'mensaje' => 'Número de factura actualizado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'No se encontró la venta o no hubo cambios.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>