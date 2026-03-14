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
    <style>
        /* --- Estilos Específicos para la Tienda --- */
        *{
            box-sizing: border-box;
            padding: 0;
            margin: 0;
        }
        /* Ajustes al body para la barra de navegación fija */
        body {
            width: 100%;
            padding-top: 0px; /* Espacio para el header fijo */
        }

        /* Header de la tienda */
        .store-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .store-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }

        .store-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        /* Buscador */
        .search-box {
            position: relative;
        }
        .search-box input {
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid #ddd;
            width: 250px;
            transition: width 0.3s ease;
        }
        .search-box input:focus {
            outline: none;
            border-color: #007bff;
            width: 300px;
        }

        /* Botón Carrito */
        .cart-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .cart-count {
            background: #ff4757;
            color: white;
            font-size: 0.8em;
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* Contenedor Principal (Grid) */
        .products-container {
            max-width: 1200px;
            margin: 136px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            background-color: transparent;
        }

        /* Tarjeta de Producto */
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover; /* Asegura que la imagen llene el espacio sin deformarse */
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .product-info {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-category {
            font-size: 0.8rem;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .product-price {
            font-size: 1.2rem;
            color: #28a745;
            font-weight: bold;
            margin-top: auto; /* Empuja el precio hacia abajo si hay espacio */
            margin-bottom: 10px;
        }

        .add-btn {
            width: 100%;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #007bff;
            color: #007bff;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .add-btn:hover {
            background-color: #007bff;
            color: white;
        }
        .add-btn:disabled {
            border-color: #ccc;
            color: #ccc;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .store-header {
                flex-direction: column;
                gap: 10px;
                padding-bottom: 15px;
            }
            .store-controls {
                width: 100%;
                justify-content: space-between;
            }
            .search-box input {
                width: 100%;
            }
            body {
                padding-top: 130px; /* Más espacio en móvil */
            }
            .cart-btn {
                width: auto;
            }
            .search-box input:focus {
                outline: none;
                border-color: #007bff;
                width: auto;
            }

            .category-bar {                
                justify-content: flex-start; /* Alineado a la izquierda para el scroll */
                padding: 10px;
                overflow-x: scroll;
                overflow-y: hidden;
                top: 120px; /* Se ajusta a la altura del header de móvil */
            }
            
            .category-btn {
                padding: 8px 14px;
                font-size: 13px;
            }
        }

        /* Contenedor principal */
        .category-bar {
            position: fixed;
            top: 60px; /* Se ajusta a la altura del header de escritorio */
            z-index: 999;
            display: flex;
            align-items: center;
            width: 100%;
            gap: 12px;
            padding: 15px 20px;
            background-color: rgba(255, 255, 255, 0.85); /* Efecto cristalino para un look moderno */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid #e0e0e0;
            overflow-x: auto;
            flex-wrap: nowrap;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        /* Ocultar scrollbar en Chrome/Safari */
        .category-bar::-webkit-scrollbar {
            display: none;
        }

        /* Estilo de los botones */
        .category-btn {
            display: flex;
            justify-content: center; /* Centra el contenido (icono y texto) */
            align-items: center;
            gap: 8px;
            width: 120px; /* Ancho fijo para consistencia */
            min-width: 120px; /* Ancho mínimo para coherencia visual */
            padding: 10px 15px;
            border: 1px solid transparent; /* Borde transparente para evitar saltos en hover */
            border-radius: 25px;
            background-color: #f0f2f5; /* Un gris más suave y moderno */
            color: #3c4043;
            font-size: 14px;
            font-weight: 600; /* Texto más legible */
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            white-space: nowrap;
        }

        .category-btn span {
            font-size: 16px;
        }

        /* Efectos Hover y Active */
        .category-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            background-color: #ffffff;
            border-color: #ddd;
        }

        /* Botón Activo/Seleccionado */
        .category-btn.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .category-btn.active:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
        }
    
    </style>  

</head>
<body>

    <!-- Header Fijo -->
    <header class="store-header">        
        <a href="#" class="store-brand">Solver Store</a>
        <div class="store-controls">
            <div class="search-box">
                <input type="text" id="buscador" placeholder="Buscar productos...">
            </div>
            <button class="cart-btn" onclick="verCarrito()">
                🛒 <span id="cart-count" class="cart-count">0</span>
            </button>
            <a href="../../logout.php" style="color: #dc3545; text-decoration: none; font-size: 0.9rem;">Salir</a>
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

    <!-- Script de Lógica -->
    <script>
        let productosGlobal = [];
        let carrito = [];

        document.addEventListener('DOMContentLoaded', () => {
            cargarProductos();

            // Listener para el buscador en tiempo real
            document.getElementById('buscador').addEventListener('keyup', (e) => {
                filtrarProductos(e.target.value);
            });
        });

        async function cargarProductos() {
            const grid = document.getElementById('grid-productos');
            try {
                // Ajusta la ruta si tu archivo está en una subcarpeta diferente
                const response = await fetch('../../api/tienda web/listar_productos_web.php');
                const data = await response.json();

                if (data.status === 'ok') {
                    productosGlobal = data.datos;
                    renderizarProductos(productosGlobal);
                } else {
                    grid.innerHTML = `<p class="error">Error: ${data.mensaje}</p>`;
                }
            } catch (error) {
                console.error(error);
                grid.innerHTML = '<p class="error">Error de conexión al cargar productos.</p>';
            }
        }

        function renderizarProductos(lista) {
            const grid = document.getElementById('grid-productos');
            grid.innerHTML = '';

            if (lista.length === 0) {
                grid.innerHTML = '<p style="text-align:center; grid-column: 1/-1;">No se encontraron productos.</p>';
                return;
            }

            lista.forEach(prod => {
                // Verificar si hay imagen, sino usar placeholder
                const imagen = prod.imagen_url ? prod.imagen_url : 'https://via.placeholder.com/300x200?text=Sin+Imagen';
                const precio = parseFloat(prod.precio_venta).toLocaleString('es-PY');
                const stock = parseInt(prod.stock_actual);
                const sinStock = stock <= 0;

                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
                    <img src="${imagen}" alt="${prod.nombre}" class="product-image" loading="lazy">
                    <div class="product-info">
                        <div class="product-category">${prod.categoria || 'General'}</div>
                        <div class="product-title">${prod.nombre}</div>
                        <p style="font-size: 0.9em; color: #666; margin-bottom: 10px;">${prod.descripcion_web || ''}</p>
                        
                        <div class="product-price">Gs. ${precio}</div>
                        
                        <button class="add-btn" 
                                onclick="agregarAlCarrito(${prod.id})" 
                                ${sinStock ? 'disabled' : ''}>
                            ${sinStock ? 'Agotado' : 'Agregar al Carrito'}
                        </button>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        function filtrarProductos(texto) {
            const textoLower = texto.toLowerCase();
            const filtrados = productosGlobal.filter(prod => {
                return prod.nombre.toLowerCase().includes(textoLower) || 
                       (prod.categoria && prod.categoria.toLowerCase().includes(textoLower));
            });
            renderizarProductos(filtrados);
        }

        function agregarAlCarrito(id) {
            const prod = productosGlobal.find(p => p.id == id);
            if(prod) {
                // Buscar si ya está en carrito
                const existente = carrito.find(item => item.id == id);
                if(existente) {
                    if(existente.cantidad < prod.stock_actual) {
                        existente.cantidad++;
                        alert(`Se agregó otra unidad de: ${prod.nombre}`);
                    } else {
                        alert("No hay más stock disponible.");
                        return;
                    }
                } else {
                    carrito.push({ ...prod, cantidad: 1 });
                    alert(`Agregado al carrito: ${prod.nombre}`);
                }
                actualizarContadorCarrito();
            }
        }

        function actualizarContadorCarrito() {
            const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
            document.getElementById('cart-count').innerText = totalItems;
        }

        function verCarrito() {
            if(carrito.length === 0) {
                alert("El carrito está vacío.");
                return;
            }
            // Aquí podrías redirigir a una página de checkout o abrir un modal
            // Por ahora, solo mostramos un resumen básico
            let mensaje = "Tu Carrito:\n\n";
            let total = 0;
            carrito.forEach(item => {
                const subtotal = item.precio_venta * item.cantidad;
                mensaje += `- ${item.nombre} (x${item.cantidad}): Gs. ${subtotal.toLocaleString('es-PY')}\n`;
                total += subtotal;
            });
            mensaje += `\nTotal: Gs. ${total.toLocaleString('es-PY')}`;
            alert(mensaje);
        }
    </script>
</body>
</html>