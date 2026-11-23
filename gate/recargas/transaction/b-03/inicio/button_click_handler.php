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

// Recibir confirmación de que un botón fue presionado
$buttonData = json_decode(file_get_contents('php://input'), true);

if (!$buttonData) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit;
}

$buttonName = $buttonData['button'] ?? null;
$transactionId = $buttonData['transactionId'] ?? null;
$callbackId = $buttonData['callbackId'] ?? null;

if (!$buttonName || !$transactionId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

// Crear archivo de log para esta acción
$logDir = __DIR__ . '/../../log';
@mkdir($logDir, 0755, true);

$logFile = $logDir . "/{$transactionId}_button_clicks.json";
$clicks = [];

if (file_exists($logFile)) {
    $clicks = json_decode(file_get_contents($logFile), true) ?? [];
}

// Registrar click del botón
$clicks[] = [
    'button' => $buttonName,
    'timestamp' => time(),
    'datetime' => date('Y-m-d H:i:s'),
    'callback_id' => $callbackId
];

file_put_contents($logFile, json_encode($clicks, JSON_PRETTY_PRINT));

// Responder al cliente
echo json_encode([
    'status' => 'success',
    'message' => "Botón '{$buttonName}' registrado",
    'button' => $buttonName,
    'timestamp' => time()
]);

// Log en consola del servidor
error_log("[" . date('Y-m-d H:i:s') . "] Button clicked: {$buttonName} - Transaction: {$transactionId}");
?>
