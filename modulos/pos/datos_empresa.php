<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$empresa = [
    'razon_social' => '',
    'ruc' => '',
    'dv' => '',
    'direccion' => '',
    'telefono' => '',
    'email' => '',
    'timbrado_vigente' => '',
    'fecha_desde_timbrado' => '',
    'fecha_hasta_timbrado' => '',
    'punto_emision' => '001',
    'sucursal' => '001',
    'actividad_economica' => ''
];

try {
    $stmt = $pdo->prepare("SELECT razon_social, ruc, dv, direccion, telefono, email,
                                  timbrado_vigente, fecha_desde_timbrado, fecha_hasta_timbrado,
                                  punto_emision, sucursal, actividad_economica
                          FROM empresa WHERE id = 1 LIMIT 1");
    $stmt->execute();
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $empresa = array_merge($empresa, $row);
        foreach (['fecha_desde_timbrado', 'fecha_hasta_timbrado'] as $f) {
            if (!empty($empresa[$f])) {
                $empresa[$f] = substr($empresa[$f], 0, 10);
            }
        }
    }
} catch (PDOException $e) {
    // Tabla no existe o sin fila: se muestran valores por defecto
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <title>Solver | Datos del negocio</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/styles-for-tool-kit-v001.css">
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 20px; }
        .contenedor-empresa { max-width: 900px; width: 95%; margin: 0 auto; }
        .seccion { background: #fff; padding: 16px; border-radius: 8px; margin-bottom: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .seccion h3 { margin: 0 0 12px 0; font-size: 1em; color: #555; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .grid-empresa { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
        .form-group { margin-bottom: 0; }
        .form-group label { display: block; margin-bottom: 4px; font-weight: 600; font-size: 0.9em; color: #333; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-guardar { background: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 1em; font-weight: 600; }
        .btn-guardar:hover { background: #218838; }
        .btn-guardar:disabled { background: #ccc; cursor: not-allowed; }
        #mensajeFeedback { margin-top: 12px; padding: 10px; border-radius: 4px; display: none; }
    </style>
</head>
<body>

<div class="contenedor-empresa">
    <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Datos del negocio</h2>
        <a href="../../dashboard.php" style="text-decoration: none; color: #666;">← Volver al Menú</a>
    </div>
    <p style="color: #666; margin-bottom: 20px;">Estos datos se usan en la factura impresa y para la facturación electrónica (SIFEN).</p>

    <form id="formEmpresa">
        <div class="seccion">
            <h3>Datos fiscales</h3>
            <div class="grid-empresa">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="razon_social">Razón social *</label>
                    <input type="text" id="razon_social" name="razon_social" value="<?php echo htmlspecialchars($empresa['razon_social']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="ruc">RUC *</label>
                    <input type="text" id="ruc" name="ruc" value="<?php echo htmlspecialchars($empresa['ruc']); ?>" required maxlength="15">
                </div>
                <div class="form-group">
                    <label for="dv">DV</label>
                    <input type="text" id="dv" name="dv" value="<?php echo htmlspecialchars($empresa['dv']); ?>" maxlength="1" placeholder="Dígito verificador">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($empresa['direccion']); ?>" placeholder="Ej: Av. Ejemplo 123, Ciudad">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($empresa['telefono']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($empresa['email']); ?>">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="actividad_economica">Actividad económica</label>
                    <input type="text" id="actividad_economica" name="actividad_economica" value="<?php echo htmlspecialchars($empresa['actividad_economica']); ?>" placeholder="Opcional">
                </div>
            </div>
        </div>

        <div class="seccion">
            <h3>Timbrado (facturación SET)</h3>
            <div class="grid-empresa">
                <div class="form-group">
                    <label for="timbrado_vigente">Timbrado vigente</label>
                    <input type="text" id="timbrado_vigente" name="timbrado_vigente" value="<?php echo htmlspecialchars($empresa['timbrado_vigente']); ?>" maxlength="8" placeholder="8 dígitos">
                </div>
                <div class="form-group">
                    <label for="fecha_desde_timbrado">Válido desde</label>
                    <input type="date" id="fecha_desde_timbrado" name="fecha_desde_timbrado" value="<?php echo htmlspecialchars($empresa['fecha_desde_timbrado']); ?>">
                </div>
                <div class="form-group">
                    <label for="fecha_hasta_timbrado">Válido hasta</label>
                    <input type="date" id="fecha_hasta_timbrado" name="fecha_hasta_timbrado" value="<?php echo htmlspecialchars($empresa['fecha_hasta_timbrado']); ?>">
                </div>
            </div>
        </div>

        <div class="seccion">
            <h3>Punto de emisión (formato factura 001-001-0000001)</h3>
            <div class="grid-empresa">
                <div class="form-group">
                    <label for="punto_emision">Punto de emisión</label>
                    <input type="text" id="punto_emision" name="punto_emision" value="<?php echo htmlspecialchars($empresa['punto_emision']); ?>" maxlength="5" placeholder="001">
                </div>
                <div class="form-group">
                    <label for="sucursal">Sucursal</label>
                    <input type="text" id="sucursal" name="sucursal" value="<?php echo htmlspecialchars($empresa['sucursal']); ?>" maxlength="5" placeholder="001">
                </div>
            </div>
        </div>

        <div class="seccion">
            <button type="submit" id="btnGuardar" class="btn-guardar">Guardar cambios</button>
            <div id="mensajeFeedback" class="mensaje"></div>
        </div>
    </form>
</div>

<script src="../../js/tool-kit-v002.js"></script>
<script>
document.getElementById('formEmpresa').addEventListener('submit', async function(e) {
    e.preventDefault();
    var btn = document.getElementById('btnGuardar');
    var msg = document.getElementById('mensajeFeedback');
    btn.disabled = true;
    msg.style.display = 'none';

    var formData = new FormData(this);
    var obj = {};
    formData.forEach(function(v, k) { obj[k] = v; });

    try {
        var res = await fetch('../../api/pos/guardar_empresa.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(obj)
        });
        var data = await res.json();

        msg.textContent = data.mensaje;
        msg.style.display = 'block';
        if (data.status === 'ok') {
            msg.className = 'mensaje exito';
            if (typeof showToast === 'function') showToast(data.mensaje, '#28a745');
        } else {
            msg.className = 'mensaje error';
        }
    } catch (err) {
        msg.textContent = 'Error de conexión.';
        msg.className = 'mensaje error';
        msg.style.display = 'block';
    }
    btn.disabled = false;
});
</script>
</body>
</html>
