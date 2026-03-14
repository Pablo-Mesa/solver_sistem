// --- VARIABLES GLOBALES ---
let detalleCompra = [];
let idProveedorSeleccionado = null; 

document.addEventListener('DOMContentLoaded', () => {
    // Referencias a elementos del DOM
    const inputRuc = document.getElementById('ruc_busqueda');
    const inputRazonSocial = document.getElementById('razon_social');
    const modalProv = document.getElementById('modalProveedor');
    const formNuevoProv = document.getElementById('formRapidoProveedor');
    const formNuevoProd = document.getElementById('formRapidoProducto');
    
    // --- PARTE A: BUSCADOR DE PROVEEDOR ---
    inputRuc.addEventListener('blur', async () => {
        const ruc = inputRuc.value.trim();
        if (ruc.length < 5) return;

        try {
            const response = await fetch(`../../api/pos/buscar_proveedor.php?ruc=${ruc}`);
            const data = await response.json();

            if (data.status === 'ok') {
                // Si existe: Llenamos los datos y marcamos el ID
                inputRazonSocial.value = data.datos.razon_social;
                idProveedorSeleccionado = data.datos.id;
                inputRazonSocial.style.backgroundColor = "#e8f5e9"; 
            } else if (data.status === 'not_found') {
                // Si NO existe: Preguntamos si quiere registrarlo
                if(confirm(`El RUC ${ruc} no existe. ¿Desea registrarlo ahora?`)) {
                    abrirModalProveedor(ruc);
                } else {
                    inputRuc.value = "";
                    inputRazonSocial.value = "";
                }
            }
        } catch (error) {
            console.error("Error en búsqueda:", error);
        }
    });

    // --- PARTE B: REGISTRO DE NUEVO PROVEEDOR (MODAL) ---
    formNuevoProv.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Creamos el objeto con los datos del modal
        const datos = new URLSearchParams();
        datos.append('ruc', document.getElementById('nuevo_ruc').value);
        datos.append('dv', document.getElementById('nuevo_dv').value);
        datos.append('razon_social', document.getElementById('nueva_razon').value);
        datos.append('tipo', document.getElementById('nuevo_tipo').value);

        try {
            const response = await fetch('../../api/pos/crear_proveedor.php', {
                method: 'POST',
                body: datos
            });
            const res = await response.json();

            if(res.status === 'ok') {
                // Si se creó con éxito, lo asignamos de una vez a la compra actual
                idProveedorSeleccionado = res.id;
                inputRuc.value = document.getElementById('nuevo_ruc').value;
                inputRazonSocial.value = document.getElementById('nueva_razon').value;
                inputRazonSocial.style.backgroundColor = "#e8f5e9";
                
                cerrarModal();
                showToast('Proveedor guardado y seleccionado.', '#28a745');

                // Foco al siguiente campo: Timbrado
                document.getElementById('timbrado').focus();
            } else {
                showToast("Error: " + res.mensaje, '#dc3545');
            }
        } catch (error) {
            showToast("Error de conexión al guardar proveedor.", '#dc3545');
        }
    });

    // --- PARTE B.2: REGISTRO DE NUEVO PRODUCTO (MODAL) ---
    formNuevoProd.addEventListener('submit', async (e) => {
        e.preventDefault();

        const datos = new URLSearchParams();
        datos.append('nombre', document.getElementById('nuevo_prod_nombre').value);
        datos.append('categoria_id', document.getElementById('nuevo_prod_categoria').value);
        datos.append('precio_costo', document.getElementById('nuevo_prod_costo').value);
        datos.append('precio_venta', document.getElementById('nuevo_prod_venta').value);
        datos.append('iva_tasa', document.getElementById('nuevo_prod_iva').value);
        datos.append('codigo_barra', document.getElementById('nuevo_prod_codigo_barra').value);
        datos.append('descripcion', document.getElementById('nuevo_prod_descripcion').value);
        datos.append('descripcion_web', document.getElementById('nuevo_prod_descripcion_web').value);

        try {
            const response = await fetch('../../api/pos/crear_producto.php', {
                method: 'POST',
                body: datos
            });
            const res = await response.json();

            if (res.status === 'ok') {
                cerrarModalProducto();
                showToast('Producto creado y seleccionado.', '#28a745');

                const nuevoProducto = res.datos;
                const selectProductos = document.getElementById('select_producto');

                // Añadir el nuevo producto al selector
                const opt = document.createElement('option');
                opt.value = nuevoProducto.id;
                opt.text = `${nuevoProducto.nombre} (Nueva)`;
                opt.dataset.iva = nuevoProducto.iva_tasa;
                opt.dataset.costo = nuevoProducto.precio_costo;
                selectProductos.appendChild(opt);

                // Seleccionarlo y pre-rellenar campos
                selectProductos.value = nuevoProducto.id;
                document.getElementById('precio_unitario').value = nuevoProducto.precio_costo;
                document.getElementById('cantidad').focus(); // Foco en cantidad para continuar
            } else {
                showToast("Error: " + res.mensaje, '#dc3545');
            }
        } catch (error) {
            showToast("Error de conexión al guardar el producto.", '#dc3545');
        }
    });

    // Añadimos el manejador del formulario principal para que envíe correctamente
    const formCompra = document.getElementById('formNuevaCompra');
    formCompra.addEventListener('submit', function(e) {
        e.preventDefault();
        guardarFacturaCompleta();
    });

    // Inicializamos el campo de fecha con la fecha actual
    const hoy = new Date();
    const año = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const día = String(hoy.getDate()).padStart(2, '0');
    document.getElementById('fecha_emision').value = `${año}-${mes}-${día}`;


    // --- PARTE C: LÓGICA DEL CARRITO (LO QUE YA TENÍAMOS) ---
    const btnAgregar = document.getElementById('btnAgregar');
    btnAgregar.addEventListener('click', () => {
        // ... (Aquí va la lógica de agregar productos que vimos antes)
            agregarProductoAlDetalle();
    });

    async function cargarProductos() {
        const select = document.getElementById('select_producto');
        
        // Limpiamos el selector (dejamos solo la opción por defecto)
        select.innerHTML = '<option value="">Seleccione un producto...</option>';

        try {
            const response = await fetch('../../api/pos/listar_productos.php');
            const res = await response.json();

            if (res.status === 'ok') {
                res.datos.forEach(prod => {
                    const opt = document.createElement('option');
                    opt.value = prod.id;
                    // Mostramos Nombre y Categoría para que sea más fácil de identificar
                    opt.text = `${prod.nombre} (${prod.categoria})`;
                    
                    // DATA-ATTRIBUTES: Aquí guardamos la "inteligencia" del producto
                    opt.dataset.iva = prod.iva_tasa; 
                    opt.dataset.costo = prod.precio_costo;
                    
                    select.appendChild(opt);
                });
                console.log("Productos cargados exitosamente.");
            } else {
                console.error("Error de API:", res.mensaje);
            }
        } catch (error) {
            console.error("Error de conexión al listar productos:", error);
        }

    }

    // No olvides llamarla al final del DOMContentLoaded
    cargarProductos();

    // Inicializamos el foco en el campo RUC para que el usuario pueda empezar a escribir inmediatamente
    document.getElementById('ruc_busqueda').focus();

    // Cuando el usuario cambia el producto, precargamos su costo
    document.getElementById('select_producto').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const precioInput = document.getElementById('precio_unitario');
        
        if (selectedOption.value !== "") {
            // Obtenemos el costo del data-attribute y lo ponemos en el input
            precioInput.value = selectedOption.dataset.costo;
        } else {
            precioInput.value = "";
        }
    });
    

});

