<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
session_start(); // Iniciamos sesión para guardar al usuario

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (empty($email) || empty($pass)) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Por favor, completa todos los campos']);
        exit;
    }

    try {
        // Buscamos al usuario por su email
        $stmt = $pdo->prepare("SELECT id, nombre_usuario, password FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verificamos si el usuario existe y si la contraseña es correcta
        if ($user && password_verify($pass, $user['password'])) {
            
            // Login exitoso: Guardamos datos en la sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre_usuario'];

            echo json_encode([
                'status' => 'ok', 
                'mensaje' => '¡Bienvenido, ' . $user['nombre_usuario'] . '!',
                'redirect' => 'dashboard.php' // Página a la que irá tras loguearse
            ]);
        } else {
            echo json_encode(['status' => 'error', 'mensaje' => 'Credenciales incorrectas']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error en el servidor']);
    }

} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
}