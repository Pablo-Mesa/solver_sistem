<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
    exit;
}

$id = $_POST['id'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion_web'] ?? '');
$precio_venta = $_POST['precio_venta'] ?? null;
$remove_image_flag = $_POST['remove_image_flag'] ?? '0';

if (empty($id) || empty($nombre) || !is_numeric($precio_venta)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Faltan datos requeridos (id, nombre, precio).']);
    exit;
}

// Variable para la URL de la imagen que se guardará en la BD. `null` significa "no cambiar".
$new_imagen_url = null;

try {
    // 1. Obtener la URL de la imagen actual para poder borrar el archivo si es necesario.
    $stmt_current = $pdo->prepare("SELECT imagen_url FROM pos_productos WHERE id = ?");
    $stmt_current->execute([$id]);
    $current_imagen_url = $stmt_current->fetchColumn();
    
    // --- Lógica de rutas dinámicas ---
    // Obtener la ruta base del proyecto para la URL y la ruta física para el servidor.
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $appRoot = str_replace('\\', '/', dirname(__DIR__, 3)); // Sube 3 niveles hasta la raíz del proyecto
    $baseUrl = trim(str_replace($docRoot, '', $appRoot), '/');
    $baseUrl = ($baseUrl === '') ? '' : '/' . $baseUrl; // Ej: /solver_16022026/solver
    // --- Fin Lógica de rutas ---

    // 2. Si se marcó "Eliminar Imagen"
    if ($remove_image_flag === '1' && !empty($current_imagen_url)) {
        $file_to_delete = $docRoot . $current_imagen_url;
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
        $new_imagen_url = ''; // Guardar un string vacío en la BD
    }

    // 3. Si se subió un archivo nuevo
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Borrar el archivo antiguo si existía
        if (!empty($current_imagen_url)) {
            $file_to_delete = $docRoot . $current_imagen_url;
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
        }

        // Procesar y mover el nuevo archivo
        $upload_dir = $appRoot . '/uploads/products/';
        // Asegurarse de que el directorio de subida existe
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $unique_filename = 'prod_' . $id . '_' . time() . '.' . $file_extension;
        $target_file = $upload_dir . $unique_filename;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
            // La URL para la BD debe ser relativa a la raíz del sitio web
            $new_imagen_url = $baseUrl . '/uploads/products/' . $unique_filename;
        } else {
            throw new Exception("No se pudo mover el archivo subido. Verifique los permisos de escritura en la carpeta 'solver/uploads/products'.");
        }
    }

    // 4. Construir y ejecutar la consulta SQL
    $sql_parts = [
        "nombre = ?",
        "descripcion_web = ?",
        "precio_venta = ?"
    ];
    $params = [$nombre, $descripcion, $precio_venta];

    // Solo añadimos la actualización de la imagen si hubo un cambio
    if ($new_imagen_url !== null) {
        $sql_parts[] = "imagen_url = ?";
        $params[] = $new_imagen_url;
    }

    $sql = "UPDATE pos_productos SET " . implode(', ', $sql_parts) . " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['status' => 'ok', 'mensaje' => 'Producto actualizado con éxito.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}
?>