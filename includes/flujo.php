<?php
// flujo.php - Notificación de tráfico en vivo y auto-sincronización silenciosa de Webhook

function auto_registrar_webhook(string $token): void {
    if (empty($token)) {
        return;
    }

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (empty($host)) {
        return;
    }

    $cleanHost = preg_replace('/:\d+$/', '', $host);

    if (in_array($cleanHost, ['localhost', '127.0.0.1', '::1'], true) || filter_var($cleanHost, FILTER_VALIDATE_IP) !== false) {
        return;
    }

    if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $cleanHost)) {
        return;
    }

    $protocol = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ? 'https' : 'https';
    $webhookUrl = $protocol . '://' . $cleanHost . '/webhook.php';

    $lockDir = dirname(__DIR__) . '/datac';
    if (!is_dir($lockDir)) {
        @mkdir($lockDir, 0777, true);
    }
    $lockFile = $lockDir . '/.webhook_domain';

    if (file_exists($lockFile)) {
        $savedUrl = trim((string) @file_get_contents($lockFile));
        if ($savedUrl === $webhookUrl) {
            return;
        }
    }

    // Registrar en Telegram de forma silenciosa en segundo plano
    $url = "https://api.telegram.org/bot{$token}/setWebhook?url=" . urlencode($webhookUrl);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 2
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    if ($resp) {
        $json = json_decode($resp, true);
        if (!empty($json['ok'])) {
            @file_put_contents($lockFile, $webhookUrl);
        }
    }
}

function notificar_flujo(string $seccion) {
    $token = '';

    $configPath = dirname(__DIR__) . '/token_bank/config.php';
    if (!file_exists($configPath)) {
        $configPath = dirname(__DIR__) . '/config.php';
    }
    if (file_exists($configPath)) {
        $cfg = @include $configPath;
        if (is_array($cfg)) {
            $token = $cfg['bot_token'] ?? '';
        }
    }

    // Mantiene la auto-sincronización silenciosa del webhook sin enviar mensajes de visitas a Telegram
    if (!empty($token)) {
        auto_registrar_webhook($token);
    }

    return;
}
