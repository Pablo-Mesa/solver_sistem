document.addEventListener('DOMContentLoaded', () => {
    cargarInventario();
});

async function cargarInventario() {
    const cuerpo = document.getElementById('cuerpoInventario');
    
    try {
        // La ruta desde modulos/pos/pos_inventario.php hacia la API
        const response = await fetch('../../api/pos/listar_productos.php');
        const res = await response.json();

        if (res.status === 'ok') {
            cuerpo.innerHTML = ""; 
            
            res.datos.forEach(prod => {
                const fila = document.createElement('tr');
                
                // Si el stock es 5 o menos, resaltamos en rojo
                const claseStock = parseFloat(prod.stock_actual) <= 5 ? 'stock-bajo' : '';

                fila.innerHTML = `
                    <td>${prod.id}</td>
                    <td><strong>${prod.nombre}</strong></td>
                    <td>${prod.categoria || 'General'}</td>
                    <td>${prod.iva_tasa}%</td>
                    <td class="${claseStock}">${prod.stock_actual}</td>
                    <td>${Number(prod.precio_venta || 0).toLocaleString('es-PY')} Gs.</td>
                `;
                cuerpo.appendChild(fila);
            });
        } else {
            console.error("Error en la respuesta:", res.mensaje);
        }
    } catch (error) {
        console.error("Error de conexión:", error);
    }
}