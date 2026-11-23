<?php
/**
 * validar-correo.php — Validación de correo en 2 niveles (gratis, ~90% precisión):
 *  1) Sintaxis RFC básica
 *  2) El dominio realmente acepta correos (registro MX o A) — dns_get_record
 *  3) Bloquea dominios desechables conocidos (tempmail, yopmail, etc.)
 *
 * Nota: verificar que el BUZÓN exacto existe (juan123@gmail) no es posible
 * gratis ni confiable — Gmail/Outlook bloquean esa consulta (VRFY/RCPT).
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$email = trim($_GET['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => true, 'valido' => false, 'razon' => 'sintaxis']);
    exit;
}

$dominio = strtolower(substr(strrchr($email, '@'), 1));

$desechables = [
    'tempmail.com', 'yopmail.com', 'guerrillamail.com', 'mailinator.com',
    '10minutemail.com', 'throwawaymail.com', 'temp-mail.org', 'fakeinbox.com',
    'trashmail.com', 'sharklasers.com', 'getnada.com', 'dispostable.com'
];
if (in_array($dominio, $desechables, true)) {
    echo json_encode(['ok' => true, 'valido' => false, 'razon' => 'desechable']);
    exit;
}

$tieneMX = false;
if (function_exists('dns_get_record')) {
    $mx = @dns_get_record($dominio, DNS_MX);
    if (is_array($mx) && count($mx) > 0) {
        $tieneMX = true;
    } else {
        // Fallback: dominio con registro A (algunos servidores reciben sin MX)
        $a = @dns_get_record($dominio, DNS_A);
        $tieneMX = is_array($a) && count($a) > 0;
    }
}

echo json_encode([
    'ok' => true,
    'valido' => $tieneMX,
    'razon' => $tieneMX ? 'dominio_valido' : 'dominio_sin_correo'
]);
