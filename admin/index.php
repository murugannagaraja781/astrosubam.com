<?php
// admin/index.php - Super Admin Visual Dashboard for AstroSubam
// Password Protected: Admin@2026
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Settings.php';
require_once __DIR__ . '/../helpers/AgoraToken.php';

$ADMIN_PASSWORD = 'Admin@2026';
$ADMIN_USER = 'admin';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['astro_admin_logged']);
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle Login
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $inputUser = trim($_POST['username'] ?? '');
    $inputPass = trim($_POST['password'] ?? '');

    if (($inputUser === $ADMIN_USER || $inputUser === 'admin@astrosubam.com') && $inputPass === $ADMIN_PASSWORD) {
        $_SESSION['astro_admin_logged'] = true;
        $_SESSION['astro_admin_user'] = $inputUser;
        header('Location: index.php');
        exit;
    } else {
        $loginError = 'தவறான பயனர் பெயர் அல்லது கடவுச்சொல் (Invalid Credentials)!';
    }
}

// Check Login Status
$isLoggedIn = !empty($_SESSION['astro_admin_logged']);

// If not logged in, render the Login Screen
if (!$isLoggedIn) {
?>
<!DOCTYPE html>
<html lang="ta-IN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroSubam | Super Admin Login</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Mukta+Malar:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        astro: {
                            base: '#041611',
                            surface: '#08231B',
                            card: '#0C2D23',
                            border: 'rgba(16, 185, 129, 0.25)',
                            emerald: '#10B981',
                            gold: '#FACC15',
                            goldAmber: '#F59E0B',
                            textMint: '#A7F3D0',
                        }
                    },
                    fontFamily: {
                        cinzel: ['Cinzel', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        tamil: ['"Mukta Malar"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-astro-base text-white flex items-center justify-center p-4" style="background-image: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.12) 0%, transparent 60%);">
    
    <div class="w-full max-w-md p-8 rounded-3xl bg-astro-surface/90 border-2 border-astro-gold/40 shadow-2xl backdrop-blur-xl relative">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl overflow-hidden mx-auto mb-4 border-2 border-astro-gold shadow-lg shadow-astro-gold/30">
                <img src="../assets/logo.jpg" alt="Logo" class="w-full h-full object-cover">
            </div>
            <h1 class="font-cinzel text-2xl font-bold tracking-wider text-astro-gold">ASTROSUBAM</h1>
            <p class="text-xs text-astro-textMint uppercase tracking-widest mt-1">Super Admin Control Center</p>
        </div>

        <?php if (!empty($loginError)): ?>
        <div class="mb-5 p-3.5 rounded-xl bg-red-500/20 border border-red-500/40 text-red-300 text-xs flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span><?= htmlspecialchars($loginError) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="index.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-white/70 mb-1.5">பயனர் பெயர் (Username)</label>
                <div class="relative">
                    <input type="text" name="username" value="admin" required class="w-full bg-astro-base border border-astro-emerald/30 rounded-xl px-4 py-3 pl-10 text-xs text-white focus:outline-none focus:border-astro-gold transition">
                    <i data-lucide="user" class="w-4 h-4 text-white/40 absolute left-3.5 top-3.5"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-white/70 mb-1.5">கடவுச்சொல் (Password)</label>
                <div class="relative">
                    <input type="password" name="password" placeholder="••••••••••••" required class="w-full bg-astro-base border border-astro-emerald/30 rounded-xl px-4 py-3 pl-10 text-xs text-white focus:outline-none focus:border-astro-gold transition">
                    <i data-lucide="lock" class="w-4 h-4 text-white/40 absolute left-3.5 top-3.5"></i>
                </div>
                <p class="text-[11px] text-white/40 mt-1">Pre-configured Password: <code class="text-astro-gold">Admin@2026</code></p>
            </div>

            <button type="submit" name="login_submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-astro-gold to-astro-goldAmber text-astro-base font-bold text-sm hover:opacity-95 transition shadow-lg shadow-astro-gold/20 flex items-center justify-center gap-2 mt-2">
                <i data-lucide="log-in" class="w-4 h-4"></i> உள்நுழைக (Login to Admin)
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="../index.php" class="text-xs text-white/50 hover:text-astro-gold flex items-center justify-center gap-1 transition">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> இணையதளத்திற்கு திரும்புக (Back to Website)
            </a>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
<?php
exit;
}

// ==============================================================================
// LOGGED IN SUPER ADMIN DASHBOARD LOGIC
// ==============================================================================

$db = getDb();
$toastMessage = '';
$toastType = 'success';

// 1. Action: Update Agora RTC Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_agora'])) {
    $appId = trim($_POST['agora_app_id'] ?? '');
    $appCert = trim($_POST['agora_app_certificate'] ?? '');
    $expire = trim($_POST['agora_token_expire_seconds'] ?? '3600');
    $prefix = trim($_POST['agora_channel_prefix'] ?? 'astrosubam_');

    if (!empty($appId)) {
        Settings::set('agora_app_id', $appId, 'Agora RTC App ID');
        if (!empty($appCert)) {
            Settings::set('agora_app_certificate', $appCert, 'Agora Certificate');
        }
        Settings::set('agora_token_expire_seconds', $expire, 'Token Expiry');
        Settings::set('agora_channel_prefix', $prefix, 'Channel Prefix');
        $toastMessage = '✅ Agora RTC அமைப்புகள் வெற்றிகரமாக சேமிக்கப்பட்டன!';
    } else {
        $toastMessage = '❌ Agora App ID அவசியமானது.';
        $toastType = 'error';
    }
}

// 2. Action: Toggle Astrologer Status
if (isset($_GET['toggle_astro_id'])) {
    $astroId = (int)$_GET['toggle_astro_id'];
    $currentStatus = $_GET['status'] ?? 'available';
    $newStatus = ($currentStatus === 'available') ? 'busy' : 'available';
    $db->prepare("UPDATE astrologers SET call_status = ? WHERE id = ?")->execute([$newStatus, $astroId]);
    header('Location: index.php?tab=astrologers');
    exit;
}

// 3. Action: Add New Astrologer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_astrologer'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $specialities = trim($_POST['specialities'] ?? 'Vedic Astrology');
    $languages = trim($_POST['languages'] ?? 'Tamil, English');
    $experience = (int)($_POST['experience'] ?? 10);
    $audioRate = (float)($_POST['audio_rate'] ?? 20);
    $videoRate = (float)($_POST['video_rate'] ?? 30);
    $chatRate = (float)($_POST['chat_rate'] ?? 10);
    $avatar = trim($_POST['avatar'] ?? 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80');

    if (!empty($name) && !empty($phone)) {
        $pwd = password_hash('subam123', PASSWORD_BCRYPT);
        $ins = $db->prepare("INSERT INTO astrologers 
            (name, phone, email, password_hash, avatar, languages, specialities, experience_years, audio_rate_min, video_rate_min, chat_rate_min, call_status, rating, total_reviews)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', 5.00, 1)");
        $ins->execute([$name, $phone, $email, $pwd, $avatar, $languages, $specialities, $experience, $audioRate, $videoRate, $chatRate]);
        $toastMessage = "✅ புதிய ஜோதிடர் '{$name}' வெற்றிகரமாக சேர்க்கப்பட்டார்!";
    }
}

// 4. Action: Manual Wallet Recharge for Client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recharge_user_wallet'])) {
    $userId = (int)$_POST['user_id'];
    $amt = (float)$_POST['amount'];
    if ($userId > 0 && $amt > 0) {
        $db->beginTransaction();
        $st = $db->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
        $st->execute([$userId]);
        $curr = (float)$st->fetchColumn();
        $newBal = $curr + $amt;
        $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")->execute([$newBal, $userId]);
        $db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_after, description, reference_id) VALUES (?, 'credit', ?, ?, 'Admin Manual Recharge', ?)")
           ->execute([$userId, $amt, $newBal, 'ADM_' . time()]);
        $db->commit();
        $toastMessage = "✅ பயனர் ID #{$userId}-க்கு ₹{$amt} வரவு வைக்கப்பட்டது! புதிய இருப்பு: ₹{$newBal}";
    }
}

