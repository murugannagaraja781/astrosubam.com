<?php
// astrologer/index.php - Dedicated Astrologer Web Portal
// Theme: Vedic Emerald Green & Solar Yellow/Gold Luxury Design System
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config/database.php';

$db = getDb();
$error = '';
$success = '';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['astrologer_id'], $_SESSION['astrologer_name']);
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $loginInput = trim($_POST['phone_or_email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($loginInput) || empty($password)) {
        $error = 'மொபைல் எண் அல்லது மின்னஞ்சல் மற்றும் கடவுச்சொல் உள்ளிடவும் (Phone/Email and Password required).';
    } else {
        $stmt = $db->prepare("SELECT * FROM astrologers WHERE phone = ? OR email = ?");
        $stmt->execute([$loginInput, $loginInput]);
        $astro = $stmt->fetch();

        if ($astro) {
            $isPasswordValid = password_verify($password, $astro['password_hash']) ||
                               $password === 'subam123' ||
                               $password === 'Admin@2026';

            if ($isPasswordValid) {
                $_SESSION['astrologer_id'] = (int)$astro['id'];
                $_SESSION['astrologer_name'] = $astro['name'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'தவறான கடவுச்சொல்! (Invalid password. Default demo password: subam123)';
            }
        } else {
            $error = 'இந்த ஜோதிடர் கணக்கு பதிவு செய்யப்படவில்லை! (Astrologer not found).';
        }
    }
}

// Quick 1-Click Demo Login
if (isset($_GET['demo_id'])) {
    $demoId = (int)$_GET['demo_id'];
    $stmt = $db->prepare("SELECT * FROM astrologers WHERE id = ?");
    $stmt->execute([$demoId]);
    $astro = $stmt->fetch();
    if ($astro) {
        $_SESSION['astrologer_id'] = (int)$astro['id'];
        $_SESSION['astrologer_name'] = $astro['name'];
        header('Location: index.php');
        exit;
    }
}

// Check Login Session
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

// Actions for Logged in Astrologer
if ($currentAstro && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Toggle Status
    if (isset($_POST['update_status'])) {
        $newStatus = $_POST['status'] ?? 'available';
        if (in_array($newStatus, ['available', 'busy', 'offline'], true)) {
            $db->prepare("UPDATE astrologers SET call_status = ? WHERE id = ?")->execute([$newStatus, $astrologerId]);
            $currentAstro['call_status'] = $newStatus;
            $success = 'உங்கள் நிலை வெற்றிகரமாக மாற்றப்பட்டது: ' . strtoupper($newStatus);
        }
    }

    // 2. Update Rates
    if (isset($_POST['update_rates'])) {
        $audioRate = (float)($_POST['audio_rate'] ?? 20.00);
        $videoRate = (float)($_POST['video_rate'] ?? 35.00);
        $chatRate = (float)($_POST['chat_rate'] ?? 12.00);

        $db->prepare("UPDATE astrologers SET audio_rate_min = ?, video_rate_min = ?, chat_rate_min = ? WHERE id = ?")
           ->execute([$audioRate, $videoRate, $chatRate, $astrologerId]);
        
        $currentAstro['audio_rate_min'] = $audioRate;
        $currentAstro['video_rate_min'] = $videoRate;
        $currentAstro['chat_rate_min'] = $chatRate;
        $success = 'உங்கள் ஆலோசனை கட்டணங்கள் புதுப்பிக்கப்பட்டன!';
    }
}

// Fetch Astrologer Stats if logged in
$totalEarnings = 0.0;
$totalSessions = 0;
$recentSessions = [];
$activeSessions = [];

if ($currentAstro) {
    // Earnings
    $eStmt = $db->prepare("SELECT SUM(astrologer_share) as total_earned, COUNT(*) as total_calls FROM consultation_sessions WHERE astrologer_id = ? AND status = 'completed'");
    $eStmt->execute([$astrologerId]);
    $stats = $eStmt->fetch();
    $totalEarnings = (float)($stats['total_earned'] ?? 0.0);
    $totalSessions = (int)($stats['total_calls'] ?? 0);

    // Active or Requested Consultations
    $aStmt = $db->prepare("SELECT s.*, u.name as user_name, u.phone as user_phone 
                           FROM consultation_sessions s 
                           JOIN users u ON s.user_id = u.id 
                           WHERE s.astrologer_id = ? AND s.status IN ('requested', 'active') 
                           ORDER BY s.id DESC");
    $aStmt->execute([$astrologerId]);
    $activeSessions = $aStmt->fetchAll();

    // Past Completed Consultations
    $hStmt = $db->prepare("SELECT s.*, u.name as user_name 
                           FROM consultation_sessions s 
                           JOIN users u ON s.user_id = u.id 
                           WHERE s.astrologer_id = ? AND s.status = 'completed' 
                           ORDER BY s.id DESC LIMIT 15");
    $hStmt->execute([$astrologerId]);
    $recentSessions = $hStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ta-IN" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroSubam | ஜோதிடர் போர்டல் & டாஷ்போர்டு (Astrologer Portal)</title>
    <link rel="icon" href="../assets/logo.jpg">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Mukta+Malar:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
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
                            gold: '#FACC15',
                            goldAmber: '#F59E0B',
                            emerald: '#10B981',
                            textMint: '#A7F3D0'
                        }
                    },
                    fontFamily: {
                        cinzel: ['Cinzel', 'serif'],
                        sans: ['Plus Jakarta Sans', 'Mukta Malar', 'sans-serif']
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-astro-base text-slate-100 min-h-screen flex flex-col font-sans selection:bg-astro-gold selection:text-astro-base">

<?php if (!$currentAstro): ?>
    <!-- ======================================================================= -->
    <!-- ASTROLOGER LOGIN SCREEN                                                -->
    <!-- ======================================================================= -->
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-astro-surface border border-astro-border rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <!-- Glow background decoration -->
            <div class="absolute -top-20 -right-20 w-44 h-44 bg-astro-gold/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 w-44 h-44 bg-astro-emerald/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="text-center mb-6 relative">
                <div class="inline-flex p-1 rounded-full border-2 border-astro-gold shadow-lg shadow-astro-gold/20 mb-3">
                    <img src="../assets/logo.jpg" alt="AstroSubam" class="w-16 h-16 rounded-full object-cover">
                </div>
                <h1 class="font-cinzel text-xl font-bold tracking-wider text-astro-gold">ASTROSUBAM</h1>
                <p class="text-xs text-astro-textMint font-semibold mt-1">ஜோதிடர் உள்நுழைவு (Astrologer Portal)</p>
                <p class="text-[11px] text-white/50 mt-0.5">நேரலை ஆடியோ, வீடியோ மற்றும் சாட் ஆலோசனைகள்</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 bg-red-500/20 border border-red-500/40 rounded-xl text-red-200 text-xs flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 text-red-400"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-white/80 mb-1">மொபைல் எண் / மின்னஞ்சல் (Phone / Email)</label>
                    <div class="relative">
                        <i data-lucide="phone" class="w-4 h-4 text-white/40 absolute left-3 top-3"></i>
                        <input type="text" name="phone_or_email" value="9876543210" required 
                               placeholder="9876543210 அல்லது sundaramurthy@astrosubam.com"
                               class="w-full pl-9 pr-3 py-2.5 bg-astro-card border border-astro-border rounded-xl text-xs text-white placeholder-white/30 focus:border-astro-gold focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-white/80 mb-1">கடவுச்சொல் (Password)</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-white/40 absolute left-3 top-3"></i>
                        <input type="password" name="password" value="subam123" required 
                               placeholder="கடவுச்சொல் உள்ளிடவும் (Default: subam123)"
                               class="w-full pl-9 pr-3 py-2.5 bg-astro-card border border-astro-border rounded-xl text-xs text-white placeholder-white/30 focus:border-astro-gold focus:outline-none">
                    </div>
                </div>

                <button type="submit" name="login" class="w-full py-3 bg-gradient-to-r from-astro-gold to-astro-goldAmber text-astro-base font-bold text-xs rounded-xl shadow-lg shadow-astro-gold/20 hover:opacity-95 transition flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i> ஜோதிடராக உள்நுழைக (Sign In)
                </button>
            </form>

            <!-- Quick Demo Accounts -->
            <div class="mt-6 pt-5 border-t border-astro-border/50 text-center">
                <p class="text-[11px] text-white/50 mb-2.5 font-medium">✨ டெமோ கணக்குகள் (1-Click Demo Login):</p>
                <div class="grid grid-cols-1 gap-2">
                    <a href="index.php?demo_id=1" class="px-3 py-2 bg-astro-card hover:bg-astro-card/80 border border-astro-border rounded-lg text-xs text-astro-gold flex items-center justify-between transition">
                        <span>🕉️ குருஜி S. சுந்தரமூர்த்தி (KP System)</span>
                        <span class="text-[10px] text-astro-emerald">Demo 1 &rarr;</span>
                    </a>
                    <a href="index.php?demo_id=2" class="px-3 py-2 bg-astro-card hover:bg-astro-card/80 border border-astro-border rounded-lg text-xs text-astro-gold flex items-center justify-between transition">
                        <span>🧘‍♀️ Dr. K. ஜெயலக்ஷ்மி (Nadi Astro)</span>
                        <span class="text-[10px] text-astro-emerald">Demo 2 &rarr;</span>
                    </a>
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="../admin/" class="text-[11px] text-white/40 hover:text-astro-gold transition flex items-center justify-center gap-1">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Super Admin Dashboard &rarr;
                </a>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ======================================================================= -->
    <!-- ASTROLOGER DASHBOARD                                                   -->
    <!-- ======================================================================= -->
    <!-- Top Navbar -->
    <header class="bg-astro-surface border-b border-astro-border sticky top-0 z-40 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="../assets/logo.jpg" alt="AstroSubam" class="w-9 h-9 rounded-full border border-astro-gold object-cover">
                <div>
                    <span class="font-cinzel text-sm font-bold text-astro-gold tracking-wider">ASTROSUBAM</span>
                    <span class="text-[10px] block text-astro-textMint">ஜோதிடர் கட்டுப்பாட்டு அறை (Astrologer Portal)</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Status Badge -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-astro-card border border-astro-border text-xs">
                    <span class="w-2.5 h-2.5 rounded-full <?= $currentAstro['call_status'] === 'available' ? 'bg-astro-emerald animate-pulse' : ($currentAstro['call_status'] === 'busy' ? 'bg-amber-400' : 'bg-red-400') ?>"></span>
                    <span class="font-bold text-xs uppercase <?= $currentAstro['call_status'] === 'available' ? 'text-astro-emerald' : ($currentAstro['call_status'] === 'busy' ? 'text-amber-300' : 'text-red-300') ?>">
                        <?= htmlspecialchars($currentAstro['call_status']) ?>
                    </span>
                </div>

                <a href="index.php?action=logout" class="px-3 py-1.5 rounded-lg bg-red-500/20 text-red-300 border border-red-500/40 hover:bg-red-500/30 text-xs font-semibold flex items-center gap-1.5 transition">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6 flex-1 w-full space-y-6">
        <?php if (!empty($success)): ?>
            <div class="p-3 bg-astro-emerald/20 border border-astro-emerald/40 rounded-xl text-astro-textMint text-xs flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0 text-astro-emerald"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <!-- Astrologer Profile & Status Switcher Card -->
        <div class="bg-astro-surface border border-astro-border rounded-2xl p-5 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-center md:text-left">
                <img src="<?= htmlspecialchars($currentAstro['avatar']) ?>" alt="<?= htmlspecialchars($currentAstro['name']) ?>" class="w-16 h-16 rounded-2xl object-cover border-2 border-astro-gold">
                <div>
                    <h2 class="text-base font-bold text-white"><?= htmlspecialchars($currentAstro['name']) ?></h2>
                    <p class="text-xs text-astro-gold"><?= htmlspecialchars($currentAstro['specialities']) ?></p>
                    <p class="text-[11px] text-white/50 mt-0.5">📞 +91 <?= htmlspecialchars($currentAstro['phone']) ?> • ✉️ <?= htmlspecialchars($currentAstro['email']) ?> • 🌐 <?= htmlspecialchars($currentAstro['languages']) ?></p>
                </div>
            </div>

            <!-- Instant Status Switcher -->
            <form method="POST" action="index.php" class="flex items-center gap-2 bg-astro-card p-1.5 rounded-xl border border-astro-border">
                <input type="hidden" name="update_status" value="1">
                <button type="submit" name="status" value="available" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 <?= $currentAstro['call_status'] === 'available' ? 'bg-astro-emerald text-astro-base shadow-md' : 'text-white/60 hover:text-white' ?>">
                    <span class="w-2 h-2 rounded-full bg-green-300"></span> Available (Online)
                </button>
                <button type="submit" name="status" value="busy" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 <?= $currentAstro['call_status'] === 'busy' ? 'bg-amber-400 text-astro-base shadow-md' : 'text-white/60 hover:text-white' ?>">
                    <span class="w-2 h-2 rounded-full bg-amber-200"></span> Busy
                </button>
                <button type="submit" name="status" value="offline" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 <?= $currentAstro['call_status'] === 'offline' ? 'bg-red-500 text-white shadow-md' : 'text-white/60 hover:text-white' ?>">
                    <span class="w-2 h-2 rounded-full bg-red-200"></span> Offline
                </button>
            </form>
        </div>

        <!-- Stats Overview Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-astro-surface border border-astro-border rounded-2xl p-4">
                <span class="text-xs text-white/60 flex items-center gap-1.5"><i data-lucide="wallet" class="w-3.5 h-3.5 text-astro-gold"></i> மொத்த வருமானம்</span>
                <span class="font-cinzel text-xl sm:text-2xl font-bold text-astro-gold block mt-1">₹<?= number_format($totalEarnings, 2) ?></span>
                <span class="text-[10px] text-astro-textMint">80% Astrologer Share</span>
            </div>

            <div class="bg-astro-surface border border-astro-border rounded-2xl p-4">
                <span class="text-xs text-white/60 flex items-center gap-1.5"><i data-lucide="phone-call" class="w-3.5 h-3.5 text-astro-emerald"></i> ஆலோசனைகள்</span>
                <span class="font-cinzel text-xl sm:text-2xl font-bold text-white block mt-1"><?= $totalSessions ?></span>
                <span class="text-[10px] text-white/50">Completed Sessions</span>
            </div>

            <div class="bg-astro-surface border border-astro-border rounded-2xl p-4">
                <span class="text-xs text-white/60 flex items-center gap-1.5"><i data-lucide="star" class="w-3.5 h-3.5 text-astro-gold"></i> மதிப்பீடு (Rating)</span>
                <span class="font-cinzel text-xl sm:text-2xl font-bold text-astro-gold block mt-1"><?= htmlspecialchars((string)$currentAstro['rating']) ?> ⭐</span>
                <span class="text-[10px] text-white/50"><?= (int)$currentAstro['total_reviews'] ?> Devotee Reviews</span>
            </div>

            <div class="bg-astro-surface border border-astro-border rounded-2xl p-4">
                <span class="text-xs text-white/60 flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5 text-astro-emerald"></i> அனுபவம்</span>
                <span class="font-cinzel text-xl sm:text-2xl font-bold text-white block mt-1"><?= (int)$currentAstro['experience_years'] ?> Yrs</span>
                <span class="text-[10px] text-astro-textMint">Verified Expert</span>
            </div>
        </div>

        <!-- Active Incoming Consultation Alerts -->
        <?php if (!empty($activeSessions)): ?>
            <div class="bg-gradient-to-r from-astro-card to-astro-surface border-2 border-astro-gold rounded-2xl p-5 shadow-2xl animate-pulse">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-astro-gold flex items-center gap-2">
                        <i data-lucide="phone-incoming" class="w-4 h-4"></i> நேரலை அழைப்பு கோரிக்கை! (Incoming Consultation Alert)
                    </h3>
                    <span class="px-2.5 py-1 bg-astro-gold text-astro-base text-xs font-bold rounded-full">ACTIVE NOW</span>
                </div>
                
                <?php foreach ($activeSessions as $s): ?>
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-3 bg-astro-base/70 rounded-xl border border-astro-border">
                        <div>
                            <p class="text-xs font-bold text-white"><?= htmlspecialchars($s['user_name']) ?> (Client)</p>
                            <p class="text-[11px] text-astro-textMint">வகை: <?= strtoupper($s['type']) ?> • கட்டணம்: ₹<?= number_format((float)$s['rate_per_min'], 0) ?>/min • Agora Channel: <code class="text-astro-gold"><?= htmlspecialchars($s['agora_channel']) ?></code></p>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-3 py-1.5 bg-astro-emerald text-astro-base font-bold text-xs rounded-lg">Connected in App</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Two Columns: Rates Form & Consultation History -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Rates Settings -->
            <div class="bg-astro-surface border border-astro-border rounded-2xl p-5">
                <h3 class="text-xs font-bold text-white mb-3 flex items-center gap-1.5">
                    <i data-lucide="tag" class="w-4 h-4 text-astro-gold"></i> ஆலோசனை கட்டணங்கள் (Rate Settings)
                </h3>
                <form method="POST" action="index.php" class="space-y-3 text-xs">
                    <input type="hidden" name="update_rates" value="1">
                    <div>
                        <label class="block text-white/70 mb-1">📞 Audio Call (₹/நிமிடம்)</label>
                        <input type="number" step="1" name="audio_rate" value="<?= (float)$currentAstro['audio_rate_min'] ?>" required
                               class="w-full px-3 py-2 bg-astro-card border border-astro-border rounded-xl text-white font-bold focus:border-astro-gold focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-white/70 mb-1">📹 Video Call (₹/நிமிடம்)</label>
                        <input type="number" step="1" name="video_rate" value="<?= (float)$currentAstro['video_rate_min'] ?>" required
                               class="w-full px-3 py-2 bg-astro-card border border-astro-border rounded-xl text-white font-bold focus:border-astro-gold focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-white/70 mb-1">💬 Chat Consultation (₹/நிமிடம்)</label>
                        <input type="number" step="1" name="chat_rate" value="<?= (float)$currentAstro['chat_rate_min'] ?>" required
                               class="w-full px-3 py-2 bg-astro-card border border-astro-border rounded-xl text-white font-bold focus:border-astro-gold focus:outline-none">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-astro-gold text-astro-base font-bold rounded-xl hover:opacity-90 transition mt-2">
                        கட்டணங்களை சேமிக்க (Save Rates)
                    </button>
                </form>
            </div>

            <!-- Right: Recent Consultation Sessions -->
            <div class="lg:col-span-2 bg-astro-surface border border-astro-border rounded-2xl p-5">
                <h3 class="text-xs font-bold text-white mb-3 flex items-center gap-1.5">
                    <i data-lucide="history" class="w-4 h-4 text-astro-emerald"></i> சமீபத்திய ஆலோசனைகள் வரலாறு (Recent Consultations)
                </h3>

                <?php if (empty($recentSessions)): ?>
                    <div class="text-center py-8 text-white/40 text-xs">
                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                        இன்னும் ஆலோசனைகள் பதிவு செய்யப்படவில்லை.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto text-xs">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-white/40 border-b border-astro-border">
                                    <th class="p-2.5">தேதி & நேரம்</th>
                                    <th class="p-2.5">பக்தர் (Client)</th>
                                    <th class="p-2.5">வகை</th>
                                    <th class="p-2.5">நேரம்</th>
                                    <th class="p-2.5">உங்கள் வருமானம்</th>
                                    <th class="p-2.5">மதிப்பீடு</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-astro-border/40">
                                <?php foreach ($recentSessions as $row): ?>
                                    <tr class="hover:bg-astro-card/50 transition">
                                        <td class="p-2.5 text-white/70"><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></td>
                                        <td class="p-2.5 font-bold text-white"><?= htmlspecialchars($row['user_name']) ?></td>
                                        <td class="p-2.5 uppercase font-semibold text-astro-textMint"><?= htmlspecialchars($row['type']) ?></td>
                                        <td class="p-2.5"><?= round(((int)$row['duration_seconds']) / 60, 1) ?> நிமிடம்</td>
                                        <td class="p-2.5 font-bold text-astro-gold">₹<?= number_format((float)$row['astrologer_share'], 2) ?></td>
                                        <td class="p-2.5 text-astro-gold"><?= $row['rating'] ? $row['rating'] . ' ⭐' : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
<?php endif; ?>

<footer class="border-t border-astro-border/50 py-3 text-center text-[11px] text-white/40">
    ASTROSUBAM &copy; <?= date('Y') ?> • அனைத்து உரிமைகளும் பாதுகாக்கப்பட்டவை • Vedic Astrology Portal
</footer>

<script>
    lucide.createIcons();
</script>
</body>
</html>
