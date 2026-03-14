document.addEventListener('DOMContentLoaded', () => {
    // 1. Inicializar reporte al cargar la página
    generarReporte();

    // 2. Configurar botón de consulta
    const btnConsultar = document.getElementById('btnConsultar');
    if (btnConsultar) {
        btnConsultar.addEventListener('click', generarReporte);
    }

    // 3. Configurar botones de exportación
    const btnVentas = document.getElementById('btnExportarVentas');
    if (btnVentas) {
        btnVentas.addEventListener('click', () => exportarMarangatu('ventas'));
    }

    const btnCompras = document.getElementById('btnExportarCompras');
    if (btnCompras) {
        btnCompras.addEventListener('click', () => exportarMarangatu('compras'));
    }
});

async function generarReporte() {
    const mes = document.getElementById('mes').value;
    const anio = document.getElementById('anio').value;

    const response = await fetch(`../../api/pos/reporte_iva.php?mes=${mes}&anio=${anio}`);
    const res = await response.json();

    if (res.status === 'ok') {
        renderTabla('tablaVentas', res.ventas);
        renderTabla('tablaCompras', res.compras);

        // Convertir valores a números seguros (0 si son null/undefined)
        const i10_ventas = parseFloat(res.ventas.i10) || 0;
        const i5_ventas = parseFloat(res.ventas.i5) || 0;
        const i10_compras = parseFloat(res.compras.i10) || 0;
        const i5_compras = parseFloat(res.compras.i5) || 0;

        const diferencia = (i10_ventas + i5_ventas) - (i10_compras + i5_compras);
        const resIva = document.getElementById('resultado_iva');
        
        // Verificar que el resultado es un número válido
        if (isNaN(diferencia)) {
            resIva.innerText = "Error: No se pudo calcular";
            document.getElementById('mensaje_iva').innerText = "Error en los datos";
            return;
        }
        
        resIva.innerText = Math.round(diferencia).toLocaleString('es-PY');
        
        document.getElementById('mensaje_iva').innerText = diferencia > 0 
            ? "Saldo a favor del Fisco (Debes pagar este monto)" 
            : "Saldo a favor del Contribuyente (Queda como crédito para el próximo mes)";
    } else {
        document.getElementById('resultado_iva').innerText = "Error al cargar datos";
    }
}

function renderTabla(id, data) {
    const tabla = document.getElementById(id);
    tabla.innerHTML = `
        <tr><td>Gravada 10%</td><td>${Number(data.g10).toLocaleString('es-PY')}</td></tr>
        <tr><td>IVA 10%</td><td>${Number(data.i10).toLocaleString('es-PY')}</td></tr>
        <tr><td>Gravada 5%</td><td>${Number(data.g5).toLocaleString('es-PY')}</td></tr>
        <tr><td>IVA 5%</td><td>${Number(data.i5).toLocaleString('es-PY')}</td></tr>
        <tr><td>Exentas</td><td>${Number(data.ex).toLocaleString('es-PY')}</td></tr>
        <tr class="total-row"><td>TOTAL</td><td>${Number(data.total).toLocaleString('es-PY')} Gs.</td></tr>
    `;
}

function exportarMarangatu(tipo) {
    // Leemos los valores directamente del DOM al momento del click
    const anio = document.getElementById('anio').value;
    const mes = document.getElementById('mes').value;
    const mesFormateado = mes < 10 ? `0${mes}` : mes;
    
    const url = `exportar_marangatu.php?tipo=${tipo}&anio=${anio}&mes=${mesFormateado}`;   
    window.open(url, '_blank');
}
