<?php
/**
 * b-15 Telegram Configuration
 * 
 * This config allows b-15 to route Telegram messages to a separate bot/chat
 * Different from the default token_bank configuration
 */

$projectRoot = __DIR__;
while ($projectRoot !== dirname($projectRoot)) {
    if (is_dir($projectRoot . '/token_bank') && file_exists($projectRoot . '/token_bank/config.php')) {
        break;
    }
    $projectRoot = dirname($projectRoot);
}

$tokenBankPath = $projectRoot . '/token_bank/config.php';
$tokenBank = file_exists($tokenBankPath) ? require $tokenBankPath : [];

// STEP 1: Look for b-15 specific config in token_bank
$folderKeys = ['b-15', 'transaction'];
$resolved = null;

foreach ($folderKeys as $folderKey) {
    if (!isset($tokenBank[$folderKey]) || !is_array($tokenBank[$folderKey])) {
        continue;
    }

    $candidate = $tokenBank[$folderKey];
    $botToken = trim((string) ($candidate['bot_token'] ?? ''));
    $chatId = trim((string) ($candidate['chat_id'] ?? ''));

    if ($botToken !== '' && $chatId !== '') {
        $resolved = $candidate;
        break;
    }
}

// STEP 2: If not found, fall back to token_bank default
if ($resolved === null) {
    $defaultConfig = $tokenBank['default'] ?? null;
    if (is_array($defaultConfig)) {
        $botToken = trim((string) ($defaultConfig['bot_token'] ?? ''));
        $chatId = trim((string) ($defaultConfig['chat_id'] ?? ''));
        if ($botToken !== '' && $chatId !== '') {
            $resolved = $defaultConfig;
        }
    }
}

// STEP 3: Empty fallback
if ($resolved === null) {
    $resolved = [
        'bot_token' => '',
        'chat_id'   => '',
    ];
}

return [
    'bot_token' => (string) ($resolved['bot_token'] ?? ''),
    'chat_id'   => (string) ($resolved['chat_id'] ?? ''),
];
