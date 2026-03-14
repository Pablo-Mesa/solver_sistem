function calcularDV(ruc) {
    let p_basemax = 11;
    let v_total = 0;
    let v_resto = 0;
    let v_digit = 0;
    let k = 2;
    
    // Invertir el RUC y sumar productos
    for (let i = ruc.length - 1; i >= 0; i--) {
        if (k > p_basemax) k = 2;
        v_total += parseInt(ruc[i]) * k;
        k++;
    }

    v_resto = v_total % 11;
    v_digit = v_resto > 1 ? 11 - v_resto : 0;

    return v_digit;
}

// Escuchador para el input de documento. Si se trabaja desde un modal con
// ids prefijados (e.g. m_doc, m_dv, m_tipo_doc), pasar segundo parámetro
// "m_" para que se calcule el dígito correctamente.
function gestionarCambioRUC(valor, prefix = '') {
    const dvInput = document.getElementById(prefix + 'cliente_dv') || document.getElementById(prefix + 'dv');
    const tipoElem = document.getElementById(prefix + 'cliente_tipo_doc') || document.getElementById(prefix + 'tipo_doc');
    const tipoDoc = tipoElem ? tipoElem.value : '';

    console.log(tipoDoc);

    // Solo calculamos DV si el tipo de documento es RUC (11)
    if (tipoDoc == "1" && valor.length > 4) {
        if (dvInput) dvInput.value = calcularDV(valor);
    } else {
        if (dvInput) dvInput.value = "";
    }
}
