<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$pedido_id = $input['id'] ?? null;
$nuevo_estado = $input['estado'] ?? null;

if (!$pedido_id || !in_array($nuevo_estado, ['procesado', 'cancelado', 'pendiente'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Datos inválidos.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Obtener el estado actual para evitar acciones repetidas o inválidas
    $stmt_check = $pdo->prepare("SELECT estado FROM pos_pedidos_web WHERE id = ?");
    $stmt_check->execute([$pedido_id]);
    $estado_actual = $stmt_check->fetchColumn();

    if ($estado_actual === $nuevo_estado) {
        $pdo->commit(); // No hay error, simplemente no hacemos nada.
        echo json_encode(['status' => 'ok', 'mensaje' => 'El pedido ya se encontraba en ese estado.']);
        exit;
    }
    
    // 2. Lógica de gestión de stock
    if ($nuevo_estado === 'procesado' && $estado_actual !== 'procesado') {
        // Descontar stock al pasar a 'procesado'
        $stmt_items = $pdo->prepare("SELECT producto_id, cantidad, precio_unitario, subtotal FROM pos_pedidos_web_detalle WHERE pedido_id = ?");
        $stmt_items->execute([$pedido_id]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        $stmt_update_stock = $pdo->prepare("UPDATE pos_productos SET stock_actual = stock_actual - ? WHERE id = ?");
        foreach ($items as $item) {
            $stmt_update_stock->execute([$item['cantidad'], $item['producto_id']]);
        }

        // --- GENERAR PRE-VENTA AUTOMÁTICA EN POS ---
        
        // A. Verificar si ya existe venta para este pedido (evitar duplicados si se procesa 2 veces)
        $nro_ref_web = "WEB-" . str_pad($pedido_id, 6, "0", STR_PAD_LEFT);
        $stmt_check_venta = $pdo->prepare("SELECT id FROM pos_ventas_cabecera WHERE nro_factura = ?");
        $stmt_check_venta->execute([$nro_ref_web]);

        if (!$stmt_check_venta->fetchColumn()) {
            // B. Obtener datos cabecera del pedido
            $stmt_ped = $pdo->prepare("SELECT * FROM pos_pedidos_web WHERE id = ?");
            $stmt_ped->execute([$pedido_id]);
            $pedido_info = $stmt_ped->fetch(PDO::FETCH_ASSOC);

            // C. Resolver Cliente: Buscar si existe por nombre, sino crear uno genérico
            $cliente_nombre = $pedido_info['nombre_contacto'] ?: 'Cliente Web';
            $stmt_cli = $pdo->prepare("SELECT id FROM pos_clientes WHERE razon_social = ? LIMIT 1");
            $stmt_cli->execute([$cliente_nombre]);
            $cliente_id_venta = $stmt_cli->fetchColumn();

            if (!$cliente_id_venta) {
                // Crear cliente rápido (usamos time() como doc temporal para evitar error de duplicados)
                $stmt_new_cli = $pdo->prepare("INSERT INTO pos_clientes (razon_social, documento, email) VALUES (?, ?, '')");
                $stmt_new_cli->execute([$cliente_nombre, time()]); 
                $cliente_id_venta = $pdo->lastInsertId();
            }

            // D. Insertar Venta en el POS (Estado 1 = Activa)
            $stmt_insert_venta = $pdo->prepare("INSERT INTO pos_ventas_cabecera (cliente_id, nro_factura, fecha_hora, total_venta, estado) VALUES (?, ?, NOW(), ?, 1)");
            $stmt_insert_venta->execute([$cliente_id_venta, $nro_ref_web, $pedido_info['total_estimado']]);
            $venta_id = $pdo->lastInsertId();

            // E. Insertar Detalle Venta
            $stmt_insert_det = $pdo->prepare("INSERT INTO pos_ventas_detalle (venta_id, producto_id, cantidad, precio_unitario_venta, subtotal) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $row) {
                $stmt_insert_det->execute([$venta_id, $row['producto_id'], $row['cantidad'], $row['precio_unitario'], $row['subtotal']]);
            }
        }
    } else if ($nuevo_estado !== 'procesado' && $estado_actual === 'procesado') {
        // Reponer stock si se cancela o vuelve a pendiente un pedido que ya estaba 'procesado'
        $stmt_items = $pdo->prepare("SELECT producto_id, cantidad FROM pos_pedidos_web_detalle WHERE pedido_id = ?");
        $stmt_items->execute([$pedido_id]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        $stmt_update_stock = $pdo->prepare("UPDATE pos_productos SET stock_actual = stock_actual + ? WHERE id = ?");
        foreach ($items as $item) {
            $stmt_update_stock->execute([$item['cantidad'], $item['producto_id']]);
        }
    }

    // 3. Actualizar el estado del pedido
    $stmt_update = $pdo->prepare("UPDATE pos_pedidos_web SET estado = ? WHERE id = ?");
    $stmt_update->execute([$nuevo_estado, $pedido_id]);

    $pdo->commit();
    echo json_encode(['status' => 'ok', 'mensaje' => 'Estado del pedido actualizado correctamente.']);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>