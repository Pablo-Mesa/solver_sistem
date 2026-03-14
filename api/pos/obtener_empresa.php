<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

try {
    $stmt = $pdo->prepare("SELECT id, razon_social, ruc, dv, direccion, telefono, email,
                                  timbrado_vigente, fecha_desde_timbrado, fecha_hasta_timbrado,
                                  punto_emision, sucursal, actividad_economica, estado
                           FROM empresa WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode(['status' => 'ok', 'datos' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'No existe configuración de empresa']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al obtener datos: ' . $e->getMessage()]);
}