$envFilePath = dirname(__DIR__) . '/.env';
$envBakPath = dirname(__DIR__) . '/.env.bak';

// 5. Action: Save .env Visual Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_env_visual'])) {
    $dbHost = trim($_POST['DB_HOST'] ?? '127.0.0.1');
    $dbPort = trim($_POST['DB_PORT'] ?? '3306');
    $dbName = trim($_POST['DB_NAME'] ?? '');
    $dbUser = trim($_POST['DB_USER'] ?? '');
    $dbPass = trim($_POST['DB_PASS'] ?? '');

    $agoraAppId = trim($_POST['AGORA_APP_ID'] ?? '');
    $agoraAppCert = trim($_POST['AGORA_APP_CERTIFICATE'] ?? '');
    $agoraExpire = trim($_POST['AGORA_TOKEN_EXPIRE_SECONDS'] ?? '3600');
    $agoraPrefix = trim($_POST['AGORA_CHANNEL_PREFIX'] ?? 'astrosubam_');

    $smsApiKey = trim($_POST['PING4SMS_API_KEY'] ?? '');
    $smsSender = trim($_POST['PING4SMS_SENDER_ID'] ?? 'ASTELV');
    $smsRoute = trim($_POST['PING4SMS_ROUTE'] ?? '4');
    $smsTemplate = trim($_POST['PING4SMS_TEMPLATE_ID'] ?? '');

    $jwtSecret = trim($_POST['JWT_SECRET'] ?? 'astrosubam_super_secure_divine_secret_key_2026');

    $newEnvContent = "# ==============================================\n" .
        "# ASTROSUBAM ENVIRONMENT CONFIGURATION\n" .
        "# Updated via Super Admin Dashboard: " . date('Y-m-d H:i:s') . "\n" .
        "# ==============================================\n\n" .
        "# Database Settings\n" .
        "DB_HOST={$dbHost}\n" .
        "DB_PORT={$dbPort}\n" .
        "DB_NAME={$dbName}\n" .
        "DB_USER={$dbUser}\n" .
        "DB_PASS={$dbPass}\n\n" .
        "# Agora RTC & Voice/Video Call Settings\n" .
        "AGORA_APP_ID={$agoraAppId}\n" .
        "AGORA_APP_CERTIFICATE={$agoraAppCert}\n" .
        "AGORA_TOKEN_EXPIRE_SECONDS={$agoraExpire}\n" .
        "AGORA_CHANNEL_PREFIX={$agoraPrefix}\n\n" .
        "# SMS Gateway Settings (Ping4SMS)\n" .
        "PING4SMS_API_KEY={$smsApiKey}\n" .
        "PING4SMS_SENDER_ID={$smsSender}\n" .
        "PING4SMS_ROUTE={$smsRoute}\n" .
        "PING4SMS_TEMPLATE_ID={$smsTemplate}\n\n" .
        "# Security & Tokens\n" .
        "JWT_SECRET={$jwtSecret}\n";

    if (file_exists($envFilePath)) {
        @copy($envFilePath, $envBakPath);
    }

    if (@file_put_contents($envFilePath, $newEnvContent) !== false) {
        if (!empty($agoraAppId)) Settings::set('agora_app_id', $agoraAppId, 'Agora RTC App ID');
        if (!empty($agoraAppCert)) Settings::set('agora_app_certificate', $agoraAppCert, 'Agora Certificate');
        Settings::set('agora_token_expire_seconds', $agoraExpire, 'Token Expiry');
        Settings::set('agora_channel_prefix', $agoraPrefix, 'Channel Prefix');
        if (!empty($smsApiKey)) Settings::set('ping4sms_api_key', $smsApiKey, 'Ping4SMS API Key');
        if (!empty($smsSender)) Settings::set('ping4sms_sender_id', $smsSender, 'Ping4SMS Sender ID');
        if (!empty($smsTemplate)) Settings::set('ping4sms_template_id', $smsTemplate, 'Ping4SMS Template ID');

        $toastMessage = '✅ .env கோப்பு மற்றும் சிஸ்டம் அமைப்புகள் வெற்றிகரமாக சேமிக்கப்பட்டன! (.env.bak காப்புப் பிரதி எடுக்கப்பட்டது)';
    } else {
        $toastMessage = '❌ .env கோப்பை எழுத அனுமதி இல்லை (Permission Denied). சர்வர் அனுமதியை சரிபார்க்கவும்.';
        $toastType = 'error';
    }
}

// 6. Action: Save .env Raw Textarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_env_raw'])) {
    $rawEnv = $_POST['raw_env_content'] ?? '';

    if (file_exists($envFilePath)) {
        @copy($envFilePath, $envBakPath);
    }

    if (@file_put_contents($envFilePath, $rawEnv) !== false) {
        $toastMessage = '✅ .env மூலக் கோப்பு (Raw file) வெற்றிகரமாக சேமிக்கப்பட்டது! (.env.bak காப்புப் பிரதி எடுக்கப்பட்டது)';
    } else {
        $toastMessage = '❌ .env கோப்பை எழுத முடியவில்லை.';
        $toastType = 'error';
    }
}

// 7. Action: Restore .env from Backup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_env_bak'])) {
    if (file_exists($envBakPath)) {
        if (@copy($envBakPath, $envFilePath)) {
            $toastMessage = '✅ .env.bak காப்புப் பிரதியிலிருந்து முந்தைய அமைப்புகள் மீட்டெடுக்கப்பட்டன!';
        } else {
            $toastMessage = '❌ காப்புப் பிரதியை மீட்டெடுக்க முடியவில்லை.';
            $toastType = 'error';
        }
    } else {
        $toastMessage = '❌ .env.bak காப்புப் பிரதி கோப்பு எதுவும் காணப்படவில்லை.';
        $toastType = 'error';
    }
}