// --- FUNCIONES DE APOYO (Fuera del DOMContentLoaded para ser globales) ---

function renderizarTabla() {
    const tablaDetalle = document.getElementById('tablaDetalle').getElementsByTagName('tbody')[0];
    tablaDetalle.innerHTML = ""; // Limpiamos la tabla para redibujarla
    
    let totalG10 = 0, totalG5 = 0, totalEx = 0;
    let totalFinal = 0;

    detalleCompra.forEach((item, index) => {
        let row = tablaDetalle.insertRow();
        
        // Acumulamos montos según el IVA del Paraguay
        if (item.tasa === 10) totalG10 += item.subtotal;
        else if (item.tasa === 5) totalG5 += item.subtotal;
        else totalEx += item.subtotal;

        totalFinal += item.subtotal;

        row.innerHTML = `
            <td>${item.nombre}</td>
            <td>${item.cantidad}</td>
            <td><span class="badge-iva">${item.tasa}%</span></td>
            <td>${item.subtotal.toLocaleString('es-PY')}</td>
            <td><button type="button" onclick="eliminarItem(${index})" style="background:#dc3545; color:white; border:none; border-radius:3px; cursor:pointer;">X</button></td>
        `;
    });

    // Actualizamos las etiquetas de la interfaz con los cálculos de la SET
    // Dividimos por 1.1 y 1.05 para obtener la "Base Gravada" que pide la RG90
    document.getElementById('lbl_g10').innerText = Math.round(totalG10 / 1.1).toLocaleString('es-PY');
    document.getElementById('lbl_g5').innerText = Math.round(totalG5 / 1.05).toLocaleString('es-PY');
    document.getElementById('lbl_ex').innerText = totalEx.toLocaleString('es-PY');
    document.getElementById('lbl_total').innerText = totalFinal.toLocaleString('es-PY');
}

