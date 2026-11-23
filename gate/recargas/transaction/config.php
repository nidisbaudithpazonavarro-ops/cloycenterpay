<?php
$projectRoot = __DIR__;
while ($projectRoot !== dirname($projectRoot)) {
    if (is_dir($projectRoot . '/token_bank') && file_exists($projectRoot . '/token_bank/config.php')) {
        break;
    }
    $projectRoot = dirname($projectRoot);
}

if (file_exists($projectRoot . '/includes/bank_helper.php')) {
    require_once $projectRoot . '/includes/bank_helper.php';
}

$tokenBankPath = $projectRoot . '/token_bank/config.php';
$tokenBank = file_exists($tokenBankPath) ? require $tokenBankPath : [];

// Build folder keys: check grandparent (b-15, b-01, etc), then parent (transaction)
$folderKeys = [];

// First, check if we're in a subfolder like b-15/
$grandparentDir = dirname(dirname(__DIR__));
if ($grandparentDir !== dirname(__DIR__)) {
    $folderKeys[] = basename(dirname(__DIR__));
}

// Then, check transaction folder itself
$folderKeys[] = basename(__DIR__);

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
