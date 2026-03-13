<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('ID de venta no válido.');
}

$empresa = null;
$venta   = null;
$detalles = [];

try {
    // Datos de la empresa
    $stmtEmp = $pdo->prepare("SELECT razon_social, ruc, dv, direccion, telefono, email,
                                     timbrado_vigente, fecha_desde_timbrado, fecha_hasta_timbrado,
                                     punto_emision, sucursal
                              FROM empresa WHERE id = 1 LIMIT 1");
    $stmtEmp->execute();
    $empresa = $stmtEmp->fetch(PDO::FETCH_ASSOC);

    // Cabecera de la venta + cliente
    $sqlVenta = "SELECT v.id, v.nro_factura, v.timbrado, v.punto_emision, v.fecha_hora,
                        v.gravada_10, v.iva_10, v.gravada_5, v.iva_5, v.exenta, v.total_venta,
                        c.documento, c.dv AS cliente_dv, c.razon_social AS cliente_nombre,
                        c.direccion AS cliente_direccion, c.email AS cliente_email
                 FROM pos_ventas_cabecera v
                 LEFT JOIN pos_clientes c ON v.cliente_id = c.id
                 WHERE v.id = ? LIMIT 1";
    $stmtVenta = $pdo->prepare($sqlVenta);
    $stmtVenta->execute([$id]);
    $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        die('Venta no encontrada.');
    }

    // Detalle de la venta
    $sqlDet = "SELECT d.cantidad, d.precio_unitario_venta, d.subtotal, p.nombre
               FROM pos_ventas_detalle d
               JOIN pos_productos p ON d.producto_id = p.id
               WHERE d.venta_id = ?";
    $stmtDet = $pdo->prepare($sqlDet);
    $stmtDet->execute([$id]);
    $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error al cargar la factura.');
}

// Valores de timbrado y punto de emisión a mostrar
$timbradoMostrar = $venta['timbrado'] ?: ($empresa['timbrado_vigente'] ?? '');
$puntoMostrar = $venta['punto_emision'] ?: ($empresa['punto_emision'] ?? '001');
$sucursalMostrar = $empresa['sucursal'] ?? '001';

