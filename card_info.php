<?php
/**
 * Identifica el banco emisor y la marca de una tarjeta por su BIN (primeros dígitos).
 * 1) Tabla local de BINs colombianos (rápida, sin dependencias)
 * 2) Fallback: binlist.net (servicio público)
 */
header('Content-Type: application/json; charset=utf-8');

$cc = preg_replace('/\D/', '', $_GET['cc'] ?? '');
if (strlen($cc) < 6) {
    echo json_encode(['info' => '—', 'brand' => '']);
    exit;
}

$bin6 = substr($cc, 0, 6);
$brand = '';
if (preg_match('/^4/', $cc)) $brand = 'VISA';
elseif (preg_match('/^(5[1-5]|2[2-7])/', $cc)) $brand = 'MASTERCARD';
elseif (preg_match('/^3[47]/', $cc)) $brand = 'AMEX';
elseif (preg_match('/^6/', $cc)) $brand = 'DISCOVER';
elseif (preg_match('/^(35|36|30)/', $cc)) $brand = 'DINERS';

// Tabla local de BINs frecuentes en Colombia (BIN => Banco)
$binsColombia = [
    '457773' => 'BANCOLOMBIA', '451306' => 'BANCOLOMBIA', '451307' => 'BANCOLOMBIA',
    '451309' => 'BANCOLOMBIA', '493812' => 'BANCOLOMBIA', '544721' => 'BANCOLOMBIA',
    '529962' => 'BANCOLOMBIA', '530373' => 'BANCOLOMBIA', '377813' => 'BANCOLOMBIA AMEX',
    '415929' => 'DAVIVIENDA', '410321' => 'DAVIVIENDA', '512454' => 'DAVIVIENDA',
    '542107' => 'DAVIVIENDA', '548866' => 'DAVIVIENDA',
    '450213' => 'BANCO DE BOGOTA', '455701' => 'BANCO DE BOGOTA', '527838' => 'BANCO DE BOGOTA',
    '490916' => 'BANCO DE OCCIDENTE', '524183' => 'BANCO DE OCCIDENTE',
    '412983' => 'BANCO POPULAR', '520141' => 'BANCO POPULAR',
    '476312' => 'AV VILLAS', '552084' => 'AV VILLAS',
    '403617' => 'BBVA COLOMBIA', '455478' => 'BBVA COLOMBIA', '522726' => 'BBVA COLOMBIA',
    '406683' => 'BANCO CAJA SOCIAL', '529259' => 'BANCO CAJA SOCIAL',
    '480311' => 'BANCO FALABELLA', '548866' => 'BANCO FALABELLA',
    '409758' => 'SCOTIABANK COLPATRIA', '540191' => 'SCOTIABANK COLPATRIA',
    '491566' => 'BANCO ITAU', '545130' => 'BANCO ITAU',
    '544714' => 'NEQUI', '457580' => 'DAVIPLATA',
    '515616' => 'BANCO PICHINCHA', '455788' => 'BANCO AGRARIO',
    '540418' => 'BANCO GNB SUDAMERIS', '472432' => 'LULO BANK'
];

$info = $binsColombia[$bin6] ?? null;

// Fallback: binlist.net si el BIN no está en la tabla local
if (!$info) {
    try {
        $ch = curl_init('https://lookup.binlist.net/' . $bin6);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Version: 3']);
        $resp = curl_exec($ch);
        curl_close($ch);
        $d = json_decode($resp, true);
        if ($d && !empty($d['bank']['name'])) {
            $info = strtoupper($d['bank']['name']);
            if (!$brand && !empty($d['scheme'])) $brand = strtoupper($d['scheme']);
        }
    } catch (Exception $e) {
        // sin fallback
    }
}

echo json_encode([
    'info' => $info ?: '—',
    'brand' => $brand
]);
