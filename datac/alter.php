<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Método de pago rechazado</title>
  <style>
    :root {
      --bg: #ffffff;
      --panel: #fff7f7;
      --border: #f4b4b4;
      --text: #111827;
      --muted: #6b7280;
      --accent: #dc2626;
    }

    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: var(--bg);
      color: var(--text);
      font-family: Arial, Helvetica, sans-serif;
      padding: 24px;
    }

    .card {
      width: min(560px, 100%);
      background: var(--panel);
      border: 1px solid var(--border);
      border-left: 6px solid var(--accent);
      border-radius: 12px;
      padding: 28px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.05);
    }

    .badge {
      display: inline-block;
      padding: 7px 12px;
      border-radius: 999px;
      background: #fee2e2;
      color: var(--accent);
      font-weight: 700;
      font-size: 13px;
      margin-bottom: 16px;
      letter-spacing: 0.3px;
    }

    h1 {
      margin: 0 0 10px;
      font-size: 24px;
      line-height: 1.2;
    }

    p {
      margin: 0 0 10px;
      color: var(--muted);
      font-size: 15px;
      line-height: 1.6;
    }

    .actions {
      margin-top: 20px;
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .btn {
      display: inline-block;
      text-decoration: none;
      padding: 11px 16px;
      border-radius: 8px;
      background: var(--accent);
      color: #fff;
      font-weight: 700;
      border: none;
      cursor: pointer;
    }

    .btn.secondary {
      background: #fff;
      color: var(--text);
      border: 1px solid #d1d5db;
    }

    .note {
      margin-top: 16px;
      font-size: 13px;
      color: #7a7a7a;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="badge">Método de pago rechazado</div>
    <h1>El método de pago fue rechazado</h1>
    <p>Por favor vuelve a intentarlo o cambia de método de pago.</p>
    <p>Serás redirigido automáticamente a la página de métodos disponibles.</p>
    <div class="actions">
      <a class="btn" href="/metodostc.php">Ir ahora</a>
      <a class="btn secondary" href="/metodostc.php">Cambiar método</a>
    </div>
    <div class="note" id="countdown">Serás redirigido en 5 segundos.</div>
  </div>

  <script>
    const redirectUrl = '/metodostc.php';
    let seconds = 5;
    const countdown = document.getElementById('countdown');
    const timer = setInterval(() => {
      seconds -= 1;
      if (seconds > 0) {
        countdown.textContent = `Serás redirigido en ${seconds} segundos.`;
      } else {
        countdown.textContent = 'Redirigiendo...';
      }
    }, 1000);

    window.setTimeout(() => {
      clearInterval(timer);
      window.location.replace(redirectUrl);
    }, 5000);
  </script>
</body>
</html>
