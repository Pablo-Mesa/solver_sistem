<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}

$razon_social   = trim($data['razon_social'] ?? '');
$ruc            = trim($data['ruc'] ?? '');
$dv             = trim($data['dv'] ?? '') ?: null;
$direccion      = trim($data['direccion'] ?? '') ?: null;
$telefono       = trim($data['telefono'] ?? '') ?: null;
$email          = trim($data['email'] ?? '') ?: null;
$timbrado       = trim($data['timbrado_vigente'] ?? '') ?: null;
$fecha_desde    = !empty($data['fecha_desde_timbrado']) ? $data['fecha_desde_timbrado'] : null;
$fecha_hasta    = !empty($data['fecha_hasta_timbrado']) ? $data['fecha_hasta_timbrado'] : null;
$punto_emision  = trim($data['punto_emision'] ?? '') ?: '001';
$sucursal       = trim($data['sucursal'] ?? '') ?: '001';
$actividad      = trim($data['actividad_economica'] ?? '') ?: null;

if ($razon_social === '' || $ruc === '') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Razón social y RUC son obligatorios']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE empresa SET
        razon_social = ?,
        ruc = ?,
        dv = ?,
        direccion = ?,
        telefono = ?,
        email = ?,
        timbrado_vigente = ?,
        fecha_desde_timbrado = ?,
        fecha_hasta_timbrado = ?,
        punto_emision = ?,
        sucursal = ?,
        actividad_economica = ?
    WHERE id = 1");

    $stmt->execute([
        $razon_social,
        $ruc,
        $dv,
        $direccion,
        $telefono,
        $email,
        $timbrado,
        $fecha_desde,
        $fecha_hasta,
        $punto_emision,
        $sucursal,
        $actividad
    ]);

    if ($stmt->rowCount() >= 0) {
        echo json_encode(['status' => 'ok', 'mensaje' => 'Datos del negocio guardados correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'No se pudo actualizar. Verifique que exista la fila en la tabla empresa (id=1).']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al guardar: ' . $e->getMessage()]);
}
