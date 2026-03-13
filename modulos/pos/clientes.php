<?php 
    require_once '../../includes/auth.php'; 
?>

<div class="card" style="max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px;">
    <h3>👤 Registro de Cliente SIFEN</h3>
    <form id="formCliente">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            
            <div>
                <label>Tipo Documento:</label>
                <select id="cliente_tipo_doc" class="form-control" onchange="gestionarCambioRUC(document.getElementById('cliente_doc').value)">
                    <option value="1">Cédula Paraguaya</option>
                    <option value="11">RUC</option>
                    <option value="3">Pasaporte</option>
                </select>
            </div>

            <div>
                <label>Nro. Documento:</label>
                <div style="display: flex; gap: 5px;">
                    <input type="text" id="cliente_doc" placeholder="Ej: 4444440" oninput="gestionarCambioRUC(this.value)" required style="flex: 3;">
                    <input type="text" id="cliente_dv" placeholder="DV" readonly style="flex: 1; background: #eee; text-align: center;">
                </div>
            </div>

            <div style="grid-column: span 2;">
                <label>Razón Social / Nombre:</label>
                <input type="text" id="cliente_nombre" placeholder="Nombre completo o Empresa" required style="width: 100%;">
            </div>

            <div style="grid-column: span 2;">
                <label>Correo Electrónico (Para envío de XML):</label>
                <input type="email" id="cliente_email" placeholder="cliente@correo.com" required style="width: 100%;">
            </div>

            <div>
                <label>Tipo Contribuyente:</label>
                <select id="cliente_tipo_cont">
                    <option value="1">Persona Física</option>
                    <option value="2">Persona Jurídica</option>
                </select>
            </div>

            <div>
                <label>Teléfono:</label>
                <input type="text" id="cliente_tel" placeholder="09xx ...">
            </div>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <button type="button" onclick="guardarCliente()" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                💾 Guardar Cliente
            </button>
        </div>
    </form>
</div>

<script src="../../js/pos_ruc_util.js"></script>

<script>
// Envía los datos del formulario al endpoint correspondiente
async function guardarCliente() {
    const data = {
        tipo_cont: document.getElementById('cliente_tipo_cont').value,
        tipo_doc: document.getElementById('cliente_tipo_doc').value,
        doc: document.getElementById('cliente_doc').value.trim(),
        dv: document.getElementById('cliente_dv').value.trim(),
        nombre: document.getElementById('cliente_nombre').value.trim(),
        email: document.getElementById('cliente_email').value.trim(),
        tel: document.getElementById('cliente_tel').value.trim()
    };
    
    // Validaciones simples
    if (!data.doc || !data.nombre || !data.email) {
        alert('Complete los campos obligatorios (documento, nombre y correo).');
        return;
    }
    
    try {
        const res = await fetch('../../api/pos/guardar_cliente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.status === 'ok') {
            alert(result.mensaje);
            // opcional: limpiar el formulario
            document.getElementById('formCliente').reset();
        } else {
            alert('❌ ' + result.mensaje);
        }
    } catch (err) {
        console.error(err);
        alert('Error al guardar cliente');
    }
}
</script>