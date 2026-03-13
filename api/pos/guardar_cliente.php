<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php'; 

$data = json_decode(file_get_contents("php://input"), true);

try {
    $sql = "INSERT INTO pos_clientes (tipo_contribuyente, tipo_documento, documento, dv, razon_social, email, telefono) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['tipo_cont'],
        $data['tipo_doc'],
        $data['doc'],
        $data['dv'] !== "" ? $data['dv'] : null,
        $data['nombre'],
        $data['email'],
        $data['tel']
    ]);

    $newId = $pdo->lastInsertId();
    echo json_encode(["status" => "ok", "mensaje" => "Cliente registrado con éxito", "id" => $newId]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "mensaje" => "Error o Documento ya existe"]);
}