// Data Queries for Dashboard
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAstrologers = (int)$db->query("SELECT COUNT(*) FROM astrologers")->fetchColumn();
$onlineAstrologers = (int)$db->query("SELECT COUNT(*) FROM astrologers WHERE call_status = 'available'")->fetchColumn();
$totalConsultations = (int)$db->query("SELECT COUNT(*) FROM consultation_sessions")->fetchColumn();
$totalRechargeAmt = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions WHERE type = 'credit'")->fetchColumn();

// List Queries
$astrologersList = $db->query("SELECT * FROM astrologers ORDER BY id DESC")->fetchAll();
$usersList = $db->query("SELECT id, name, phone, email, wallet_balance, created_at FROM users ORDER BY id DESC LIMIT 20")->fetchAll();
$sessionsList = $db->query("SELECT s.*, u.name as user_name, a.name as astro_name FROM consultation_sessions s 
    LEFT JOIN users u ON s.user_id = u.id 
    LEFT JOIN astrologers a ON s.astrologer_id = a.id 
    ORDER BY s.id DESC LIMIT 15")->fetchAll();
$transactionsList = $db->query("SELECT t.*, u.name as user_name, u.phone FROM wallet_transactions t 
    LEFT JOIN users u ON t.user_id = u.id 
    ORDER BY t.id DESC LIMIT 20")->fetchAll();

// Current Settings
$agoraAppId = Settings::get('agora_app_id', '');
$agoraAppCert = Settings::get('agora_app_certificate', '');
$agoraExpire = Settings::get('agora_token_expire_seconds', '3600');
$agoraPrefix = Settings::get('agora_channel_prefix', 'astrosubam_');

// .env File Parser for Visual Editor
$envRaw = file_exists($envFilePath) ? (string)file_get_contents($envFilePath) : '';
$envWritable = is_writable(file_exists($envFilePath) ? $envFilePath : dirname($envFilePath));
$envLastModified = file_exists($envFilePath) ? date('d M Y, h:i A', (int)filemtime($envFilePath)) : 'Not Found';
$hasBackup = file_exists($envBakPath);
$bakLastModified = $hasBackup ? date('d M Y, h:i A', (int)filemtime($envBakPath)) : 'None';

$envVars = [];
if (!empty($envRaw)) {
    $lines = explode("\n", $envRaw);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $envVars[trim($parts[0])] = trim($parts[1]);
        }
    }
}

