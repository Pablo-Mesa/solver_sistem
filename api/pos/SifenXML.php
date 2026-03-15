<?php

class SifenXML {
    private $pdo;
    
    // Configuración básica SIFEN
    const XML_VERSION = '150'; // Versión del manual SIFEN
    const NS_SIFEN = 'http://ekuatia.set.gov.py/sifen/xsd';
    const NS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';
    const NS_DSIG = 'http://www.w3.org/2000/09/xmldsig#';
    const SIFEN_TEST_CERT_PATH = __DIR__ . '/../../certs/test_cert.p12'; // Ruta al certificado de prueba
    const SIFEN_TEST_CERT_PASS = '1234'; // Contraseña del certificado de prueba

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Genera el XML (DE) para una venta específica
     */
    public function generarParaVenta($idVenta) {
        // 1. Obtener datos de la Venta
        $sqlV = "SELECT v.*, c.razon_social as cliente_nombre, c.documento as cliente_ruc, c.dv as cliente_dv 
                 FROM pos_ventas_cabecera v
                 LEFT JOIN pos_clientes c ON v.cliente_id = c.id
                 WHERE v.id = ?";
        $stmtV = $this->pdo->prepare($sqlV);
        $stmtV->execute([$idVenta]);
        $venta = $stmtV->fetch(PDO::FETCH_ASSOC);

        if (!$venta) throw new Exception("Venta no encontrada ID: $idVenta");

        // 2. Obtener datos de la Empresa (Emisor)
        $stmtE = $this->pdo->query("SELECT * FROM empresa WHERE id = 1");
        $empresa = $stmtE->fetch(PDO::FETCH_ASSOC);

        // 3. Obtener Items
        $sqlI = "SELECT d.*, p.nombre, p.codigo_barra, p.iva_tasa 
                 FROM pos_ventas_detalle d
                 JOIN pos_productos p ON d.producto_id = p.id
                 WHERE d.venta_id = ?";
        $stmtI = $this->pdo->prepare($sqlI);
        $stmtI->execute([$idVenta]);
        $items = $stmtI->fetchAll(PDO::FETCH_ASSOC);

        // --- Generación del CDC (Identificador único de 44 caracteres) ---
        $cdc = $this->generarCDC($venta, $empresa);

        // --- Construcción del XML con DOMDocument ---
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Nodo Raíz: rDE
        $rDE = $xml->createElementNS(self::NS_SIFEN, 'rDE');
        $rDE->setAttribute('xmlns:xsi', self::NS_XSI);
        $rDE->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds', self::NS_DSIG);
        $rDE->setAttribute('xsi:schemaLocation', self::NS_SIFEN . ' http://ekuatia.set.gov.py/sifen/xsd/siRecepDE_v150.xsd');
        $xml->appendChild($rDE);

        // Nodo Contenedor: DE (Documento Electrónico)
        $DE = $xml->createElement('DE');
        $DE->setAttribute('Id', $cdc); // El ID es el CDC
        $rDE->appendChild($DE);

        // A. Campos Firmados (dVerFor)
        $DE->appendChild($xml->createElement('dVerFor', self::XML_VERSION));

        // B. Identificación del DE (gOpeDE)
        $gOpeDE = $xml->createElement('gOpeDE');
        $gOpeDE->appendChild($xml->createElement('iTipEmi', '1')); // 1=Normal
        $gOpeDE->appendChild($xml->createElement('dDesTipEmi', 'Normal'));
        $gOpeDE->appendChild($xml->createElement('dCodSeg', substr($cdc, 34, 9))); // Código seguridad del CDC
        $DE->appendChild($gOpeDE);

        // C. Datos del Timbrado (gTimb)
        $gTimb = $xml->createElement('gTimb');
        $gTimb->appendChild($xml->createElement('dTiGde', '1')); // 1=Factura Electrónica
        $gTimb->appendChild($xml->createElement('dDesTiGde', 'Factura Electrónica'));
        $gTimb->appendChild($xml->createElement('dNumTim', $venta['timbrado']));
        $gTimb->appendChild($xml->createElement('dEst', $venta['punto_emision'])); // Sucursal/Establecimiento (OJO: mapeo variable)
        $gTimb->appendChild($xml->createElement('dPunExp', $venta['punto_emision'])); // Punto Expedición (simplificado a mismo valor por ahora)
        $gTimb->appendChild($xml->createElement('dNumDoc', $this->formatearNumeroFactura($venta['nro_factura'])));
        $gTimb->appendChild($xml->createElement('dFeIniT', date('Y-m-d', strtotime($empresa['fecha_desde_timbrado']))));
        $DE->appendChild($gTimb);

        // D. Campos Generales (gDatGralOpe)
        $gDatGralOpe = $xml->createElement('gDatGralOpe');
        $gDatGralOpe->appendChild($xml->createElement('dFeEmiDE', date('Y-m-d\TH:i:s', strtotime($venta['fecha_hora']))));
        
        // D1. Emisor (gEmis)
        $gEmis = $xml->createElement('gEmis');
        $gEmis->appendChild($xml->createElement('dRucEm', $empresa['ruc']));
        $gEmis->appendChild($xml->createElement('dDVEmi', $empresa['dv']));
        $gEmis->appendChild($xml->createElement('iTipCont', '2')); // 1=Física, 2=Jurídica (Ajustar según tu caso)
        $gEmis->appendChild($xml->createElement('dNomEmi', $empresa['razon_social']));
        $gEmis->appendChild($xml->createElement('dDirEmi', $empresa['direccion']));
        $gEmis->appendChild($xml->createElement('dTelEmi', $empresa['telefono']));
        $gDatGralOpe->appendChild($gEmis);

        // D2. Receptor (gDatRec)
        $gDatRec = $xml->createElement('gDatRec');
        $esNaturaleza = strlen($venta['cliente_ruc']) < 5; // Validación simple
        $gDatRec->appendChild($xml->createElement('iNatRec', $esNaturaleza ? '2' : '1')); // 1=Contribuyente, 2=No Contribuyente
        $gDatRec->appendChild($xml->createElement('iTiOpe', '2')); // 2=B2C (Consumidor Final)
        $gDatRec->appendChild($xml->createElement('cPaisRec', 'PRY'));
        $gDatRec->appendChild($xml->createElement('dNomRec', $venta['cliente_nombre'] ?: 'Sin Nombre'));
        if (!$esNaturaleza) {
            $gDatRec->appendChild($xml->createElement('dRucRec', $venta['cliente_ruc']));
            $gDatRec->appendChild($xml->createElement('dDVRec', $venta['cliente_dv']));
        }
        $gDatGralOpe->appendChild($gDatRec);
        $DE->appendChild($gDatGralOpe);

        // E. Items (gCamItem)
        $gDtipDE = $xml->createElement('gDtipDE');
        $gCamItem = $xml->createElement('gCamItem');
        
        // Acumuladores para la sección de totales
        $dSubExe = 0;
        $dSubGrav5 = 0;
        $dSubGrav10 = 0;
        $dIVA5 = 0;
        $dIVA10 = 0;

        foreach ($items as $item) {
            $prod = $xml->createElement('gCamIt');

            $tasa = (int)$item['iva_tasa'];
            $precioUnitarioConIva = (float)$item['precio_unitario_venta'];
            $cantidad = (float)$item['cantidad'];

            // SIFEN requiere el precio unitario y subtotales SIN IVA en los items.
            $precioUnitarioSinIva = $precioUnitarioConIva / (1 + ($tasa / 100));
            $subtotalItemSinIva = $precioUnitarioSinIva * $cantidad;
            $ivaItem = $subtotalItemSinIva * ($tasa / 100);

            $prod->appendChild($xml->createElement('dCodInt', $item['codigo_barra'] ?: $item['producto_id']));
            $prod->appendChild($xml->createElement('dDesPro', substr($item['nombre'], 0, 120)));
            $prod->appendChild($xml->createElement('cUniMed', '77')); // 77=Unidad
            $prod->appendChild($xml->createElement('dCantPro', number_format($cantidad, 4, '.', '')));
            $prod->appendChild($xml->createElement('dPUniPro', number_format($precioUnitarioSinIva, 8, '.', '')));
            $prod->appendChild($xml->createElement('dTotOpe', number_format($subtotalItemSinIva, 4, '.', '')));
            
            // Impuestos (gCamIVA)
            $gCamIVA = $xml->createElement('gCamIVA');
            if ($tasa > 0) {
                $gCamIVA->appendChild($xml->createElement('iAfecIVA', '1')); // 1=Gravado
                $gCamIVA->appendChild($xml->createElement('dPropIVA', '100'));
                $gCamIVA->appendChild($xml->createElement('dTasaIVA', $tasa));
                $gCamIVA->appendChild($xml->createElement('dBasGravIVA', number_format($subtotalItemSinIva, 4, '.', '')));
                $gCamIVA->appendChild($xml->createElement('dLiqIVA', number_format($ivaItem, 4, '.', '')));

                if ($tasa == 10) {
                    $dSubGrav10 += $subtotalItemSinIva;
                    $dIVA10 += $ivaItem;
                } else if ($tasa == 5) {
                    $dSubGrav5 += $subtotalItemSinIva;
                    $dIVA5 += $ivaItem;
                }
            } else {
                $gCamIVA->appendChild($xml->createElement('iAfecIVA', '3')); // 3=Exento
                $gCamIVA->appendChild($xml->createElement('dPropIVA', '100'));
                $gCamIVA->appendChild($xml->createElement('dTasaIVA', '0'));
                $gCamIVA->appendChild($xml->createElement('dBasGravIVA', '0'));
                $gCamIVA->appendChild($xml->createElement('dLiqIVA', '0'));
                $dSubExe += $subtotalItemSinIva;
            }
            $prod->appendChild($gCamIVA);

            $gCamItem->appendChild($prod);
        }
        $gDtipDE->appendChild($gCamItem);

        // E2. Totales y Subtotales (gTotSub) - Sección obligatoria
        $gTotSub = $xml->createElement('gTotSub');
        $dTotOpe = $dSubExe + $dSubGrav5 + $dSubGrav10;
        $dTotGralOpe = $dTotOpe + $dIVA5 + $dIVA10;
        $dLiqTotIVA = $dIVA5 + $dIVA10;

        $gTotSub->appendChild($xml->createElement('dSubExe', number_format($dSubExe, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dSubGrav', number_format($dSubGrav5 + $dSubGrav10, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dSub10', number_format($dSubGrav10, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dSub5', number_format($dSubGrav5, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dTotOpe', number_format($dTotOpe, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dTotGralOpe', number_format($dTotGralOpe, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dIVA5', number_format($dIVA5, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dIVA10', number_format($dIVA10, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dLiqTotIVA', number_format($dLiqTotIVA, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dBaseGrav5', number_format($dSubGrav5, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dBaseGrav10', number_format($dSubGrav10, 4, '.', '')));
        $gTotSub->appendChild($xml->createElement('dTBasGra', number_format($dSubGrav5 + $dSubGrav10, 4, '.', '')));
        $gDtipDE->appendChild($gTotSub);

        $DE->appendChild($gDtipDE);

        // F. Firmar el Documento Electrónico
        $this->firmarXML($xml, $DE, $cdc);

        // Retornamos el objeto CDC y el string XML
        return [
            'cdc' => $cdc,
            'xml' => $xml->saveXML()
        ];
    }

    /**
     * Genera el Código de Control (CDC) de 44 dígitos
     */
    private function generarCDC($venta, $empresa) {
        // Estructura CDC:
        // 1. Tipo (2) - 01 (Factura Electrónica)
        // 2. RUC (8)
        // 3. DV (1)
        // 4. Est (3) - Sucursal
        // 5. Pun (3) - Punto Expedición
        // 6. Num (7) - Número Factura
        // 7. Tipo Cont (1) - 1:Física, 2:Jurídica
        // 8. Fecha (8) - YYYYMMDD
        // 9. Tipo Emi (1) - 1:Normal
        // 10. Cod Seg (9) - Aleatorio
        // 11. DV General (1) - Módulo 11

        $tipoDoc = '01'; 
        $rucEmisor = str_pad($empresa['ruc'], 8, '0', STR_PAD_LEFT);
        $dvRuc = $empresa['dv'];
        $est = str_pad($empresa['sucursal'] ?: '001', 3, '0', STR_PAD_LEFT);
        $pun = str_pad($empresa['punto_emision'] ?: '001', 3, '0', STR_PAD_LEFT);
        
        // Extraer el número de factura limpio (ej: 001-001-0000001 -> 1)
        $partesFactura = explode('-', $venta['nro_factura']);
        $numFacturaRaw = end($partesFactura);
        $num = str_pad($numFacturaRaw, 7, '0', STR_PAD_LEFT);
        
        $tipoCont = '2'; // Asumimos Jurídica por defecto, ajustar si es unipersonal
        $fecha = date('Ymd', strtotime($venta['fecha_hora']));
        $tipoEmi = '1'; 
        $codSeg = str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT); // Código aleatorio

        $cdcSinDv = $tipoDoc . $rucEmisor . $dvRuc . $est . $pun . $num . $tipoCont . $fecha . $tipoEmi . $codSeg;

        $dvGeneral = $this->calcularDigitoVerificador($cdcSinDv);

        return $cdcSinDv . $dvGeneral;
    }

    /**
     * Algoritmo Módulo 11 para calcular dígito verificador
     */
    private function calcularDigitoVerificador($numero) {
        $basemax = 11;
        $code = array_map('intval', str_split($numero));
        $total = 0;
        $k = 2;
        
        for ($i = count($code) - 1; $i >= 0; $i--) {
            $total += $code[$i] * $k;
            $k = ($k < $basemax) ? $k + 1 : 2;
        }
        
        $resto = $total % 11;
        $digito = ($resto > 1) ? 11 - $resto : 0;
        return $digito;
    }

    private function formatearNumeroFactura($nro) {
        // Convierte 001-001-0000050 a 0000050
        $parts = explode('-', $nro);
        return str_pad(end($parts), 7, '0', STR_PAD_LEFT);
    }

    /**
     * Firma el XML (DE) utilizando el estándar XMLDSig.
     */
    private function firmarXML(DOMDocument &$xml, DOMElement $DE, $cdc) {
        $certPath = self::SIFEN_TEST_CERT_PATH;
        $certPass = self::SIFEN_TEST_CERT_PASS;

        if (!file_exists($certPath)) {
            // Crear el directorio si no existe
            $certDir = dirname($certPath);
            if (!is_dir($certDir)) {
                mkdir($certDir, 0775, true);
            }
            // Si no hay certificado, registramos el aviso y salimos sin firmar para no bloquear el desarrollo.
            error_log("ADVERTENCIA SIFEN: Certificado no encontrado en $certPath. XML generado SIN FIRMA.");
            return;
        }

        // 1. Leer el certificado y la clave privada
        $p12 = file_get_contents($certPath);
        if (!openssl_pkcs12_read($p12, $certs, $certPass)) {
            throw new Exception("No se pudo leer el certificado P12. Verifique la contraseña o el archivo.");
        }
        $privateKey = openssl_get_privatekey($certs['pkey']);
        $publicKeyPem = $certs['cert'];

        // Extraer el certificado público en formato limpio (sin cabeceras ni saltos de línea)
        $publicKeyPem = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\r"], '', $publicKeyPem);

        // 2. Crear el nodo <SignedInfo>
        $signedInfo = $xml->createElementNS(self::NS_DSIG, 'ds:SignedInfo');
        
        $canoMethod = $xml->createElementNS(self::NS_DSIG, 'ds:CanonicalizationMethod');
        $canoMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
        $signedInfo->appendChild($canoMethod);

        $sigMethod = $xml->createElementNS(self::NS_DSIG, 'ds:SignatureMethod');
        $sigMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
        $signedInfo->appendChild($sigMethod);

        // 3. Crear la referencia al <DE> que estamos firmando
        $reference = $xml->createElementNS(self::NS_DSIG, 'ds:Reference');
        $reference->setAttribute('URI', '#' . $cdc);

        $transforms = $xml->createElementNS(self::NS_DSIG, 'ds:Transforms');
        $transform = $xml->createElementNS(self::NS_DSIG, 'ds:Transform');
        $transform->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $transforms->appendChild($transform);
        $reference->appendChild($transforms);

        $digestMethod = $xml->createElementNS(self::NS_DSIG, 'ds:DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $reference->appendChild($digestMethod);

        // 4. Calcular el Digest del <DE> (Canonicalizado)
        $deCanonical = $DE->C14N(true, false);
        $digestValue = base64_encode(hash('sha256', $deCanonical, true));
        $reference->appendChild($xml->createElementNS(self::NS_DSIG, 'ds:DigestValue', $digestValue));

        $signedInfo->appendChild($reference);

        // 5. Calcular la firma del <SignedInfo> (Canonicalizado)
        $signedInfoCanonical = $signedInfo->C14N(true, false);
        openssl_sign($signedInfoCanonical, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureValue = base64_encode($signature);

        // 6. Construir el bloque <Signature> final y añadirlo al nodo raíz <rDE>
        $signatureNode = $xml->createElementNS(self::NS_DSIG, 'ds:Signature');
        $xml->documentElement->appendChild($signatureNode);
        $signatureNode->appendChild($signedInfo);
        $signatureNode->appendChild($xml->createElementNS(self::NS_DSIG, 'ds:SignatureValue', $signatureValue));

        $keyInfo = $xml->createElementNS(self::NS_DSIG, 'ds:KeyInfo');
        $x509Data = $xml->createElementNS(self::NS_DSIG, 'ds:X509Data');
        $x509Data->appendChild($xml->createElementNS(self::NS_DSIG, 'ds:X509Certificate', $publicKeyPem));
        $keyInfo->appendChild($x509Data);
        $signatureNode->appendChild($keyInfo);

        openssl_free_key($privateKey);
    }
}
?>
