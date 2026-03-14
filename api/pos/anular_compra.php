<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$data = json_decode(file_get_contents("php://input"), true);
$idCompra = $data['id'] ?? 0;

if (!$idCompra) {
    echo json_encode(["status" => "error", "mensaje" => "ID de compra no válido"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verificar si ya está anulada o si existe
    $stmt = $pdo->prepare("SELECT estado FROM pos_compras_cabecera WHERE id = ?");
    $stmt->execute([$idCompra]);
    $compra = $stmt->fetch();

    if (!$compra || $compra['estado'] == 0) {
        throw new Exception("La compra ya está anulada o no existe.");
    }

    // 2. Obtener el detalle para saber qué productos restar del stock
    $stmtDet = $pdo->prepare("SELECT producto_id, cantidad FROM pos_compras_detalle WHERE compra_id = ?");
    $stmtDet->execute([$idCompra]);
    $items = $stmtDet->fetchAll();

    // 3. Restar el stock (Validando que no quede en negativo si prefieres ser estricto)
    $sqlStock = "UPDATE pos_productos SET stock_actual = stock_actual - ? WHERE id = ?";
    $stmtStock = $pdo->prepare($sqlStock);

    foreach ($items as $item) {
        $stmtStock->execute([$item['cantidad'], $item['producto_id']]);
    }

    // 4. Marcar la cabecera como anulada (estado = 0)
    $stmtAnular = $pdo->prepare("UPDATE pos_compras_cabecera SET estado = 0 WHERE id = ?");
    $stmtAnular->execute([$idCompra]);

    $pdo->commit();
    echo json_encode(["status" => "ok", "mensaje" => "Compra anulada y stock ajustado correctamente."]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}