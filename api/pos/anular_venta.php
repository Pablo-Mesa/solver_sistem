<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$venta_id = $input['id'] ?? null;

if (!$venta_id) {
    echo json_encode(['status' => 'error', 'mensaje' => 'ID requerido']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verificar estado actual
    $stmt = $pdo->prepare("SELECT estado FROM pos_ventas_cabecera WHERE id = ?");
    $stmt->execute([$venta_id]);
    $estado = $stmt->fetchColumn();

    if ($estado == 0) {
        throw new Exception("La venta ya está anulada.");
    }

    // 2. Obtener productos para devolver stock
    $stmtDet = $pdo->prepare("SELECT producto_id, cantidad FROM pos_ventas_detalle WHERE venta_id = ?");
    $stmtDet->execute([$venta_id]);
    $items = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

    // 3. Devolver Stock
    $stmtUpdStock = $pdo->prepare("UPDATE pos_productos SET stock_actual = stock_actual + ? WHERE id = ?");
    foreach ($items as $item) {
        $stmtUpdStock->execute([$item['cantidad'], $item['producto_id']]);
    }

    // 4. Marcar venta como anulada (estado 0)
    $stmtAnular = $pdo->prepare("UPDATE pos_ventas_cabecera SET estado = 0 WHERE id = ?");
    $stmtAnular->execute([$venta_id]);

    $pdo->commit();
    echo json_encode(['status' => 'ok', 'mensaje' => 'Venta anulada y stock repuesto.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}
?>