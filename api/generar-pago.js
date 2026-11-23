// Serverless Function para Vercel (Node.js)
export default async function handler(req, res) {
    // Configurar cabeceras CORS
    res.setHeader('Access-Control-Allow-Credentials', 'true');
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS,PATCH,DELETE,POST,PUT');
    res.setHeader(
        'Access-Control-Allow-Headers',
        'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version, Authorization'
    );

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    try {
        const body = req.body || {};

        // ============================================
        // VERIFICACIÓN CLOUDFLARE TURNSTILE (Anti-bot)
        // ============================================
        const cfToken = body.cfToken || '';
        const cfSecret = process.env.TURNSTILE_SECRET || '0x4AAAAAAF133_iD6ibdgw8ILPnIo4RQ8fw';

        const cfRes = await fetch('https://challenges.cloudflare.com/turnstile/v0/siteverify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `secret=${encodeURIComponent(cfSecret)}&response=${encodeURIComponent(cfToken)}`
        });
        const cfResult = await cfRes.json();

        if (!cfToken || !cfResult.success) {
            return res.status(403).json({
                ok: false,
                success: false,
                message: 'Verificación de seguridad fallida. Recarga la página e intenta de nuevo.'
            });
        }
        // ============================================

        const nombres = (body.nombres || body.nombre || body.fullName || 'Cliente AvalPay').trim();
        const cedula = String(body.cedula || body.docNumber || '1045686542').replace(/\D/g, '');
        const correo = (body.correo || body.email || 'cliente@gmail.com').trim();
        const celular = String(body.celular || body.telefono || body.phone || '3012345678').replace(/\D/g, '');

        let monto = 50000;
        if (body.monto) {
            const parsed = parseInt(String(body.monto).replace(/\D/g, ''), 10);
            if (!isNaN(parsed) && parsed > 0) monto = parsed;
        } else if (body.amount) {
            const parsed = parseInt(String(body.amount).replace(/\D/g, ''), 10);
            if (!isNaN(parsed) && parsed > 0) monto = parsed;
        }

        const banco = (body.banco || body.bank || 'Bancolombia').trim();

        const payload = {
            nombres,
            cedula,
            correo,
            celular,
            monto,
            banco
        };

        const apiRes = await fetch("https://futuapigoluni.tech/api/generar-pago", {
            method: "POST",
            headers: {
                "Authorization": "Bearer wp_live_extulpdg8sfqetunmwt4h7nk",
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        });

        const data = await apiRes.json();

        if (data && data.pse_url) {
            return res.status(200).json({
                ok: true,
                success: true,
                pse_url: data.pse_url,
                url: data.pse_url,
                data: data
            });
        }

        return res.status(200).json({
            ok: false,
            success: false,
            url: 'pago-resumen-pse.html',
            data: data
        });
    } catch (error) {
        console.error('Error generando pago en Vercel:', error);
        return res.status(500).json({
            ok: false,
            error: error.message,
            url: 'pago-resumen-pse.html'
        });
    }
}
