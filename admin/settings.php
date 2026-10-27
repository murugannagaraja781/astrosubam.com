<?php
// admin/settings.php - Super Admin Visual Settings Dashboard
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Settings.php';
require_once __DIR__ . '/../helpers/AgoraToken.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appId = trim($_POST['agora_app_id'] ?? '');
    $appCert = trim($_POST['agora_app_certificate'] ?? '');
    $expire = trim($_POST['agora_token_expire_seconds'] ?? '3600');
    $prefix = trim($_POST['agora_channel_prefix'] ?? 'astrosubam_');

    if (!empty($appId)) {
        Settings::set('agora_app_id', $appId, 'Agora App ID');
        if (!empty($appCert)) {
            Settings::set('agora_app_certificate', $appCert, 'Agora App Certificate');
        }
        Settings::set('agora_token_expire_seconds', $expire, 'Token Expiry');
        Settings::set('agora_channel_prefix', $prefix, 'Channel Prefix');

        $message = "✅ Agora Configuration saved successfully to Database & .env file!";
        $messageType = "success";
    } else {
        $message = "❌ Agora App ID is required.";
        $messageType = "error";
    }
}

$currentAppId = Settings::get('agora_app_id', '');
$currentAppCert = Settings::get('agora_app_certificate', '');
$currentExpire = Settings::get('agora_token_expire_seconds', '3600');
$currentPrefix = Settings::get('agora_channel_prefix', 'astrosubam_');

$isConfigured = !empty($currentAppId) && $currentAppId !== 'YOUR_AGORA_APP_ID' && !empty($currentAppCert) && $currentAppCert !== 'YOUR_AGORA_APP_CERTIFICATE';
?>
<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroSubam Super Admin - Agora Call Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #090A15;
            --card-bg: #13162C;
            --card-border: #232746;
            --gold: #FFD700;
            --gold-hover: #F39C12;
            --text-main: #FFFFFF;
            --text-muted: #8E94B8;
            --green: #2ECC71;
            --red: #E74C3C;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background-color: #101225;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--card-border);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .brand img {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            border: 1px solid var(--gold);
        }
        .brand-title {
            font-family: 'Cinzel', serif;
            color: var(--gold);
            font-size: 20px;
            letter-spacing: 1px;
        }
        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .badge-success { background: rgba(46, 204, 113, 0.15); color: var(--green); border: 1px solid var(--green); }
        .badge-warning { background: rgba(243, 156, 18, 0.15); color: var(--gold-hover); border: 1px solid var(--gold-hover); }
        
        main {
            flex: 1;
            max-width: 900px;
            width: 100%;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }
        h1 {
            color: var(--gold);
            font-size: 24px;
            margin-bottom: 8px;
        }
        p.subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 24px;
        }
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .alert-success { background: rgba(46, 204, 113, 0.15); border: 1px solid var(--green); color: var(--green); }
        .alert-error { background: rgba(231, 76, 60, 0.15); border: 1px solid var(--red); color: var(--red); }
        
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #D3D7EE;
        }
        label span {
            color: var(--gold);
            font-size: 12px;
            font-weight: 400;
            margin-left: 6px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            background: #0B0D1E;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 14px 18px;
            color: #FFFFFF;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
        }
        .help-text {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
        }
        .btn-submit {
            background: linear-gradient(135deg, var(--gold), var(--gold-hover));
            color: #000000;
            border: none;
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
            margin-top: 10px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 215, 0, 0.3);
        }
        .sync-info {
            margin-top: 24px;
            padding: 16px;
            border-radius: 12px;
            background: rgba(255, 215, 0, 0.05);
            border: 1px dashed rgba(255, 215, 0, 0.25);
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        footer {
            text-align: center;
            padding: 24px;
            color: var(--text-muted);
            font-size: 13px;
            border-top: 1px solid var(--card-border);
            margin-top: auto;
        }
    </style>
</head>
<body>
    <header>
        <div class="brand">
            <img src="../assets/logo.jpg" alt="AstroSubam Logo">
            <div>
                <div class="brand-title">ASTROSUBAM</div>
                <div style="font-size: 11px; color: var(--text-muted);">Super Admin Control Center</div>
            </div>
        </div>
        <div>
            <?php if ($isConfigured): ?>
                <span class="badge badge-success">● Agora RTC Active</span>
            <?php else: ?>
                <span class="badge badge-warning">▲ Agora Keys Required</span>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="card">
            <h1>⚙️ Agora Voice & Video Call Configuration</h1>
            <p class="subtitle">Set up your Agora Project credentials below. Changes are automatically updated in both MySQL Database and <code>.env</code> file.</p>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST" action="settings.php">
                <div class="form-group">
                    <label>Agora App ID <span>(From Agora Console)</span></label>
                    <input type="text" name="agora_app_id" value="<?= htmlspecialchars($currentAppId) ?>" placeholder="e.g. 4a8b7c9d1e2f3a4b5c6d7e8f9a0b1c2d" required>
                    <div class="help-text">Unique 32-character Project Identifier provided in Agora.io Console.</div>
                </div>

                <div class="form-group">
                    <label>Agora App Certificate <span>(Primary Certificate)</span></label>
                    <input type="password" name="agora_app_certificate" value="<?= htmlspecialchars($currentAppCert) ?>" placeholder="e.g. 9f8e7d6c5b4a3a2b1c0d9e8f7a6b5c4d">
                    <div class="help-text">Used by the backend to securely generate Dynamic RTC Authentication Tokens with HMAC-SHA256.</div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Token Expiry (Seconds)</label>
                        <input type="text" name="agora_token_expire_seconds" value="<?= htmlspecialchars((string)$currentExpire) ?>" placeholder="3600">
                        <div class="help-text">Default: 3600 (1 Hour)</div>
                    </div>

                    <div class="form-group">
                        <label>Channel Prefix</label>
                        <input type="text" name="agora_channel_prefix" value="<?= htmlspecialchars($currentPrefix) ?>" placeholder="astrosubam_">
                        <div class="help-text">Default: astrosubam_</div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">💾 Save & Sync Agora Settings</button>

                <div class="sync-info">
                    <span style="font-size: 20px;">🔄</span>
                    <div>
                        <b>Automatic Dual Sync:</b> Clicking Save will immediately update the <code>system_settings</code> MySQL table AND update <code>.env</code> file so all backend APIs and Agora Call Engine pick up new keys instantly!
                    </div>
                </div>
            </form>
        </div>
    </main>

    <footer>
        AstroSubam Astrology Platform © 2026 • Connected to Agora RTC Global Network
    </footer>
</body>
</html>
