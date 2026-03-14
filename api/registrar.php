<?php
// 1. Encabezado para que el navegador/App sepa que recibe un JSON
header('Content-Type: application/json');

// 2. Incluimos la conexión a la base de datos
require_once '../includes/db.php';

// 3. Verificamos que los datos lleguen por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtenemos y limpiamos los datos (puedes añadir más campos aquí)
    $usuario = trim($_POST['usuario'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $activo    =  0;

    // Validaciones básicas
    if (empty($usuario) || empty($email) || empty($pass)) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Todos los campos son obligatorios']);
        exit;
    }

    try {
        // 4. Verificamos si el email ya existe para evitar duplicados
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'mensaje' => 'El correo ya está registrado']);
            exit;
        }

        // 5. Encriptamos la contraseña (Práctica de seguridad esencial)
        $passHash = password_hash($pass, PASSWORD_BCRYPT);

        // 6. Insertamos el nuevo usuario
        $sql = "INSERT INTO usuarios (nombre_usuario, email, password, activo) VALUES (?, ?, ?, 0)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$usuario, $email, $passHash])) {
            echo json_encode(['status' => 'ok', 'mensaje' => 'Usuario registrado con éxito']);
        } else {
            echo json_encode(['status' => 'error', 'mensaje' => 'No se pudo guardar el usuario']);
        }

    } catch (PDOException $e) {
        // Error de base de datos (puedes loguear $e->getMessage() internamente)
        echo json_encode(['status' => 'error', 'mensaje' => 'Error en el servidor al procesar el registro']);
    }

} else {
    // Si alguien intenta entrar directamente al archivo sin enviar datos
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
}