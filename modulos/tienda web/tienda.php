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

    <!-- Overlay Oscuro -->
    <div id="cart-overlay" class="cart-overlay"></div>

    <!-- Panel Lateral del Carrito (Drawer) -->
    <div id="cart-drawer" class="cart-drawer">
        <div class="cart-header">
            <h2 id="cart-title">Tu Carrito</h2>
            <button class="close-cart-btn" onclick="toggleCart()">&times;</button>
        </div>

        <div class="cart-body">
            <!-- PASO 1: LISTA DE PRODUCTOS -->
            <div id="step-1" class="checkout-step active">
                <div id="cart-items-container">
                    <!-- Ejemplo estático para visualización -->
                    <div class="cart-item">
                        <div class="cart-item-img" style="background-color:#ccc; display:flex; align-items:center; justify-content:center;">IMG</div>
                        <div class="cart-item-details">
                            <div class="cart-item-title">Producto Ejemplo</div>
                            <div class="cart-item-price">1 x 50.000 Gs.</div>
                        </div>
                        <button style="border:none; background:none; color:red; cursor:pointer;">&times;</button>
                    </div>
                    <p style="text-align:center; color:#888; margin-top:20px;">Tu carrito está vacío (demo).</p>
                </div>
            </div>

            <!-- PASO 2: ENTREGA Y DATOS -->
            <div id="step-2" class="checkout-step">
                <h3>Opciones de Entrega</h3>
                <div class="form-group">
                    <label>Nombre de quien recibe:</label>
                    <input type="text" id="nombre_contacto" placeholder="Tu nombre o quien recibe...">
                </div>
                <div class="form-group">
                    <label>Tipo de Entrega:</label>
                    <div class="form-radio-group">
                        <label style="display:block; margin-bottom:5px;">
                            <input type="radio" name="delivery_type" value="pickup" checked onchange="toggleDeliveryAddress(false)"> Retiro en Local
                        </label>
                        <label style="display:block;">
                            <input type="radio" name="delivery_type" value="delivery" onchange="toggleDeliveryAddress(true)"> Delivery (Envío)
                        </label>
                    </div>
                </div>

                <div id="address-fields" style="display:none;">
                    <div class="form-group">
                        <label>Dirección de Envío:</label>
                        <input type="text" id="direccion" placeholder="Calle, número, referencia...">
                    </div>
                    <div class="form-group">
                        <label>Teléfono de Contacto:</label>
                        <input type="text" id="telefono" placeholder="09XX...">
                    </div>
                    <div class="form-group">
                        <label>Ubicación (Google Maps):</label>
                        <input type="text" id="ubicacion" placeholder="Pegar enlace (opcional)">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Observaciones:</label>
                    <textarea id="observacion" rows="2" style="width:100%; border:1px solid #ccc; border-radius:4px; padding:5px;"></textarea>
                </div>
            </div>

            <!-- PASO 3: PAGO -->
            <div id="step-3" class="checkout-step">
                <h3>Método de Pago</h3>
                <div class="form-radio-group">
                    <label style="display:block; margin-bottom:8px;">
                        <input type="radio" name="pago" value="efectivo" checked> Efectivo
                    </label>
                    <label style="display:block; margin-bottom:8px;">
                        <input type="radio" name="pago" value="qr"> Transferencia / QR
                    </label>
                    <label style="display:block;">
                        <input type="radio" name="pago" value="tarjeta"> Tarjeta (POS al entregar)
                    </label>
                </div>
                <div style="background:#e3f2fd; padding:10px; border-radius:5px; font-size:0.9em; color:#0d47a1;">
                    ℹ️ El pago se realiza al momento de la entrega o retiro.
                </div>
            </div>
        </div>

        <div class="cart-footer">
            <div class="cart-total-row">
                <span>Total Estimado:</span>
                <span id="cart-total-amount">0 Gs.</span>
            </div>
            <button id="btn-main-action" class="btn-primary-block" onclick="nextStep()">Iniciar Pedido</button>
            <button id="btn-back-action" class="btn-secondary-block" onclick="prevStep()" style="display:none;">Volver</button>
        </div>
    </div>

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