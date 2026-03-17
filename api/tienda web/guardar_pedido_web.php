<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php'; // Asumimos que el usuario está logueado en la tienda

// 1. Obtener y decodificar el JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "mensaje" => "Datos no recibidos o inválidos"]);
    exit;
}

// 2. Validaciones básicas
if (empty($data['items']) || !is_array($data['items'])) {
    echo json_encode(["status" => "error", "mensaje" => "El carrito está vacío"]);
    exit;
}

if ($data['delivery_type'] === 'delivery' && empty($data['direccion'])) {
    echo json_encode(["status" => "error", "mensaje" => "La dirección es obligatoria para Delivery"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 3. Preparar datos de cabecera
    // Intentamos obtener el ID de usuario de la sesión si existe
    $cliente_id = $_SESSION['user_id'] ?? null; 
    // OJO: user_id suele ser de la tabla 'usuarios'. Si tienes tabla 'pos_clientes' separada,
    // deberías buscar el cliente asociado al usuario, o guardar NULL si es genérico.
    
    $nombre = $data['nombre_contacto'] ?? 'Cliente Web'; // Podrías sacar esto de la sesión también
    $telefono = $data['telefono'] ?? '';
    $direccion = $data['direccion'] ?? '';
    $ubicacion = $data['ubicacion'] ?? '';
    $observacion = $data['observacion'] ?? '';
    $tipo_entrega = $data['delivery_type']; // 'pickup' o 'delivery'
    $metodo_pago = $data['pago']; // 'efectivo', 'qr', 'tarjeta'
    
    // 4. Calcular total servidor (Seguridad: no confiar en el total del JS)
    $total_calculado = 0;

    // Preparamos insert cabecera
    $sqlCab = "INSERT INTO pos_pedidos_web 
               (cliente_id, nombre_contacto, telefono_contacto, direccion_envio, ubicacion_maps, observacion, tipo_entrega, metodo_pago, total_estimado, estado) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'pendiente')";
    $stmtCab = $pdo->prepare($sqlCab);
    $stmtCab->execute([$cliente_id, $nombre, $telefono, $direccion, $ubicacion, $observacion, $tipo_entrega, $metodo_pago]);
    
    $pedido_id = $pdo->lastInsertId();

    // 5. Insertar Detalle
    $sqlDet = "INSERT INTO pos_pedidos_web_detalle (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
    $stmtDet = $pdo->prepare($sqlDet);

    foreach ($data['items'] as $item) {
        $prod_id = $item['id'];
        $cantidad = $item['cantidad'];
        
        // Consultar precio real y stock actual para seguridad
        $stmtProd = $pdo->prepare("SELECT precio_venta, stock_actual, nombre FROM pos_productos WHERE id = ?");
        $stmtProd->execute([$prod_id]);
        $productoDb = $stmtProd->fetch(PDO::FETCH_ASSOC);

        if (!$productoDb) {
            throw new Exception("Producto ID $prod_id no encontrado.");
        }

        // Validación de Stock (Opcional: ¿Permitir pedir sin stock?)
        if ($productoDb['stock_actual'] < $cantidad) {
            throw new Exception("Stock insuficiente para: " . $productoDb['nombre']);
        }

        $precio_real = $productoDb['precio_venta'];
        $subtotal = $precio_real * $cantidad;
        $total_calculado += $subtotal;

        $stmtDet->execute([$pedido_id, $prod_id, $cantidad, $precio_real, $subtotal]);
    }

    // 6. Actualizar el total correcto en la cabecera
    $pdo->prepare("UPDATE pos_pedidos_web SET total_estimado = ? WHERE id = ?")->execute([$total_calculado, $pedido_id]);

    $pdo->commit();
    echo json_encode(["status" => "ok", "mensaje" => "Pedido #$pedido_id recibido correctamente.", "pedido_id" => $pedido_id]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "mensaje" => "Error al guardar: " . $e->getMessage()]);
}
?>