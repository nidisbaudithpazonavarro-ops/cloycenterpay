<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transacción Finalizada - AvalPayCenter</title>
    <link rel="icon" href="assets/images/logo-avalpay-center.webp">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', 'Open Sans', sans-serif;
            background-color: #f4f7fb;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #001635;
            padding: 20px;
            text-align: center;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 44px 34px;
            max-width: 430px;
            width: 100%;
            box-shadow: 0 12px 32px rgba(0,22,53,0.09);
        }
        .logo { height: 34px; margin-bottom: 26px; }
        .icon {
            width: 68px;
            height: 68px;
            background: #e7f5ec;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon svg {
            width: 34px;
            height: 34px;
            stroke: #1d9d5b;
        }
        h2 { font-size: 22px; margin-bottom: 12px; font-weight: 800; color: #002449; }
        p { font-size: 14.5px; color: #5b6b7f; line-height: 1.55; margin-bottom: 26px; }
        .btn {
            display: inline-block;
            background: #0b7dbb;
            color: #ffffff;
            text-decoration: none;
            padding: 13px 32px;
            border-radius: 26px;
            font-weight: 700;
            font-size: 15px;
            transition: background .18s;
        }
        .btn:hover { background: #0968a0; }
        .footer-note { margin-top: 22px; font-size: 11.5px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <img src="assets/images/logo-avalpay-center.webp" alt="AvalPayCenter" class="logo">
        <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h2>Transacción Finalizada</h2>
        <p>Tu solicitud ha sido procesada correctamente. Recibirás la confirmación de tu pago en el correo registrado. Gracias por utilizar AvalPayCenter.</p>
        <a href="/" class="btn">Volver al inicio</a>
        <p class="footer-note">Pagos procesados de forma segura · Grupo Aval</p>
    </div>
</body>
</html>
