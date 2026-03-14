<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$tipo = $_GET['tipo'] ?? 'ventas'; // 'ventas' o 'compras'
$mes  = $_GET['mes'] ?? date('m');
$anio = $_GET['anio'] ?? date('Y');

// Nombre del archivo para descarga
$filename = "Marangatu_{$tipo}_{$anio}_{$mes}.csv";

// Cabeceras para forzar descarga CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Abrir salida
$output = fopen('php://output', 'w');

// BOM para que Excel reconozca caracteres especiales (tildes, ñ)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

if ($tipo === 'ventas') {
    // --- ENCABEZADOS LIBRO VENTAS (Formato Estándar) ---
    fputcsv($output, [
        'RUC/Documento', 'Razon Social', 'Tipo Comprobante', 'Fecha Emision', 
        'Timbrado', 'Numero Comprobante', 
        'Gravada 10%', 'IVA 10%', 'Gravada 5%', 'IVA 5%', 'Exenta', 'Total', 
        'Condicion (1=Contado, 2=Credito)'
    ], ';');

    // Consulta Ventas
    // NOTA: Usamos IFNULL para evitar vacíos en campos clave
    $sql = "SELECT 
                c.documento, 
                c.dv, 
                c.razon_social, 
                v.fecha_hora, 
                v.timbrado, 
                v.nro_factura, 
                v.gravada_10, v.iva_10, 
                v.gravada_5, v.iva_5, 
                v.exenta, 
                v.total_venta
            FROM pos_ventas_cabecera v
            LEFT JOIN pos_clientes c ON v.cliente_id = c.id
            WHERE MONTH(v.fecha_hora) = ? AND YEAR(v.fecha_hora) = ? AND v.estado = 1
            ORDER BY v.fecha_hora ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mes, $anio]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Formatear RUC (ej: 80001234-5)
        $ruc_completo = $row['documento'] . ($row['dv'] ? '-'.$row['dv'] : '');
        
        // Formatear Fecha (dd/mm/aaaa)
        $fecha = date('d/m/Y', strtotime($row['fecha_hora']));

        // Tipo Comprobante: 109 = Factura (Valor estándar SET)
        $tipo_comp = 109; 

        // Condición: Por defecto 1 (Contado) si no tienes el campo en BD aún
        $condicion = 1; 

        fputcsv($output, [
            $ruc_completo,
            $row['razon_social'],
            $tipo_comp,
            $fecha,
            $row['timbrado'] ?? '0', // Timbrado es obligatorio
            $row['nro_factura'],
            $row['gravada_10'],
            $row['iva_10'],
            $row['gravada_5'],
            $row['iva_5'],
            $row['exenta'],
            $row['total_venta'],
            $condicion
        ], ';');
    }

} else {
    // --- ENCABEZADOS LIBRO COMPRAS ---
    fputcsv($output, [
        'RUC Proveedor', 'Razon Social', 'Tipo Comprobante', 'Fecha Emision', 
        'Timbrado', 'Numero Comprobante', 
        'Gravada 10%', 'IVA 10%', 'Gravada 5%', 'IVA 5%', 'Exenta', 'Total', 
        'Condicion'
    ], ';');

    // Consulta Compras
    $sql = "SELECT 
                p.ruc, 
                p.dv, 
                p.razon_social, 
                c.fecha_emision, 
                c.timbrado, 
                c.nro_comprobante, 
                c.gravada_10, c.iva_10, 
                c.gravada_5, c.iva_5, 
                c.exenta, 
                c.total_factura,
                c.condicion
            FROM pos_compras_cabecera c
            LEFT JOIN pos_proveedores p ON c.proveedor_id = p.id
            WHERE MONTH(c.fecha_emision) = ? AND YEAR(c.fecha_emision) = ? AND c.estado = 1
            ORDER BY c.fecha_emision ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mes, $anio]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ruc_completo = $row['ruc'] . ($row['dv'] ? '-'.$row['dv'] : '');
        $fecha = date('d/m/Y', strtotime($row['fecha_emision']));
        
        // Tipo 109 = Factura
        $tipo_comp = 109; 

        fputcsv($output, [
            $ruc_completo,
            $row['razon_social'],
            $tipo_comp,
            $fecha,
            $row['timbrado'],
            $row['nro_comprobante'],
            $row['gravada_10'],
            $row['iva_10'],
            $row['gravada_5'],
            $row['iva_5'],
            $row['exenta'],
            $row['total_factura'],
            $row['condicion'] // 1=Contado, 2=Crédito
        ], ';');
    }
}

fclose($output);
exit;