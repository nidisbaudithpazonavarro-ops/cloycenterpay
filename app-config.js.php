<?php
/**
 * Expone la configuración pública al frontend como JS.
 * Se incluye así: <script src="app-config.js.php"></script>
 * NUNCA expone secretos (secret_key, tokens) — solo lo que el navegador necesita.
 */
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/includes/app-config.php';

$public = [
    'turnstileActivo' => app_turnstile_activo(),
    'turnstileSiteKey' => app_turnstile_activo() ? app_cfg('cloudflare.site_key') : '',
    'tarjetaActiva' => (bool)app_cfg('tarjeta_activa', true),
    'sistemaActivo' => (bool)app_cfg('sistema_activo', true)
];

echo 'window.APP_CONFIG = ' . json_encode($public, JSON_UNESCAPED_SLASHES) . ';';
