
let detalleVenta = [];
let totalVenta = 0;

document.addEventListener('DOMContentLoaded', () => {
    // 0. Cargar datos de factura (timbrado, punto emisión, número sugerido)
    cargarDatosFactura();

    // 1. Cargamos productos (reutilizamos la API)
    cargarProductosVenta();

    // 2. Al elegir producto, mostramos el precio y el stock disponible
    document.getElementById('select_producto').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value !== "") {
            document.getElementById('precio_venta').value = selected.dataset.precio;
            // Guardamos el stock disponible en una propiedad temporal para validar
            this.dataset.currentStock = selected.dataset.stock;
        }
    });

    // 3. Botón agregar
    document.getElementById('btnAgregarVenta').addEventListener('click', () => {
        agregarProductoAVenta();
    });
});

async function cargarDatosFactura() {
    try {
        const response = await fetch('../../api/pos/obtener_datos_factura.php');
        const res = await response.json();
        if (res.status === 'ok') {
            document.getElementById('nro_factura').value = res.nro_factura_sugerido || '';
            document.getElementById('timbrado_venta').value = res.timbrado_vigente || '';
            document.getElementById('punto_emision_venta').value = res.punto_emision || '';
            const leyenda = document.getElementById('timbrado_leyenda');
            if (res.timbrado_vigente) {
                leyenda.textContent = 'Timbrado: ' + res.timbrado_vigente;
            } else {
                leyenda.textContent = 'Configure timbrado en Datos del negocio.';
            }
        }
    } catch (error) {
        console.error("Error cargando datos de factura:", error);
    }
}

async function cargarProductosVenta() {
    const select = document.getElementById('select_producto');
    try {
        const response = await fetch('../../api/pos/listar_productos.php');
        const res = await response.json();

        if (res.status === 'ok') {
            res.datos.forEach(prod => {
                const opt = document.createElement('option');
                opt.value = prod.id;
                opt.text = `${prod.nombre} (Stock: ${prod.stock_actual})`;
                // IMPORTANTE: Aquí usamos precio_venta y stock_actual
                opt.dataset.precio = prod.precio_venta;
                opt.dataset.stock = prod.stock_actual;
                opt.dataset.iva = prod.iva_tasa;
                select.appendChild(opt);
            });
        }
    } catch (error) {
        console.error("Error cargando productos:", error);
    }
}

function agregarProductoAVenta() {
    const select = document.getElementById('select_producto');
    const selectedOption = select.options[select.selectedIndex];
    const cantidadInput = document.getElementById('cantidad_venta');
    const cantidad = parseFloat(cantidadInput.value);

    // 1. Validaciones básicas
    if (select.value === "") {
        alert("Debe seleccionar un producto.");
        return;
    }
    if (isNaN(cantidad) || cantidad <= 0) {
        alert("Ingrese una cantidad válida.");
        return;
    }

    // 2. VALIDACIÓN ESTRICTA DE STOCK
    const stockDisponible = parseFloat(selectedOption.dataset.stock);
    
    // Verificamos si ya hay este producto en el carrito para sumar las cantidades
    const itemExistente = detalleVenta.find(item => item.id === select.value);
    const cantidadTotalEnCarrito = itemExistente ? (itemExistente.cantidad + cantidad) : cantidad;

    if (cantidadTotalEnCarrito > stockDisponible) {
        alert(`❌ Stock Insuficiente.\nDisponible: ${stockDisponible}\nEn carrito: ${itemExistente ? itemExistente.cantidad : 0}\nIntenta agregar: ${cantidad}\n\nNo se puede vender más de lo que hay en existencia.`);
        return;
    }

    // 3. Si pasa la validación, lo agregamos al detalle
    const precio = parseFloat(selectedOption.dataset.precio);
    const tasaIva = parseInt(selectedOption.dataset.iva);
    
    if(itemExistente) {
        itemExistente.cantidad += cantidad;
        itemExistente.subtotal = itemExistente.cantidad * precio;
    } else {
        detalleVenta.push({
            id: select.value,
            nombre: selectedOption.text.split(' (Stock:')[0], // Limpiamos el nombre para la tabla
            cantidad: cantidad,
            precio: precio,
            tasa: tasaIva,
            subtotal: cantidad * precio
        });
    }

    // 4. Limpiar input y refrescar tabla
    cantidadInput.value = 1;
    renderizarTablaVenta();
}

