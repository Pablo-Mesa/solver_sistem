<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$data = json_decode(file_get_contents("php://input"), true);
$idVenta = $data['id'] ?? 0;

if (!$idVenta) {
    echo json_encode(["status" => "error", "mensaje" => "ID de venta no válido"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verificamos si la venta existe y su estado actual
    $stmt = $pdo->prepare("SELECT estado FROM pos_ventas_cabecera WHERE id = ?");
    $stmt->execute([$idVenta]);
    $venta = $stmt->fetch();

    if (!$venta) throw new Exception("Venta no encontrada.");
    if ($venta['estado'] == 0) throw new Exception("Esta venta ya fue anulada anteriormente.");

    // 2. Obtenemos los productos vendidos para DEVOLVERLOS al stock
    $stmtDet = $pdo->prepare("SELECT producto_id, cantidad FROM pos_ventas_detalle WHERE venta_id = ?");
    $stmtDet->execute([$idVenta]);
    $items = $stmtDet->fetchAll();

    // 3. Actualizamos el stock (SUMAMOS porque la venta se cancela)
    $sqlStock = "UPDATE pos_productos SET stock_actual = stock_actual + ? WHERE id = ?";
    $stmtStock = $pdo->prepare($sqlStock);

    foreach ($items as $item) {
        $stmtStock->execute([$item['cantidad'], $item['producto_id']]);
    }

    // 4. Marcamos la factura como anulada
    $stmtAnular = $pdo->prepare("UPDATE pos_ventas_cabecera SET estado = 0 WHERE id = ?");
    $stmtAnular->execute([$idVenta]);

    $pdo->commit();
    echo json_encode(["status" => "ok", "mensaje" => "Venta anulada. El stock ha sido devuelto al inventario."]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}