function agregarProductoAlDetalle() {
    const select = document.getElementById('select_producto');
    const cantInput = document.getElementById('cantidad');
    const precioInput = document.getElementById('precio_unitario');

    const id = select.value;
    const nombre = select.options[select.selectedIndex].text;
    const cantidad = parseFloat(cantInput.value);
    const precio = parseFloat(precioInput.value);
    
    // Extraemos la tasa IVA del atributo "data-iva" que pusimos en el HTML
    const tasa = parseInt(select.options[select.selectedIndex].getAttribute('data-iva'));

    // Validaciones básicas de seguridad
    if (!id) { showToast('Seleccione un producto.', '#dc3545'); return; }
    if (isNaN(cantidad) || cantidad <= 0) { showToast('Cantidad inválida.', '#dc3545'); return; }
    if (isNaN(precio) || precio < 0) { showToast('Precio inválido.', '#dc3545'); return; }

    // Creamos el objeto del ítem
    const nuevoItem = {
        id: id,
        nombre: nombre,
        cantidad: cantidad,
        precio: precio,
        tasa: tasa,
        subtotal: cantidad * precio
    };

    // Lo agregamos al array global
    detalleCompra.push(nuevoItem);

    // Redibujamos la tabla para que se vea el cambio
    renderizarTabla();

    // Limpiamos los campos para el siguiente producto
    cantInput.value = 1;
    precioInput.value = "";
    select.value = "";
    select.focus(); // Ponemos el foco otra vez en el buscador
}

// Usando camelCase para corregir la sintaxis
function abrirModalProveedor(rucPrecargado = '') {
    document.getElementById('modalProveedor').style.display = 'block';
    document.getElementById('nuevo_ruc').value = rucPrecargado || '';
    document.getElementById('nuevo_dv').value = '';
    document.getElementById('nueva_razon').value = '';
    
    // Lógica de foco inteligente
    if (rucPrecargado && rucPrecargado.trim() !== '') {
        // Si viene con RUC precargado, foco en DV para continuar el flujo
        document.getElementById('nuevo_dv').focus();
    } else {
        // Si abre sin RUC, foco en RUC para empezar desde ahí
        document.getElementById('nuevo_ruc').focus();
    }
}

function abrirModalProducto() {
    document.getElementById('modalProducto').style.display = 'block';
    document.getElementById('nuevo_prod_nombre').focus();
    cargarCategoriasParaModal();
}

function cerrarModalProducto() {
    document.getElementById('modalProducto').style.display = 'none';
    document.getElementById('formRapidoProducto').reset();
}

async function cargarCategoriasParaModal() {
    const select = document.getElementById('nuevo_prod_categoria');
    select.innerHTML = '<option value="">Cargando...</option>';
    try {
        const response = await fetch('../../api/pos/listar_categorias.php');
        const res = await response.json();
        if (res.status === 'ok') {
            select.innerHTML = '<option value="">Seleccione una categoría</option>';
            res.datos.forEach(cat => {
                select.innerHTML += `<option value="${cat.id}">${cat.nombre}</option>`;
            });
        }
    } catch (error) {
        select.innerHTML = '<option value="">Error al cargar</option>';
    }
}

function cerrarModal() {
    document.getElementById('modalProveedor').style.display = 'none';
    document.getElementById('formRapidoProveedor').reset();
}

function eliminarItem(index) {
    if (confirm("¿Quitar este producto de la factura?")) {
        detalleCompra.splice(index, 1);
        renderizarTabla(); // <--- Aquí es donde se recalculan los totales de la SET
    }
}

async function guardarFacturaCompleta() {
    // 1. Validaciones previas
    if (!idProveedorSeleccionado) {
        showToast('Error: Debe seleccionar un proveedor válido.', '#1199ff');
        return;
    }
    if (detalleCompra.length === 0) {
        showToast('Error: No hay productos en la factura.', '#1199ff');
        return;
    }

    // 2. Recolectamos los datos de la cabecera
    const datosEnvio = {
        proveedor_id: idProveedorSeleccionado,
        timbrado: document.getElementById('timbrado').value,
        nro_factura: document.getElementById('nro_factura').value,
        fecha_emision: document.getElementById('fecha_emision').value,
        items: detalleCompra // El array con todos los productos
    };

    // 3. Enviamos a la API mediante POST
    try {
        const response = await fetch('../../api/pos/guardar_compra.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datosEnvio)
        });

        const res = await response.json();

        if (res.status === 'ok') {
            showToast("✅ " + res.mensaje, '#1199ff');
            // Limpiamos todo para una nueva carga
            location.reload(); 
        } else {
            showToast("❌ Error: " + res.mensaje, '#1199ff');
        }
    } catch (error) {
        console.error("Error en el envío:", error);
        alert("Ocurrió un error de conexión al intentar guardar.");
    }
}