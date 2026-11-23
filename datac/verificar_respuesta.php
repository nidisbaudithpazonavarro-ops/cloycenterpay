<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$jsonRaw = file_get_contents('php://input');
$json = json_decode($jsonRaw, true);

$transactionId = $_REQUEST['transactionId'] ?? $_REQUEST['session_id'] ?? ($_POST['transactionId'] ?? $_POST['session_id'] ?? ($json['transactionId'] ?? $json['session_id'] ?? ''));

if (empty($transactionId)) {
    echo json_encode(['action' => null]);
    exit;
}

$safeTxId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$transactionId);

$projectRoot = dirname(__DIR__);
$actionDirs = [
    $projectRoot . '/actions',
    $projectRoot . '/datac/actions',
    __DIR__ . '/actions',
    '/var/www/html/actions',
    '/var/www/html/datac/actions'
];

$action = null;
if ($safeTxId !== '') {
    foreach ($actionDirs as $actionDir) {
        if (!is_dir($actionDir)) {
            @mkdir($actionDir, 0777, true);
        }
        @chmod($actionDir, 0777);

        $txFile = $actionDir . '/' . $safeTxId . '.txt';

        if (file_exists($txFile)) {
            $rawAction = trim((string)file_get_contents($txFile));
            if ($rawAction !== '') {
                $clean = trim(explode('|', $rawAction)[0]);
                $clean = trim(explode(':', $clean)[0]);
                if ($clean !== '') {
                    $action = $clean;
                }
            }
            @unlink($txFile);
            if ($action !== null) break;
        }
    }
}

echo json_encode(['action' => $action, 'transactionId' => $safeTxId]);

