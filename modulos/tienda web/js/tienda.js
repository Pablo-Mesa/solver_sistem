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