function formato_num($n) {
    return number_format((float)$n, 0, ',', '.');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <title>Factura Venta #<?php echo htmlspecialchars($venta['nro_factura']); ?></title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .factura-container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .encabezado { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .empresa-datos { max-width: 60%; }
        .empresa-datos h2 { margin: 0 0 8px 0; font-size: 1.4em; }
        .empresa-datos p { margin: 2px 0; font-size: 0.9em; color: #444; }
        .factura-info { text-align: right; font-size: 0.9em; }
        .factura-info h3 { margin: 0 0 8px 0; font-size: 1.1em; }
        .bloque { margin-bottom: 15px; }
        .bloque h4 { margin: 0 0 6px 0; font-size: 1em; border-bottom: 1px solid #eee; padding-bottom: 4px; color: #555; }
        .bloque p { margin: 2px 0; font-size: 0.9em; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 0.9em; }
        th, td { padding: 6px 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
        .totales { margin-top: 10px; width: 100%; }
        .totales td { border: none; padding: 4px 0; }
        .totales td.label { text-align: right; padding-right: 10px; }
        .totales td.valor { text-align: right; font-weight: bold; }
        .acciones { text-align: right; margin-top: 15px; }
        .btn-imprimir { padding: 8px 16px; background: #28a745; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
        .nota { margin-top: 10px; font-size: 0.8em; color: #777; }
        @media print {
            body { background: #fff; padding: 0; }
            .factura-container { box-shadow: none; border-radius: 0; margin: 0; width: 100%; }
            .acciones { display: none; }
        }
    </style>
</head>
<body>

<div class="factura-container">
    <div class="encabezado">
        <div class="empresa-datos">
            <h2><?php echo htmlspecialchars($empresa['razon_social'] ?? ''); ?></h2>
            <p>RUC: <?php
                $ruc = $empresa['ruc'] ?? '';
                $dvEmp = $empresa['dv'] ?? '';
                echo htmlspecialchars($ruc . ($dvEmp ? '-' . $dvEmp : ''));
            ?></p>
            <?php if (!empty($empresa['direccion'])): ?>
                <p>Dirección: <?php echo htmlspecialchars($empresa['direccion']); ?></p>
            <?php endif; ?>
            <?php if (!empty($empresa['telefono'])): ?>
                <p>Teléfono: <?php echo htmlspecialchars($empresa['telefono']); ?></p>
            <?php endif; ?>
            <?php if (!empty($empresa['email'])): ?>
                <p>Email: <?php echo htmlspecialchars($empresa['email']); ?></p>
            <?php endif; ?>
        </div>
        <div class="factura-info">
            <h3>FACTURA</h3>
            <p><strong>N°:</strong> <?php echo htmlspecialchars($venta['nro_factura']); ?></p>
            <?php if ($timbradoMostrar): ?>
                <p><strong>Timbrado:</strong> <?php echo htmlspecialchars($timbradoMostrar); ?></p>
            <?php endif; ?>
            <p><strong>Punto / Sucursal:</strong> <?php echo htmlspecialchars($puntoMostrar . '-' . $sucursalMostrar); ?></p>
            <p><strong>Fecha / Hora:</strong> <?php echo htmlspecialchars($venta['fecha_hora']); ?></p>
            <?php if (!empty($empresa['fecha_desde_timbrado']) || !empty($empresa['fecha_hasta_timbrado'])): ?>
                <p><strong>Vigencia:</strong>
                    <?php
                    $desde = $empresa['fecha_desde_timbrado'] ?? '';
                    $hasta = $empresa['fecha_hasta_timbrado'] ?? '';
                    echo htmlspecialchars(($desde ?: '-') . ' al ' . ($hasta ?: '-'));
                    ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bloque">
        <h4>Datos del Cliente</h4>
        <p><strong>Nombre / Razón Social:</strong>
            <?php echo htmlspecialchars($venta['cliente_nombre'] ?: 'Cliente Ocasional'); ?>
        </p>
        <p><strong>Documento:</strong>
            <?php
            $doc = $venta['documento'] ?? '';
            $dvCli = $venta['cliente_dv'] ?? '';
            echo htmlspecialchars($doc . ($dvCli ? '-' . $dvCli : ''));
            ?>
        </p>
        <?php if (!empty($venta['cliente_direccion'])): ?>
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($venta['cliente_direccion']); ?></p>
        <?php endif; ?>
        <?php if (!empty($venta['cliente_email'])): ?>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($venta['cliente_email']); ?></p>
        <?php endif; ?>
    </div>

    <div class="bloque">
        <h4>Detalle de la Venta</h4>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="width:80px; text-align:center;">Cantidad</th>
                    <th style="width:120px; text-align:right;">Precio Unit.</th>
                    <th style="width:120px; text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($detalles as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                    <td style="text-align:center;"><?php echo htmlspecialchars($item['cantidad']); ?></td>
                    <td style="text-align:right;"><?php echo formato_num($item['precio_unitario_venta']); ?> Gs.</td>
                    <td style="text-align:right;"><?php echo formato_num($item['subtotal']); ?> Gs.</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <table class="totales">
        <tr>
            <td class="label">Gravada 10%:</td>
            <td class="valor"><?php echo formato_num($venta['gravada_10']); ?> Gs.</td>
        </tr>
        <tr>
            <td class="label">IVA 10%:</td>
            <td class="valor"><?php echo formato_num($venta['iva_10']); ?> Gs.</td>
        </tr>
        <tr>
            <td class="label">Gravada 5%:</td>
            <td class="valor"><?php echo formato_num($venta['gravada_5']); ?> Gs.</td>
        </tr>
        <tr>
            <td class="label">IVA 5%:</td>
            <td class="valor"><?php echo formato_num($venta['iva_5']); ?> Gs.</td>
        </tr>
        <tr>
            <td class="label">Exentas:</td>
            <td class="valor"><?php echo formato_num($venta['exenta']); ?> Gs.</td>
        </tr>
        <tr>
            <td class="label">TOTAL FACTURA:</td>
            <td class="valor"><?php echo formato_num($venta['total_venta']); ?> Gs.</td>
        </tr>
    </table>

    <p class="nota">Este documento es generado por el sistema Solver. La versión electrónica (SIFEN) se implementará conforme a la normativa vigente de la SET.</p>

    <div class="acciones">
        <button class="btn-imprimir" onclick="window.print()">Imprimir</button>
    </div>
</div>

</body>
</html>

