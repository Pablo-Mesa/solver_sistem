document.addEventListener('DOMContentLoaded', () => {
    cargarPedidos();
});

async function cargarPedidos() {
    const tbody = document.getElementById('cuerpo-tabla-pedidos');
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Cargando...</td></tr>';

    try {
        const response = await fetch('../../api/tienda web/listar_pedidos_web.php');
        const res = await response.json();

        if (res.status === 'ok') {
            renderizarTabla(res.datos);
        } else {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:red;">${res.mensaje}</td></tr>`;
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:red;">Error de conexión.</td></tr>`;
    }
}

function renderizarTabla(pedidos) {
    const tbody = document.getElementById('cuerpo-tabla-pedidos');
    tbody.innerHTML = '';

    if (pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No hay pedidos registrados.</td></tr>';
        return;
    }

    pedidos.forEach(p => {
        const tr = document.createElement('tr');
        const fecha = new Date(p.fecha_creacion).toLocaleString('es-PY');
        const total = Number(p.total_estimado).toLocaleString('es-PY');

        const statusOptions = `
            <option value="pendiente" ${p.estado === 'pendiente' ? 'selected' : ''}>Pendiente</option>
            <option value="procesado" ${p.estado === 'procesado' ? 'selected' : ''}>Procesado</option>
            <option value="cancelado" ${p.estado === 'cancelado' ? 'selected' : ''}>Cancelado</option>
        `;

        tr.innerHTML = `
            <td>#${p.id}</td>
            <td>${fecha}</td>
            <td>${p.nombre_contacto}</td>
            <td>${p.telefono_contacto || '-'}</td>
            <td>${p.tipo_entrega}</td>
            <td>${total} Gs.</td>
            <td>
                <select class="status-select status-${p.estado}" data-id="${p.id}" onchange="cambiarEstado(this)">
                    ${statusOptions}
                </select>
            </td>
            <td>
                <button class="btn-ver" onclick="verDetalle(${p.id})">Ver</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function cambiarEstado(selectElement) {
    const pedidoId = selectElement.dataset.id;
    const nuevoEstado = selectElement.value;

    if (!confirm(`¿Seguro que quieres cambiar el estado del pedido #${pedidoId} a "${nuevoEstado}"?`)) {
        cargarPedidos(); // Recargar para revertir el cambio visual
        return;
    }

    try {
        const response = await fetch('../../api/tienda web/actualizar_estado_pedido.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: pedidoId, estado: nuevoEstado })
        });
        const res = await response.json();

        if (res.status === 'ok') {
            alert(res.mensaje);
            cargarPedidos(); // Recargar para ver el cambio
        } else {
            alert('Error: ' + res.mensaje);
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión al actualizar el estado.');
    }
}

async function verDetalle(pedidoId) {
    const modal = document.getElementById('modal-detalle');
    const idLabel = document.getElementById('detalle-pedido-id');
    const clienteInfo = document.getElementById('detalle-info-cliente');
    const detalleBody = document.getElementById('cuerpo-tabla-detalle');

    idLabel.textContent = '...';
    clienteInfo.innerHTML = 'Cargando...';
    detalleBody.innerHTML = '';
    modal.style.display = 'block';

    try {
        const response = await fetch(`../../api/tienda web/detalles_pedido_web.php?id=${pedidoId}`);
        const res = await response.json();

        if (res.status === 'ok') {
            const { cabecera, detalles } = res.datos;
            idLabel.textContent = cabecera.id;
            clienteInfo.innerHTML = `
                <b>Cliente:</b> ${cabecera.nombre_contacto}<br>
                <b>Teléfono:</b> ${cabecera.telefono_contacto || 'No especificado'}<br>
                <b>Dirección:</b> ${cabecera.direccion_envio || 'No especificada (Retiro en local)'}<br>
                <b>Observación:</b> ${cabecera.observacion || 'Ninguna'}
            `;

            detalles.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.producto_nombre}</td>
                    <td>${item.cantidad}</td>
                    <td>${Number(item.precio_unitario).toLocaleString('es-PY')} Gs.</td>
                    <td>${Number(item.subtotal).toLocaleString('es-PY')} Gs.</td>
                `;
                detalleBody.appendChild(tr);
            });
        } else {
            clienteInfo.innerHTML = `<p style="color:red">${res.mensaje}</p>`;
        }
    } catch (error) {
        console.error(error);
        clienteInfo.innerHTML = `<p style="color:red">Error de conexión al cargar detalles.</p>`;
    }
}

function cerrarModal() {
    document.getElementById('modal-detalle').style.display = 'none';
}