<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

try {
    // Seleccionamos los campos necesarios para el listado y la selección
    $stmt = $pdo->query("SELECT id, documento, dv, razon_social, email 
                        FROM pos_clientes 
                        ORDER BY razon_social ASC");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'ok', 'datos' => $clientes]);

} catch (PDOException $e) {
    // En caso de error, devolvemos un mensaje para depuración
    echo json_encode(['status' => 'error', 'mensaje' => 'Error al consultar la base de datos: ' . $e->getMessage()]);
}