function renderizarTablaVenta() {
    const cuerpo = document.getElementById('cuerpoVenta');
    const totalLabel = document.getElementById('total_factura');
    cuerpo.innerHTML = "";
    totalVenta = 0;

    detalleVenta.forEach((item, index) => {
        totalVenta += item.subtotal;
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${item.nombre}</td>
            <td>${item.cantidad}</td>
            <td>${item.precio.toLocaleString('es-PY')}</td>
            <td>${item.subtotal.toLocaleString('es-PY')}</td>
            <td>${item.tasa}%</td>
            <td><button onclick="eliminarItemVenta(${index})" style="background:red; color:white; border:none; cursor:pointer;">X</button></td>
        `;
        cuerpo.appendChild(fila);
    });

    totalLabel.innerText = totalVenta.toLocaleString('es-PY');
}
async function buscarCliente() {
    const doc = document.getElementById('ruc_cliente').value;
    const nombreInput = document.getElementById('nombre_cliente');
    const emailInput = document.getElementById('email_cliente');
    const idHidden = document.getElementById('id_cliente_seleccionado');

    if (doc.length < 5) return; // no hacemos consulta si muy corto

    try {
        const response = await fetch(`../../api/pos/buscar_cliente.php?doc=${doc}`);
        const res = await response.json();

            if (res.status === 'ok') {
                // Cliente encontrado: Completamos los datos
                nombreInput.value = res.datos.razon_social + (res.datos.dv ? "-" + res.datos.dv : "");
                emailInput.value = res.datos.email || "";
                idHidden.value = res.datos.id;
                nombreInput.style.backgroundColor = "#d4edda"; // Verde éxito
            } else if (res.status === 'not_found') {
                // No encontrado: abrimos modal para registrar
                abrirModalCliente(doc);
            }
    } catch (error) {
        console.error("Error al buscar cliente:", error);
    }
}  

// --- envío de venta  --------------------------------------------------
document.getElementById('btnFinalizarVenta').addEventListener('click', async () => {
    if (detalleVenta.length === 0) {
        alert("Agregue productos a la venta.");
        return;
    }

    const factura = document.getElementById('nro_factura').value.trim();
    if (factura === "") {
        alert("Debe ingresar un número de factura.");
        return;
    }

    const datosVenta = {
        nro_factura: factura,
        timbrado: document.getElementById('timbrado_venta').value || null,
        punto_emision: document.getElementById('punto_emision_venta').value || null,
        cliente_id: document.getElementById('id_cliente_seleccionado').value || null,
        ruc_cliente: document.getElementById('ruc_cliente').value,
        items: detalleVenta
    };

    if (!confirm("¿Desea finalizar la venta?")) return;

    try {
        const response = await fetch('../../api/pos/guardar_venta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosVenta)
        });

        const res = await response.json();

        if (res.status === 'ok') {
            alert("✅ " + res.mensaje);
            location.reload(); // Limpiar para la siguiente venta
        } else {
            alert("❌ " + res.mensaje);
        }
    } catch (error) {
        console.error("Error:", error);
    }
});

function limpiarCamposCliente() {
    document.getElementById('nombre_cliente').value = "";
    document.getElementById('email_cliente').value = "";
    document.getElementById('id_cliente_seleccionado').value = "";
    document.getElementById('nombre_cliente').style.backgroundColor = "";
}

// --- funciones del modal para registrar clientes sin salir de ventas  ----
function abrirModalCliente(doc = '') {
    document.getElementById('modalCliente').style.display = 'block';
    const tipoSelect = document.getElementById('m_tipo_doc');
    if (doc) {
        document.getElementById('m_doc').value = doc;
        // si la búsqueda devuelve un número largo, asumimos que es RUC
        if (doc.length >= 8) {
            tipoSelect.value = '11';            
        }
        // calcular DV con posible prefijo m_
        gestionarCambioRUC(doc, 'm_');
    }
    document.getElementById('m_nombre').focus();
    //document.getElementById('cliente_doc').focus();    
}

function cerrarModalCliente() {
    document.getElementById('modalCliente').style.display = 'none';
    document.getElementById('formModalCliente').reset();
}

async function guardarClienteModal() {
    const data = {
        tipo_cont: document.getElementById('m_tipo_cont').value,
        tipo_doc: document.getElementById('m_tipo_doc').value,
        doc: document.getElementById('m_doc').value.trim(),
        dv: document.getElementById('m_dv').value.trim(),
        nombre: document.getElementById('m_nombre').value.trim(),
        email: document.getElementById('m_email').value.trim(),
        tel: document.getElementById('m_tel').value.trim()
    };

    if (!data.doc || !data.nombre || !data.email) {
        alert('Complete los campos obligatorios.');
        return;
    }

    try {
        const resFetch = await fetch('../../api/pos/guardar_cliente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const res = await resFetch.json();
        if (res.status === 'ok') {
            // Rellenar los campos de ventas con el nuevo cliente
            document.getElementById('id_cliente_seleccionado').value = res.id;
            document.getElementById('ruc_cliente').value = data.doc;
            document.getElementById('nombre_cliente').value = data.nombre + (data.dv ? '-' + data.dv : '');
            document.getElementById('email_cliente').value = data.email;
            document.getElementById('nombre_cliente').style.backgroundColor = '#d4edda';
            cerrarModalCliente();
        } else {
            alert('❌ ' + res.mensaje);
        }
    } catch (err) {
        console.error(err);
        alert('Error al guardar cliente');
    }
}

function eliminarItemVenta(index) {
    detalleVenta.splice(index, 1);
    renderizarTablaVenta();
}

// --- funciones del modal para LISTAR clientes ---

function abrirModalListarClientes() {
    document.getElementById('modalListarClientes').style.display = 'block';
    cargarYMostrarClientes(); // Carga los clientes cada vez que se abre
}

function cerrarModalListarClientes() {
    document.getElementById('modalListarClientes').style.display = 'none';
}

async function cargarYMostrarClientes() {
    const tbody = document.getElementById('cuerpoTablaClientes');
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Cargando...</td></tr>';
    try {
        // Corregido: La ruta apuntaba a /api/pos/, pero el archivo está en la misma carpeta que ventas.php
        const response = await fetch('listar_clientes.php');
        const res = await response.json();

        if (res.status === 'ok' && res.datos.length > 0) {
            tbody.innerHTML = ''; // Limpiar
            res.datos.forEach(cliente => {
                const fila = document.createElement('tr');
                // Guardamos el objeto cliente completo como un string JSON en un atributo
                const clienteData = JSON.stringify(cliente).replace(/'/g, "&apos;");
                fila.innerHTML = `
                    <td>${cliente.documento}${cliente.dv ? '-' + cliente.dv : ''}</td>
                    <td>${cliente.razon_social}</td>
                    <td>${cliente.email || ''}</td>
                    <td><button onclick='seleccionarClienteDesdeModal(${clienteData})' style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; width: auto;">Seleccionar</button></td>
                `;
                tbody.appendChild(fila);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No hay clientes registrados o error al cargar.</td></tr>`;
        }
    } catch (error) {
        console.error("Error al cargar clientes:", error);
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Error de conexión.</td></tr>';
    }
}

