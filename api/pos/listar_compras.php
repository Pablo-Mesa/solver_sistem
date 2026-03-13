<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

try {
    // Seleccionamos explícitamente el campo estado para asegurarnos de recibirlo
    $sql = "SELECT c.id, c.fecha_emision, c.proveedor_id, c.timbrado, c.nro_comprobante,
                   c.gravada_10, c.iva_10, c.gravada_5, c.iva_5, c.exenta, c.total_factura,
                   c.estado, p.razon_social 
            FROM pos_compras_cabecera c
            JOIN pos_proveedores p ON c.proveedor_id = p.id
            ORDER BY c.fecha_emision DESC";
            
    $stmt = $pdo->query($sql);
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'datos' => $compras]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}