<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php'; 

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "mensaje" => "No se recibieron datos"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // A. Calcular totales de IVA para la tabla Cabecera (Doble chequeo)
    $g10 = 0; $i10 = 0; $g5 = 0; $i5 = 0; $ex = 0; $totalFactura = 0;

    foreach ($data['items'] as $item) {
        $sub = $item['subtotal'];
        $totalFactura += $sub;
        if ($item['tasa'] == 10) { $g10 += $sub / 1.1; $i10 += $sub / 11; }
        elseif ($item['tasa'] == 5) { $g5 += $sub / 1.05; $i5 += $sub / 21; }
        else { $ex += $sub; }
    }

    // B. Insertar en pos_compras_cabecera (estado=1 para compras válidas)
    // agregamos la columna estado con valor 1; si la tabla tiene un DEFAULT diferente,
    // conviene ajustarla en la base de datos también.
    $sqlCab = "INSERT INTO pos_compras_cabecera 
               (proveedor_id, timbrado, nro_comprobante, fecha_emision, gravada_10, iva_10, gravada_5, iva_5, exenta, total_factura, estado) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
    $stmtCab = $pdo->prepare($sqlCab);
    $stmtCab->execute([
        $data['proveedor_id'], $data['timbrado'], $data['nro_factura'], 
        $data['fecha_emision'], $g10, $i10, $g5, $i5, $ex, $totalFactura
    ]);
    
    $idCompra = $pdo->lastInsertId();

    // C. Insertar Detalle y Actualizar Stock/Costo
    $sqlDet = "INSERT INTO pos_compras_detalle (compra_id, producto_id, cantidad, precio_unitario_costo, subtotal) VALUES (?, ?, ?, ?, ?)";
    $stmtDet = $pdo->prepare($sqlDet);

    $sqlStock = "UPDATE pos_productos SET stock_actual = stock_actual + ?, precio_costo = ? WHERE id = ?";
    $stmtStock = $pdo->prepare($sqlStock);

    error_log("Procesando " . count($data['items']) . " items para compra " . $idCompra);

    foreach ($data['items'] as $item) {
        error_log("Item: prod_id={$item['id']}, cant={$item['cantidad']}, precio={$item['precio']}, sub={$item['subtotal']}");
        $stmtDet->execute([$idCompra, $item['id'], $item['cantidad'], $item['precio'], $item['subtotal']]);
        $stockResult = $stmtStock->execute([$item['cantidad'], $item['precio'], $item['id']]);
        error_log("Stock update result: " . ($stockResult ? "OK" : "FAILED"));
    }

    $pdo->commit();
    echo json_encode(["status" => "ok", "mensaje" => "Compra registrada y stock actualizado con éxito"]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}