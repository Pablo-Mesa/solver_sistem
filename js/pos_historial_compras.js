// Cargamos el historial al iniciar la página

document.addEventListener('DOMContentLoaded', () => {
    cargarHistorial();
});

async function cargarHistorial() {
    const cuerpo = document.getElementById('cuerpoHistorial');
    try {
        const response = await fetch('../../api/pos/listar_compras.php');
        const res = await response.json();

        if (res.status === 'ok') {
            cuerpo.innerHTML = "";
            res.datos.forEach(compra => {
                // asegurarnos de que estado sea un número para comparaciones fiables
                compra.estado = Number(compra.estado);
                const fila = document.createElement('tr');
                if (compra.estado === 0) fila.style.opacity = "0.5";

                // escapar valores que podrían contener comillas para no romper el onclick
                const nroEsc = compra.nro_comprobante.replace(/'/g, "\\'");
                const provEsc = compra.razon_social.replace(/'/g, "\\'");

                // formato manual para evitar el desplazamiento de zona horaria
                function formatLocalDate(dateStr) {
                    // dateStr viene como "YYYY-MM-DD" o "YYYY-MM-DD hh:mm:ss";
                    // al crear un Date directamente el navegador lo interpreta en UTC,
                    // lo cual desplaza el día hacia atrás en zonas horarias negativas.
                    const parts = dateStr.split(' ')[0].split('-');
                    const y = parseInt(parts[0], 10);
                    const m = parseInt(parts[1], 10) - 1;
                    const d = parseInt(parts[2], 10);
                    return new Date(y, m, d).toLocaleDateString();
                }

                fila.innerHTML = `
                    <td>${formatLocalDate(compra.fecha_emision)}</td>
                    <td>${compra.razon_social}</td>
                    <td>${compra.nro_comprobante}</td>
                    <td><strong>${Number(compra.total_factura).toLocaleString('es-PY')}</strong></td>
                    <td>${Number(compra.iva_10).toLocaleString('es-PY')}</td>
                    <td>${Number(compra.iva_5).toLocaleString('es-PY')}</td>
                    <td>
                        <button class="btn-ver" onclick="verDetalle(${compra.id}, '${nroEsc}', '${provEsc}')">👁️ Ver</button>
                        ${compra.estado == 1 ? `<button class="btn-anular" onclick="anularCompra(${compra.id})">🚫 Anular</button>` : '<span>Anulada</span>'}
                    </td>
                `;
                cuerpo.appendChild(fila);
            });
        }
    } catch (error) { console.error(error); }
}


async function verDetalle(id, nro, prov) {
    document.getElementById('det_nro').innerText = nro;
    document.getElementById('det_proveedor').innerText = prov;
    const cuerpoModal = document.getElementById('cuerpoDetalleModal');
    cuerpoModal.innerHTML = "Cargando...";
    document.getElementById('modalDetalle').style.display = 'block';

    try {
        // corregir ruta al script correcto
        const response = await fetch(`../../api/pos/detalles_compra.php?id=${id}`);
        const res = await response.json();
        if (res.status === 'ok') {
            cuerpoModal.innerHTML = "";
            res.datos.forEach(item => {
                const fila = `<tr>
                    <td>${item.nombre}</td>
                    <td>${item.cantidad}</td>
                    <td>${Number(item.precio_unitario_costo).toLocaleString('es-PY')}</td>
                    <td>${Number(item.subtotal).toLocaleString('es-PY')}</td>
                </tr>`;
                cuerpoModal.innerHTML += fila;
            });
        }
    } catch (error) { console.error(error); }
}

function cerrarModal() {
    document.getElementById('modalDetalle').style.display = 'none';
}

async function anularCompra(id) {
    if (!confirm("⚠️ ¿Estás seguro de anular esta compra?\n\nEsto restará los productos del stock actual y no se puede deshacer.")) {
        return;
    }

    try {
        const response = await fetch('../../api/pos/anular_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        const res = await response.json();

        if (res.status === 'ok') {
            alert("✅ " + res.mensaje);
            cargarHistorial(); // Refrescamos la lista
        } else {
            alert("❌ Error: " + res.mensaje);
        }
    } catch (error) {
        console.error("Error al anular:", error);
        alert("Ocurrió un error de conexión.");
    }
}