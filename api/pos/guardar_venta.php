<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php'; // Seguridad ante todo

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['items'])) {
    echo json_encode(["status" => "error", "mensaje" => "Datos incompletos"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // A. Calcular totales de IVA
    $g10 = 0; $i10 = 0; $g5 = 0; $i5 = 0; $ex = 0; $totalVenta = 0;

    foreach ($data['items'] as $item) {
        $sub = $item['subtotal'];
        $totalVenta += $sub;
        if ($item['tasa'] == 10) { $g10 += $sub / 1.1; $i10 += $sub / 11; }
        elseif ($item['tasa'] == 5) { $g5 += $sub / 1.05; $i5 += $sub / 21; }
        else { $ex += $sub; }
    }

    // B. Insertar Cabecera de Venta (timbrado y punto_emision desde Datos del negocio)
    $sqlCab = "INSERT INTO pos_ventas_cabecera 
               (cliente_id, nro_factura, timbrado, punto_emision, gravada_10, iva_10, gravada_5, iva_5, exenta, total_venta) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtCab = $pdo->prepare($sqlCab);
    $clienteId = isset($data['cliente_id']) && $data['cliente_id'] !== '' ? $data['cliente_id'] : null;
    $timbrado = !empty($data['timbrado']) ? $data['timbrado'] : null;
    $puntoEmision = !empty($data['punto_emision']) ? $data['punto_emision'] : null;
    $stmtCab->execute([
        $clienteId,
        $data['nro_factura'],
        $timbrado,
        $puntoEmision,
        $g10, $i10, $g5, $i5, $ex, $totalVenta
    ]);
    
    $idVenta = $pdo->lastInsertId();

    // C. Insertar Detalle y RESTAR Stock
    $sqlDet = "INSERT INTO pos_ventas_detalle (venta_id, producto_id, cantidad, precio_unitario_venta, subtotal) VALUES (?, ?, ?, ?, ?)";
    $stmtDet = $pdo->prepare($sqlDet);

    $sqlStock = "UPDATE pos_productos SET stock_actual = stock_actual - ? WHERE id = ? AND stock_actual >= ?";
    $stmtStock = $pdo->prepare($sqlStock);

    foreach ($data['items'] as $item) {
        // Guardar detalle
        $stmtDet->execute([$idVenta, $item['id'], $item['cantidad'], $item['precio'], $item['subtotal']]);
        
        // Restar stock con doble validación en el WHERE para evitar negativos por concurrencia
        $stmtStock->execute([$item['cantidad'], $item['id'], $item['cantidad']]);
        
        if ($stmtStock->rowCount() == 0) {
            throw new Exception("Error: Stock insuficiente para el producto ID: " . $item['id']);
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "ok", "mensaje" => "Venta procesada con éxito y stock actualizado."]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}