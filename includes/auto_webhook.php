<?php
declare(strict_types=1);

/**
 * Sistema Autónomo de Detección y Auto-Healing para Webhook de Telegram
 * Detección 100% dinámica de dominio y mantenimiento continuo sin intervención manual.
 * POLÍTICA DE CERO SPAM: Solo alerta a Telegram en fallos irrecuperables del bot.
 */

function sura_detect_webhook_url(): string
{
    $host = '';
    $hostCacheFile = __DIR__ . '/host_cache.txt';

    // 1. Detección en peticiones web
    if (php_sapi_name() !== 'cli') {
        if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_HOST'])[0]);
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $host = (string)$_SERVER['HTTP_HOST'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $host = (string)$_SERVER['SERVER_NAME'];
        }

        $host = trim($host);
        $host = preg_replace('#^https?://#i', '', $host);
        $host = rtrim($host, '/');

        if ($host !== '' && strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false) {
            @file_put_contents($hostCacheFile, $host, LOCK_EX);
        }
    }

    // 2. Detección en CLI / Cron o si host aún está vacío
    if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || filter_var($host, FILTER_VALIDATE_IP)) {
        if (file_exists($hostCacheFile)) {
            $cached = trim((string)@file_get_contents($hostCacheFile));
            if ($cached !== '' && strpos($cached, 'localhost') === false && !filter_var($cached, FILTER_VALIDATE_IP)) {
                $host = $cached;
            }
        }
    }

    if ($host === '' || filter_var($host, FILTER_VALIDATE_IP)) {
        $configPath = dirname(__DIR__) . '/token_bank/config.php';
        if (file_exists($configPath)) {
            $cfg = include $configPath;
            if (!empty($cfg['webhook_url'])) {
                $parsed = parse_url((string)$cfg['webhook_url'], PHP_URL_HOST);
                if ($parsed && !filter_var($parsed, FILTER_VALIDATE_IP)) {
                    $host = $parsed;
                }
            }
        }
    }

    if ($host === '') {
        $host = 'segursuracol.bid';
    }

    // 3. Determinar directorio base relativo (para subdirectorios web)
    $dir = '';
    if (php_sapi_name() !== 'cli') {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '/index.php';
        $scriptDir = dirname($scriptName);
        $scriptDir = str_replace(['/includes', '/recaudos', '/gate/process', '/gate/recargas', '/datac', '/api', '/caras'], '', $scriptDir);
        $dir = ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') ? '' : '/' . trim($scriptDir, '/\\');
    }

    // Si token_bank trae webhook_url explícita y del mismo host, respetar su path
    $configPath = dirname(__DIR__) . '/token_bank/config.php';
    if (file_exists($configPath)) {
        $cfgWh = include $configPath;
        if (!empty($cfgWh['webhook_url'])) {
            $cfgHost = parse_url((string)$cfgWh['webhook_url'], PHP_URL_HOST);
            if ($cfgHost === $host) {
                return (string)$cfgWh['webhook_url'];
            }
        }
    }

    return 'https://' . $host . $dir . '/telegram-webhook.php';
}

