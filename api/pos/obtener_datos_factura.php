<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

try {
    // 1. Datos de empresa (timbrado, punto de emisión, sucursal)
    $stmtEmp = $pdo->prepare("SELECT timbrado_vigente, punto_emision, sucursal FROM empresa WHERE id = 1 LIMIT 1");
    $stmtEmp->execute();
    $empresa = $stmtEmp->fetch(PDO::FETCH_ASSOC);

    if (!$empresa) {
        echo json_encode([
            'status' => 'ok',
            'timbrado_vigente' => '',
            'punto_emision' => '001',
            'sucursal' => '001',
            'nro_factura_sugerido' => '001-001-0000001'
        ]);
        exit;
    }

    $punto  = $empresa['punto_emision'] ?: '001';
    $sucursal = $empresa['sucursal'] ?: '001';
    $prefix = $punto . '-' . $sucursal . '-';

    // 2. Siguiente número de factura para este punto/sucursal
    $like = $prefix . '%';
    $stmtNum = $pdo->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(nro_factura, '-', -1) AS UNSIGNED)), 0) + 1 AS siguiente
                              FROM pos_ventas_cabecera WHERE nro_factura LIKE ?");
    $stmtNum->execute([$like]);
    $row = $stmtNum->fetch(PDO::FETCH_ASSOC);
    $siguiente = (int) ($row['siguiente'] ?? 1);
    $nro_sugerido = $prefix . str_pad($siguiente, 7, '0', STR_PAD_LEFT);

    echo json_encode([
        'status' => 'ok',
        'timbrado_vigente' => $empresa['timbrado_vigente'] ?? '',
        'punto_emision' => $punto,
        'sucursal' => $sucursal,
        'nro_factura_sugerido' => $nro_sugerido
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al obtener datos de factura']);
}
