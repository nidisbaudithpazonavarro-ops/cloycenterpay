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

$folderKeys = [];
$folderKeys[] = basename(__DIR__);
$parentDir = dirname(__DIR__);
if ($parentDir !== __DIR__) {
    $folderKeys[] = basename($parentDir);
}
$folderKeys = array_values(array_unique(array_filter($folderKeys, 'strlen')));

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

if ($resolved === null) {
    $rootConfigPath = $projectRoot . '/config.php';
    if (file_exists($rootConfigPath)) {
        $resolved = require $rootConfigPath;
    }
}

if (!is_array($resolved)) {
    $resolved = [
        'bot_token' => '',
        'chat_id' => '',
    ];
}

return [
    'bot_token' => (string) ($resolved['bot_token'] ?? ''),
    'chat_id' => (string) ($resolved['chat_id'] ?? ''),
];
