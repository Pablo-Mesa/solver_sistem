<?php 
    require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">    
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Dashboard</title>
    <link rel="icon" href="assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0px;
            padding: 0px;
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;            
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding: 0;
            margin: 0;
            background: linear-gradient(135deg, #999 0%, #f9f9f9 100%);
            width: 100%;
            min-height: 100vh ;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            color: #333;
        }

        /* Contenedor principal: más contenido visible en desktop */
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            padding: 16px;
            margin: 6px auto;
            max-width: 1100px;
        }

        .form-container h2 {
            color: #222;
            font-size: 22px;
            margin: 0 0 8px 0;
            font-weight: 600;
        }

        .form-container > p {
            color: #666;
            font-size: 14px;
            margin: 0 0 18px 0;
        }

        /* Grid: permitir más columnas en desktop pero tamaños más contenidos */
        .grid-modulos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-top: 16px;
        }

        .modulo-card {
            background: #fff;
            padding: 2px 0px;
            border: 1px solid #ececec;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            display: flex;
            flex-direction: column;
        }

        .modulo-card:hover {
            transform: translateY(-6px);
            border-color: #5b6de8;
            box-shadow: 0 10px 20px rgba(91, 109, 232, 0.08);
        }

        .modulo-header {
            display: flex;
            flex-direction: column;
            margin-bottom: 12px;
            border-bottom: 1px solid #f5f5f5;
            padding-bottom: 10px;
        }

        .modulo-header strong {
            font-size: 16px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modulo-header small {
            color: #888;
            font-size: 12px;
        }

        .modulo-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .btn-action {
            display: block;
            padding: 10px 12px;
            background: #fbfbfb;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            border-left: 4px solid transparent;
            transition: background 0.15s ease, transform 0.15s ease;
            font-size: 14px;
            font-weight: 500;
            text-align: left;
            min-height: 40px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action:active {
            transform: translateY(1px);
            background: #f1f1f1;
        }

        .pos .btn-action { border-left-color: #28a745; }
        .pos .btn-action:hover { background: #f0fbf4; }
        .web .btn-action { border-left-color: #17a2b8; }
        .web .btn-action:hover { background: #eef9fb; }
        .personal .btn-action { border-left-color: #ffc107; }
        .personal .btn-action:hover { background: #fff9ec; }

        .pos { border-top: 3px solid #28a745; }
        .web { border-top: 3px solid #17a2b8; }
        .personal { border-top: 3px solid #ffc107; }

        .logout-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #eee;
        }

        .logout-section a {
            display: inline-block;
            padding: 10px 18px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.2s ease, box-shadow 0.2s ease;
            font-weight: 600;
            min-width: 140px;
            font-size: 14px;
            width: 100%;
        }

        .logout-section a:hover {
            background: #c82333;
            box-shadow: 0 6px 12px rgba(200, 35, 51, 0.12);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 0px;
            padding: 0 20px;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            box-sizing: border-box;
        }
        
        .dashboard-card {
            background: white;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 140px;
        }
        
        .dashboard-card h3 {
            margin: 0 0 10px 0;
            font-size: 1em;
            font-weight: 600;
        }
        
        .dashboard-card p {
            font-size: 2em;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .dashboard-card small {
            font-size: 0.85em;
            opacity: 0.9;
        }
        
        /* MOBILE OPTIMIZATION - Tablets */
        @media (max-width: 768px) {
            .form-container {
                margin: 15px;
                padding: 16px;
            }
            
            .form-container h2 {
                font-size: 24px;
            }
            
            .grid-modulos {
                grid-template-columns: 1fr;
                gap: 16px;
            }
                        
            .btn-action {
                padding: 16px 14px;
                font-size: 16px;
                min-height: 48px;
            }
            
            .dashboard-grid {
                padding: 0 15px;
                gap: 12px;
            }
        }
        
        /* MOBILE OPTIMIZATION - Small screens */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .form-container {
                margin: 10px;
                padding: 14px;
                border-radius: 8px;
            }
            
            .form-container h2 {
                font-size: 22px;
                margin-bottom: 8px;
            }
            
            .form-container > p {
                font-size: 14px;
                margin-bottom: 18px;
            }
            
            .grid-modulos {
                gap: 12px;
                margin-top: 15px;
            }
                        
            .modulo-header {
                margin-bottom: 14px;
                padding-bottom: 10px;
            }
            
            .modulo-header strong {
                font-size: 17px;
                margin-bottom: 3px;
            }
            
            .modulo-header small {
                font-size: 12px;
            }
            
            .modulo-actions {
                gap: 8px;
            }
            
            .btn-action {
                padding: 12px 12px;
                font-size: 14px;
                min-height: 44px;
                border-radius: 6px;
            }
            
            .dashboard-grid {
                padding: 0 15px;
                gap: 12px;
                grid-template-columns: 1fr;
            }
            
            .dashboard-card p {
                font-size: 1.5em;
            }
        }
        
        /* Media query para grid de gráficas en tablet */
        @media (max-width: 1024px) {
            #graficoVentas_section {
                grid-template-columns: 1fr !important;
                gap: 20px;
            }
        }
        
        /* Media query para grid de gráficas en móvil */
        @media (max-width: 768px) {
            #graficoVentas_section {
                grid-template-columns: 1fr !important;
                padding: 0 15px !important;
                margin-top: 20px;
                gap: 15px;
            }
            
            #graficoVentas_section > div {
                min-width: 0;
            }
        }
        
        /* Media query para móviles muy pequeños */
        @media (max-width: 480px) {
            #graficoVentas_section {
                grid-template-columns: 1fr !important;
                padding: 0 10px !important;
                gap: 12px;
                margin-top: 15px;
            }
        }
        /* details/summary: comportamiento acordeón en móvil */
        .modulo-card summary { list-style: none; cursor: pointer; outline: none; display: flex; align-items: center; }
        .modulo-card summary::-webkit-details-marker { display: none; }
        .modulo-card summary::after { content: '\25BE'; margin-left: auto; font-size: 14px; color: #666; }
        .modulo-card[open] summary::after { content: '\25B4'; }

        @media (max-width: 768px) {
            /* En móvil ocultamos las acciones por defecto y las mostramos al abrir */
            .modulo-actions { display: none; }
            .modulo-card[open] .modulo-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 12px; }
            .modulo-card { padding: 12px; }
            .modulo-card summary { padding: 6px 0; }
        }

        /* --- NUEVO LAYOUT DESKTOP --- */
        .main-content-wrapper {
            width: 100%;
        }

        @media (min-width: 1024px) {
            body {
                flex-direction: row;
                align-items: flex-start;
                background: #f4f7f9;
            }

            .form-container {
                position: fixed;
                left: 0;
                top: 0;
                width: 320px;
                max-width: 320px;
                height: 100vh;
                overflow-y: auto;
                border-radius: 0;
                margin: 0;
                border-right: 1px solid #e0e0e0;
                box-shadow: none;
            }

            .main-content-wrapper {
                margin-left: 320px; /* Ancho del sidebar */
                width: calc(100% - 320px);
                padding: 25px;
                height: 100vh;
                overflow-y: auto;
            }

            .dashboard-grid, #graficoVentas_section {
                max-width: 100%;
            }
        }
    </style>

</head>
<body>

    <!-- Contenedor Principal -->
    <div class="form-container">
        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 0px;">            
            <img src="assets/icono_solver_nobg.png" alt="Solver Logo" style="width: 28px; height: 28px;">
            <h1 style="font-size: 24px; color: #333;">Solver</h1>
        </div>
        <small>Hola, <strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?> </strong> selecciona el área de trabajo:</small>
        
        <div class="grid-modulos">
            <details class="modulo-card pos">
                <summary class="modulo-header">
                    <strong>💼 Sistema POS</strong>
                    <small>Ventas y Stock</small>
                </summary>
                <div class="modulo-actions">
                    <a href="modulos/pos/datos_empresa.php" class="btn-action">🏢 Datos del negocio</a>
                    <a href="modulos/pos/compras.php" class="btn-action">📦 Cargar Compra</a>
                    <a href="modulos/pos/ventas.php" class="btn-action">🛒 Registrar Venta</a>
                    <a href="modulos/pos/inventario.php" class="btn-action">📊 Stock / Precios</a>
                    <a href="modulos/pos/historial_compras.php" class="btn-action">📋 Historial Compras</a>
                    <a href="modulos/pos/historial_ventas.php" class="btn-action">📋 Historial Ventas</a>
                    <a href="modulos/pos/reporte_mensual.php" class="btn-action">📊 Reporte Mensual</a>
                </div>
            </details>

            <details class="modulo-card web">
                <summary class="modulo-header">
                    <strong>🚚 Delivery / Web</strong>
                    <small>Gestión de Pedidos</small>
                </summary>
                <div class="modulo-actions">
                    <a href="modulos/tienda web/pedidos.php" class="btn-action">🔔 Pedidos Pendientes</a>
                    <a href="modulos/tienda web/productos_web.php" class="btn-action">🌐 Editar Tienda</a>
                    <a href="modulos/tienda web/tienda.php" class="btn-action">🏪 Tienda</a>
                </div>
            </details>

            <details class="modulo-card personal">
                <summary class="modulo-header">
                    <strong>🏠 Control Ingresos / Egresos</strong>
                    <small>Ingresos y Gastos</small>
                </summary>
                <div class="modulo-actions">
                    <a href="modulos/personal/gastos.php" class="btn-action">💸 Registrar Gasto</a>
                    <a href="modulos/personal/moto.php" class="btn-action">🏍️ Control Moto</a>
                </div>
            </details>
        </div>

        <div class="logout-section">
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <!-- Contenido Principal (Dashboard y Gráficos) -->
    <div class="main-content-wrapper">
        <!-- Dashboard Resumen -->
        <div class="dashboard-grid">

            <div class="dashboard-card" style="background: #28a745;">
                <h3>Ventas de Hoy</h3>
                <p id="dash_ventas_hoy">0 Gs.</p>
                <small id="dash_conteo_ventas">0 transacciones</small>
            </div>

            <div class="dashboard-card" style="background: #dc3545;">
                <h3>Alertas de Stock</h3>
                <p id="dash_stock_bajo">0</p>
                <small>Productos por agotarse</small>
            </div>

            <div class="dashboard-card" style="background: #007bff;">
                <h3>Estado de Caja</h3>
                <p>Abierta</p>
                <small>Usuario: <?php echo $_SESSION['user_name']; ?></small>
            </div>
        </div>

        <!-- Gráficos y Top 5 Productos -->
        <div id="graficoVentas_section" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 30px; padding: 0 20px; max-width: 1100px; margin-left: auto; margin-right: auto; width: 100%; box-sizing: border-box; margin-bottom: 40px;">
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3 style="color: #333; margin: 0 0 15px 0;">📊 Tendencia de Ventas (Últimos 7 días)</h3>
                <canvas id="graficoVentas" style="max-height: 400px;"></canvas>
            </div>

            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3 style="color: #333; margin: 0 0 15px 0;">🏆 Top 5 Productos</h3>
                <ul id="lista_top" style="list-style: none; padding: 0; margin: 0;">
                    <li style="color: #999; padding: 10px; text-align: center;">Cargando...</li>
                </ul>
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #eee;">
                <div style="text-align: center;">
                    <button onclick="window.location.href='modulos/pos/reporte_mensual.php'" 
                            style="background: #6c757d; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; width: 100%; font-weight: 600; transition: background 0.2s ease;">
                        📊 Cierre de Mes (IVA)
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

        // control open state of details according to viewport width
        function adjustDetails() {
            const desktop = window.matchMedia('(min-width: 769px)').matches;
            document.querySelectorAll('.modulo-card').forEach(d => {
                if (desktop) {
                    d.setAttribute('open', '');
                } else {
                    d.removeAttribute('open');
                }
            });
        }
        //window.addEventListener('DOMContentLoaded', adjustDetails);
        //window.addEventListener('resize', adjustDetails);

        async function actualizarResumen() {
            try {
                // Apuntamos a la API del POS desde la raíz
                const response = await fetch('api/pos/obtener_resumen.php');
                const res = await response.json();

                if (res.status === 'ok') {
                    document.getElementById('dash_ventas_hoy').innerText = 
                        Number(res.ventas_hoy).toLocaleString('es-PY') + " Gs.";
                    
                    document.getElementById('dash_conteo_ventas').innerText = 
                        res.conteo_ventas + " facturas emitidas hoy";

                    document.getElementById('dash_stock_bajo').innerText = res.alertas_stock;
                }
            } catch (error) {
                console.error("Error cargando el dashboard:", error);
            }
        }

        async function renderizarGrafica() {
            try {
                const response = await fetch('api/pos/ventas_semana.php');
                const res = await response.json();

                if (res.status === 'ok' && res.datos && res.datos.length > 0) {
                    const etiquetas = res.datos.map(d => d.fecha);
                    const montos = res.datos.map(d => d.total);

                    const canvasElement = document.getElementById('graficoVentas');
                    if (canvasElement && canvasElement.getContext) {
                        const ctx = canvasElement.getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: etiquetas,
                                datasets: [{
                                    label: 'Ventas Diarias (Gs.)',
                                    data: montos,
                                    borderColor: '#28a745',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                scales: {
                                    y: { beginAtZero: true }
                                }
                            }
                        });
                    }
                } else {
                    console.warn("No hay datos de ventas disponibles para la gráfica.");
                }
            } catch (error) {
                console.error("Error cargando gráfica de ventas:", error);
            }
        }

        async function cargarTopProductos() {
            const lista = document.getElementById('lista_top');
            try {
                const response = await fetch('api/pos/productos_top.php');
                const res = await response.json();

                if (res.status === 'ok') {
                    lista.innerHTML = ""; // Limpiamos
                    if (res.datos.length === 0) {
                        lista.innerHTML = "<li style='color: #666; padding: 10px;'>No hay ventas registradas aún.</li>";
                        return;
                    }

                    res.datos.forEach((prod, index) => {
                        const li = document.createElement('li');
                        li.style.cssText = "padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;";
                        
                        // Le damos un toque visual: el #1 en negrita y color
                        const colorMedalla = index === 0 ? '#ffd700' : (index === 1 ? '#c0c0c0' : '#cd7f32');
                        const badge = index < 3 ? `<span style="background:${colorMedalla}; color:white; padding:2px 8px; border-radius:10px; font-size:0.8em; margin-right:10px;">${index+1}</span>` : `<span style="margin-right:25px;">${index+1}</span>`;

                        li.innerHTML = `
                            <div>${badge} <strong>${prod.nombre}</strong></div>
                            <span style="background: #e9ecef; padding: 2px 10px; border-radius: 15px; font-size: 0.9em;">${prod.total_vendido} unid.</span>
                        `;
                        lista.appendChild(li);
                    });
                }
            } catch (error) {
                console.error("Error cargando el Top 5:", error);
            }
        }

        // IMPORTANTE: Asegúrate de llamarla aquí
        document.addEventListener('DOMContentLoaded', () => {
            actualizarResumen();
            renderizarGrafica();
            cargarTopProductos(); // <-- Asegúrate de que esta línea esté presente
        });

    </script>

</body>
</html>
