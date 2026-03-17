let productosGlobal = [];
let carrito = [];
let currentStep = 1;

document.addEventListener('DOMContentLoaded', () => {
    cargarProductos();
    // Listener para el buscador en tiempo real
    document.getElementById('buscador').addEventListener('keyup', (e) => {
        filtrarProductos(e.target.value);
    });
    actualizarContadorCarrito();    

    // Cerrar al hacer click fuera del modal (Overlay)
    document.getElementById('cart-overlay').addEventListener('click', toggleCart);
});

/*drawCube(element, title, tamanho)*/

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
        const imagenPrincipal = prod.imagen_url ? prod.imagen_url : 'https://via.placeholder.com/300x200?text=Sin+Imagen';
        
        // Construimos un array con TODAS las imagenes (Principal + Galería)
        let imagenes = [imagenPrincipal];
        if (prod.galeria && Array.isArray(prod.galeria)) {
            prod.galeria.forEach(g => imagenes.push(g.imagen_url));
        }

        const precio = parseFloat(prod.precio_venta).toLocaleString('es-PY');
        const stock = parseInt(prod.stock_actual);
        const sinStock = stock <= 0;

        // Generamos el HTML de las imagenes para el carrusel
        let imagesHtml = '';
        imagenes.forEach((img, index) => {
            // Solo la primera (índice 0) tendrá la clase 'active'
            const activeClass = index === 0 ? 'active' : '';
            imagesHtml += `<img src="${img}" alt="${prod.nombre}" class="product-image ${activeClass}" data-index="${index}">`;
        });

        // Botones de control (solo si hay más de 1 imagen)
        let controlsHtml = '';
        if (imagenes.length > 1) {
            controlsHtml = `
                <button class="carousel-btn prev-btn" onclick="cambiarImagen(this, -1)">❮</button>
                <button class="carousel-btn next-btn" onclick="cambiarImagen(this, 1)">❯</button>
            `;
        }

        const card = document.createElement('div');
        card.className = 'product-card';
        card.innerHTML = `
            <div class="product-image-container">
                ${imagesHtml}
                ${controlsHtml}
            </div>
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
                // Usamos showToast si está disponible (tool-kit), sino un log silencioso
                if(typeof showToast === 'function') showToast(`+1 ${prod.nombre}`, '#28a745');
            } else {
                alert("No hay más stock disponible.");
                return;
            }
        } else {
            carrito.push({ ...prod, cantidad: 1 });
            if(typeof showToast === 'function') showToast(`Agregado: ${prod.nombre}`, '#28a745');
        }
        actualizarContadorCarrito();
        renderizarCarrito(); // Actualizar visualmente el modal
    }
}

function eliminarDelCarrito(id) {
    carrito = carrito.filter(item => item.id !== id);
    actualizarContadorCarrito();
    renderizarCarrito();
}

function actualizarCantidad(id, cambio) {
    const item = carrito.find(i => i.id === id);
    if(item) {
        const nuevaCant = item.cantidad + cambio;
        if(nuevaCant > 0 && nuevaCant <= item.stock_actual) {
            item.cantidad = nuevaCant;
        } else if (nuevaCant <= 0) {
            eliminarDelCarrito(id);
            return; // Salimos porque eliminar ya renderiza
        } else {
            alert("Stock máximo alcanzado para este producto.");
            return;
        }
        actualizarContadorCarrito();
        renderizarCarrito();
    }
}

function renderizarCarrito() {
    const container = document.getElementById('cart-items-container');
    const totalLabel = document.getElementById('cart-total-amount');
    if(!container) return;

    container.innerHTML = '';
    let total = 0;

    if(carrito.length === 0) {
        container.innerHTML = '<p style="text-align:center; color:#888; margin-top:20px;">Tu carrito está vacío.</p>';
        if(totalLabel) totalLabel.innerText = '0 Gs.';
        return;
    }

    carrito.forEach(item => {
        const subtotal = item.precio_venta * item.cantidad;
        total += subtotal;

        // Creamos el elemento visual
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <img src="${item.imagen_url || '../../assets/no-image.png'}" class="cart-item-img" alt="Img">
            <div class="cart-item-details">
                <div class="cart-item-title">${item.nombre}</div>
                <div class="cart-item-price">${parseInt(item.precio_venta).toLocaleString('es-PY')} Gs.</div>
            </div>
            <div style="display:flex; align-items:center; gap:5px;">
                <button onclick="actualizarCantidad(${item.id}, -1)" style="width:25px; height:25px; border:1px solid #ddd; background:#fff; cursor:pointer; border-radius:3px;">-</button>
                <span style="min-width:20px; text-align:center; font-weight:bold;">${item.cantidad}</span>
                <button onclick="actualizarCantidad(${item.id}, 1)" style="width:25px; height:25px; border:1px solid #ddd; background:#fff; cursor:pointer; border-radius:3px;">+</button>
            </div>
            <button onclick="eliminarDelCarrito(${item.id})" style="margin-left:10px; border:none; background:none; color:#dc3545; font-size:1.2rem; cursor:pointer;">&times;</button>
        `;
        container.appendChild(div);
    });

    if(totalLabel) {
        totalLabel.innerText = total.toLocaleString('es-PY') + ' Gs.';
    }
}