function sura_auto_heal_webhook(?string $botToken = null, bool $force = false, ?string $customUrl = null): array
{
    $configFile = dirname(__DIR__) . '/token_bank/config.php';
    $cfg = file_exists($configFile) ? include $configFile : [];
    
    if (!$botToken) {
        $botToken = $cfg['bot_token'] ?? null;
    }

    if (empty($botToken)) {
        return ['ok' => false, 'error' => 'No se encontró bot_token configurado'];
    }

    $targetWebhookUrl = $customUrl ?: sura_detect_webhook_url();
    $host = parse_url($targetWebhookUrl, PHP_URL_HOST) ?? '';
    
    // Ignorar en entorno local
    if ($host === 'localhost' || $host === '127.0.0.1') {
        return ['ok' => true, 'local' => true, 'url' => $targetWebhookUrl];
    }

    $cacheFile = __DIR__ . '/webhook_cache_' . substr(md5($botToken . '|' . $targetWebhookUrl), 0, 8) . '.json';
    $now = time();

    // 1. Verificación en caché (600 segundos = 10 minutos) con control de concurrencia
    if (!$force && file_exists($cacheFile)) {
        $cache = json_decode((string)@file_get_contents($cacheFile), true);
        if (is_array($cache) && !empty($cache['checked_at']) && ($now - (int)$cache['checked_at'] < 600)) {
            $cache['action_taken'] = 'cached_healthy';
            return $cache;
        }
    }

    // Actualizar marca de tiempo de verificación para evitar peticiones concurrentes
    @file_put_contents($cacheFile, json_encode(['checked_at' => $now, 'ok' => true, 'url' => $targetWebhookUrl]));

    // 2. Consultar estado actual del Webhook en Telegram
    $infoUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 4,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);

    $rawInfo = @file_get_contents($infoUrl, false, $ctx);
    $info = $rawInfo ? json_decode($rawInfo, true) : null;

    if (!$info || empty($info['ok'])) {
        $errorMsg = $info['description'] ?? 'No se pudo contactar con la API de Telegram';
        
        // Alerta crítica solo si el token es inválido o no existe
        if (!empty($info['error_code']) && ($info['error_code'] == 401 || $info['error_code'] == 404)) {
            sura_send_critical_alert($botToken, $cfg['chat_id'] ?? '', $host, "Error de Token ({$info['error_code']}): {$errorMsg}");
        }
        
        return ['ok' => false, 'error' => $errorMsg];
    }

    $currentUrl = $info['result']['url'] ?? '';
    $lastErrorMsg = $info['result']['last_error_message'] ?? '';
    $pendingUpdates = (int)($info['result']['pending_update_count'] ?? 0);
    $currentIp = $info['result']['ip_address'] ?? '';

    // Si la URL registrada ya es correcta y no hay errores de conexión de Telegram, no hacer nada
    $needsUpdate = $force || ($currentUrl !== $targetWebhookUrl);

    $resultState = [
        'ok' => true,
        'checked_at' => $now,
        'url' => $targetWebhookUrl,
        'telegram_registered_url' => $currentUrl,
        'telegram_ip' => $currentIp,
        'pending_updates' => $pendingUpdates,
        'last_error' => $lastErrorMsg ?: null,
        'action_taken' => 'verified_healthy'
    ];

    if ($needsUpdate) {
        $params = [
            'url' => $targetWebhookUrl,
            'drop_pending_updates' => 'false',
            'allowed_updates' => json_encode(['message', 'callback_query'])
        ];

        $setUrl = "https://api.telegram.org/bot{$botToken}/setWebhook?" . http_build_query($params);
        $rawSet = @file_get_contents($setUrl, false, $ctx);
        $setRes = $rawSet ? json_decode($rawSet, true) : null;

        if (!empty($setRes['ok'])) {
            $resultState['action_taken'] = 'webhook_updated';
            $resultState['telegram_registered_url'] = $targetWebhookUrl;
            $resultState['pending_updates'] = 0;
            $resultState['last_error'] = null;
        } else {
            $errDesc = $setRes['description'] ?? 'Error desconocido al registrar webhook';
            $resultState['ok'] = false;
            $resultState['error'] = $errDesc;

            // Ignorar alertas de "Too Many Requests" (rate limit temporal de la API)
            if (stripos($errDesc, 'Too Many Requests') === false) {
                sura_send_critical_alert($botToken, $cfg['chat_id'] ?? '', $host, "Fallo al registrar Webhook: {$errDesc}");
            }
        }
    }

    @file_put_contents($cacheFile, json_encode($resultState, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    return $resultState;
}

/**
 * Enviar alerta a Telegram SOLO en errores críticos (con throttle de 1 hora)
 */
function sura_send_critical_alert(string $botToken, string $chatId, string $host, string $detail): void
{
    if (empty($botToken) || empty($chatId)) {
        return;
    }

    $lockFile = __DIR__ . '/last_critical_alert.txt';
    $lastAlert = file_exists($lockFile) ? (int)@file_get_contents($lockFile) : 0;
    
    // Throttle: máximo 1 mensaje por hora
    if ((time() - $lastAlert) < 3600) {
        return;
    }

    @file_put_contents($lockFile, (string)time());

    $msg = "🚨 <b>ALERTA CRÍTICA: WEBHOOK DESCONECTADO</b>\n\n"
         . "🌐 <b>Dominio:</b> <code>{$host}</code>\n"
         . "⚠️ <b>Detalle:</b> {$detail}\n\n"
         . "🔧 <i>El sistema intentó auto-reconectar pero falló. Verifique el bot o el certificado SSL.</i>";

    $payload = [
        'chat_id' => $chatId,
        'text' => $msg,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init("https://api.telegram.org/bot{$botToken}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// Compatibilidad
function jelpi_detect_webhook_url(): string { return sura_detect_webhook_url(); }
function jelpi_auto_heal_webhook(?string $botToken = null, bool $force = false): array { return sura_auto_heal_webhook($botToken, $force); }
