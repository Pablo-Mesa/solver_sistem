<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

// Recibir y validar datos
$nombre = trim($_POST['nombre'] ?? '');
$categoria_id = filter_var($_POST['categoria_id'] ?? null, FILTER_VALIDATE_INT);
$precio_costo = filter_var($_POST['precio_costo'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$precio_venta = filter_var($_POST['precio_venta'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$iva_tasa = filter_var($_POST['iva_tasa'] ?? null, FILTER_VALIDATE_INT);
$codigo_barra = trim($_POST['codigo_barra'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$descripcion_web = trim($_POST['descripcion_web'] ?? '');

if (empty($nombre) || $categoria_id === false || $precio_costo === false || $precio_venta === false || $iva_tasa === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Todos los campos son obligatorios y deben ser válidos.']);
    exit;
}

try {
    // Insertar en la base de datos
    $sql = "INSERT INTO pos_productos (nombre, categoria_id, precio_costo, precio_venta, iva_tasa, stock_actual, codigo_barra, descripcion, descripcion_web) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $categoria_id, $precio_costo, $precio_venta, $iva_tasa, $codigo_barra, $descripcion, $descripcion_web]);
    
    $nuevo_id = $pdo->lastInsertId();

    // Devolver el producto recién creado para que el frontend lo use
    $nuevo_producto = [
        'id' => $nuevo_id,
        'nombre' => $nombre,
        'categoria_id' => $categoria_id,
        'precio_costo' => $precio_costo,
        'precio_venta' => $precio_venta,
        'iva_tasa' => $iva_tasa,
        'codigo_barra' => $codigo_barra,
        'descripcion' => $descripcion,
        'descripcion_web' => $descripcion_web
    ];

    echo json_encode(['status' => 'ok', 'mensaje' => 'Producto creado con éxito.', 'datos' => $nuevo_producto]);

} catch (PDOException $e) {
    http_response_code(500);
    if ($e->errorInfo[1] == 1062) { // Error de entrada duplicada
        echo json_encode(['status' => 'error', 'mensaje' => 'Ya existe un producto con ese nombre.']);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error de base de datos: ' . $e->getMessage()]);
    }
}
?>