function seleccionarClienteDesdeModal(cliente) {
    document.getElementById('id_cliente_seleccionado').value = cliente.id;
    document.getElementById('ruc_cliente').value = cliente.documento;
    document.getElementById('nombre_cliente').value = cliente.razon_social + (cliente.dv ? '-' + cliente.dv : '');
    document.getElementById('email_cliente').value = cliente.email || '';
    document.getElementById('nombre_cliente').style.backgroundColor = '#d4edda';
    cerrarModalListarClientes();
}

function filtrarClientes() {
    const input = document.getElementById('buscadorClientes');
    const filter = input.value.toUpperCase();
    const tr = document.getElementById('cuerpoTablaClientes').getElementsByTagName('tr');

    for (let i = 0; i < tr.length; i++) {
        const tdDoc = tr[i].getElementsByTagName('td')[0];
        const tdName = tr[i].getElementsByTagName('td')[1];
        if (tdDoc && tdName && (tdDoc.textContent.toUpperCase().indexOf(filter) > -1 || tdName.textContent.toUpperCase().indexOf(filter) > -1)) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}

// cerrar modales si hacen clic fuera
window.onclick = function(event) {
    if (event.target == document.getElementById('modalCliente')) cerrarModalCliente();
    if (event.target == document.getElementById('modalListarClientes')) cerrarModalListarClientes();
};