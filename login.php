<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Login</title>
    <link rel="icon" href="assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="form-container">
    <h2>Iniciar Sesión</h2>
    
    <form id="formLogin">
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" id="btnEntrar">Entrar</button>
    </form>

    <div id="mensajeFeedback" class="mensaje"></div>
    
    <p style="margin-top: 15px; text-align: center;">
        ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
    </p>
</div>

<script src="js/login.js"></script>
</body>
</html>