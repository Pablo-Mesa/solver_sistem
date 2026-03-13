<?php 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Registro</title>
    <link rel="icon" href="assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <h2>Crear Cuenta</h2>

        <form id="formRegistro" action="api/registrar.php" method="POST">
            <div class="form-group">
                <label for="usuario">Nombre de Usuario</label>
                <input type="text" id="usuario" name="usuario" required minlength="3">
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <button type="submit" id="btnEnviar">Registrarse</button>
        </form>

        <div id="mensajeFeedback" class="mensaje"></div>
    </div>

    <p style="margin-top: 15px; text-align: center;">
        Volver al <a href="login.php">Iniciar Sesión</a>
    </p>

    <script src="js/registro.js"></script>
    
</body>
</html>