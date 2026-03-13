<?php 
    require_once 'includes/auth.php';
?>
<!doctype html>
<html lang="es" class="h-full">

<head>
  <meta charset="UTF-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
  <title>Solver | Dashboard</title>
  <link rel="icon" href="assets/icono_solver_nobg.png" />
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
  
  <style>
    * {
      font-family: 'DM Sans', sans-serif;
    }

    .nav-item {
      transition: all 0.2s ease;
    }

    .nav-item:active {
      transform: scale(0.97);
    }

    .menu-item {
      transition: all 0.15s ease;
    }

    .menu-item:active {
      background-color: rgba(0, 0, 0, 0.05);
    }

    .fade-in {
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(8px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .slide-up {
      animation: slideUp 0.25s ease;
    }

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

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(100%);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
  
  <style>
    body {
      box-sizing: border-box;
    }
  </style>

</head>

<body class="h-full bg-stone-50 overflow-auto">
  
  <div id="app" class="w-full min-h-full flex flex-col bg-gradient-to-br from-slate-200 to-gray-300">
    
    <!-- Header -->
    <header id="header" class="px-5 py-4 text-[#444]">
      

      <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 0px;">
          <img src="assets/icono_solver_nobg.png" alt="Solver Logo" style="width: 28px; height: 28px;">
          <h1 style="font-size: 24px; color: #333;">Solver</h1>
      </div>
      <small>Hola, <strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?> </strong> selecciona el área de trabajo:</small>

    </header>
    
    <!-- Main Content -->
    <main id="main-content" class="flex-1 px-4 py-5">
      
      <!-- Navigation Cards -->
      <div id="nav-grid" class="grid grid-cols-1 gap-3">
          
          <!-- Sistema POS --> 
          <button onclick="openSection('pos')" class="nav-item bg-white rounded-2xl p-5 text-left shadow-sm border border-stone-100 hover:border-stone-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-stone-900 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <h2 class="font-semibold text-stone-900">Sistema POS</h2>
                  <p class="text-sm text-stone-500">Ventas y compras</p>
                </div>
              </div>
              <svg class="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </button>
          
          <!-- Tienda Web y Delivery -->
          <button onclick="openSection('tienda')" class="nav-item bg-white rounded-2xl p-5 text-left shadow-sm border border-stone-100 hover:border-stone-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div>
                  <h2 class="font-semibold text-stone-900">Tienda Web y Delivery</h2>
                  <p class="text-sm text-stone-500">Pedidos online</p>
                </div>
              </div>
              <svg class="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </button>
        
          <!-- Control de Gastos -->
          <button onclick="openSection('gastos')" class="nav-item bg-white rounded-2xl p-5 text-left shadow-sm border border-stone-100 hover:border-stone-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div>
                  <h2 class="font-semibold text-stone-900">Control de Gastos</h2>
                  <p class="text-sm text-stone-500">Finanzas y reportes</p>
                </div>
              </div>
              <svg class="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </button>

      </div>
      
      <!-- Quick Stats -->
      <div class="mt-6 grid grid-cols-2 gap-3">
        <div class="bg-white rounded-xl p-4 border border-stone-100">
          <p class="text-xs text-stone-500 uppercase tracking-wide">Ventas Hoy</p>
          <p id="dash_ventas_hoy" class="text-2xl font-semibold text-stone-900 mt-1">0 Gs.</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-stone-100">
          <p class="text-xs text-stone-500 uppercase tracking-wide">Transacciones</p>
          <p id="dash_conteo_ventas" class="text-2xl font-semibold text-stone-900 mt-1">0</p>
        </div>
      </div>

      <div class="logout-section">
          <a href="logout.php">Cerrar Sesión</a>
      </div>

    </main>
    
    <!-- Section Panel (Hidden by default) -->
    <div id="section-panel" class="fixed inset-0 bg-stone-50 z-50 hidden flex-col">
      <!-- Section Header -->
      <header class="bg-white border-b border-stone-200 px-4 py-4 flex items-center gap-3">
        <button onclick="closeSection()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-stone-100 transition">
          <svg class="w-5 h-5 text-stone-700" fill="none" stroke="currentColor" viewbox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <h2 id="section-title" class="text-lg font-semibold text-stone-900"></h2>
      </header>
      <!-- Section Menu -->
      <div id="section-menu" class="flex-1 overflow-auto p-4">
        <!-- Menu items will be injected here -->
      </div>
    </div>

  </div>

  <script>
    // Section data
    const sections = {
      pos: {
        title: 'Sistema POS',
        icon: 'stone-900',
        items: [{
            name: 'Datos de la empresa',
            icon: 'building',
            link: 'modulos/pos/datos_empresa.php'
          },
          {
            name: 'Cargar compras',
            icon: 'upload',
            link: 'modulos/pos/compras.php'
          },
          {
            name: 'Registrar ventas',
            icon: 'cart',
            link: 'modulos/pos/ventas.php'
          },
          {
            name: 'Stock y precios',
            icon: 'box',
            link: 'modulos/pos/inventario.php'
          },
          {
            name: 'Historial compras',
            icon: 'history',
            link: 'modulos/pos/historial_compras.php'
          },
          {
            name: 'Historial ventas',
            icon: 'receipt',
            link: 'modulos/pos/historial_ventas.php'
          },
          {
            name: 'Reporte Mensual',
            icon: 'chart',
            link: 'modulos/pos/reporte_mensual.php'
          }
        ]
      },
      tienda: {
        title: 'Tienda Web y Delivery',
        icon: 'amber-500',
        items: [{
            name: 'Configuración tienda',
            icon: 'settings'
          },
          {
            name: 'Catálogo de productos',
            icon: 'grid'
          },
          {
            name: 'Pedidos pendientes',
            icon: 'clock'
          },
          {
            name: 'Pedidos entregados',
            icon: 'check'
          },
          {
            name: 'Zonas de delivery',
            icon: 'map'
          },
          {
            name: 'Métodos de pago',
            icon: 'card'
          }
        ]
      },
      gastos: {
        title: 'Control de Gastos',
        icon: 'emerald-600',
        items: [{
            name: 'Registrar gasto',
            icon: 'plus'
          },
          {
            name: 'Categorías',
            icon: 'tag'
          },
          {
            name: 'Historial de gastos',
            icon: 'list'
          },
          {
            name: 'Gastos fijos',
            icon: 'repeat'
          },
          {
            name: 'Balance general',
            icon: 'scale'
          },
          {
            name: 'Reporte mensual',
            icon: 'chart'
          }
        ]
      }
    };

    // Icons library (Simplificada para el ejemplo)
    const icons = {
      building: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
      upload: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>',
      cart: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>',
      box: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
      history: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      receipt: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
      chart: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
      settings: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
      grid: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
      clock: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      check: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      map: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
      card: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
      plus: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>',
      tag: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
      list: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
      repeat: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
      scale: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>'
    };

    function openSection(sectionId) {
      const section = sections[sectionId];
      const panel = document.getElementById('section-panel');
      const title = document.getElementById('section-title');
      const menu = document.getElementById('section-menu');

      title.textContent = section.title;

      menu.innerHTML = section.items.map((item, index) => `
        <button class="menu-item w-full flex items-center gap-4 p-4 bg-white rounded-xl mb-2 text-left border border-stone-100 fade-in" style="animation-delay: ${index * 0.05}s" onclick="selectItem('${item.name}')">
          <div class="w-10 h-10 bg-stone-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              ${icons[item.icon]}
            </svg>
          </div>          
          <a href="${item.link}" class="font-medium text-stone-800">${item.name}</a>
          <svg class="w-4 h-4 text-stone-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      `).join('');

      panel.classList.remove('hidden');
      panel.classList.add('flex', 'slide-up');
    }

    function closeSection() {
      const panel = document.getElementById('section-panel');
      panel.classList.add('hidden');
      panel.classList.remove('flex', 'slide-up');
    }

    function selectItem(itemName) {
      // Show selected feedback
      const buttons = document.querySelectorAll('.menu-item');
      buttons.forEach(btn => {
        if (btn.textContent.includes(itemName)) {
          btn.classList.add('ring-2', 'ring-stone-900', 'ring-offset-2');
          setTimeout(() => {
            btn.classList.remove('ring-2', 'ring-stone-900', 'ring-offset-2');
          }, 300);
        }
      });
    }

    // Lógica para obtener datos reales
    async function actualizarResumen() {
        try {
            const response = await fetch('api/pos/obtener_resumen.php');
            const res = await response.json();

            if (res.status === 'ok') {
                document.getElementById('dash_ventas_hoy').innerText = 
                    Number(res.ventas_hoy).toLocaleString('es-PY') + " Gs.";
                
                document.getElementById('dash_conteo_ventas').innerText = 
                    res.conteo_ventas;
            }
        } catch (error) {
            console.error("Error cargando el dashboard:", error);
        }
    }

    document.addEventListener('DOMContentLoaded', actualizarResumen);
  </script>

</body>

</html>