$activeTab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="ta-IN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroSubam | Super Admin Dashboard</title>
    <link rel="icon" href="../assets/logo.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Mukta+Malar:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        astro: {
                            base: '#041611',
                            surface: '#08231B',
                            card: '#0C2D23',
                            border: 'rgba(16, 185, 129, 0.22)',
                            emerald: '#10B981',
                            emeraldLight: '#34D399',
                            gold: '#FACC15',
                            goldAmber: '#F59E0B',
                            goldLight: '#FEF08A',
                            textMint: '#A7F3D0',
                        }
                    },
                    fontFamily: {
                        cinzel: ['Cinzel', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        tamil: ['"Mukta Malar"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background-color: #041611;
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: radial-gradient(circle at 10% 10%, rgba(16, 185, 129, 0.08) 0%, transparent 40%),
                              radial-gradient(circle at 90% 10%, rgba(250, 204, 21, 0.08) 0%, transparent 40%);
            background-attachment: fixed;
        }
        .glass-card {
            background: rgba(8, 35, 27, 0.75);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(16, 185, 129, 0.22);
        }
        .btn-gold {
            background: linear-gradient(135deg, #FACC15 0%, #F59E0B 100%);
            color: #041611;
            font-weight: 700;
            transition: all 0.2s ease;
        }
        .btn-gold:hover {
            box-shadow: 0 4px 20px rgba(250, 204, 21, 0.4);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="min-h-screen flex antialiased">

    <!-- DESKTOP SIDEBAR -->
    <aside class="w-64 bg-astro-surface border-r border-astro-border flex flex-col flex-shrink-0 hidden md:flex">
        <!-- Brand Header -->
        <div class="h-20 px-6 flex items-center gap-3 border-b border-astro-border">
            <img src="../assets/logo.jpg" alt="Logo" class="w-10 h-10 rounded-xl border border-astro-gold">
            <div>
                <span class="font-cinzel text-lg font-bold text-astro-gold block leading-none">ASTROSUBAM</span>
                <span class="text-[10px] text-astro-emeraldLight font-semibold tracking-wider">SUPER ADMIN</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 text-xs font-semibold">
            <a href="?tab=overview" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition <?= $activeTab === 'overview' ? 'bg-astro-gold text-astro-base shadow-md' : 'text-white/70 hover:bg-astro-card hover:text-white' ?>">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> பொது பார்வை (Overview)
            </a>
            <a href="?tab=astrologers" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition <?= $activeTab === 'astrologers' ? 'bg-astro-gold text-astro-base shadow-md' : 'text-white/70 hover:bg-astro-card hover:text-white' ?>">
                <i data-lucide="users" class="w-4 h-4"></i> ஜோதிடர்கள் (Astrologers)
            </a>
            <a href="?tab=consultations" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition <?= $activeTab === 'consultations' ? 'bg-astro-gold text-astro-base shadow-md' : 'text-white/70 hover:bg-astro-card hover:text-white' ?>">
                <i data-lucide="phone-call" class="w-4 h-4"></i> ஆலோசனைகள் (Sessions)
            </a>
            <a href="?tab=wallet" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition <?= $activeTab === 'wallet' ? 'bg-astro-gold text-astro-base shadow-md' : 'text-white/70 hover:bg-astro-card hover:text-white' ?>">
                <i data-lucide="wallet" class="w-4 h-4"></i> வாலட் & வரவு (Ledger)
            </a>
            <a href="?tab=agora" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition <?= $activeTab === 'agora' ? 'bg-astro-gold text-astro-base shadow-md' : 'text-white/70 hover:bg-astro-card hover:text-white' ?>">
                <i data-lucide="settings" class="w-4 h-4"></i> Agora & System Config
            </a>
            <a href="?tab=env" class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition <?= $activeTab === 'env' ? 'bg-astro-gold text-astro-base shadow-md' : 'text-white/70 hover:bg-astro-card hover:text-white' ?>">
                <i data-lucide="file-code" class="w-4 h-4"></i> .env File Editor
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-astro-border space-y-2">
            <a href="../index.php" target="_blank" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-astro-card border border-astro-emerald/30 text-xs text-astro-textMint hover:bg-astro-surface transition">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i> நேரலை இணையதளம்
            </a>
            <a href="?action=logout" class="flex items-center justify-center gap-2 py-2 rounded-xl text-xs text-red-400 hover:bg-red-500/10 transition">
                <i data-lucide="log-out" class="w-3.5 h-3.5"></i> வெளியேறுக (Logout)
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <!-- Top Navigation Bar -->
        <header class="h-20 bg-astro-surface/90 border-b border-astro-border px-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="font-cinzel text-xl font-bold text-white capitalize">
                    <?php 
                    if ($activeTab === 'overview') echo '📊 நிர்வாகப் பொதுப் பார்வை (Admin Overview)';
                    elseif ($activeTab === 'astrologers') echo '🧘‍♂️ ஜோதிடர்கள் மேலாண்மை (Astrologers Directory)';
                    elseif ($activeTab === 'consultations') echo '📞 நேரலை ஆலோசனைகள் வரலாறு (Consultation Log)';
                    elseif ($activeTab === 'wallet') echo '💰 வாலட் பரிவர்த்தனைகள் (Wallet & Ledger)';
                    elseif ($activeTab === 'agora') echo '⚙️ Agora RTC & சிஸ்டம் அமைப்புகள்';
                    elseif ($activeTab === 'env') echo '📝 .env கோப்பு கட்டமைப்பு & திருத்தி (.env Editor)';
                    ?>
                </h2>
            </div>

            <div class="flex items-center gap-4">
                <span class="px-3 py-1 rounded-full bg-astro-emerald/20 text-astro-emerald text-xs font-bold border border-astro-emerald/40 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-astro-emerald animate-pulse"></span> Super Admin Online
                </span>
                <span class="text-xs text-white/60 hidden sm:inline">Logged as: <b class="text-astro-gold"><?= htmlspecialchars($_SESSION['astro_admin_user'] ?? 'admin') ?></b></span>
                <a href="?action=logout" class="md:hidden p-2 text-red-400">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                </a>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="flex-1 p-6 overflow-y-auto space-y-6">

            <!-- Toast Flash Notification -->
            <?php if (!empty($toastMessage)): ?>
            <div class="p-4 rounded-2xl <?= $toastType === 'success' ? 'bg-astro-emerald/20 border-astro-emerald/40 text-astro-textMint' : 'bg-red-500/20 border-red-500/40 text-red-300' ?> border text-xs font-semibold flex items-center gap-2">
                <i data-lucide="<?= $toastType === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-4 h-4 flex-shrink-0"></i>
                <span><?= htmlspecialchars($toastMessage) ?></span>
            </div>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- TAB 1: OVERVIEW METRICS DASHBOARD -->
            <!-- ============================================== -->
            <?php if ($activeTab === 'overview'): ?>
            <!-- 4 Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="glass-card p-5 rounded-2xl border-l-4 border-l-astro-gold">
                    <div class="flex items-center justify-between text-white/60 text-xs mb-2">
                        <span>மொத்த வருவாய் (Recharge Volume)</span>
                        <i data-lucide="dollar-sign" class="w-4 h-4 text-astro-gold"></i>
                    </div>
                    <span class="font-cinzel text-2xl font-bold text-astro-gold">₹<?= number_format($totalRechargeAmt, 2) ?></span>
                    <p class="text-[11px] text-astro-textMint mt-1">+100% பாதுகாப்பான நிதிப் பதிவேடு</p>
                </div>

                <div class="glass-card p-5 rounded-2xl border-l-4 border-l-astro-emerald">
                    <div class="flex items-center justify-between text-white/60 text-xs mb-2">
                        <span>மொத்த ஆலோசனைகள் (Calls/Chats)</span>
                        <i data-lucide="phone-call" class="w-4 h-4 text-astro-emerald"></i>
                    </div>
                    <span class="font-cinzel text-2xl font-bold text-white"><?= $totalConsultations ?></span>
                    <p class="text-[11px] text-white/50 mt-1">Agora RTC Real-time பில்லிங்</p>
                </div>

                <div class="glass-card p-5 rounded-2xl border-l-4 border-l-astro-goldAmber">
                    <div class="flex items-center justify-between text-white/60 text-xs mb-2">
                        <span>ஜோதிடர்கள் (Astrologers)</span>
                        <i data-lucide="user-check" class="w-4 h-4 text-astro-goldAmber"></i>
                    </div>
                    <span class="font-cinzel text-2xl font-bold text-astro-goldAmber"><?= $totalAstrologers ?></span>
                    <p class="text-[11px] text-astro-emeraldLight mt-1"><?= $onlineAstrologers ?> பேர் தற்போது Online</p>
                </div>

                <div class="glass-card p-5 rounded-2xl border-l-4 border-l-cyan-500">
                    <div class="flex items-center justify-between text-white/60 text-xs mb-2">
                        <span>பதிவு செய்த பக்தர்கள் (Users)</span>
                        <i data-lucide="users" class="w-4 h-4 text-cyan-400"></i>
                    </div>
                    <span class="font-cinzel text-2xl font-bold text-white"><?= $totalUsers ?></span>
                    <p class="text-[11px] text-white/50 mt-1">₹100 போனஸ் வரவு இணைக்கப்பட்டது</p>
                </div>
            </div>

            <!-- Recent Consultation Sessions Table -->
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-cinzel text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-astro-gold"></i> அண்மைய ஆலோசனைகள் (Recent Sessions)
                    </h3>
                    <a href="?tab=consultations" class="text-xs text-astro-gold hover:underline">அனைத்தையும் பார்க்க &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-astro-surface text-astro-gold uppercase text-[10px] tracking-wider border-b border-astro-border">
                            <tr>
                                <th class="p-3">Session ID</th>
                                <th class="p-3">பக்தர் (Client)</th>
                                <th class="p-3">ஜோதிடர் (Astrologer)</th>
                                <th class="p-3">வகை (Type)</th>
                                <th class="p-3">நேரம் (Sec)</th>
                                <th class="p-3">கட்டணம் (Total)</th>
                                <th class="p-3">நிலை (Status)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-astro-border/50">
                            <?php if (empty($sessionsList)): ?>
                            <tr>
                                <td colspan="7" class="p-4 text-center text-white/40">இன்னும் ஆலோசனைகள் தொடங்கப்படவில்லை.</td>
                            </tr>
                            <?php else: foreach ($sessionsList as $sess): ?>
                            <tr class="hover:bg-astro-card/50 transition">
                                <td class="p-3 font-mono text-[11px] text-astro-textMint"><?= htmlspecialchars($sess['session_id']) ?></td>
                                <td class="p-3 font-semibold text-white"><?= htmlspecialchars($sess['user_name'] ?? 'Devotee') ?></td>
                                <td class="p-3 text-astro-gold"><?= htmlspecialchars($sess['astro_name'] ?? 'Guruji') ?></td>
                                <td class="p-3 capitalize font-bold"><?= htmlspecialchars($sess['call_type']) ?></td>
                                <td class="p-3"><?= (int)$sess['duration_seconds'] ?>s</td>
                                <td class="p-3 font-bold text-astro-gold">₹<?= number_format((float)$sess['total_amount'], 2) ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $sess['status'] === 'completed' ? 'bg-astro-emerald/20 text-astro-emerald' : 'bg-amber-500/20 text-amber-300' ?>">
                                        <?= strtoupper($sess['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- TAB 2: ASTROLOGERS MANAGEMENT -->
            <!-- ============================================== -->
            <?php if ($activeTab === 'astrologers'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Add Astrologer Form (Left Column) -->
                <div class="lg:col-span-5 glass-card p-6 rounded-2xl h-fit">
                    <h3 class="font-cinzel text-base font-bold text-astro-gold mb-4 flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> புதிய ஜோதிடரை இணைக்க
                    </h3>
                    <form method="POST" action="index.php?tab=astrologers" class="space-y-3 text-xs">
                        <div>
                            <label class="block text-white/70 mb-1">ஜோதிடர் பெயர் (Astrologer Name)</label>
                            <input type="text" name="name" placeholder="எ.கா: Acharya Ramanan Sarma" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white focus:outline-none focus:border-astro-gold">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-white/70 mb-1">மொபைல் எண்</label>
                                <input type="tel" name="phone" placeholder="9876543210" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white focus:outline-none focus:border-astro-gold">
                            </div>
                            <div>
                                <label class="block text-white/70 mb-1">அனுபவம் (ஆண்டுகள்)</label>
                                <input type="number" name="experience" value="12" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white focus:outline-none focus:border-astro-gold">
                            </div>
                        </div>
                        <div>
                            <label class="block text-white/70 mb-1">சிறப்பு நிபுணத்துவம் (Specialities)</label>
                            <input type="text" name="specialities" value="Vedic Astrology, KP System, Marriage Compatibility" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white focus:outline-none focus:border-astro-gold">
                        </div>
                        <div>
                            <label class="block text-white/70 mb-1">மொழிகள் (Languages)</label>
                            <input type="text" name="languages" value="Tamil, English" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white focus:outline-none focus:border-astro-gold">
                        </div>
                        <!-- Rate Cards -->
                        <div class="grid grid-cols-3 gap-2 pt-1">
                            <div>
                                <label class="block text-[11px] text-astro-gold mb-1">Call Rate (₹/m)</label>
                                <input type="number" name="audio_rate" value="20" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2 text-white font-bold">
                            </div>
                            <div>
                                <label class="block text-[11px] text-astro-emerald mb-1">Video (₹/m)</label>
                                <input type="number" name="video_rate" value="30" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2 text-white font-bold">
                            </div>
                            <div>
                                <label class="block text-[11px] text-cyan-400 mb-1">Chat (₹/m)</label>
                                <input type="number" name="chat_rate" value="10" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2 text-white font-bold">
                            </div>
                        </div>
                        <div>
                            <label class="block text-white/70 mb-1">புகைப்பட URL (Avatar URL)</label>
                            <input type="url" name="avatar" value="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white focus:outline-none focus:border-astro-gold">
                        </div>

                        <button type="submit" name="add_astrologer" class="btn-gold w-full py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 mt-2">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> ஜோதிடரை இணைக்க (Add Astrologer)
                        </button>
                    </form>
                </div>

                <!-- Astrologers Directory List (Right Column) -->
                <div class="lg:col-span-7 glass-card p-6 rounded-2xl">
                    <h3 class="font-cinzel text-base font-bold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-astro-gold"></i> பதிவுபெற்ற ஜோதிடர்கள் பட்டியல் (<?= count($astrologersList) ?>)
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($astrologersList as $astro): ?>
                        <div class="p-4 rounded-xl bg-astro-base/70 border border-astro-border flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <img src="<?= htmlspecialchars($astro['avatar']) ?>" alt="Avatar" class="w-12 h-12 rounded-xl object-cover border border-astro-gold">
                                <div>
                                    <h4 class="font-bold text-sm text-white font-cinzel"><?= htmlspecialchars($astro['name']) ?></h4>
                                    <p class="text-[11px] text-astro-gold"><?= htmlspecialchars($astro['specialities']) ?></p>
                                    <p class="text-[10px] text-white/50"><?= (int)$astro['experience_years'] ?> yrs exp • Call: ₹<?= (int)$astro['audio_rate_min'] ?>/m | Video: ₹<?= (int)$astro['video_rate_min'] ?>/m</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="?tab=astrologers&toggle_astro_id=<?= (int)$astro['id'] ?>&status=<?= htmlspecialchars($astro['call_status']) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 <?= $astro['call_status'] === 'available' ? 'bg-astro-emerald/20 text-astro-emerald border border-astro-emerald/40 hover:bg-red-500/20 hover:text-red-300' : 'bg-red-500/20 text-red-300 border border-red-500/40 hover:bg-astro-emerald/20 hover:text-astro-emerald' ?>">
                                    <span class="w-2 h-2 rounded-full <?= $astro['call_status'] === 'available' ? 'bg-astro-emerald' : 'bg-red-500' ?>"></span>
                                    <?= $astro['call_status'] === 'available' ? 'Available (ஆன்லைன்)' : 'Busy (பிஸி)' ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- TAB 3: SESSIONS & CONSULTATIONS -->
            <!-- ============================================== -->
            <?php if ($activeTab === 'consultations'): ?>
            <div class="glass-card rounded-2xl p-6">
                <h3 class="font-cinzel text-base font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="phone-outgoing" class="w-4 h-4 text-astro-gold"></i> அனைத்து ஆலோசனை அமர்வுகள்
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-astro-surface text-astro-gold uppercase text-[10px] border-b border-astro-border">
                            <tr>
                                <th class="p-3">Session ID</th>
                                <th class="p-3">பக்தர் (Client)</th>
                                <th class="p-3">ஜோதிடர்</th>
                                <th class="p-3">அழைப்பு வகை</th>
                                <th class="p-3">நேரம்</th>
                                <th class="p-3">மொத்த கட்டணம்</th>
                                <th class="p-3">ஜோதிடர் பங்கு (80%)</th>
                                <th class="p-3">நிர்வாக பங்கு (20%)</th>
                                <th class="p-3">முடிந்த நேரம்</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-astro-border/50">
                            <?php if (empty($sessionsList)): ?>
                            <tr><td colspan="9" class="p-4 text-center text-white/40">தரவுகள் ஏதுமில்லை.</td></tr>
                            <?php else: foreach ($sessionsList as $sess): ?>
                            <tr class="hover:bg-astro-card/50 transition">
                                <td class="p-3 font-mono text-[11px] text-astro-textMint"><?= htmlspecialchars($sess['session_id']) ?></td>
                                <td class="p-3 font-semibold text-white"><?= htmlspecialchars($sess['user_name'] ?? 'Devotee') ?></td>
                                <td class="p-3 text-astro-gold"><?= htmlspecialchars($sess['astro_name'] ?? 'Guruji') ?></td>
                                <td class="p-3 capitalize font-bold"><?= htmlspecialchars($sess['call_type']) ?></td>
                                <td class="p-3"><?= (int)$sess['duration_seconds'] ?>s</td>
                                <td class="p-3 font-bold text-white">₹<?= number_format((float)$sess['total_amount'], 2) ?></td>
                                <td class="p-3 text-astro-emerald">₹<?= number_format((float)$sess['astrologer_share'], 2) ?></td>
                                <td class="p-3 text-astro-gold">₹<?= number_format((float)$sess['platform_share'], 2) ?></td>
                                <td class="p-3 text-white/50 text-[11px]"><?= htmlspecialchars($sess['ended_at'] ?? $sess['created_at']) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- TAB 4: WALLET & USERS MANAGEMENT -->
            <!-- ============================================== -->
            <?php if ($activeTab === 'wallet'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Manual Credit Form -->
                <div class="lg:col-span-4 glass-card p-6 rounded-2xl h-fit">
                    <h3 class="font-cinzel text-base font-bold text-astro-gold mb-4 flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> பக்தருக்கு வாலட் ரீசார்ஜ் செய்க
                    </h3>
                    <form method="POST" action="index.php?tab=wallet" class="space-y-4 text-xs">
                        <div>
                            <label class="block text-white/70 mb-1">பயனரைத் தேர்ந்தெடுக்கவும்</label>
                            <select name="user_id" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white focus:outline-none focus:border-astro-gold">
                                <?php foreach ($usersList as $u): ?>
                                <option value="<?= (int)$u['id'] ?>">ID #<?= (int)$u['id'] ?> - <?= htmlspecialchars($u['name']) ?> (₹<?= number_format((float)$u['wallet_balance'], 2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-white/70 mb-1">தொகை (₹ Amount)</label>
                            <input type="number" name="amount" value="500" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-bold text-sm">
                        </div>
                        <button type="submit" name="recharge_user_wallet" class="btn-gold w-full py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5">
                            <i data-lucide="zap" class="w-4 h-4"></i> உடனடி ரீசார்ஜ் வரவு வைக்க
                        </button>
                    </form>
                </div>

                <!-- Ledger History -->
                <div class="lg:col-span-8 glass-card p-6 rounded-2xl">
                    <h3 class="font-cinzel text-base font-bold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-astro-gold"></i> நிதிப் பதிவேடு (Wallet Ledger Transactions)
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-astro-surface text-astro-gold uppercase text-[10px] border-b border-astro-border">
                                <tr>
                                    <th class="p-3">Ref ID</th>
                                    <th class="p-3">பயனர்</th>
                                    <th class="p-3">வகை</th>
                                    <th class="p-3">தொகை</th>
                                    <th class="p-3">மீதமுள்ள இருப்பு</th>
                                    <th class="p-3">விவரம்</th>
                                    <th class="p-3">தேதி</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-astro-border/50">
                                <?php foreach ($transactionsList as $tx): ?>
                                <tr class="hover:bg-astro-card/50 transition">
                                    <td class="p-3 font-mono text-[10px] text-white/50"><?= htmlspecialchars($tx['reference_id'] ?? 'REF') ?></td>
                                    <td class="p-3 font-semibold text-white"><?= htmlspecialchars($tx['user_name'] ?? 'User') ?></td>
                                    <td class="p-3 font-bold <?= $tx['type'] === 'credit' ? 'text-astro-emerald' : 'text-red-400' ?>"><?= strtoupper($tx['type']) ?></td>
                                    <td class="p-3 font-bold text-white">₹<?= number_format((float)$tx['amount'], 2) ?></td>
                                    <td class="p-3 text-astro-gold font-bold">₹<?= number_format((float)$tx['balance_after'], 2) ?></td>
                                    <td class="p-3 text-[11px] text-white/70"><?= htmlspecialchars($tx['description']) ?></td>
                                    <td class="p-3 text-[10px] text-white/50"><?= htmlspecialchars($tx['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- TAB 5: AGORA RTC & SYSTEM SETTINGS -->
            <!-- ============================================== -->
            <?php if ($activeTab === 'agora'): ?>
            <div class="max-w-2xl mx-auto glass-card p-6 sm:p-8 rounded-3xl border border-astro-gold/30">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-astro-border">
                    <div class="w-12 h-12 rounded-2xl bg-astro-gold/20 flex items-center justify-center text-astro-gold border border-astro-gold/40">
                        <i data-lucide="video" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-cinzel text-lg font-bold text-white">Agora RTC Engine Configuration</h3>
                        <p class="text-xs text-white/60">நேரலை ஆடியோ மற்றும் வீடியோ அழைப்புகளுக்கான முக்கிய நற்சான்றிதழ்கள்</p>
                    </div>
                </div>

                <form method="POST" action="index.php?tab=agora" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-white/80 mb-1">Agora App ID (32-character Hex Key)</label>
                        <input type="text" name="agora_app_id" value="<?= htmlspecialchars($agoraAppId) ?>" placeholder="e.g. 4d8b9a7c6e5f1a2b3c4d5e6f7a8b9c0d" required class="w-full bg-astro-base border border-astro-border rounded-xl p-3 text-white font-mono focus:outline-none focus:border-astro-gold">
                    </div>

                    <div>
                        <label class="block font-semibold text-white/80 mb-1">Agora App Certificate (Token Signature Key)</label>
                        <input type="password" name="agora_app_certificate" value="<?= htmlspecialchars($agoraAppCert) ?>" placeholder="••••••••••••••••••••••••••••••••" class="w-full bg-astro-base border border-astro-border rounded-xl p-3 text-white font-mono focus:outline-none focus:border-astro-gold">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-white/80 mb-1">டோக்கன் செல்லுபடியாகும் நேரம் (வினாடிகள்)</label>
                            <input type="number" name="agora_token_expire_seconds" value="<?= htmlspecialchars($agoraExpire) ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-3 text-white focus:outline-none focus:border-astro-gold">
                        </div>
                        <div>
                            <label class="block font-semibold text-white/80 mb-1">சேனல் முன்னொட்டு (Channel Prefix)</label>
                            <input type="text" name="agora_channel_prefix" value="<?= htmlspecialchars($agoraPrefix) ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-3 text-white focus:outline-none focus:border-astro-gold">
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-astro-base border border-astro-border text-[11px] text-white/60">
                        💡 <b>Dual Sync Guard:</b> இந்த மதிப்புகளை சேமித்தால் MySQL <code>system_settings</code> அட்டவணை மற்றும் <code>.env</code> கோப்பு இரண்டிலும் ஒரே நேரத்தில் Zero-latency உடன் அப்டேட் செய்யப்படும்.
                    </div>

                    <button type="submit" name="update_agora" class="btn-gold w-full py-3.5 rounded-xl font-bold text-xs flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> அமைப்புகளை சேமிக்க (Save Settings)
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- TAB 6: .ENV FILE VISUAL & RAW EDITOR -->
            <!-- ============================================== -->
            <?php if ($activeTab === 'env'): ?>
            <div class="max-w-4xl mx-auto space-y-6">

                <!-- File Status Bar -->
                <div class="glass-card p-5 rounded-3xl border border-astro-gold/30 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-astro-gold/20 flex items-center justify-center text-astro-gold border border-astro-gold/40 flex-shrink-0">
                            <i data-lucide="file-code" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-cinzel text-base font-bold text-white flex items-center gap-2">
                                .env Configuration File
                                <?php if ($envWritable): ?>
                                <span class="bg-astro-emerald/20 text-astro-emerald text-[10px] font-bold px-2 py-0.5 rounded-full border border-astro-emerald/40 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-astro-emerald"></span> Writable
                                </span>
                                <?php else: ?>
                                <span class="bg-red-500/20 text-red-400 text-[10px] font-bold px-2 py-0.5 rounded-full border border-red-500/40">
                                    Read Only
                                </span>
                                <?php endif; ?>
                            </h3>
                            <p class="text-xs text-white/50 font-mono mt-0.5"><?= htmlspecialchars($envFilePath) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-white/70">
                        <span class="px-2.5 py-1 rounded-xl bg-astro-surface border border-astro-border">
                            கடைசியாக திருத்தப்பட்டது: <b class="text-astro-gold"><?= $envLastModified ?></b>
                        </span>
                        <?php if ($hasBackup): ?>
                        <span class="px-2.5 py-1 rounded-xl bg-astro-surface border border-astro-emerald/40 text-astro-textMint">
                            🛡️ Backup Active: <?= $bakLastModified ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mode Switcher Tabs -->
                <div class="flex items-center gap-2 border-b border-astro-border pb-2">
                    <button id="btn-env-mode-visual" onclick="switchEnvMode('visual')" class="px-4 py-2 rounded-xl text-xs font-bold transition bg-astro-gold text-astro-base flex items-center gap-2 shadow-sm">
                        <i data-lucide="sliders" class="w-3.5 h-3.5"></i> படிவ முறை (Smart Visual Form)
                    </button>
                    <button id="btn-env-mode-raw" onclick="switchEnvMode('raw')" class="px-4 py-2 rounded-xl text-xs font-bold transition text-white/70 hover:bg-astro-surface hover:text-white flex items-center gap-2">
                        <i data-lucide="code" class="w-3.5 h-3.5"></i> மூலக் குறியீடு (Raw Editor)
                    </button>
                </div>

                <!-- MODE A: SMART VISUAL FORM -->
                <div id="env-mode-visual" class="space-y-6">
                    <form method="POST" action="index.php?tab=env" class="space-y-6">

                        <!-- 1. Database Settings Card -->
                        <div class="glass-card p-6 rounded-3xl border border-astro-border space-y-4">
                            <div class="flex items-center gap-2 text-astro-gold border-b border-astro-border pb-3">
                                <i data-lucide="database" class="w-5 h-5"></i>
                                <h4 class="font-cinzel font-bold text-sm text-white">1. MySQL தரவுத்தள அமைப்புகள் (Database Config)</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                                <div>
                                    <label class="block text-white/70 mb-1">DB_HOST</label>
                                    <input type="text" name="DB_HOST" value="<?= htmlspecialchars($envVars['DB_HOST'] ?? '127.0.0.1') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-white/70 mb-1">DB_PORT</label>
                                    <input type="text" name="DB_PORT" value="<?= htmlspecialchars($envVars['DB_PORT'] ?? '3306') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-white/70 mb-1">DB_NAME</label>
                                    <input type="text" name="DB_NAME" value="<?= htmlspecialchars($envVars['DB_NAME'] ?? 'u170490412_subamadmin') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-white/70 mb-1">DB_USER</label>
                                    <input type="text" name="DB_USER" value="<?= htmlspecialchars($envVars['DB_USER'] ?? 'u170490412_subamastro') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-white/70 mb-1">DB_PASS</label>
                                    <div class="relative">
                                        <input type="password" id="input_db_pass" name="DB_PASS" value="<?= htmlspecialchars($envVars['DB_PASS'] ?? 'aGreen@2026a') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 pr-10 text-white font-mono focus:border-astro-gold focus:outline-none">
                                        <button type="button" onclick="togglePass('input_db_pass')" class="absolute right-3 top-2.5 text-white/40 hover:text-white">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Agora RTC Engine Card -->
                        <div class="glass-card p-6 rounded-3xl border border-astro-border space-y-4">
                            <div class="flex items-center gap-2 text-astro-gold border-b border-astro-border pb-3">
                                <i data-lucide="video" class="w-5 h-5"></i>
                                <h4 class="font-cinzel font-bold text-sm text-white">2. Agora RTC அழைப்பு அமைப்புகள் (Call, Video & Chat Engine)</h4>
                            </div>
                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block text-white/70 mb-1">AGORA_APP_ID (32-character Hexadecimal ID)</label>
                                    <input type="text" name="AGORA_APP_ID" value="<?= htmlspecialchars($envVars['AGORA_APP_ID'] ?? '8c0ca8c1b8bc403d814f8f86653e07c3') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-white/70 mb-1">AGORA_APP_CERTIFICATE (Primary Certificate for RTC Tokens)</label>
                                    <div class="relative">
                                        <input type="password" id="input_agora_cert" name="AGORA_APP_CERTIFICATE" value="<?= htmlspecialchars($envVars['AGORA_APP_CERTIFICATE'] ?? 'f42df2eb1af44ac0a3394ab2af3dd5ed') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 pr-10 text-white font-mono focus:border-astro-gold focus:outline-none">
                                        <button type="button" onclick="togglePass('input_agora_cert')" class="absolute right-3 top-2.5 text-white/40 hover:text-white">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-white/70 mb-1">AGORA_TOKEN_EXPIRE_SECONDS</label>
                                        <input type="number" name="AGORA_TOKEN_EXPIRE_SECONDS" value="<?= htmlspecialchars($envVars['AGORA_TOKEN_EXPIRE_SECONDS'] ?? '3600') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white focus:border-astro-gold focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-white/70 mb-1">AGORA_CHANNEL_PREFIX</label>
                                        <input type="text" name="AGORA_CHANNEL_PREFIX" value="<?= htmlspecialchars($envVars['AGORA_CHANNEL_PREFIX'] ?? 'astrosubam_') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white focus:border-astro-gold focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. SMS Gateway (Ping4SMS) Card -->
                        <div class="glass-card p-6 rounded-3xl border border-astro-border space-y-4">
                            <div class="flex items-center gap-2 text-astro-gold border-b border-astro-border pb-3">
                                <i data-lucide="message-square" class="w-5 h-5"></i>
                                <h4 class="font-cinzel font-bold text-sm text-white">3. SMS Gateway அமைப்புகள் (Ping4SMS DLT OTP)</h4>
                            </div>
                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block text-white/70 mb-1">PING4SMS_API_KEY</label>
                                    <input type="text" name="PING4SMS_API_KEY" value="<?= htmlspecialchars($envVars['PING4SMS_API_KEY'] ?? 'e1fc54301c0da93b95d3502a3151f420') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-white/70 mb-1">PING4SMS_SENDER_ID</label>
                                        <input type="text" name="PING4SMS_SENDER_ID" value="<?= htmlspecialchars($envVars['PING4SMS_SENDER_ID'] ?? 'ASTELV') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-white/70 mb-1">PING4SMS_ROUTE</label>
                                        <input type="text" name="PING4SMS_ROUTE" value="<?= htmlspecialchars($envVars['PING4SMS_ROUTE'] ?? '4') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-white/70 mb-1">PING4SMS_TEMPLATE_ID</label>
                                        <input type="text" name="PING4SMS_TEMPLATE_ID" value="<?= htmlspecialchars($envVars['PING4SMS_TEMPLATE_ID'] ?? '1677100000000387344') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-astro-surface border border-astro-emerald/30 text-[11px] text-astro-textMint">
                                    📱 <b>DLT Bypass Guard:</b> சோதனை கணக்குகளுக்கான மொபைல் எண்கள் (<code>8000000001</code>, <code>9000000001</code>, <code>8000000000</code>, <code>9000000000</code>) நிலையான OTP <code>0101</code> மூலம் உடனடியாக உள்நுழைய தானியங்கியாக அனுமதிக்கப்பட்டுள்ளன.
                                </div>
                            </div>
                        </div>

                        <!-- 4. Security & JWT Card -->
                        <div class="glass-card p-6 rounded-3xl border border-astro-border space-y-4">
                            <div class="flex items-center gap-2 text-astro-gold border-b border-astro-border pb-3">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                                <h4 class="font-cinzel font-bold text-sm text-white">4. பாதுகாப்பு & JWT இரகசிய சாவி (Security & Secret Key)</h4>
                            </div>
                            <div class="text-xs">
                                <label class="block text-white/70 mb-1">JWT_SECRET (Used to sign authentication tokens)</label>
                                <input type="text" name="JWT_SECRET" value="<?= htmlspecialchars($envVars['JWT_SECRET'] ?? 'astrosubam_super_secure_divine_secret_key_2026') ?>" required class="w-full bg-astro-base border border-astro-border rounded-xl p-2.5 text-white font-mono focus:border-astro-gold focus:outline-none">
                            </div>
                        </div>

                        <button type="submit" name="save_env_visual" class="btn-gold w-full py-4 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg shadow-astro-gold/20">
                            <i data-lucide="save" class="w-5 h-5"></i> படிவ மாறிகளை .env கோப்பில் சேமிக்க (Save All to .env)
                        </button>
                    </form>
                </div>

                <!-- MODE B: RAW CODE EDITOR -->
                <div id="env-mode-raw" class="hidden space-y-4">
                    <form method="POST" action="index.php?tab=env" class="space-y-4">
                        <div class="glass-card p-4 rounded-3xl border border-astro-border">
                            <div class="flex items-center justify-between text-xs text-white/60 mb-2 px-1">
                                <span>.env Direct Text Stream (UTF-8)</span>
                                <span>தானியங்கி .env.bak காப்புப் பிரதி செயல்படுத்தப்படும்</span>
                            </div>
                            <textarea name="raw_env_content" rows="18" class="w-full bg-astro-base border border-astro-border rounded-2xl p-4 text-xs text-white font-mono leading-relaxed focus:border-astro-gold focus:outline-none selection:bg-astro-gold selection:text-astro-base" spellcheck="false"><?= htmlspecialchars($envRaw) ?></textarea>
                        </div>

                        <button type="submit" name="save_env_raw" class="btn-gold w-full py-4 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg shadow-astro-gold/20">
                            <i data-lucide="file-check" class="w-5 h-5"></i> மூல .env கோப்பை நேரடியாகச் சேமிக்க (Save Raw .env File)
                        </button>
                    </form>
                </div>

                <!-- Backup & Rollback Card -->
                <?php if ($hasBackup): ?>
                <div class="p-5 rounded-3xl bg-astro-surface border border-astro-emerald/30 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i data-lucide="rotate-ccw" class="w-6 h-6 text-astro-gold flex-shrink-0"></i>
                        <div>
                            <h5 class="text-xs font-bold text-white">காப்புப் பிரதியை மீட்டெடுக்கவும் (.env.bak Rollback)</h5>
                            <p class="text-[11px] text-white/50">முந்தைய வேலை செய்யும் நிலைக்கு .env கோப்பை மீட்டமைக்கலாம் (காப்பு தேதி: <?= $bakLastModified ?>)</p>
                        </div>
                    </div>
                    <form method="POST" action="index.php?tab=env" onsubmit="return confirm('நிச்சயமாக முந்தைய .env.bak காப்புப் பிரதியை மீட்டெடுக்க விரும்புகிறீர்களா?');">
                        <button type="submit" name="restore_env_bak" class="px-4 py-2 rounded-xl bg-astro-card border border-astro-emerald/40 text-astro-textMint hover:bg-astro-surface text-xs font-bold transition flex items-center gap-1.5">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5"></i> Restore Backup
                        </button>
                    </form>
                </div>
                <?php endif; ?>

            </div>

            <script>
                function switchEnvMode(mode) {
                    const visualDiv = document.getElementById('env-mode-visual');
                    const rawDiv = document.getElementById('env-mode-raw');
                    const btnVisual = document.getElementById('btn-env-mode-visual');
                    const btnRaw = document.getElementById('btn-env-mode-raw');

                    if (mode === 'visual') {
                        visualDiv.classList.remove('hidden');
                        rawDiv.classList.add('hidden');
                        btnVisual.classList.add('bg-astro-gold', 'text-astro-base');
                        btnVisual.classList.remove('text-white/70');
                        btnRaw.classList.remove('bg-astro-gold', 'text-astro-base');
                        btnRaw.classList.add('text-white/70');
                    } else {
                        visualDiv.classList.add('hidden');
                        rawDiv.classList.remove('hidden');
                        btnRaw.classList.add('bg-astro-gold', 'text-astro-base');
                        btnRaw.classList.remove('text-white/70');
                        btnVisual.classList.remove('bg-astro-gold', 'text-astro-base');
                        btnVisual.classList.add('text-white/70');
                    }
                }

                function togglePass(id) {
                    const elem = document.getElementById(id);
                    if (elem) {
                        elem.type = elem.type === 'password' ? 'text' : 'password';
                    }
                }
            </script>
            <?php endif; ?>

        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

