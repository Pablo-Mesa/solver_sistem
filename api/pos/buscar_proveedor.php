<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$ruc = $_GET['ruc'] ?? '';

if (empty($ruc)) {
    echo json_encode(['status' => 'error', 'mensaje' => 'RUC no proporcionado']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, razon_social, dv FROM pos_proveedores WHERE ruc = ? LIMIT 1");
    $stmt->execute([$ruc]);
    $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($proveedor) {
        echo json_encode(['status' => 'ok', 'datos' => $proveedor]);
    } else {
        echo json_encode(['status' => 'not_found', 'mensaje' => 'Proveedor no registrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}