<?php 
    // Usamos la autenticación para proteger la página
    require_once '../../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Productos Tienda</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        html {
            height: 100%;
        }
        *{
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        /* Estilos generales */
        body {
            background-color: #F9FAFB;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            height: 100%; /* Ocupa toda la altura de la ventana */
            width: 100%;
            overflow: hidden; /* Evita que el body principal tenga su propio scroll */
            margin: 0;
        }

        /* Contenedor principal */
        .main-container {
            background-color: #F9FAFB;
            width: 60%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 0px;
            height: 100%;
            display: flex;
            flex-direction: column; /* Apila los hijos (header y grid) verticalmente */
            box-sizing: border-box; /* Asegura que el padding no aumente la altura */
        }

        /* Cabecera de la página */
        .page-header {
            flex-shrink: 0; /* Evita que la cabecera se encoja */
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px 0px;
            border-bottom: 1px solid #e0e0e0;
            background-color: transparent;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
        }

        .btn-volver {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .btn-volver:hover {
            background-color: #2980b9;
        }

        /* Grid de productos */
        .product-grid {
            /*
            flex-grow: 1; 
            height: 0; 
            min-height: 0;  
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            */
            
            display: grid;
            /* Crea columnas de mínimo 150px (ajustable) que llenan el espacio, 
                pero nunca menos de 2 columnas gracias al cálculo de porcentaje */
            grid-template-columns: repeat(auto-fit, minmax(calc(50% - 10px), 1fr));
            gap: 20px; /* Espacio entre los divs hijos */
            
            overflow-y: auto; /* Habilita el scroll vertical SOLO para la grilla */
            padding: 4px 2px; /* Un poco de aire alrededor de las tarjetas y para el scroll */
            align-items: stretch; /* Asegura que las tarjetas llenen toda la altura de la fila */
            background-color: #F9FAFB;
        }

        /* Tarjeta de producto */
        .product-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            overflow: hidden;
            
            min-height: 360px; /* Ajusta según tu necesidad */
            height: auto;      /* Permite que crezca si hay mucho contenido */
            display: flex;     /* Opcional: para centrar contenido interno */
            flex-direction: column;

            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        }

        .product-image {
            width: 100%;
            height: 180px;
            background-color: #ecf0f1;
            background-size: cover;
            background-position: center;
        }
        
        .product-image.placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #bdc3c7;
            font-size: 1.5em;
        }

        .product-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-info h3 {
            margin: 0 0 10px 0;
            font-size: 1.1em;
            font-weight: 600;
        }

        .product-info p.product-description {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 10px;
            
            /* Limitar a 3 líneas con ellipsis */
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
        }

        .product-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 10px;
        }

        .product-price {
            font-size: 1.2em;
            font-weight: 700;
            color: #27ae60;
        }

        .product-stock {
            font-size: 0.9em;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: #e9ecef;
            color: #495057;
        }

        .product-actions {
            border-top: 1px solid #f0f0f0;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Toggle Switch para publicar */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 28px;
        }
        .switch input { display: none; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 28px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: #28a745; }
        input:checked + .slider:before { transform: translateX(22px); }

        .action-label {
            font-size: 0.9em;
            font-weight: 500;
        }

        .btn-edit {
            padding: 8px 16px;
            background-color: #f39c12;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
            transition: background-color 0.2s ease;
        }
        .btn-edit:hover {
            background-color: #e67e22;
        }

        /* Loader */
        #loader {
            text-align: center;
            padding: 50px;
            font-size: 1.2em;
            color: #7f8c8d;
        }

        :root {
        --primary-color: #6366f1; /* Azul moderno */
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-wrapper {
            padding: 0px;
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .search-container {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px); /* Efecto cristal */
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            transition: var(--transition);
        }

        /* Animación al interactuar */
        .search-container:focus-within {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.15);
            border-color: var(--primary-color);
            background: #ffffff;
        }

        .search-emoji {
            font-size: 1.2rem;
            margin-right: 10px;
            user-select: none;
        }

        .search-input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 1rem;
            color: #374151;
        }

        .search-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 4px;
            border-radius: 25px;
            cursor: pointer;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: bold;
            font-size: 0.8rem;            
            transition: var(--transition);
            margin-left: 10px;
            width: 20%;    
        }

        .search-button:hover {
            background: #4f46e5;
            transform: scale(1.05);
        }

        .search-button:active {
            transform: scale(0.95);
        }

        /* Responsividad para móviles */
        @media (max-width: 780px){
            /* Contenedor principal */
            .main-container {
                width: 80%;
            }

        }

        /* Responsividad para móviles */
        @media (max-width: 480px) {

            /* Contenedor principal */
            .main-container {
                width: 100%;
                max-width: none;
                padding: 10px;
            }

            .product-grid {
                grid-template-columns: 1fr; /* Una columna en móviles */
                padding: 0; /* Elimina el padding para aprovechar todo el espacio */
            }            

            .search-button {
                padding: 10px;
                font-size: 0; /* Oculta el texto "Buscar" */
            }
            .search-button::after {
                content: '➔'; /* Cambia texto por flecha en móviles */
                font-size: 1.2rem;
            }
        }


    </style>
