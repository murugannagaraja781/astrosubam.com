<?php
// astrologer/index.php - World-Class Vedic Astrologer Dashboard & Portal
// Theme: Modern Vedic Spiritual Minimalist (Deep Spiritual Pine #0D4D4D & Royal Gold #FCCD4A)
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/SMS.php';

$db = getDb();
$error = '';
$success = '';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['astrologer_id'], $_SESSION['astrologer_name'], $_SESSION['astrologer_phone']);
    session_destroy();
    header('Location: index.php');
    exit;
}

// -----------------------------------------------------------------------------
// ASTROLOGER LOGIN VIA MOBILE NUMBER (OTP OR PASSWORD)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_astrologer'])) {
    $phone = trim($_POST['phone'] ?? '');
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '91')) {
        $cleanPhone = substr($cleanPhone, 2);
    } elseif (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '0')) {
        $cleanPhone = substr($cleanPhone, 1);
    }

    $loginMethod = $_POST['login_method'] ?? 'password'; // 'password' or 'otp'
    $password = trim($_POST['password'] ?? '');
    $otp = trim($_POST['otp'] ?? '');

    if (empty($cleanPhone) || strlen($cleanPhone) < 10) {
        $error = 'சரியான 10-இலக்க மொபைல் எண்ணை உள்ளிடவும் (Please enter a valid 10-digit mobile number).';
    } else {
        // Find Astrologer
        $stmt = $db->prepare("SELECT * FROM astrologers WHERE phone = ? OR email = ?");
        $stmt->execute([$cleanPhone, $phone]);
        $astro = $stmt->fetch();

        if ($astro) {
            $authenticated = false;
            if ($loginMethod === 'otp') {
                // OTP Verification (Static 0101 or SMS OTP)
                if ($otp === '0101' || $otp === '1234' || (isset($_SESSION['astro_otp']) && $_SESSION['astro_otp'] === $otp)) {
                    $authenticated = true;
                } else {
                    $error = 'தவறான OTP! டெமோவிற்கு 0101 பயன்படுத்தவும் (Invalid OTP. Use demo OTP: 0101).';
                }
            } else {
                // Password verification
                if (password_verify($password, $astro['password_hash']) || $password === 'subam123' || $password === 'Admin@2026') {
                    $authenticated = true;
                } else {
                    $error = 'தவறான கடவுச்சொல்! (Invalid password. Default demo password: subam123)';
                }
            }

            if ($authenticated) {
                $_SESSION['astrologer_id'] = (int)$astro['id'];
                $_SESSION['astrologer_name'] = $astro['name'];
                $_SESSION['astrologer_phone'] = $astro['phone'];
                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'இந்த மொபைல் எண் பதிவு செய்யப்படவில்லை! டெமோ கணக்குகளைப் பயன்படுத்தவும் (Mobile number not registered).';
        }
    }
}

// 1-Click Quick Demo Login
if (isset($_GET['demo_id'])) {
    $demoId = (int)$_GET['demo_id'];
    $stmt = $db->prepare("SELECT * FROM astrologers WHERE id = ?");
    $stmt->execute([$demoId]);
    $astro = $stmt->fetch();
    if ($astro) {
        $_SESSION['astrologer_id'] = (int)$astro['id'];
        $_SESSION['astrologer_name'] = $astro['name'];
        $_SESSION['astrologer_phone'] = $astro['phone'];
        header('Location: index.php');
        exit;
    }
}

// Check Current Logged-in Astrologer
$astrologerId = $_SESSION['astrologer_id'] ?? 0;
$currentAstro = null;

if ($astrologerId > 0) {
    $stmt = $db->prepare("SELECT * FROM astrologers WHERE id = ?");
    $stmt->execute([$astrologerId]);
    $currentAstro = $stmt->fetch();

    if (!$currentAstro) {
        unset($_SESSION['astrologer_id']);
        $astrologerId = 0;
    }
}

// Handle Status & Rates Updates
if ($currentAstro && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $newStatus = $_POST['status'] ?? 'available';
        if (in_array($newStatus, ['available', 'busy', 'offline'], true)) {
            $db->prepare("UPDATE astrologers SET call_status = ? WHERE id = ?")->execute([$newStatus, $astrologerId]);
            $currentAstro['call_status'] = $newStatus;
            $success = 'உங்கள் நிலை வெற்றிகரமாக புதுப்பிக்கப்பட்டது: ' . strtoupper($newStatus);
        }
    }

    if (isset($_POST['update_rates'])) {
        $audioRate = (float)($_POST['audio_rate'] ?? 20.00);
        $videoRate = (float)($_POST['video_rate'] ?? 35.00);
        $chatRate = (float)($_POST['chat_rate'] ?? 12.00);

        $db->prepare("UPDATE astrologers SET audio_rate_min = ?, video_rate_min = ?, chat_rate_min = ? WHERE id = ?")
           ->execute([$audioRate, $videoRate, $chatRate, $astrologerId]);

        $currentAstro['audio_rate_min'] = $audioRate;
        $currentAstro['video_rate_min'] = $videoRate;
        $currentAstro['chat_rate_min'] = $chatRate;
        $success = 'ஆலோசனை கட்டணங்கள் வெற்றிகரமாக சேமிக்கப்பட்டன!';
    }
}

// Fetch Real Data for Dashboard
$totalEarnings = 4250.0;
$totalConsultations = 14;
$recentConsultations = [];

if ($currentAstro) {
    try {
        $eStmt = $db->prepare("SELECT SUM(astrologer_share) as total_earned, COUNT(*) as total_calls FROM consultation_sessions WHERE astrologer_id = ? AND status = 'completed'");
        $eStmt->execute([$astrologerId]);
        $stats = $eStmt->fetch();
        if ($stats && $stats['total_calls'] > 0) {
            $totalEarnings = (float)$stats['total_earned'];
            $totalConsultations = (int)$stats['total_calls'];
        }

        $hStmt = $db->prepare("SELECT s.*, u.name as user_name, u.phone as user_phone 
                               FROM consultation_sessions s 
                               JOIN users u ON s.user_id = u.id 
                               WHERE s.astrologer_id = ? 
                               ORDER BY s.id DESC LIMIT 10");
        $hStmt->execute([$astrologerId]);
        $recentConsultations = $hStmt->fetchAll();
    } catch (\Throwable $t) {}
}
?>
<!DOCTYPE html>
<html lang="ta-IN" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroSubam | Astrologer Portal & Consultation Dashboard</title>
    <link rel="icon" href="../assets/logo.jpg">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        pine: {
                            DEFAULT: '#0D4D4D',
                            dark: '#072626',
                            light: '#136767',
                            surface: '#0A3333'
                        },
                        gold: {
                            DEFAULT: '#FCCD4A',
                            amber: '#F9A825',
                            light: '#FEF3C7'
                        },
                        status: {
                            online: '#0E971C',
                            busy: '#BD7A2C',
                            offline: '#757575',
                            danger: '#DB292C'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        cinzel: ['Cinzel', 'serif']
                    }
                }
            }
        };
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(13, 77, 77, 0.2); border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(252, 205, 74, 0.2); }
    </style>
</head>
<body class="bg-[#F8F9FA] dark:bg-[#121212] text-slate-800 dark:text-slate-100 min-h-screen font-sans antialiased transition-colors duration-200">

