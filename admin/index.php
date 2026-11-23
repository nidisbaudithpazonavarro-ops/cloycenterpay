<?php
session_start();
require_once __DIR__ . '/../includes/app-config.php';

$configPath = __DIR__ . '/config.json';
$config = app_config();
$defaultPassword = (string)($config['admin_password'] ?? '1234567');
$bankList = [
    ['code' => '1051', 'label' => 'Davivienda'],
    ['code' => '1007', 'label' => 'Bancolombia'],
    ['code' => '1001', 'label' => 'Banco de Bogotá'],
    ['code' => '1023', 'label' => 'Occidente'],
    ['code' => '1002', 'label' => 'Banco Popular'],
    ['code' => '1052', 'label' => 'Av Villas'],
    ['code' => '1013', 'label' => 'BBVA'],
    ['code' => '1032', 'label' => 'Caja Social'],
    ['code' => '1062', 'label' => 'Falabella'],
    ['code' => '1063', 'label' => 'Finandina'],
    ['code' => '1006', 'label' => 'Itaú'],
    ['code' => '1047', 'label' => 'Mundo Mujer'],
    ['code' => '1069', 'label' => 'Serfinanza'],
    ['code' => '1070', 'label' => 'Lulo'],
    ['code' => '1019', 'label' => 'Scotiabank / Colpatria'],
    ['code' => '1040', 'label' => 'Agrario'],
    ['code' => '1060', 'label' => 'Pichincha'],
    ['code' => '1061', 'label' => 'Bancolombia / Omeva'],
    ['code' => '1507', 'label' => 'Nequi'],
    ['code' => '1551', 'label' => 'Daviplata'],
    ['code' => '1097', 'label' => 'Dale'],
    ['code' => '1809', 'label' => 'Nu'],
];

$activeBanks = array_values(array_unique(array_filter(array_map('strval', (array)($config['bancos_pse_activos'] ?? [])))));
$loggedIn = !empty($_SESSION['apc_admin_logged']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    unset($_SESSION['apc_admin_logged']);
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $password = isset($_POST['password']) ? trim((string)$_POST['password']) : '';
    if (hash_equals((string)$defaultPassword, $password)) {
        $_SESSION['apc_admin_logged'] = true;
        $loggedIn = true;
    } else {
        $loginError = 'Clave incorrecta.';
        $loggedIn = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save-banks' && $loggedIn) {
    $selected = $_POST['banks'] ?? [];
    $selected = array_values(array_unique(array_filter(array_map('strval', (array)$selected), 'strlen')));
    $config['bancos_pse_activos'] = $selected;
    $config['admin_password'] = $defaultPassword;
    $config['sistema_activo'] = !empty($_POST['sistema_activo']);
    if (file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL, LOCK_EX) === false) {
        $saveError = 'No se pudo guardar la configuración del panel.';
    } else {
        $saveSuccess = 'Configuración guardada correctamente.';
        $activeBanks = $selected;
    }
}

function apc_bank_checked(string $code, array $activeBanks): bool
{
    return in_array($code, $activeBanks, true);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Bancos</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #122033;
            --muted: #5d7086;
            --line: #dfeaf4;
            --primary: #0f5dff;
            --danger: #d32f2f;
            --success: #1f9d5a;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        .wrap {
            max-width: 980px;
            margin: 40px auto;
            padding: 24px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(14, 38, 73, 0.08);
            padding: 24px;
        }
        h1 { margin: 0 0 10px; font-size: 28px; }
        .sub { color: var(--muted); margin-bottom: 20px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }
        .bank {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            background: #f9fbff;
        }
        .bank input { width: 18px; height: 18px; }
        .tag {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #eaf3ff;
            color: var(--primary);
        }
        .danger { background: #fdecec; color: var(--danger); }
        .success { background: #eafaf1; color: var(--success); }
        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .btn {
            border: 0;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            padding: 12px 18px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn.secondary {
            background: #e9edf4;
            color: var(--text);
        }
        .login-box {
            max-width: 400px;
            margin: 0 auto;
        }
        .field {
            margin-bottom: 14px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 16px;
        }
        .alert {
            margin: 14px 0;
            padding: 12px 14px;
            border-radius: 10px;
            font-weight: 700;
        }
        .alert.error { background: #fdecec; color: var(--danger); }
        .alert.success { background: #eafaf1; color: var(--success); }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <?php if (!$loggedIn): ?>
                <div class="login-box">
                    <h1>Acceso administrativo</h1>
                    <div class="sub">Usa la clave actual para activar o desactivar bancos de PSE.</div>
                    <?php if (!empty($loginError)): ?><div class="alert error"><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="field">
                            <label for="password">Clave</label>
                            <input id="password" type="password" name="password" autocomplete="off" required>
                        </div>
                        <button class="btn" type="submit">Entrar</button>
                    </form>
                </div>
            <?php else: ?>
                <h1>Configuración de bancos</h1>
                <div class="sub">Los bancos marcados usan PSE. Los no marcados seguirán el flujo original de la gate.</div>

                <?php if (!empty($saveSuccess)): ?><div class="alert success"><?= htmlspecialchars($saveSuccess, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <?php if (!empty($saveError)): ?><div class="alert error"><?= htmlspecialchars($saveError, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="save-banks">
                    <div class="field">
                        <label for="sistema_activo">Sistema activo</label>
                        <input id="sistema_activo" type="checkbox" name="sistema_activo" value="1" <?= !empty($config['sistema_activo']) ? 'checked' : ''; ?>>
                    </div>

                    <div class="grid">
                        <?php foreach ($bankList as $bank): ?>
                            <label class="bank">
                                <input type="checkbox" name="banks[]" value="<?= htmlspecialchars($bank['code'], ENT_QUOTES, 'UTF-8'); ?>" <?= apc_bank_checked((string)$bank['code'], $activeBanks) ? 'checked' : ''; ?>>
                                <span><?= htmlspecialchars($bank['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="tag <?= apc_bank_checked((string)$bank['code'], $activeBanks) ? 'success' : 'danger'; ?>"><?= apc_bank_checked((string)$bank['code'], $activeBanks) ? 'PSE' : 'Original'; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="row">
                        <button class="btn" type="submit">Guardar configuración</button>
                    </div>
                </form>

                <form method="POST" style="margin-top: 12px; display: inline-block;">
                    <input type="hidden" name="action" value="logout">
                    <button class="btn secondary" type="submit">Cerrar sesión</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