</head>
<body>

    <div class="main-container">
        <div class="page-header">
            <h1>Gestionar Productos de la Tienda</h1>
            <a href="../../dashboard.php" class="btn-volver">← Volver</a>
        </div>
        <div class="search-wrapper">
            <div class="search-container">
                <span class="search-emoji">🔍</span>
                <input type="text" class="search-input" placeholder="Buscar contenido...">
                <button class="search-button">Buscar</button>
            </div>
        </div>

        <div id="product-grid" class="product-grid">
            <div id="loader">Cargando productos...</div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        cargarProductos();
    });

    async function cargarProductos() {
        const grid = document.getElementById('product-grid');
        const loader = document.getElementById('loader');
        
        try {
            // Este endpoint necesita ser creado. Debe devolver los productos para la tienda.
            const response = await fetch('../../api/tienda web/listar_productos_web.php');
            const res = await response.json();

            loader.style.display = 'none';

            if (res.status === 'ok' && res.datos.length > 0) {
                res.datos.forEach(producto => {
                    grid.appendChild(crearTarjetaProducto(producto));
                });
            } else {
                grid.innerHTML = '<p>No se encontraron productos para la tienda.</p>';
            }
        } catch (error) {
            console.error('Error al cargar productos:', error);
            loader.style.display = 'none';
            grid.innerHTML = '<p>Ocurrió un error al cargar los productos. Revise la consola para más detalles.</p>';
        }
    }

    function crearTarjetaProducto(producto) {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.dataset.id = producto.id;

        const precioVenta = Number(producto.precio_venta || 0).toLocaleString('es-PY');
        const stock = Number(producto.stock_actual || 0);
        
        const imageUrl = producto.imagen_url ? `url('${producto.imagen_url}')` : 'none';
        const imageClass = producto.imagen_url ? 'product-image' : 'product-image placeholder';
        const imageContent = producto.imagen_url ? '' : '📷';

        card.innerHTML = `
            <div class="${imageClass}" style="background-image: ${imageUrl}">${imageContent}</div>
            <div class="product-info">
                <h3>${producto.nombre || 'Nombre no disponible'}</h3>
                <p class="product-description">${producto.descripcion_web || ''}</p>
                <div class="product-details">
                    <span class="product-price">${precioVenta} Gs.</span>
                    <span class="product-stock">Stock: ${stock}</span>
                </div>
            </div>
            <div class="product-actions">
                <label class="action-label">Publicado</label>
                <label class="switch">
                    <input type="checkbox" class="toggle-publish" ${producto.publicado_web ? 'checked' : ''}>
                    <span class="slider"></span>
                </label>
                <a href="editar_producto_web.php?id=${producto.id}" class="btn-edit">Editar</a>
            </div>
        `;

        // Añadir evento al toggle
        const toggle = card.querySelector('.toggle-publish');
        toggle.addEventListener('change', () => {
            actualizarEstado(producto.id, toggle.checked);
        });

        return card;
    }

    async function actualizarEstado(id, nuevoEstado) {
        console.log(`Cambiando estado del producto ${id} a: ${nuevoEstado}`);
        
        const datos = new URLSearchParams();
        datos.append('id', id);
        datos.append('publicado', nuevoEstado ? '1' : '0');

        try {
            // Este endpoint necesita ser creado para actualizar el estado en la BD.
            const response = await fetch('../../api/tienda web/actualizar_estado_producto.php', {
                method: 'POST',
                body: datos
            });
            const res = await response.json();

            if (res.status !== 'ok') {
                alert('Error al actualizar el estado del producto.');
                // Revertir el toggle si falla la API
                const card = document.querySelector(`.product-card[data-id='${id}']`);
                if(card) {
                    card.querySelector('.toggle-publish').checked = !nuevoEstado;
                }
            }
        } catch (error) {
            console.error('Error en la petición de actualización:', error);
            alert('Error de conexión al actualizar.');
        }
    }
    </script>

</body>
</html>