<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ruc   = $_POST['ruc'];
    $dv    = $_POST['dv'];
    $razon = $_POST['razon_social'];
    $tipo  = $_POST['tipo'];

    try {
        $sql = "INSERT INTO pos_proveedores (ruc, dv, razon_social, tipo_contribuyente) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ruc, $dv, $razon, $tipo]);

        echo json_encode([
            'status' => 'ok', 
            'id' => $pdo->lastInsertId(), 
            'mensaje' => 'Proveedor creado con éxito'
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'El RUC ya podría existir']);
    }
}