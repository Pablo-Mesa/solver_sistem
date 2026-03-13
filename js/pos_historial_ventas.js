document.addEventListener('DOMContentLoaded', () => {
    cargarHistorialVentas();
});

async function cargarHistorialVentas() {
    const cuerpo = document.getElementById('cuerpoHistorialVentas');
    try {
        const response = await fetch('../../api/pos/listar_ventas.php');
        const res = await response.json();

        if (res.status === 'ok') {
            cuerpo.innerHTML = "";
            res.datos.forEach(v => {
                const fila = document.createElement('tr');
                if (v.estado == 0) fila.classList.add('anulado');

                fila.innerHTML = `
                    <td>${v.fecha_hora}</td>
                    <td>${v.nro_factura}</td>
                    <td><strong>${Number(v.total_venta).toLocaleString('es-PY')} Gs.</strong></td>
                    <td>${v.estado == 1 ? '✅ Pagado' : '🚫 Anulado'}</td>
                    <td>
                        <button onclick="verDetalleVenta(${v.id}, '${v.nro_factura}', '${v.fecha_hora}')" style="background:#17a2b8; color:white; border:none; padding:5px; cursor:pointer; border-radius:3px;">👁️ Ver</button>
                        <button onclick="imprimirFactura(${v.id})" style="background:#6c757d; color:white; border:none; padding:5px; cursor:pointer; border-radius:3px;">🖨️</button>
                        ${v.estado == 1 ? `
                        <button class="btn-anular" onclick="anularVenta(${v.id})">🚫 Anular</button>` : ''}
                    </td>
                `;
                cuerpo.appendChild(fila);
            });
        }
    } catch (error) { console.error(error); }
}

async function anularVenta(id) {
    if (!confirm("⚠️ ¿Confirmas la anulación de esta venta?\nLos productos regresarán al inventario.")) {
        return;
    }

    try {
        const response = await fetch('../../api/pos/anular_venta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        const res = await response.json();

        if (res.status === 'ok') {
            alert("✅ " + res.mensaje);
            cargarHistorialVentas(); // Recargamos la tabla
        } else {
            alert("❌ " + res.mensaje);
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Error de conexión al anular.");
    }
}

// Añade esto a tu archivo JS existente

async function verDetalleVenta(id, nro, fecha) {
    document.getElementById('det_nro_venta').innerText = nro;
    document.getElementById('det_fecha_venta').innerText = fecha;
    const cuerpoModal = document.getElementById('cuerpoDetalleVentaModal');
    
    cuerpoModal.innerHTML = "<tr><td colspan='4'>Cargando...</td></tr>";
    document.getElementById('modalDetalleVenta').style.display = 'block';

    try {
        const response = await fetch(`../../api/pos/detalle_venta.php?id=${id}`);
        const res = await response.json();
        
        if (res.status === 'ok') {
            cuerpoModal.innerHTML = "";
            res.datos.forEach(item => {
                const fila = `<tr>
                    <td>${item.nombre}</td>
                    <td>${item.cantidad}</td>
                    <td>${Number(item.precio_unitario_venta).toLocaleString('es-PY')} Gs.</td>
                    <td>${Number(item.subtotal).toLocaleString('es-PY')} Gs.</td>
                </tr>`;
                cuerpoModal.innerHTML += fila;
            });
        }
    } catch (error) {
        console.error("Error al cargar detalle:", error);
    }
}

function cerrarModalVenta() {
    document.getElementById('modalDetalleVenta').style.display = 'none';
}

function imprimirFactura(id) {
    window.open(`factura_venta.php?id=${id}`, '_blank');
}