function actualizarContadorCarrito() {
    const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    document.getElementById('cart-count').innerText = totalItems;
}

function verCarrito() {
    // Abrir el modal (función definida en tienda.php)
    if(typeof toggleCart === 'function') {
        renderizarCarrito(); // Aseguramos que esté actualizado al abrir
        toggleCart();
    } else {
        console.error("Función toggleCart no encontrada.");
    }
}

// Función para el carrusel de imágenes
function cambiarImagen(btn, direccion) {
    // Encontrar el contenedor de la imagen
    const container = btn.parentElement;
    const imagenes = container.querySelectorAll('.product-image');
    let activeIndex = 0;

    // Buscar cual está activa
    imagenes.forEach((img, index) => {
        if (img.classList.contains('active')) {
            activeIndex = index;
            img.classList.remove('active');
        }
    });

    // Calcular nuevo índice (circular)
    let newIndex = (activeIndex + direccion + imagenes.length) % imagenes.length;
    
    // Activar nueva imagen
    imagenes[newIndex].classList.add('active');
}

/* --- LÓGICA DEL MODAL Y CHECKOUT --- */

function toggleCart() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    drawer.classList.toggle('active');
    overlay.classList.toggle('active');
}

function toggleDeliveryAddress(show) {
    const fields = document.getElementById('address-fields');
    fields.style.display = show ? 'block' : 'none';
}

function showStep(step) {
    // Ocultar todos los pasos
    document.querySelectorAll('.checkout-step').forEach(el => el.classList.remove('active'));
    // Mostrar paso actual
    document.getElementById(`step-${step}`).classList.add('active');
    
    // Actualizar botones y títulos según el paso
    const btnMain = document.getElementById('btn-main-action');
    const btnBack = document.getElementById('btn-back-action');
    const title = document.getElementById('cart-title');

    if (step === 1) {
        title.innerText = "Tu Carrito";
        btnMain.innerText = "Iniciar Pedido";
        btnBack.style.display = 'none';
    } else if (step === 2) {
        title.innerText = "Datos de Entrega";
        btnMain.innerText = "Ir al Pago";
        btnBack.style.display = 'block';
    } else if (step === 3) {
        title.innerText = "Confirmar Pago";
        btnMain.innerText = "Finalizar Compra";
        btnBack.style.display = 'block';
    }
    currentStep = step;
}

function nextStep() {
    if (currentStep === 1) {
        if (carrito.length === 0) {
            alert("Tu carrito está vacío.");
            return;
        }
        showStep(2);
    } else if (currentStep === 2) {
        // Validaciones del paso 2
        const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
        const nombre = document.getElementById('nombre_contacto').value.trim();
        
        if (deliveryType === 'delivery') {
            const dir = document.getElementById('direccion').value.trim();
            const tel = document.getElementById('telefono').value.trim();
            if (!dir || !tel) {
                alert("Para envíos por Delivery, la dirección y el teléfono son obligatorios.");
                return;
            }
        }
        
        showStep(3);
    } else if (currentStep === 3) {
        finalizarCompra();
    }
}

function prevStep() {
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

async function finalizarCompra() {
    const btnMain = document.getElementById('btn-main-action');
    btnMain.disabled = true;
    btnMain.innerText = "Procesando...";

    const datosPedido = {
        items: carrito,
        nombre_contacto: document.getElementById('nombre_contacto').value.trim(),
        delivery_type: document.querySelector('input[name="delivery_type"]:checked').value,
        direccion: document.getElementById('direccion').value.trim(),
        telefono: document.getElementById('telefono').value.trim(),
        ubicacion: document.getElementById('ubicacion').value.trim(),
        observacion: document.getElementById('observacion').value.trim(),
        pago: document.querySelector('input[name="pago"]:checked').value
    };

    try {
        const response = await fetch('../../api/tienda web/guardar_pedido_web.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosPedido)
        });

        const res = await response.json();

        if (res.status === 'ok') {
            alert(`✅ ¡Pedido realizado con éxito!\nID de Pedido: #${res.pedido_id}`);
            // Resetear carrito y formulario
            carrito = [];
            actualizarContadorCarrito();
            renderizarCarrito();
            toggleCart(); // Cerrar modal
            showStep(1);  // Volver al paso 1
        } else {
            alert("❌ Error al guardar el pedido: " + res.mensaje);
        }
    } catch (error) {
        console.error(error);
        alert("Error de conexión al procesar el pedido.");
    } finally {
        btnMain.disabled = false;
        if(currentStep === 3) btnMain.innerText = "Finalizar Compra";
    }
}