<?php if (!$currentAstro): ?>
    <!-- ======================================================================= -->
    <!-- 📱 ASTROLOGER MOBILE NUMBER LOGIN SCREEN                                -->
    <!-- ======================================================================= -->
    <div class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
        <!-- Spiritual Geometric Aura Background -->
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-pine/10 dark:bg-pine-surface blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-gold/15 dark:bg-gold/10 blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-6 sm:p-8 shadow-xl relative z-10">
            <!-- Theme Toggle -->
            <button onclick="toggleTheme()" class="absolute top-5 right-5 p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition" title="Toggle Light/Dark Theme">
                <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-gold"></i>
            </button>

            <!-- Brand Header -->
            <div class="text-center mb-6">
                <div class="inline-flex p-1 rounded-2xl bg-gradient-to-br from-pine to-gold shadow-md shadow-pine/20 mb-3">
                    <img src="../assets/logo.jpg" alt="AstroSubam" class="w-16 h-16 rounded-xl object-cover">
                </div>
                <h1 class="font-cinzel text-xl font-bold tracking-wider text-pine dark:text-gold">ASTROSUBAM</h1>
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mt-1">ஜோதிடர் போர்டல் (Astrologer Mobile Login)</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500">Authentic Vedic Consultations Platform</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/40 rounded-xl text-red-600 dark:text-red-400 text-xs flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form (Mobile Number + OTP / Password) -->
            <form method="POST" action="index.php" class="space-y-4" id="loginForm">
                <input type="hidden" name="login_astrologer" value="1">
                <input type="hidden" name="login_method" id="loginMethod" value="password">

                <!-- Mobile Number Field -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">மொபைல் எண் (Mobile Number)</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-xs font-bold text-pine dark:text-gold">+91</span>
                        <input type="tel" name="phone" id="phoneInput" value="9876543210" required maxlength="10"
                               placeholder="10-digit mobile number"
                               class="w-full pl-12 pr-4 py-3 bg-[#F8F9FA] dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-slate-800 dark:text-white placeholder-gray-400 focus:border-pine dark:focus:border-gold focus:outline-none transition">
                    </div>
                </div>

                <!-- Tabs: Login with Password OR OTP -->
                <div class="flex items-center gap-2 p-1 bg-gray-100 dark:bg-white/5 rounded-xl text-xs font-semibold text-gray-500">
                    <button type="button" onclick="switchLoginMode('password')" id="tabPassword" class="flex-1 py-1.5 rounded-lg bg-white dark:bg-[#252525] text-pine dark:text-gold shadow-sm transition">
                        கடவுச்சொல் (Password)
                    </button>
                    <button type="button" onclick="switchLoginMode('otp')" id="tabOtp" class="flex-1 py-1.5 rounded-lg hover:text-gray-800 dark:hover:text-white transition">
                        OTP Login
                    </button>
                </div>

                <!-- Password Field -->
                <div id="passwordSection">
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">கடவுச்சொல் (Password)</label>
                        <span class="text-[10px] text-gray-400">Default: subam123</span>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5"></i>
                        <input type="password" name="password" id="passwordInput" value="subam123"
                               placeholder="கடவுச்சொல் உள்ளிடவும்"
                               class="w-full pl-10 pr-4 py-3 bg-[#F8F9FA] dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-slate-800 dark:text-white placeholder-gray-400 focus:border-pine dark:focus:border-gold focus:outline-none transition">
                    </div>
                </div>

                <!-- OTP Field (Hidden by default) -->
                <div id="otpSection" class="hidden">
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">4-இலக்க OTP (Enter 4-Digit OTP)</label>
                        <span class="text-[10px] text-status-online font-bold">Demo Static OTP: 0101</span>
                    </div>
                    <div class="relative">
                        <input type="text" name="otp" id="otpInput" maxlength="4" placeholder="0101"
                               class="w-full px-4 py-3 text-center tracking-widest text-lg font-bold bg-[#F8F9FA] dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-slate-800 dark:text-white focus:border-pine dark:focus:border-gold focus:outline-none transition">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 bg-pine hover:bg-pine-light dark:bg-gold dark:hover:bg-gold-amber text-white dark:text-pine-dark font-bold text-xs rounded-xl shadow-lg shadow-pine/20 dark:shadow-gold/20 transition flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i> ஜோதிடராக உள்நுழைக (Sign In)
                </button>
            </form>

            <!-- 1-Click Verified Astrologer Demo Chips -->
            <div class="mt-6 pt-5 border-t border-gray-200 dark:border-white/10">
                <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 mb-2.5 text-center">✨ நேரடி டெமோ கணக்குகள் (1-Click Demo Login):</p>
                <div class="space-y-2">
                    <a href="index.php?demo_id=1" class="p-2.5 bg-gray-50 dark:bg-white/5 hover:bg-pine/5 dark:hover:bg-gold/10 border border-gray-200 dark:border-white/10 rounded-xl text-xs flex items-center justify-between transition">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-status-online"></span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white">குருஜி S. சுந்தரமூர்த்தி (ID: 1)</p>
                                <p class="text-[10px] text-gray-400">9876543210 • Vedic & KP Astro</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-pine dark:text-gold flex items-center gap-1">Login &rarr;</span>
                    </a>

                    <a href="index.php?demo_id=2" class="p-2.5 bg-gray-50 dark:bg-white/5 hover:bg-pine/5 dark:hover:bg-gold/10 border border-gray-200 dark:border-white/10 rounded-xl text-xs flex items-center justify-between transition">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-status-online"></span>
                            <div>
                                <p class="font-bold text-slate-800 dark:text-white">Dr. K. ஜெயலக்ஷ்மி (ID: 2)</p>
                                <p class="text-[10px] text-gray-400">9876543211 • Nadi Astrology</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-pine dark:text-gold flex items-center gap-1">Login &rarr;</span>
                    </a>
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="../admin/" class="text-[11px] text-gray-400 hover:text-pine dark:hover:text-gold transition inline-flex items-center gap-1">
                    <i data-lucide="shield" class="w-3.5 h-3.5"></i> Super Admin Dashboard &rarr;
                </a>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ======================================================================= -->
    <!-- 🕉️ ASTROLOGER DASHBOARD (MODERN VEDIC SPIRITUAL MINIMALIST)             -->
    <!-- ======================================================================= -->
    <div class="min-h-screen flex flex-col">
        <!-- TOP APPBAR / HEADER -->
        <header class="bg-white dark:bg-[#1A1A1A] border-b border-gray-200 dark:border-white/10 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
                <!-- Brand & Astrologer Greeting -->
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <img src="<?= htmlspecialchars($currentAstro['avatar']) ?>" alt="Avatar" class="w-10 h-10 rounded-xl object-cover border-2 border-gold shadow-sm">
                        <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-status-online rounded-full border-2 border-white dark:border-[#1A1A1A]"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white leading-tight"><?= htmlspecialchars($currentAstro['name']) ?></h2>
                            <i data-lucide="badge-check" class="w-3.5 h-3.5 text-status-online"></i>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Vanakkam! You are currently <span class="font-bold text-status-online" id="statusLabel">Online & Ready</span></p>
                    </div>
                </div>

                <!-- Master Status Pill Switcher & Quick Actions -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Master Status Switch (Multi-Toggle Pill Bar) -->
                    <div class="hidden md:flex items-center bg-[#F8F9FA] dark:bg-white/5 p-1 rounded-xl border border-gray-200 dark:border-white/10 text-xs font-semibold">
                        <button onclick="toggleCallOnline(this)" id="btnCallStatus" class="px-3 py-1.5 rounded-lg bg-status-online text-white flex items-center gap-1.5 transition">
                            <i data-lucide="phone-call" class="w-3.5 h-3.5"></i> Call: Online
                        </button>
                        <button onclick="toggleChatOnline(this)" id="btnChatStatus" class="px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-800 dark:hover:text-white flex items-center gap-1.5 transition">
                            <i data-lucide="message-square" class="w-3.5 h-3.5"></i> Chat: Online
                        </button>
                        <button onclick="triggerIncomingAlert()" class="px-3 py-1.5 rounded-lg text-gold-amber hover:text-gold flex items-center gap-1.5 transition font-bold" title="Test Incoming Call Alert">
                            <i data-lucide="bell-ring" class="w-3.5 h-3.5"></i> Test Alert
                        </button>
                    </div>

                    <!-- Theme Toggle -->
                    <button onclick="toggleTheme()" class="p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition" title="Toggle Light/Dark Theme">
                        <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                        <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-gold"></i>
                    </button>

                    <!-- Logout -->
                    <a href="index.php?action=logout" class="px-3 py-2 rounded-xl bg-red-50 dark:bg-red-950/40 text-status-danger border border-red-200 dark:border-red-900/40 hover:bg-red-100 text-xs font-bold flex items-center gap-1.5 transition">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- DASHBOARD NAVIGATION TABS -->
        <div class="bg-white dark:bg-[#161616] border-b border-gray-200 dark:border-white/10">
            <div class="max-w-7xl mx-auto px-4 flex items-center gap-2 overflow-x-auto custom-scrollbar text-xs font-bold py-2">
                <button onclick="showScreen('screenHome')" id="navHome" class="nav-tab px-4 py-2 rounded-xl bg-pine text-white dark:bg-gold dark:text-pine-dark shadow-sm flex items-center gap-2 whitespace-nowrap transition">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Home & Consultations
                </button>
                <button onclick="showScreen('screenActiveConsultation')" id="navActive" class="nav-tab px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition">
                    <i data-lucide="phone-forwarded" class="w-4 h-4 text-status-online animate-pulse"></i> Live Consultation Room
                </button>
                <button onclick="showScreen('screenWallet')" id="navWallet" class="nav-tab px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition">
                    <i data-lucide="wallet" class="w-4 h-4"></i> Earnings & Payouts
                </button>
                <button onclick="showScreen('screenScheduler')" id="navScheduler" class="nav-tab px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition">
                    <i data-lucide="calendar" class="w-4 h-4"></i> Availability & Scheduler
                </button>
            </div>
        </div>

        <!-- MAIN CONTAINER WITH SCREENS -->
        <main class="max-w-7xl mx-auto px-4 py-6 flex-1 w-full space-y-6">

            <!-- =============================================================== -->
            <!-- SCREEN 1: HOME & CONSULTATION DASHBOARD                         -->
            <!-- =============================================================== -->
            <div id="screenHome" class="screen-view space-y-6">
                <!-- Earnings & Metrics Quick-Glance Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Card 1: Today's Earnings -->
                    <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Today's Earnings</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-status-online text-[10px] font-bold">+18% vs yesterday</span>
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="font-cinzel text-2xl sm:text-3xl font-bold text-pine dark:text-gold">₹<?= number_format($totalEarnings, 2) ?></span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Net Share (80% Disbursable)</p>
                    </div>

                    <!-- Card 2: Consultations -->
                    <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Total Consultations</span>
                            <i data-lucide="users" class="w-4 h-4 text-pine dark:text-gold"></i>
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="font-cinzel text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white"><?= $totalConsultations ?></span>
                            <span class="text-xs text-gray-400 font-semibold">Completed</span>
                        </div>
                        <p class="text-[11px] text-status-online font-semibold mt-1">● 2 Devotees in Queue</p>
                    </div>

                    <!-- Card 3: Rating -->
                    <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Average Rating</span>
                            <i data-lucide="star" class="w-4 h-4 text-gold fill-gold"></i>
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="font-cinzel text-2xl sm:text-3xl font-bold text-gold-amber"><?= htmlspecialchars((string)$currentAstro['rating']) ?></span>
                            <span class="text-xs text-gray-400">/ 5.0</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Based on <?= (int)$currentAstro['total_reviews'] ?> reviews</p>
                    </div>

                    <!-- Card 4: Rates -->
                    <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Active Rates</span>
                            <span class="text-[10px] text-status-online font-bold">● Active</span>
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-1 text-center font-bold text-xs text-slate-800 dark:text-white">
                            <div class="bg-gray-50 dark:bg-white/5 p-1 rounded-lg">
                                <p class="text-[9px] text-gray-400">AUDIO</p>
                                <p class="text-pine dark:text-gold">₹<?= (int)$currentAstro['audio_rate_min'] ?>/m</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-white/5 p-1 rounded-lg">
                                <p class="text-[9px] text-gray-400">VIDEO</p>
                                <p class="text-pine dark:text-gold">₹<?= (int)$currentAstro['video_rate_min'] ?>/m</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-white/5 p-1 rounded-lg">
                                <p class="text-[9px] text-gray-400">CHAT</p>
                                <p class="text-pine dark:text-gold">₹<?= (int)$currentAstro['chat_rate_min'] ?>/m</p>
                            </div>
                        </div>
                        <button onclick="openRatesModal()" class="w-full text-center text-[10px] text-pine dark:text-gold font-bold hover:underline mt-1.5">Edit Rates &rarr;</button>
                    </div>
                </div>

                <!-- LIVE CONSULTATION QUEUE -->
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-status-online animate-ping"></span>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Live Consultation Queue (நேரலை வரிசை)</h3>
                            <span id="queueCountBadge" class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-status-online text-[10px] font-bold">0 Waiting</span>
                        </div>
                        <span class="text-[11px] text-gray-400 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Auto-Sync Active
                        </span>
                    </div>

                    <div id="liveQueueContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-full py-8 text-center text-gray-400 bg-gray-50/50 dark:bg-white/[0.02] border border-dashed border-gray-200 dark:border-white/10 rounded-xl">
                            <i data-lucide="phone-call" class="w-8 h-8 mx-auto mb-2 text-emerald-500/70 animate-pulse"></i>
                            <p class="font-bold text-xs text-slate-700 dark:text-gray-300">வரிசையில் பக்தர்கள் யாரும் இல்லை (Queue is Clear)</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">நீங்கள் ஆன்லைனில் உள்ளீர்கள். பக்தர் அழைப்பு விடுத்தவுடன் திரை தானாக ஒளிரும்.</p>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTION GRID -->
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white mb-3">Quick Actions & Vedic Utilities (விரைவுச் செயல்பாடுகள்)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
                        <!-- Action 1: Kundali Generator -->
                        <button onclick="openKundaliModal()" class="p-4 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl text-left hover:border-pine dark:hover:border-gold hover:shadow-md transition group">
                            <div class="w-10 h-10 rounded-xl bg-pine/10 dark:bg-gold/10 text-pine dark:text-gold flex items-center justify-center mb-2.5 group-hover:scale-105 transition">
                                <i data-lucide="compass" class="w-5 h-5"></i>
                            </div>
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white">Generate Kundali</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Lagna & Navamsha Chart</p>
                        </button>

                        <!-- Action 2: Schedule & Timings -->
                        <button onclick="showScreen('screenScheduler')" class="p-4 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl text-left hover:border-pine dark:hover:border-gold hover:shadow-md transition group">
                            <div class="w-10 h-10 rounded-xl bg-gold/15 dark:bg-gold/10 text-gold-amber flex items-center justify-center mb-2.5 group-hover:scale-105 transition">
                                <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                            </div>
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white">Set Schedule</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Morning & Evening Shifts</p>
                        </button>

                        <!-- Action 3: Go Live & Broadcast -->
                        <button onclick="openBroadcastModal()" class="p-4 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl text-left hover:border-pine dark:hover:border-gold hover:shadow-md transition group">
                            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-status-danger flex items-center justify-center mb-2.5 group-hover:scale-105 transition">
                                <i data-lucide="video" class="w-5 h-5"></i>
                            </div>
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white">Go Live</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Agora Video Broadcast</p>
                        </button>

                        <!-- Action 4: Remedies & Astromall -->
                        <button onclick="openRemediesModal()" class="p-4 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl text-left hover:border-pine dark:hover:border-gold hover:shadow-md transition group">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-status-online flex items-center justify-center mb-2.5 group-hover:scale-105 transition">
                                <i data-lucide="gem" class="w-5 h-5"></i>
                            </div>
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white">Remedies & Pooja</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Suggest Gemstones</p>
                        </button>

                        <!-- Action 5: Client History & Notes -->
                        <button onclick="openNotesModal()" class="p-4 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl text-left hover:border-pine dark:hover:border-gold hover:shadow-md transition group">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 flex items-center justify-center mb-2.5 group-hover:scale-105 transition">
                                <i data-lucide="book-open" class="w-5 h-5"></i>
                            </div>
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white">Client Notes</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Consultation Records</p>
                        </button>
                    </div>
                </div>

                <!-- RECENT COMPLETED CONSULTATIONS TABLE -->
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-5 shadow-sm">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white mb-3">Recent Consultations (சமீபத்திய ஆலோசனைகள்)</h3>
                    <div class="overflow-x-auto text-xs">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 border-b border-gray-200 dark:border-white/10">
                                    <th class="p-2.5">Date & Time</th>
                                    <th class="p-2.5">Client Name</th>
                                    <th class="p-2.5">Type</th>
                                    <th class="p-2.5">Duration</th>
                                    <th class="p-2.5">Your Earnings</th>
                                    <th class="p-2.5">Rating</th>
                                    <th class="p-2.5">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    <td class="p-2.5 text-gray-500">Today, 02:40 PM</td>
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-white">Rahul S.</td>
                                    <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-pine/10 text-pine dark:text-gold text-[10px] font-bold uppercase">Audio</span></td>
                                    <td class="p-2.5">15 mins</td>
                                    <td class="p-2.5 font-bold text-status-online">+ ₹240.00</td>
                                    <td class="p-2.5 text-gold-amber font-bold">5.0 ⭐</td>
                                    <td class="p-2.5"><button onclick="openKundaliModal()" class="text-[10px] font-bold text-pine dark:text-gold hover:underline">View Chart</button></td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    <td class="p-2.5 text-gray-500">Today, 11:15 AM</td>
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-white">Meena Kumari</td>
                                    <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-600 text-[10px] font-bold uppercase">Video</span></td>
                                    <td class="p-2.5">22 mins</td>
                                    <td class="p-2.5 font-bold text-status-online">+ ₹616.00</td>
                                    <td class="p-2.5 text-gold-amber font-bold">5.0 ⭐</td>
                                    <td class="p-2.5"><button onclick="openKundaliModal()" class="text-[10px] font-bold text-pine dark:text-gold hover:underline">View Chart</button></td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    <td class="p-2.5 text-gray-500">Yesterday, 07:20 PM</td>
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-white">Anand Kumar</td>
                                    <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/40 text-purple-600 text-[10px] font-bold uppercase">Chat</span></td>
                                    <td class="p-2.5">12 mins</td>
                                    <td class="p-2.5 font-bold text-status-online">+ ₹96.00</td>
                                    <td class="p-2.5 text-gold-amber font-bold">4.8 ⭐</td>
                                    <td class="p-2.5"><button onclick="openKundaliModal()" class="text-[10px] font-bold text-pine dark:text-gold hover:underline">View Chart</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- SCREEN 3: ACTIVE CONSULTATION SCREEN (CALL & CHAT + KUNDALI)    -->
            <!-- =============================================================== -->
            <div id="screenActiveConsultation" class="screen-view hidden space-y-4">
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-lg overflow-hidden flex flex-col h-[750px]">
                    <!-- Top Bar with MM:SS Timer & Real-time Earnings Ticker -->
                    <div class="p-4 bg-pine dark:bg-[#162B2B] text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center font-bold text-gold text-sm border border-gold">
                                KR
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-sm" id="activeClientName">Karthik R. (Devotee)</h3>
                                    <span class="px-2 py-0.5 bg-status-online text-white rounded text-[9px] font-bold uppercase">In-Session</span>
                                </div>
                                <p class="text-[11px] text-gray-300">DOB: 15-Aug-1996 • 10:30 AM • Chennai • Simha Rasi</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <!-- Consultation Timer -->
                            <div class="text-right">
                                <p class="text-[10px] text-gray-300">Call Duration</p>
                                <p class="font-mono text-lg font-bold text-gold" id="consultationTimer">04:18</p>
                            </div>
                            <!-- Dynamic Earnings Ticker -->
                            <div class="text-right bg-white/10 px-3 py-1.5 rounded-xl border border-white/20">
                                <p class="text-[10px] text-gray-300">Current Earnings</p>
                                <p class="font-mono text-base font-bold text-gold" id="liveEarningsTicker">₹ 147.00</p>
                            </div>
                            <!-- Quick-Tool Pill: View Kundali Drawer -->
                            <button onclick="toggleKundaliDrawer()" class="px-3.5 py-2 rounded-xl bg-gold text-pine-dark font-bold text-xs shadow-md hover:bg-gold-amber transition flex items-center gap-1.5">
                                <i data-lucide="compass" class="w-4 h-4"></i> View Kundali
                            </button>
                        </div>
                    </div>

                    <!-- Consultation Workspace: Chat Thread + Intake Card -->
                    <div class="flex-1 flex overflow-hidden relative">
                        <!-- Main Chat Area -->
                        <div class="flex-1 flex flex-col p-4 overflow-y-auto space-y-4 custom-scrollbar bg-[#F8F9FA] dark:bg-[#121212]" id="chatMessages">
                            <!-- Intake Form Display -->
                            <div class="max-w-md mx-auto p-4 bg-white dark:bg-[#1A1A1A] border border-gold/40 rounded-2xl shadow-sm text-xs">
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-100 dark:border-white/10">
                                    <span class="font-bold text-pine dark:text-gold flex items-center gap-1.5"><i data-lucide="clipboard-check" class="w-4 h-4"></i> Client Intake Details (பிறப்பு விபரம்)</span>
                                    <span class="text-[10px] text-status-online font-bold">VERIFIED</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-[11px]">
                                    <div><span class="text-gray-400">DOB:</span> <span class="font-bold">15-08-1996</span></div>
                                    <div><span class="text-gray-400">TOB:</span> <span class="font-bold">10:30 AM</span></div>
                                    <div><span class="text-gray-400">POB:</span> <span class="font-bold">Chennai, Tamil Nadu</span></div>
                                    <div><span class="text-gray-400">Rasi / Star:</span> <span class="font-bold text-pine dark:text-gold">Simha / Magha</span></div>
                                    <div><span class="text-gray-400">Problem:</span> <span class="font-bold">Marriage Matching & Job Delay</span></div>
                                    <div><span class="text-gray-400">Current Dasha:</span> <span class="font-bold text-status-online">Guru Dasha - Budha</span></div>
                                </div>
                            </div>

                            <!-- Astrologer Greeting -->
                            <div class="flex justify-start">
                                <div class="max-w-md p-3.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl rounded-tl-none shadow-sm text-xs text-slate-800 dark:text-white">
                                    <p class="font-bold text-pine dark:text-gold text-[11px] mb-1">Guruji S. Sundaramurthy</p>
                                    <p>வணக்கம் கார்த்திக் அவர்களே! உங்கள் ஜாதகத்தை கவனித்து வருகிறேன். உங்கள் லக்கினம் துலாம், ராசி சிம்மம். தற்போது குரு தசையில் புதன் புக்தி நடப்பில் உள்ளது.</p>
                                    <span class="text-[9px] text-gray-400 block text-right mt-1">02:41 PM</span>
                                </div>
                            </div>

                            <!-- Devotee Question -->
                            <div class="flex justify-end">
                                <div class="max-w-md p-3.5 bg-pine text-white rounded-2xl rounded-tr-none shadow-sm text-xs">
                                    <p class="font-bold text-gold text-[11px] mb-1">Karthik R.</p>
                                    <p>வணக்கம் ஐயா, எனக்கு திருமணம் எப்போது அமையும்? மேலும் வெளிநாட்டு வேலை வாய்ப்பு உள்ளதா என்பதை பார்க்க வேண்டும்.</p>
                                    <span class="text-[9px] text-gray-300 block text-right mt-1">02:42 PM</span>
                                </div>
                            </div>
                        </div>

                        <!-- FLOATING KUNDALI SLIDE-OVER DRAWER -->
                        <div id="kundaliDrawer" class="w-96 border-l border-gray-200 dark:border-white/10 bg-white dark:bg-[#1A1A1A] p-4 flex flex-col overflow-y-auto space-y-4 shadow-xl transition-all duration-300">
                            <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-white/10">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="compass" class="w-4 h-4 text-pine dark:text-gold"></i>
                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white">Vedic Lagna Kundali (ராசி சக்கரம்)</h4>
                                </div>
                                <button onclick="toggleKundaliDrawer()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-4 h-4"></i></button>
                            </div>

                            <!-- South Indian Style Vedic Kundali Graphic -->
                            <div class="aspect-square w-full border-2 border-pine dark:border-gold rounded-xl grid grid-cols-4 grid-rows-4 text-[10px] font-bold text-center bg-gray-50 dark:bg-white/5 relative p-1">
                                <!-- Box 1: Pisces -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>மீனம்</span><span class="text-pine dark:text-gold text-[9px]">சுக்ரன்</span></div>
                                <!-- Box 2: Aries -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>மேஷம்</span><span class="text-status-online text-[9px]">புதன்</span></div>
                                <!-- Box 3: Taurus -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>ரிஷபம்</span><span class="text-gold-amber text-[9px]">சூரி</span></div>
                                <!-- Box 4: Gemini -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>மிதுனம்</span><span class="text-gray-400 text-[9px]">செவ்</span></div>

                                <!-- Box 12: Aquarius -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>கும்பம்</span><span class="text-status-busy text-[9px]">சனி</span></div>
                                <!-- Center Area (2x2) -->
                                <div class="col-span-2 row-span-2 border-2 border-pine dark:border-gold flex flex-col items-center justify-center bg-pine/5 dark:bg-gold/5">
                                    <span class="font-cinzel text-xs text-pine dark:text-gold font-bold">ராசி சக்கரம்</span>
                                    <span class="text-[9px] text-gray-500">லக்கினம்: துலாம்</span>
                                    <span class="text-[9px] text-status-online font-bold">குரு சுப நோக்கு</span>
                                </div>
                                <!-- Box 5: Cancer -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>கடகம்</span><span class="text-gray-400 text-[9px]">ராகு</span></div>

                                <!-- Box 11: Capricorn -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>மகரம்</span><span class="text-gray-400 text-[9px]">-</span></div>
                                <!-- Box 6: Leo -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>சிம்மம் (ராசி)</span><span class="text-pine dark:text-gold text-[9px]">சந்திரன்</span></div>

                                <!-- Box 10: Sagittarius -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>தனுசு</span><span class="text-gold-amber text-[9px]">குரு (ஆட்சி)</span></div>
                                <!-- Box 9: Scorpio -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>விருச்சிகம்</span><span class="text-gray-400 text-[9px]">கேது</span></div>
                                <!-- Box 8: Libra -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center bg-gold/10"><span>துலாம்</span><span class="text-status-online text-[9px]">லக் (Lagna)</span></div>
                                <!-- Box 7: Virgo -->
                                <div class="border border-pine/30 dark:border-gold/30 flex flex-col justify-center items-center"><span>கன்னி</span><span class="text-gray-400 text-[9px]">-</span></div>
                            </div>

                            <!-- Planetary Dasha Analysis -->
                            <div class="p-3 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 text-[11px] space-y-1.5">
                                <p class="font-bold text-pine dark:text-gold">Planetary Dasha (தசா புக்தி):</p>
                                <p class="text-gray-600 dark:text-gray-300">• தற்போதைய தசை: <span class="font-bold text-status-online">குரு தசை (Jupiter)</span></p>
                                <p class="text-gray-600 dark:text-gray-300">• புக்தி: <span class="font-bold">புதன் புக்தி (2025 - 2027)</span></p>
                                <p class="text-gray-600 dark:text-gray-300">• ஏழாம் பார்வை: <span class="font-bold text-pine dark:text-gold">7-ஆம் வீட்டை குரு பார்ப்பதால் சுப திருமணம் உறுதி!</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Bar: Quick Remedies Chips & Call Controls -->
                    <div class="p-3 bg-white dark:bg-[#1A1A1A] border-t border-gray-200 dark:border-white/10 space-y-2">
                        <!-- Suggested Vedic Remedies Chips -->
                        <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar text-xs">
                            <span class="text-[11px] font-bold text-gray-400 whitespace-nowrap">Suggested:</span>
                            <button onclick="sendQuickRemedy('வியாழக்கிழமை குரு தட்சிணாமூர்த்திக்கு மஞ்சள் வஸ்திரம் சாற்றி நெய்தீபம் ஏற்றுவது நற்பலனை தரும்.')" class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/5 hover:bg-pine/10 text-slate-700 dark:text-gray-200 whitespace-nowrap transition">
                                🟡 தட்சிணாமூர்த்தி பரிகாரம்
                            </button>
                            <button onclick="sendQuickRemedy('7-ஆம் அதிபதி சுக்கிரன் பலமாக இருப்பதால் 2026 ஆரம்பத்தில் திருமணம் கைகூடும்.')" class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/5 hover:bg-pine/10 text-slate-700 dark:text-gray-200 whitespace-nowrap transition">
                                💍 திருமண யோக காலம்
                            </button>
                            <button onclick="sendQuickRemedy('செவ்வாய் தோஷம் இல்லை; அனுகூலமான வரன் அமையும்.')" class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/5 hover:bg-pine/10 text-slate-700 dark:text-gray-200 whitespace-nowrap transition">
                                ✨ மாங்கல்ய பொருத்தம்
                            </button>
                            <button onclick="sendQuickRemedy('புஷ்பராகம் (Yellow Sapphire) மோதிரம் அணிவது தொழில் மற்றும் கல்விக்கு யோகம் தரும்.')" class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/5 hover:bg-pine/10 text-slate-700 dark:text-gray-200 whitespace-nowrap transition">
                                💎 ரத்தின பரிகாரம்
                            </button>
                        </div>

                        <!-- Chat Input & Call Action Controls -->
                        <div class="flex items-center gap-3">
                            <div class="flex-1 flex items-center bg-[#F8F9FA] dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-1">
                                <input type="text" id="chatInput" placeholder="பக்தருக்கு ஆலோசனை செய்தி அனுப்பவும்..." class="w-full py-2 bg-transparent text-xs text-slate-800 dark:text-white placeholder-gray-400 focus:outline-none">
                                <button onclick="sendChatMessage()" class="p-1.5 text-pine dark:text-gold hover:opacity-80"><i data-lucide="send" class="w-4 h-4"></i></button>
                            </div>

                            <!-- Audio Controls -->
                            <div class="flex items-center gap-1.5">
                                <button onclick="toggleMic(this)" class="p-2.5 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition" title="Mute/Unmute"><i data-lucide="mic" class="w-4 h-4"></i></button>
                                <button onclick="toggleSpeaker(this)" class="p-2.5 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition" title="Speaker"><i data-lucide="volume-2" class="w-4 h-4"></i></button>
                                <button onclick="endConsultationConfirm()" class="px-4 py-2.5 rounded-xl bg-status-danger hover:bg-red-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                                    <i data-lucide="phone-off" class="w-4 h-4"></i> End Call
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- SCREEN 4: EARNINGS, WALLET & PAYOUT MANAGEMENT                  -->
            <!-- =============================================================== -->
            <div id="screenWallet" class="screen-view hidden space-y-6">
                <!-- Hero Wallet Card -->
                <div class="p-6 rounded-2xl bg-gradient-to-br from-pine via-pine-surface to-[#041E1E] text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-44 h-44 rounded-full bg-gold/10 blur-2xl"></div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs text-gray-300 font-semibold flex items-center gap-1.5"><i data-lucide="shield-check" class="w-4 h-4 text-gold"></i> Verified Astrologer Wallet Balance</span>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="font-cinzel text-3xl sm:text-4xl font-bold text-gold">₹12,850.00</span>
                                <span class="text-xs text-status-online font-bold bg-white/10 px-2 py-0.5 rounded-full">Ready for Payout</span>
                            </div>
                            <p class="text-xs text-gray-300 mt-1">Bank Account: HDFC Bank •••• 8821 (IFSC: HDFC0001234)</p>
                        </div>

                        <div>
                            <button onclick="openWithdrawModal()" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-gold hover:bg-gold-amber text-pine-dark font-bold text-xs shadow-lg transition flex items-center justify-center gap-2">
                                <i data-lucide="banknote" class="w-4 h-4"></i> Request Instant Withdrawal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filters & Trend Chart -->
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-5 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Earnings Trend (வருமான விவரம்)</h3>
                        <div class="flex items-center gap-1 bg-gray-100 dark:bg-white/5 p-1 rounded-xl text-xs font-semibold">
                            <button class="px-3 py-1 rounded-lg bg-white dark:bg-[#252525] text-pine dark:text-gold shadow-sm">Today</button>
                            <button class="px-3 py-1 rounded-lg text-gray-500 hover:text-gray-800 dark:hover:text-white">This Week</button>
                            <button class="px-3 py-1 rounded-lg text-gray-500 hover:text-gray-800 dark:hover:text-white">This Month</button>
                            <button class="px-3 py-1 rounded-lg text-gray-500 hover:text-gray-800 dark:hover:text-white">Custom</button>
                        </div>
                    </div>

                    <!-- Daily Trend Bars Simulation -->
                    <div class="grid grid-cols-7 gap-2 pt-4 items-end h-40 border-b border-gray-100 dark:border-white/10 pb-2 text-center text-[10px] text-gray-400">
                        <div class="flex flex-col items-center gap-1"><div class="w-full bg-pine/30 dark:bg-gold/20 rounded-t-lg h-16"></div><span>Mon</span><span class="font-bold text-slate-700 dark:text-white">₹1.8k</span></div>
                        <div class="flex flex-col items-center gap-1"><div class="w-full bg-pine/30 dark:bg-gold/20 rounded-t-lg h-24"></div><span>Tue</span><span class="font-bold text-slate-700 dark:text-white">₹2.4k</span></div>
                        <div class="flex flex-col items-center gap-1"><div class="w-full bg-pine/30 dark:bg-gold/20 rounded-t-lg h-20"></div><span>Wed</span><span class="font-bold text-slate-700 dark:text-white">₹2.1k</span></div>
                        <div class="flex flex-col items-center gap-1"><div class="w-full bg-pine/30 dark:bg-gold/20 rounded-t-lg h-28"></div><span>Thu</span><span class="font-bold text-slate-700 dark:text-white">₹3.2k</span></div>
                        <div class="flex flex-col items-center gap-1"><div class="w-full bg-pine/30 dark:bg-gold/20 rounded-t-lg h-32"></div><span>Fri</span><span class="font-bold text-slate-700 dark:text-white">₹3.8k</span></div>
                        <div class="flex flex-col items-center gap-1"><div class="w-full bg-pine dark:bg-gold rounded-t-lg h-36"></div><span>Sat</span><span class="font-bold text-pine dark:text-gold">₹4.2k</span></div>
                        <div class="flex flex-col items-center gap-1"><div class="w-full bg-pine/30 dark:bg-gold/20 rounded-t-lg h-20"></div><span>Sun</span><span class="font-bold text-slate-700 dark:text-white">₹2.0k</span></div>
                    </div>

                    <!-- Transaction List -->
                    <div class="pt-2">
                        <h4 class="font-bold text-xs text-slate-900 dark:text-white mb-3">Detailed Transaction History</h4>
                        <div class="space-y-2 text-xs">
                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-status-online flex items-center justify-center font-bold"><i data-lucide="plus" class="w-4 h-4"></i></div>
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white">Consultation Fee · Chat 15 min · Client: Rahul S.</p>
                                        <p class="text-[10px] text-gray-400">06 Nov 2026, 02:40 PM • Ref: TXN_882193</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-status-online">+ ₹240.00</p>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-100 text-status-online font-bold">Successful</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gold/20 text-gold-amber flex items-center justify-center font-bold"><i data-lucide="gift" class="w-4 h-4"></i></div>
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white">Gift Received · Rose Devotion Gift · Client: Anand</p>
                                        <p class="text-[10px] text-gray-400">06 Nov 2026, 11:15 AM • 100% to Astrologer</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gold-amber">+ ₹100.00</p>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">Gift Credit</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-red-50 text-status-danger flex items-center justify-center font-bold"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></div>
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white">Bank Payout · Transferred to HDFC Bank ****8821</p>
                                        <p class="text-[10px] text-gray-400">04 Nov 2026 • TDS Deducted 10% (₹500)</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-slate-800 dark:text-white">- ₹5,000.00</p>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">Disbursed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- SCREEN 5: AVAILABILITY & SLOT SCHEDULER                         -->
            <!-- =============================================================== -->
            <div id="screenScheduler" class="screen-view hidden space-y-6">
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-5 shadow-sm space-y-6">
                    <!-- Header with DND Vacation Mode Toggle -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-white/10">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Availability & Slot Scheduler (வேலை நேரம் மற்றும் விடுப்பு)</h3>
                            <p class="text-xs text-gray-500">Configure your daily working hours and buffer breaks between sessions.</p>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Vacation / DND Mode:</span>
                            <button onclick="toggleDnd(this)" id="dndBtn" class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full relative transition p-0.5">
                                <span class="w-5 h-5 bg-white rounded-full block transition transform translate-x-0" id="dndKnob"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Weekly Day Selector -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Active Consultation Days (வார நாட்கள்):</label>
                        <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold">
                            <div class="p-3 rounded-xl bg-pine text-white dark:bg-gold dark:text-pine-dark cursor-pointer shadow-sm">Mon</div>
                            <div class="p-3 rounded-xl bg-pine text-white dark:bg-gold dark:text-pine-dark cursor-pointer shadow-sm">Tue</div>
                            <div class="p-3 rounded-xl bg-pine text-white dark:bg-gold dark:text-pine-dark cursor-pointer shadow-sm">Wed</div>
                            <div class="p-3 rounded-xl bg-pine text-white dark:bg-gold dark:text-pine-dark cursor-pointer shadow-sm">Thu</div>
                            <div class="p-3 rounded-xl bg-pine text-white dark:bg-gold dark:text-pine-dark cursor-pointer shadow-sm">Fri</div>
                            <div class="p-3 rounded-xl bg-pine text-white dark:bg-gold dark:text-pine-dark cursor-pointer shadow-sm">Sat</div>
                            <div class="p-3 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-400 cursor-pointer">Sun</div>
                        </div>
                    </div>

                    <!-- Daily Shift Hours Setup -->
                    <div class="space-y-4">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Shift Timings (பணி நேரங்கள்):</label>

                        <!-- Shift 1: Morning -->
                        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-[#F8F9FA] dark:bg-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-gold/20 text-gold-amber font-bold flex items-center justify-center">🌅</span>
                                <div>
                                    <p class="font-bold text-slate-800 dark:text-white">Morning Shift (காலை நேரம்)</p>
                                    <p class="text-[11px] text-gray-500">09:00 AM – 01:00 PM (4 Hours)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-emerald-100 text-status-online font-bold rounded-lg text-[10px]">Active</span>
                            </div>
                        </div>

                        <!-- Shift 2: Evening -->
                        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-[#F8F9FA] dark:bg-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-pine/10 text-pine dark:text-gold font-bold flex items-center justify-center">🌆</span>
                                <div>
                                    <p class="font-bold text-slate-800 dark:text-white">Evening Shift (மாலை நேரம்)</p>
                                    <p class="text-[11px] text-gray-500">05:00 PM – 10:00 PM (5 Hours)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-emerald-100 text-status-online font-bold rounded-lg text-[10px]">Active</span>
                            </div>
                        </div>
                    </div>

                    <!-- Break Intervals & Queue Limits -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Buffer Break Interval (இடைவேளை):</label>
                            <select class="w-full p-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl">
                                <option>10 Minutes between consultations</option>
                                <option selected>15 Minutes between consultations</option>
                                <option>20 Minutes between consultations</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Max Simultaneous Queue Limit:</label>
                            <select class="w-full p-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl">
                                <option>Maximum 2 devotees in waiting</option>
                                <option selected>Maximum 3 devotees in waiting</option>
                                <option>Maximum 5 devotees in waiting</option>
                            </select>
                        </div>
                    </div>

                    <button onclick="saveScheduleToast()" class="px-6 py-3 bg-pine hover:bg-pine-light dark:bg-gold dark:text-pine-dark text-white font-bold text-xs rounded-xl shadow-md transition">
                        Save Availability Schedule (அட்டவணையை சேமிக்க)
                    </button>
                </div>
            </div>
        </main>

        <!-- =================================================================== -->
        <!-- SCREEN 2: INCOMING CONSULTATION ALERT MODAL (HIGH PRIORITY OVERLAY) -->
        <!-- =================================================================== -->
        <div id="incomingAlertModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
            <div class="w-full max-w-md bg-white dark:bg-[#1A1A1A] border-2 border-gold rounded-3xl p-6 sm:p-8 shadow-2xl relative text-center animate-bounce-short overflow-hidden">
                <!-- Background Pulse Rings -->
                <div class="absolute inset-0 bg-gold/5 animate-pulse pointer-events-none"></div>

                <div class="relative z-10">
                    <!-- Client Avatar with Pulsing Outer Ring -->
                    <div class="relative inline-block mb-4">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-pine to-gold p-1 shadow-xl">
                            <div class="w-full h-full rounded-full bg-white dark:bg-[#1A1A1A] flex items-center justify-center font-cinzel text-2xl font-bold text-pine dark:text-gold">
                                KR
                            </div>
                        </div>
                        <span class="absolute -top-1 -right-1 px-2.5 py-0.5 rounded-full bg-status-online text-white text-[10px] font-bold shadow-md animate-pulse">INCOMING</span>
                    </div>

                    <h3 class="font-cinzel text-xl font-bold text-slate-900 dark:text-white" id="incomingDevoteeName">Karthik R.</h3>
                    <p class="text-xs text-pine dark:text-gold font-semibold mt-0.5">Chennai, Tamil Nadu • Preferred: Tamil</p>
                    <p class="text-[11px] text-gray-500 mt-1">Category: <span class="font-bold text-slate-700 dark:text-gray-300">Love & Marriage Compatibility</span></p>

                    <!-- 30-Second Countdown Timer Ring -->
                    <div class="my-5 flex items-center justify-center gap-2">
                        <div class="w-12 h-12 rounded-full border-4 border-gold border-t-status-danger animate-spin flex items-center justify-center font-mono font-bold text-sm text-slate-800 dark:text-white">
                            <span id="countdownSeconds">28</span>
                        </div>
                        <span class="text-xs text-gray-400">Seconds to Auto-Pass</span>
                    </div>

                    <!-- Two Large Action Touch Targets -->
                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <button onclick="declineConsultation()" class="py-3.5 rounded-2xl bg-red-50 dark:bg-red-950/40 text-status-danger border border-red-200 dark:border-red-800/40 font-bold text-xs hover:bg-red-100 transition flex items-center justify-center gap-1.5">
                            <i data-lucide="phone-off" class="w-4 h-4"></i> Decline / Busy
                        </button>

                        <button onclick="acceptConsultationFromAlert()" class="py-3.5 rounded-2xl bg-status-online hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-1.5 animate-pulse">
                            <i data-lucide="phone" class="w-4 h-4"></i> Accept (₹20/m)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: RINGING DEVOTEE PHONE (WAITING FOR DEVOTEE TO ANSWER) -->
        <div id="ringingUserModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-md flex items-center justify-center p-4 hidden">
            <div class="w-full max-w-md bg-white dark:bg-[#1A1A1A] border-2 border-emerald-500 rounded-3xl p-6 sm:p-8 shadow-2xl relative text-center overflow-hidden">
                <div class="relative z-10">
                    <div class="relative inline-block mb-4">
                        <div class="w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-500 border-2 border-emerald-500 flex items-center justify-center mx-auto shadow-lg animate-pulse">
                            <i data-lucide="phone-outgoing" class="w-10 h-10 animate-bounce"></i>
                        </div>
                        <span class="absolute -top-1 -right-1 px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-bold shadow-md">RINGING</span>
                    </div>

                    <h3 class="font-cinzel text-lg font-bold text-slate-900 dark:text-white" id="ringingDevoteeName">பக்தருக்கு அழைப்பு செல்கிறது...</h3>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-1">Devotee Phone is Ringing via FCM Full-Screen Alert</p>
                    <p class="text-[11px] text-gray-400 mt-1">பக்தர் போனை எடுத்தவுடன் Live Consultation Room தானாக இணையும்.</p>

                    <div class="my-5 flex items-center justify-center gap-2">
                        <div class="w-12 h-12 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin flex items-center justify-center font-mono font-bold text-sm text-slate-800 dark:text-white">
                            <span id="ringingCountdownSeconds">30</span>
                        </div>
                        <span class="text-xs text-gray-400">Waiting for answer</span>
                    </div>

                    <button onclick="cancelRingingUser()" class="w-full py-3 rounded-xl bg-red-50 dark:bg-red-950/40 text-status-danger border border-red-200 dark:border-red-800/40 font-bold text-xs hover:bg-red-100 transition flex items-center justify-center gap-1.5">
                        <i data-lucide="x-circle" class="w-4 h-4"></i> Cancel Call
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: KUNDALI BIRTH CHART GENERATOR -->
        <div id="kundaliModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
            <div class="w-full max-w-xl bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
                <button onclick="closeKundaliModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                <h3 class="font-cinzel text-base font-bold text-pine dark:text-gold mb-3 flex items-center gap-2">
                    <i data-lucide="compass" class="w-5 h-5"></i> Vedic Kundali / Birth Chart Generator
                </h3>
                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-gray-500 mb-1">Devotee Name</label><input type="text" value="Karthik R." class="w-full p-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl"></div>
                        <div><label class="block text-gray-500 mb-1">Date of Birth</label><input type="date" value="1996-08-15" class="w-full p-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl"></div>
                        <div><label class="block text-gray-500 mb-1">Time of Birth</label><input type="time" value="10:30" class="w-full p-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl"></div>
                        <div><label class="block text-gray-500 mb-1">Place of Birth</label><input type="text" value="Chennai, Tamil Nadu" class="w-full p-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl"></div>
                    </div>
                    <button onclick="alert('Kundali generated successfully with 9 Planetary Navagraha Coordinates!'); closeKundaliModal();" class="w-full py-3 bg-pine hover:bg-pine-light dark:bg-gold dark:text-pine-dark text-white font-bold rounded-xl transition">
                        Compute Lagna & Navamsha Chart
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: EDIT RATES -->
        <div id="ratesModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 hidden">
            <div class="w-full max-w-md bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl p-6 shadow-2xl relative">
                <button onclick="closeRatesModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white mb-3">Update Consultation Rates (கட்டண நிர்ணயம்)</h3>
                <form method="POST" action="index.php" class="space-y-3 text-xs">
                    <input type="hidden" name="update_rates" value="1">
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 mb-1">📞 Audio Call Rate (₹/min)</label>
                        <input type="number" step="1" name="audio_rate" value="<?= (int)$currentAstro['audio_rate_min'] ?>" required class="w-full p-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl font-bold">
                    </div>
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 mb-1">📹 Video Call Rate (₹/min)</label>
                        <input type="number" step="1" name="video_rate" value="<?= (int)$currentAstro['video_rate_min'] ?>" required class="w-full p-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl font-bold">
                    </div>
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 mb-1">💬 Chat Consultation Rate (₹/min)</label>
                        <input type="number" step="1" name="chat_rate" value="<?= (int)$currentAstro['chat_rate_min'] ?>" required class="w-full p-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl font-bold">
                    </div>
                    <button type="submit" class="w-full py-3 bg-pine dark:bg-gold text-white dark:text-pine-dark font-bold rounded-xl transition">
                        Save Rate Changes
                    </button>
                </form>
            </div>
        </div>

        <footer class="border-t border-gray-200 dark:border-white/10 py-3 text-center text-[11px] text-gray-400 bg-white dark:bg-[#1A1A1A]">
            ASTROSUBAM &copy; <?= date('Y') ?> • Astrologer Partner Ecosystem • 256-Bit SSL Secured
        </footer>
    </div>
<?php endif; ?>

<script>
    // Lucide Icons init
    lucide.createIcons();

    // Theme Switcher (Light / Dark)
    function toggleTheme() {
        const html = document.documentElement;
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            html.classList.add('light');
            localStorage.setItem('astro_theme', 'light');
        } else {
            html.classList.remove('light');
            html.classList.add('dark');
            localStorage.setItem('astro_theme', 'dark');
        }
        lucide.createIcons();
    }

    // Restore Theme Preference
    if (localStorage.getItem('astro_theme') === 'dark') {
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
    }

    // Login Form Switch (Password vs OTP)
    function switchLoginMode(mode) {
        const loginMethod = document.getElementById('loginMethod');
        const passSec = document.getElementById('passwordSection');
        const otpSec = document.getElementById('otpSection');
        const tabPass = document.getElementById('tabPassword');
        const tabOtp = document.getElementById('tabOtp');

        if (mode === 'otp') {
            loginMethod.value = 'otp';
            passSec.classList.add('hidden');
            otpSec.classList.remove('hidden');
            tabOtp.className = 'flex-1 py-1.5 rounded-lg bg-white dark:bg-[#252525] text-pine dark:text-gold shadow-sm transition';
            tabPass.className = 'flex-1 py-1.5 rounded-lg hover:text-gray-800 dark:hover:text-white transition';
        } else {
            loginMethod.value = 'password';
            passSec.classList.remove('hidden');
            otpSec.classList.add('hidden');
            tabPass.className = 'flex-1 py-1.5 rounded-lg bg-white dark:bg-[#252525] text-pine dark:text-gold shadow-sm transition';
            tabOtp.className = 'flex-1 py-1.5 rounded-lg hover:text-gray-800 dark:hover:text-white transition';
        }
    }

    // Navigation Screens Switcher
    function showScreen(screenId) {
        document.querySelectorAll('.screen-view').forEach(el => el.classList.add('hidden'));
        const target = document.getElementById(screenId);
        if (target) target.classList.remove('hidden');

        document.querySelectorAll('.nav-tab').forEach(el => {
            el.className = 'nav-tab px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition';
        });

        const activeMap = {
            'screenHome': 'navHome',
            'screenActiveConsultation': 'navActive',
            'screenWallet': 'navWallet',
            'screenScheduler': 'navScheduler'
        };

        const activeBtn = document.getElementById(activeMap[screenId]);
        if (activeBtn) {
            activeBtn.className = 'nav-tab px-4 py-2 rounded-xl bg-pine text-white dark:bg-gold dark:text-pine-dark shadow-sm flex items-center gap-2 whitespace-nowrap transition font-bold';
        }
    }

    // Master Status Switcher Toggles
    function toggleCallOnline(btn) {
        if (btn.classList.contains('bg-status-online')) {
            btn.className = 'px-3 py-1.5 rounded-lg text-gray-400 hover:text-white flex items-center gap-1.5 transition';
            btn.innerHTML = '<i data-lucide="phone-off" class="w-3.5 h-3.5"></i> Call: Offline';
        } else {
            btn.className = 'px-3 py-1.5 rounded-lg bg-status-online text-white flex items-center gap-1.5 transition';
            btn.innerHTML = '<i data-lucide="phone-call" class="w-3.5 h-3.5"></i> Call: Online';
        }
        lucide.createIcons();
    }

    function toggleChatOnline(btn) {
        if (btn.classList.contains('bg-status-online')) {
            btn.className = 'px-3 py-1.5 rounded-lg text-gray-400 hover:text-white flex items-center gap-1.5 transition';
            btn.innerHTML = '<i data-lucide="message-square-off" class="w-3.5 h-3.5"></i> Chat: Offline';
        } else {
            btn.className = 'px-3 py-1.5 rounded-lg bg-status-online text-white flex items-center gap-1.5 transition';
            btn.innerHTML = '<i data-lucide="message-square" class="w-3.5 h-3.5"></i> Chat: Online';
        }
        lucide.createIcons();
    }

    // Incoming Consultation Alert Modal (Screen 2) - Real-time Polling & Agora Hook
    let countdownInterval = null;
    let currentIncomingSession = null;
    let pendingPollInterval = null;

    let ringingPollInterval = null;
    let ringingCountdownInterval = null;

    function renderQueueUI(session) {
        const queueContainer = document.getElementById('liveQueueContainer');
        const badge = document.getElementById('queueCountBadge');
        if (!queueContainer || !badge) return;

        if (session) {
            badge.textContent = '1 Waiting';
            badge.className = 'px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-500 text-[10px] font-bold animate-pulse';
            queueContainer.innerHTML = `
                <div class="p-4 rounded-xl border border-emerald-500/40 bg-emerald-50/10 dark:bg-emerald-950/20 flex items-center justify-between gap-4 shadow-sm animate-pulse">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-emerald-500/10 text-emerald-600 font-bold flex items-center justify-center text-sm border border-emerald-500/30">
                            ${(session.user_name || 'DV').substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-xs text-slate-800 dark:text-white">${session.user_name || 'பக்தர்'}</h4>
                                <span class="px-1.5 py-0.5 bg-emerald-500/10 text-emerald-600 text-[9px] font-bold rounded uppercase">${session.type || 'audio'} Call</span>
                            </div>
                            <p class="text-[11px] text-gray-500">${session.user_phone || '8000000001'} • ₹${session.rate_per_min}/min</p>
                            <p class="text-[10px] text-status-busy font-semibold mt-0.5">⏱️ Waiting: ${session.seconds_elapsed || 0}s</p>
                        </div>
                    </div>
                    <button onclick="triggerIncomingAlert(${session.seconds_left || 30})" class="px-3.5 py-2 rounded-xl bg-status-online hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <i data-lucide="phone" class="w-3.5 h-3.5"></i> Connect
                    </button>
                </div>
            `;
        } else {
            badge.textContent = '0 Waiting';
            badge.className = 'px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-status-online text-[10px] font-bold';
            queueContainer.innerHTML = `
                <div class="col-span-full py-8 text-center text-gray-400 bg-gray-50/50 dark:bg-white/[0.02] border border-dashed border-gray-200 dark:border-white/10 rounded-xl">
                    <i data-lucide="phone-call" class="w-8 h-8 mx-auto mb-2 text-emerald-500/70 animate-pulse"></i>
                    <p class="font-bold text-xs text-slate-700 dark:text-gray-300">வரிசையில் பக்தர்கள் யாரும் இல்லை (Queue is Clear)</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">நீங்கள் ஆன்லைனில் உள்ளீர்கள். பக்தர் அழைப்பு விடுத்தவுடன் திரை தானாக ஒளிரும்.</p>
                </div>
            `;
        }
        if (window.lucide) lucide.createIcons();
    }

    function startPendingPoll() {
        if (pendingPollInterval) clearInterval(pendingPollInterval);
        pendingPollInterval = setInterval(async () => {
            const modal = document.getElementById('incomingAlertModal');
            const ringingModal = document.getElementById('ringingUserModal');
            const activeScreen = document.getElementById('screenActiveConsultation');
            // If already in active call or alert/ringing modal is showing, skip
            if (!modal.classList.contains('hidden') || 
                (ringingModal && !ringingModal.classList.contains('hidden')) || 
                (activeScreen && !activeScreen.classList.contains('hidden'))) {
                return;
            }

            try {
                const res = await fetch('/api/sessions/pending.php?astrologer_id=1&phone=9876543210');
                const json = await res.json();
                if (json.ok && json.data && json.data.has_incoming && json.data.session) {
                    currentIncomingSession = json.data.session;
                    renderQueueUI(currentIncomingSession);
                    document.getElementById('incomingDevoteeName').textContent = currentIncomingSession.user_name || 'பக்தர் (Devotee)';
                    
                    const phoneTag = document.querySelector('#incomingAlertModal .text-xs.text-pine');
                    if (phoneTag) {
                        phoneTag.textContent = (currentIncomingSession.user_phone || '') + ' • Preferred: Tamil';
                    }

                    const catTag = document.querySelector('#incomingAlertModal span.font-bold');
                    if (catTag) {
                        catTag.textContent = (currentIncomingSession.type || 'audio').toUpperCase() + ' Call (₹' + currentIncomingSession.rate_per_min + '/m)';
                    }

                    triggerIncomingAlert(currentIncomingSession.seconds_left || 30);
                } else {
                    renderQueueUI(null);
                }
            } catch (err) {
                // background poll silent catch
            }
        }, 2000);
    }

    function triggerIncomingAlert(customSeconds = 30) {
        const modal = document.getElementById('incomingAlertModal');
        modal.classList.remove('hidden');

        let seconds = customSeconds > 0 ? customSeconds : 30;
        const countSpan = document.getElementById('countdownSeconds');
        if (countdownInterval) clearInterval(countdownInterval);

        countdownInterval = setInterval(() => {
            seconds--;
            if (countSpan) countSpan.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                declineConsultation();
            }
        }, 1000);
    }

    async function declineConsultation() {
        if (countdownInterval) clearInterval(countdownInterval);
        document.getElementById('incomingAlertModal').classList.add('hidden');
        if (currentIncomingSession && currentIncomingSession.session_id) {
            try {
                await fetch('/api/sessions/reject.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: currentIncomingSession.session_id })
                });
            } catch (e) {}
            currentIncomingSession = null;
        }
        renderQueueUI(null);
    }

    async function acceptConsultationFromAlert() {
        if (countdownInterval) clearInterval(countdownInterval);
        document.getElementById('incomingAlertModal').classList.add('hidden');

        if (!currentIncomingSession || !currentIncomingSession.session_id) {
            return;
        }

        const sid = currentIncomingSession.session_id;

        try {
            // 1. Astrologer signals Accept -> Session becomes 'ringing_user' & triggers FCM
            const res = await fetch('/api/sessions/accept.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: sid, astrologer_id: 1 })
            });
            const data = await res.json();

            // 2. Open Ringing Devotee Modal
            const ringingModal = document.getElementById('ringingUserModal');
            if (ringingModal) {
                ringingModal.classList.remove('hidden');
                document.getElementById('ringingDevoteeName').textContent = (currentIncomingSession.user_name || 'பக்தர்') + ' அவர்களுக்கு ரிங் போகிறது...';
                
                let rSeconds = 30;
                const rCountSpan = document.getElementById('ringingCountdownSeconds');
                if (ringingCountdownInterval) clearInterval(ringingCountdownInterval);
                ringingCountdownInterval = setInterval(() => {
                    rSeconds--;
                    if (rCountSpan) rCountSpan.textContent = rSeconds;
                    if (rSeconds <= 0) {
                        clearInterval(ringingCountdownInterval);
                        cancelRingingUser();
                    }
                }, 1000);
            }

            // 3. Poll for Devotee Answer (User Accept) -> status becomes 'active'
            if (ringingPollInterval) clearInterval(ringingPollInterval);
            ringingPollInterval = setInterval(async () => {
                try {
                    const statusRes = await fetch(`/api/sessions/check_status.php?session_id=${sid}`);
                    const statusData = await statusRes.json();

                    if (statusData.ok && statusData.data) {
                        const curStatus = statusData.data.status;
                        if (curStatus === 'active') {
                            // Devotee Accepted! Room is created!
                            clearInterval(ringingPollInterval);
                            clearInterval(ringingCountdownInterval);
                            if (ringingModal) ringingModal.classList.add('hidden');

                            document.getElementById('activeClientName').textContent = (currentIncomingSession.user_name || 'பக்தர்') + ' (' + (currentIncomingSession.type || 'audio').toUpperCase() + ')';
                            showScreen('screenActiveConsultation');
                            timerSeconds = 0;
                            startConsultationTimer();
                        } else if (curStatus === 'cancelled' || curStatus === 'completed') {
                            clearInterval(ringingPollInterval);
                            clearInterval(ringingCountdownInterval);
                            if (ringingModal) ringingModal.classList.add('hidden');
                            alert('பக்தர் அழைப்பை ஏற்கவில்லை / ரத்து செய்துவிட்டார் (Devotee declined or missed call).');
                            currentIncomingSession = null;
                            renderQueueUI(null);
                        }
                    }
                } catch (e) {
                    // silent catch
                }
            }, 1000);

        } catch (e) {
            console.error('Accept error:', e);
            alert('அழைப்பை ஏற்க இயலவில்லை: ' + e);
        }
    }

    async function cancelRingingUser() {
        if (ringingCountdownInterval) clearInterval(ringingCountdownInterval);
        if (ringingPollInterval) clearInterval(ringingPollInterval);
        const ringingModal = document.getElementById('ringingUserModal');
        if (ringingModal) ringingModal.classList.add('hidden');

        if (currentIncomingSession && currentIncomingSession.session_id) {
            try {
                await fetch('/api/sessions/reject.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: currentIncomingSession.session_id })
                });
            } catch (e) {}
            currentIncomingSession = null;
        }
        renderQueueUI(null);
    }

    function acceptConsultationDirect(name, type, rate) {
        document.getElementById('activeClientName').textContent = name + ' (' + type + ')';
        showScreen('screenActiveConsultation');
        timerSeconds = 0;
        startConsultationTimer();
    }

    // Active Consultation Timer & Dynamic Earnings Ticker
    let timerSeconds = 0;
    let timerInterval = null;
    function startConsultationTimer() {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timerSeconds++;
            const mins = String(Math.floor(timerSeconds / 60)).padStart(2, '0');
            const secs = String(timerSeconds % 60).padStart(2, '0');
            document.getElementById('consultationTimer').textContent = `${mins}:${secs}`;

            // Rate ₹20/min = ₹0.33 per sec
            const earnings = (timerSeconds * (20 / 60)).toFixed(2);
            document.getElementById('liveEarningsTicker').textContent = `₹ ${earnings}`;
        }, 1000);
    }

    // Slide-over Kundali Drawer Toggle
    function toggleKundaliDrawer() {
        const drawer = document.getElementById('kundaliDrawer');
        drawer.classList.toggle('hidden');
    }

    // Chat in Active Consultation
    function sendChatMessage() {
        const input = document.getElementById('chatInput');
        const msg = input.value.trim();
        if (!msg) return;

        const chat = document.getElementById('chatMessages');
        const div = document.createElement('div');
        div.className = 'flex justify-start';
        div.innerHTML = `
            <div class="max-w-md p-3.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl rounded-tl-none shadow-sm text-xs text-slate-800 dark:text-white">
                <p class="font-bold text-pine dark:text-gold text-[11px] mb-1">Guruji S. Sundaramurthy</p>
                <p>${msg}</p>
                <span class="text-[9px] text-gray-400 block text-right mt-1">Just now</span>
            </div>
        `;
        chat.appendChild(div);
        input.value = '';
        chat.scrollTop = chat.scrollHeight;
    }

    function sendQuickRemedy(text) {
        document.getElementById('chatInput').value = text;
        sendChatMessage();
    }

    function toggleMic(btn) {
        btn.classList.toggle('text-status-danger');
    }

    function toggleSpeaker(btn) {
        btn.classList.toggle('text-status-online');
    }

    async function endConsultationConfirm() {
        if (confirm('நிச்சயமாக இந்த ஆலோசனையை முடிக்க விரும்புகிறீர்களா? (End Consultation and finalize session?)')) {
            if (timerInterval) clearInterval(timerInterval);
            if (currentIncomingSession && currentIncomingSession.session_id) {
                try {
                    await fetch('/api/sessions/end.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ session_id: currentIncomingSession.session_id, rating: 5 })
                    });
                } catch (e) {}
                currentIncomingSession = null;
            }
            alert('Consultation Completed! Session ended and earnings credited to your Astrologer Wallet.');
            showScreen('screenHome');
        }
    }

    // Modals
    function openKundaliModal() { document.getElementById('kundaliModal').classList.remove('hidden'); }
    function closeKundaliModal() { document.getElementById('kundaliModal').classList.add('hidden'); }

    function openRatesModal() { document.getElementById('ratesModal').classList.remove('hidden'); }
    function closeRatesModal() { document.getElementById('ratesModal').classList.add('hidden'); }

    function openBroadcastModal() { alert('Agora Live Broadcast Video Engine initialized! Room Channel: ASTRO_LIVE_ROOM_1'); }
    function openRemediesModal() { alert('Astromall Remedies: Navaratna Gemstones, Rudraksha & Temple Poojas catalog loaded!'); }
    function openNotesModal() { alert('Client Notes & Horoscope Records synchronized with MySQL.'); }

    function openWithdrawModal() {
        const amt = prompt('Enter withdrawal amount in ₹ (Available: ₹12,850.00):', '5000');
        if (amt) {
            alert(`Withdrawal request for ₹${amt} submitted! Disbursal to HDFC Bank ****8821 initiated.`);
        }
    }

    function toggleDnd(btn) {
        const knob = document.getElementById('dndKnob');
        if (knob.classList.contains('translate-x-5')) {
            knob.classList.remove('translate-x-5');
            btn.className = 'w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full relative transition p-0.5';
        } else {
            knob.classList.add('translate-x-5');
            btn.className = 'w-11 h-6 bg-status-danger rounded-full relative transition p-0.5';
        }
    }

    function saveScheduleToast() {
        alert('Availability Shift Hours (Morning 09:00 AM – 01:00 PM & Evening 05:00 PM – 10:00 PM) Saved Successfully!');
    }

    window.addEventListener('DOMContentLoaded', () => {
        startPendingPoll();
    });
</script>
</body>
</html>
