<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$doc = $_GET['doc'] ?? '';

if (strlen($doc) < 5) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Documento demasiado corto']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, razon_social, dv, email, tipo_documento FROM pos_clientes WHERE documento = ? LIMIT 1");
    $stmt->execute([$doc]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        echo json_encode(['status' => 'ok', 'datos' => $cliente]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}