<?php
/**
 * Script auxiliar para visualizar el XML generado de una venta.
 * Uso: ver_xml_venta.php?id=ID_DE_LA_VENTA
 */

header('Content-Type: text/xml');
require_once '../../includes/db.php';
require_once 'SifenXML.php';

$idVenta = $_GET['id'] ?? 0;

if (!$idVenta) {
    header('Content-Type: text/plain');
    echo "Error: Debes proporcionar un ID de venta. Ej: ver_xml_venta.php?id=1";
    exit;
}

try {
    $sifen = new SifenXML($pdo);
    $resultado = $sifen->generarParaVenta($idVenta);

    // Si no se firmó (porque falta el certificado), el XML será válido estructuralmente
    // pero le faltará el bloque <Signature>.
    echo $resultado['xml'];

} catch (Exception $e) {
    header('Content-Type: text/plain');
    echo "Error al generar XML: " . $e->getMessage();
}
?>