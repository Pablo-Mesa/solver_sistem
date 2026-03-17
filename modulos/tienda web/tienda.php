<?php
    require_once '../../includes/auth.php'; // Seguridad de sesión
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" /> 
    <title>Solver | Tienda Online</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/css_cubo.css">
    <!-- Hoja de estilos específica para la tienda -->
    <link rel="stylesheet" href="css/tienda.css">
</head>
<body>

    <!-- Header Fijo -->
    <header class="store-header">
        <div class="container-store-brand"> 
            <div id="here_cube"></div>
            <!-- <a href="#" class="store-brand">Solver Store</a> -->
            <a href="../../dashboard.php" class="back-to-dashboard" >Dashboard</a>            
        </div>
        <div class="store-controls">
            <div class="search-box">
                <input type="text" id="buscador" placeholder="Buscar productos...">
            </div>
            <button class="cart-btn" onclick="verCarrito()">
                🛒 <span id="cart-count" class="cart-count">0</span>
            </button>            
        </div>
    </header>

    <div class="category-bar">
        <button class="category-btn active"><span>🏠</span> Inicio</button>
        <button class="category-btn"><span>🔥</span> Tendencias</button>
        <button class="category-btn"><span>💻</span> Tecnología</button>
        <button class="category-btn"><span>🎨</span> Diseño</button>
        <button class="category-btn"><span>⚽</span> Deportes</button>
        <button class="category-btn"><span>🍕</span> Comida</button>
    </div>

    <!-- Contenedor de Productos -->
    <main id="grid-productos" class="products-container">
        <!-- Los productos se cargarán aquí vía JS -->
        <p style="text-align:center; width:100%; color:#666;">Cargando catálogo...</p>
    </main>

    <!-- Script de lógica de la tienda -->
    <script src="js/tienda.js"></script>
    <script src="../../js/tool-kit-v002.js"></script>
    <script>
        // Llamamos a la función para dibujar el cubo en el div 'here_cube'
        // La sintaxis es: drawCube(selector_destino, mostrar_titulo, tamaño);
        drawCube('#here_cube', true, '28px');
    </script>
</body>
</html>