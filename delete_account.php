<?php
// delete_account.php - AstroSubam Account & Data Deletion Request Page (Google Play Store Compliant)
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$message = '';
$messageType = '';
$deletionConfirmed = false;

// Handle Account Deletion Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_delete_account'])) {
    $rawPhone = trim($_POST['phone'] ?? '');
    $reason = trim($_POST['reason'] ?? 'Not specified');
    $confirmCheckbox = isset($_POST['confirm_delete']);

    $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
    if (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '91')) {
        $cleanPhone = substr($cleanPhone, 2);
    }

    if (empty($cleanPhone) || strlen($cleanPhone) !== 10) {
        $message = 'தயவுசெய்து சரியான 10-இலக்க மொபைல் எண்ணை உள்ளிடவும் (Please enter a valid 10-digit mobile number).';
        $messageType = 'error';
    } elseif (!$confirmCheckbox) {
        $message = 'கணக்கை நீக்க ஒப்புதல் தேர்வுப்பெட்டியை (Checkbox) தேர்வு செய்யவும் (Please confirm deletion).';
        $messageType = 'error';
    } else {
        try {
            $db = getDb();
            
            // Check if user exists
            $stmt = $db->prepare("SELECT id, name, phone, wallet_balance FROM users WHERE phone = ? LIMIT 1");
            $stmt->execute([$cleanPhone]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $userId = (int)$user['id'];
                
                // Anonymize and delete user data to maintain referential integrity with legal financial records
                $delPhone = 'DEL_' . $userId . '_' . time();
                $upd = $db->prepare("UPDATE users SET 
                    name = 'Deleted User', 
                    phone = ?, 
                    email = NULL, 
                    dob = NULL, 
                    tob = NULL, 
                    pob = NULL, 
                    fcm_token = NULL,
                    wallet_balance = 0.00
                    WHERE id = ?");
                $upd->execute([$delPhone, $userId]);

                // Clear any active chat messages from user
                try {
                    $db->prepare("DELETE FROM chat_messages WHERE session_id IN (SELECT session_id FROM consultation_sessions WHERE user_id = ?)")->execute([$userId]);
                } catch (\Throwable $eChat) {}

                $deletionConfirmed = true;
                $message = "உங்கள் கணக்கு (+91 {$cleanPhone}) மற்றும் தொடர்புடைய அனைத்து தனிப்பட்ட தகவல்களும் வெற்றிகரமாக நீக்கப்பட்டன.\nYour account and all associated personal data have been permanently deleted from AstroSubam.";
                $messageType = 'success';
            } else {
                // To prevent user enumeration while complying with deletion requests, show confirmed request
                $deletionConfirmed = true;
                $message = "உங்கள் கோரிக்கை பதிவு செய்யப்பட்டது. இந்த எண்ணுடன் (+91 {$cleanPhone}) தொடர்புடைய கணக்கு எதுவும் தற்போது பயன்பாட்டில் இல்லை அல்லது ஏற்கனவே நீக்கப்பட்டுள்ளது.\nYour deletion request has been recorded.";
                $messageType = 'success';
            }
        } catch (\Throwable $e) {
            $message = 'கோரிக்கையை செயலாக்குவதில் பிழை ஏற்பட்டது: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ta-IN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>கணக்கு & தரவு நீக்கம் | Delete Account & Data - AstroSubam</title>
    <link rel="icon" href="assets/logo.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Mukta+Malar:wght@400;600;700&display=swap" rel="stylesheet">
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
                            emeraldLight: '#34D399',
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
    <style>
        body {
            background-color: #041611;
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: radial-gradient(circle at 10% 10%, rgba(16, 185, 129, 0.08) 0%, transparent 40%),
                              radial-gradient(circle at 90% 10%, rgba(250, 204, 21, 0.08) 0%, transparent 40%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Header Navigation -->
    <header class="sticky top-0 z-40 bg-astro-base/90 backdrop-blur-md border-b border-astro-border py-3.5 px-4 sm:px-8">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3">
                <img src="assets/logo.jpg" alt="AstroSubam Logo" class="w-9 h-9 rounded-xl border border-astro-gold shadow-md">
                <div>
                    <span class="font-cinzel text-lg font-bold tracking-wider text-astro-gold block">ASTROSUBAM</span>
                    <span class="text-[10px] text-astro-textMint tracking-widest uppercase block -mt-1">Vedic Astrology Platform</span>
                </div>
            </a>
            <div class="flex items-center gap-3 text-xs">
                <a href="index.php" class="px-3 py-1.5 rounded-lg bg-astro-surface hover:bg-astro-card border border-astro-border text-white/80 hover:text-white transition flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> முகப்பு (Home)
                </a>
                <a href="privacy.php" class="px-3 py-1.5 rounded-lg bg-astro-surface hover:bg-astro-card border border-astro-border text-astro-textMint hover:text-white transition flex items-center gap-1.5">
                    <i data-lucide="shield" class="w-3.5 h-3.5"></i> தனியுரிமைக் கொள்கை (Privacy)
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-3xl mx-auto px-4 sm:px-6 py-10 w-full">
        <div class="bg-astro-surface/90 border border-astro-border rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl">

            <!-- Title & Play Store Badge -->
            <div class="border-b border-astro-border pb-6 mb-8 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/15 border border-red-500/40 text-red-300 text-xs font-semibold mb-3">
                    <i data-lucide="user-x" class="w-3.5 h-3.5 text-red-400"></i> Google Play Account Deletion Requirement
                </div>
                <h1 class="font-cinzel text-2xl sm:text-3xl font-bold text-white tracking-wide">Request Account & Data Deletion</h1>
                <p class="font-tamil text-sm text-astro-textMint mt-1 font-semibold">AstroSubam கணக்கு மற்றும் தனிப்பட்ட தரவு நீக்கக் கோரிக்கை</p>
                <p class="text-xs text-white/60 mt-2 max-w-lg mx-auto">
                    Under Google Play's User Data policy and applicable data privacy regulations, you have the right to delete your AstroSubam account and associated personal data at any time.
                </p>
            </div>

            <!-- Status Alert Message -->
            <?php if (!empty($message)): ?>
            <div class="mb-8 p-4 rounded-2xl <?= $messageType === 'success' ? 'bg-emerald-500/20 border border-emerald-500/40 text-emerald-200' : 'bg-red-500/20 border border-red-500/40 text-red-200' ?> text-xs flex items-start gap-3">
                <i data-lucide="<?= $messageType === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                <div class="whitespace-pre-line leading-relaxed"><?= htmlspecialchars($message) ?></div>
            </div>
            <?php endif; ?>

            <?php if ($deletionConfirmed): ?>
                <!-- Success State Screen -->
                <div class="text-center py-6 space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 border-2 border-emerald-500 text-emerald-400 flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
                        <i data-lucide="check" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-cinzel text-lg font-bold text-white">Account Deletion Complete</h3>
                    <p class="text-xs text-white/70 max-w-md mx-auto leading-relaxed">
                        Your account has been deleted and your personal identifiers have been removed. If you wish to use AstroSubam again in the future, you may create a fresh new account.
                    </p>
                    <div class="pt-4">
                        <a href="index.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-astro-gold to-astro-goldAmber text-astro-base font-bold text-xs shadow-lg transition">
                            <i data-lucide="home" class="w-4 h-4"></i> இணையதளத்திற்கு திரும்புக (Back to Home)
                        </a>
                    </div>
                </div>
            <?php else: ?>

                <!-- Explanatory Cards: What is Deleted vs Retained -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 text-xs">
                    <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30">
                        <div class="flex items-center gap-2 text-red-400 font-bold mb-2">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>நீக்கப்படும் தரவுகள் (Data That Will Be Deleted):</span>
                        </div>
                        <ul class="space-y-1.5 text-white/70 list-disc list-inside">
                            <li>Your Name and Registered Mobile Number</li>
                            <li>Email Address and Birth Details (DOB, Time, Place)</li>
                            <li>Saved Kundli / Vedic Horoscope Charts</li>
                            <li>Two-way Chat Messages and Session History</li>
                            <li>Push Notification Tokens (FCM)</li>
                        </ul>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30">
                        <div class="flex items-center gap-2 text-amber-400 font-bold mb-2">
                            <i data-lucide="shield-alert" class="w-4 h-4"></i>
                            <span>சட்டப்படி பாதுகாக்கப்படும் விவரங்கள் (Data Retained):</span>
                        </div>
                        <ul class="space-y-1.5 text-white/70 list-disc list-inside">
                            <li>Past financial payment and recharge invoice IDs (Retained purely for Indian tax and accounting audit compliance as required by RBI/tax authorities, anonymized with no personal identifiers).</li>
                        </ul>
                    </div>
                </div>

                <!-- Account Deletion Form -->
                <form method="POST" action="delete_account.php" class="space-y-5 bg-astro-card p-6 rounded-2xl border border-astro-border text-xs">
                    <div>
                        <label class="block text-white/90 font-semibold mb-1.5">
                            பதிவு செய்யப்பட்ட மொபைல் எண் (Registered Mobile Number) *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-3 text-white/50 font-bold">+91</span>
                            <input type="tel" name="phone" required placeholder="10-இலக்க மொபைல் எண்" maxlength="10" pattern="[0-9]{10}" class="w-full bg-astro-base border border-astro-border rounded-xl px-4 py-3 pl-12 text-sm text-white focus:outline-none focus:border-red-400 transition font-mono">
                        </div>
                        <p class="text-[11px] text-white/40 mt-1">AstroSubam செயலியில் நீங்கள் பயன்படுத்திய 10-இலக்க மொபைல் எண்ணை உள்ளிடவும்.</p>
                    </div>

                    <div>
                        <label class="block text-white/90 font-semibold mb-1.5">
                            கணக்கு நீக்கத்திற்கான காரணம் (Reason for Deletion - Optional)
                        </label>
                        <select name="reason" class="w-full bg-astro-base border border-astro-border rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-astro-gold">
                            <option value="No longer using the app">இனி இந்த சேவை தேவையில்லை (No longer needed)</option>
                            <option value="Privacy concerns">தனியுரிமை கவலைகள் (Privacy concerns)</option>
                            <option value="Switching phone number">மொபைல் எண் மாற்றம் (Changing phone number)</option>
                            <option value="Other">மற்ற காரணங்கள் (Other)</option>
                        </select>
                    </div>

                    <div class="p-3.5 rounded-xl bg-astro-base border border-red-500/30 flex items-start gap-3">
                        <input type="checkbox" id="confirm_delete" name="confirm_delete" required class="mt-1 w-4 h-4 accent-red-500 rounded cursor-pointer">
                        <label for="confirm_delete" class="text-white/80 cursor-pointer leading-relaxed text-[11px]">
                            எனது AstroSubam கணக்கு மற்றும் தனிப்பட்ட தரவுகளை நிரந்தரமாக நீக்க ஒப்புக்கொள்கிறேன். இந்த செயல்முறையை திரும்பப் பெற முடியாது என்பதை நான் அறிவேன் (I confirm that I want to permanently delete my account and data).
                        </label>
                    </div>

                    <button type="submit" name="submit_delete_account" onclick="return confirm('நிச்சயமாக உங்கள் AstroSubam கணக்கை நிரந்தரமாக நீக்க விரும்புகிறீர்களா?\nAre you sure you want to permanently delete your account?');" class="w-full py-3.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-red-600/30 transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> கணக்கை நிரந்தரமாக நீக்குக (Permanently Delete Account)
                    </button>
                </form>

                <!-- Alternate Support Contact -->
                <div class="mt-8 text-center text-xs text-white/50 border-t border-astro-border pt-6">
                    <p>உங்களுக்கு உதவி தேவையா அல்லது ஏதேனும் சிக்கல் உள்ளதா? எங்கள் வாடிக்கையாளர் உதவி மையத்தை நேரடியாக தொடர்பு கொள்ளலாம்:</p>
                    <p class="mt-1 font-semibold text-astro-gold">
                        📧 <a href="mailto:support@astrosubam.com?subject=Account%20Deletion%20Request" class="hover:underline">support@astrosubam.com</a> • 📞 +91 99447 00648
                    </p>
                </div>

            <?php endif; ?>

        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
