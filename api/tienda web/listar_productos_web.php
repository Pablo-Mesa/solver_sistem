<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php'; // Seguridad: solo usuarios logueados
require_once '../../includes/db.php';   // Conexión a la base de datos

try {
    // La consulta SQL para obtener los productos.
    // Unimos con categorías para tener más contexto si se necesita en el futuro.
    // Seleccionamos los campos que la UI (productos_web.php) espera.
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.nombre,
            p.descripcion_web,
            p.precio_venta,
            p.stock_actual,
            p.imagen_url,
            p.publicado_web,
            c.nombre AS categoria
        FROM 
            pos_productos p
        LEFT JOIN 
            pos_categorias c ON p.categoria_id = c.id
        ORDER BY 
            p.nombre ASC
    ");

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolvemos una respuesta exitosa con los datos.
    echo json_encode(['status' => 'ok', 'datos' => $productos]);

} catch (PDOException $e) {
    // En caso de un error en la base de datos, devolvemos un mensaje claro.
    http_response_code(500); // Error interno del servidor
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al consultar la base de datos: ' . $e->getMessage()]);
}
?>