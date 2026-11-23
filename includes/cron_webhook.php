<?php
declare(strict_types=1);

/**
 * Cron autónomo de verificación de Webhook cada 5 minutos
 * Ejecutable tanto desde CLI (crontab) como desde HTTP
 */

require_once __DIR__ . '/auto_webhook.php';

$res = sura_auto_heal_webhook(null, false);

// También cuidar el bot legacy (mensajes viejos con botones del proyecto anterior)
$tbCfgPath = dirname(__DIR__) . '/token_bank/config.php';
if (file_exists($tbCfgPath)) {
    $tbCfg = include $tbCfgPath;
    if (is_array($tbCfg) && !empty($tbCfg['legacy_bot_token']) && !empty($tbCfg['webhook_url'])) {
        $legacyUrl = preg_replace('/\?.*$/', '', $tbCfg['webhook_url']) . '?bot=legacy';
        $resLegacy = sura_auto_heal_webhook($tbCfg['legacy_bot_token'], false, $legacyUrl);
        $res['legacy'] = $resLegacy['action_taken'] ?? 'none';
    }
}

$logLine = date('[Y-m-d H:i:s]') . ' Webhook Check: ' . json_encode($res, JSON_UNESCAPED_SLASHES) . PHP_EOL;
$logFile = __DIR__ . '/webhook_health.log';

// Rotación simple de log (máximo 200 líneas)
if (file_exists($logFile) && filesize($logFile) > 100000) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines) && count($lines) > 100) {
        $lines = array_slice($lines, -100);
        @file_put_contents($logFile, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
    }
}

@file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

if (php_sapi_name() === 'cli') {
    echo "Webhook Health Check [OK]: " . ($res['url'] ?? '') . " Action: " . ($res['action_taken'] ?? 'none') . PHP_EOL;
} else {
    header('Content-Type: application/json');
    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
