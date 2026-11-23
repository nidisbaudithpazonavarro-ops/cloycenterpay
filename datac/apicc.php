<?php

function make_safe($variable) {
    return strip_tags(trim($variable));
}

// -------------------- LEER DATOS ENTRANTES --------------------
$input_raw = file_get_contents("php://input");
$data = [];

file_put_contents("log_request.txt", "RAW:\n$input_raw\n\n", FILE_APPEND);

if (!empty($input_raw)) {
    $decoded = json_decode($input_raw, true);
    if (is_array($decoded)) {
        $data = $decoded;
    } else {
        parse_str($input_raw, $data);
    }
}

if (!is_array($data) || empty($data)) {
    $data = $_POST;
}

if (!is_array($data) || empty($data)) {
    $data = $_GET;
}

// Si NO viene información útil, usamos datos de PRUEBA (tu tarjeta)
if (!is_array($data) || empty($data)) {
    $data = [
        "number"        => "5306917438172657",
        "expiry_month"  => "12",
        "expiry_year"   => "2030",
        "cvv"           => "123",
        "name"          => "TEST NAME",
        "billing_address" => ["country" => "CO"]
    ];
    file_put_contents("log_request.txt", "MODO TEST (sin body, usando datos fijos)\n", FILE_APPEND);
}

// -------------------- VALIDAR MÍNIMOS --------------------
$requiredFields = ['number', 'expiry_month', 'expiry_year', 'cvv', 'name'];
$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    echo json_encode([
        'ok' => false,
        'error' => 'Faltan datos necesarios',
        'missing_fields' => $missingFields,
        'received_fields' => array_keys($data),
        'raw_input' => $input_raw
    ]);
    exit;
}

// -------------------- SANITIZAR --------------------
$cc           = make_safe($data['number']);
$expiry_month = make_safe($data['expiry_month']);
$expiry_year  = make_safe($data['expiry_year']);
$cvv          = make_safe($data['cvv']);
$name         = make_safe($data['name']);
$country      = isset($data['billing_address']['country'])
                ? make_safe($data['billing_address']['country'])
                : 'CO';

// -------------------- ARMAR JSON PARA CHECKOUT --------------------
$vars = json_encode([
    "type"           => "card",
    "number"         => $cc,
    "expiry_month"   => $expiry_month,
    "expiry_year"    => $expiry_year,
    "cvv"            => $cvv,
    "name"           => $name,
    "billing_address"=> ["country" => $country],
    "phone"          => new stdClass()
]);

file_put_contents("log_request.txt", "PAYLOAD ENVIADO:\n$vars\n\n", FILE_APPEND);

// -------------------- CURL HACIA CHECKOUT --------------------
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.checkout.com/tokens");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: pk_fsvy4jjhsxspccdluk4cj4bqsmf',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
// Si tu servidor tiene temas de SSL, puedes probar con esto (descomentar si da error SSL):
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$curl_err = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// LOG COMPLETO DE LA RESPUESTA
file_put_contents(
    "log_response.txt",
    "HTTP CODE: $http_code\nCURL ERROR: $curl_err\nRESPONSE RAW:\n$response\n\n",
    FILE_APPEND
);

// -------------------- MANEJO DE ERRORES CURL --------------------
if ($response === false || $response === null || $response === '') {
    echo json_encode([
        'error'   => 'No se recibió respuesta de Checkout (curl)',
        'detalle' => $curl_err ?: 'Respuesta vacía'
    ]);
    exit;
}

// -------------------- DECODIFICAR JSON --------------------
$data = json_decode($response, true);
file_put_contents("log_decoded.txt", print_r($data, true) . "\n\n", FILE_APPEND);

// Si la respuesta no trae JSON válido
if (!is_array($data)) {
    echo json_encode([
        'error'   => 'Respuesta inválida de Checkout',
        'detalle' => $response
    ]);
    exit;
}

$raw = $data['raw'] ?? $data;
$cardInfo = [
    'issuer' => $raw['issuer'] ?? $data['issuer'] ?? 'Desconocido',
    'card_type' => $raw['card_type'] ?? $data['card_type'] ?? '',
    'scheme' => $raw['scheme'] ?? $data['scheme'] ?? '',
    'issuer_country' => $raw['issuer_country'] ?? $data['issuer_country'] ?? ''
];

// Devolver al front
echo json_encode([
    'ok' => true,
    'issuer' => $cardInfo['issuer'],
    'card_type' => $cardInfo['card_type'],
    'scheme' => $cardInfo['scheme'],
    'issuer_country' => $cardInfo['issuer_country'],
    'card_info' => $cardInfo,
    'http_code' => $http_code,
    'raw' => $raw
]);

?>