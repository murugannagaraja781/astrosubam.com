<?php
// index.php - AstroSubam Vedic Astrology & Live Consultation Web Portal
// Theme: Vedic Emerald Green & Solar Yellow/Gold Luxury Design System
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

// Fetch astrologers from MySQL database if available, else fallback to default seed
$astrologers = [];
try {
    $db = getDb();
    $stmt = $db->query("SELECT * FROM astrologers ORDER BY rating DESC");
    $astrologers = $stmt->fetchAll();
} catch (\Throwable $e) {
    // Fallback if DB is initializing
    $astrologers = [
        [
            'id' => 1,
            'name' => 'Guruji S. Sundaramurthy',
            'specialities' => 'Vedic Astrology • KP System • Marriage Compatibility',
            'languages' => 'Tamil, English',
            'experience_years' => 22,
            'rating' => 4.95,
            'total_reviews' => 450,
            'audio_rate_min' => 20.00,
            'video_rate_min' => 35.00,
            'chat_rate_min' => 12.00,
            'call_status' => 'available',
            'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80',
        ],
        [
            'id' => 2,
            'name' => 'Dr. K. Jayalakshmi (Gold Medalist)',
            'specialities' => 'Nadi Astrology • Career & Business Astro • Gemology',
            'languages' => 'Tamil, English, Telugu',
            'experience_years' => 16,
            'rating' => 4.88,
            'total_reviews' => 312,
            'audio_rate_min' => 18.00,
            'video_rate_min' => 30.00,
            'chat_rate_min' => 10.00,
            'call_status' => 'available',
            'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80',
        ],
        [
            'id' => 3,
            'name' => 'Acharya Ramanan Sarma',
            'specialities' => 'Prasanna Jyothidam • Dosha Nivarana • Vasthu Sastra',
            'languages' => 'Tamil, Sanskrit, Hindi',
            'experience_years' => 14,
            'rating' => 4.92,
            'total_reviews' => 280,
            'audio_rate_min' => 15.00,
            'video_rate_min' => 25.00,
            'chat_rate_min' => 10.00,
            'call_status' => 'available',
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
        ],
        [
            'id' => 4,
            'name' => 'Astrologer Meenakshi Ammal',
            'specialities' => 'Numerology • Tarot Reader • Child Horoscope',
            'languages' => 'Tamil, Malayalam',
            'experience_years' => 19,
            'rating' => 4.91,
            'total_reviews' => 390,
            'audio_rate_min' => 16.00,
            'video_rate_min' => 28.00,
            'chat_rate_min' => 10.00,
            'call_status' => 'available',
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="ta-IN" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROSUBAM | 🌟 வேத ஜோதிடம் & நேரலை ஆலோசனை (Call, Video & Chat)</title>
    <meta name="description" content="AstroSubam - Authentic Vedic Astrology consultations via live Audio Call, Video Call, and Chat. Talk with verified Gurujis instantly.">
    <link rel="icon" href="assets/logo.jpg">
    <!-- Google Fonts: Cinzel & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Mukta+Malar:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN with custom config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        astro: {
                            base: '#041611',        // Deep Emerald Forest Night
                            surface: '#08231B',     // Sacred Forest Surface
                            card: '#0C2D23',        // Frosted Jade Card
                            cardHover: '#10392C',   // Hover Jade Card
                            border: 'rgba(16, 185, 129, 0.22)',
                            emerald: '#10B981',     // Sacred Basil / Emerald (Budha)
                            emeraldLight: '#34D399',
                            emeraldDark: '#064E3B',
                            gold: '#FACC15',        // Solar Yellow / Guru Brihaspati Gold
                            goldAmber: '#F59E0B',   // Deep Amber Turmeric Gold
                            goldLight: '#FEF08A',   // Champagne Light Gold
                            goldDark: '#B45309',
                            textMuted: '#94A3B8',
                            textMint: '#A7F3D0',
                        }
                    },
                    fontFamily: {
                        cinzel: ['Cinzel', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        tamil: ['"Mukta Malar"', 'sans-serif'],
                    },
                    boxShadow: {
                        'glow-gold': '0 0 25px rgba(250, 204, 21, 0.28)',
                        'glow-emerald': '0 0 30px rgba(16, 185, 129, 0.25)',
                        'card-elevate': '0 12px 36px -10px rgba(0, 0, 0, 0.65)',
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Custom Customizations & Glassmorphism */
        body {
            background-color: #041611;
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(16, 185, 129, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 85% 15%, rgba(250, 204, 21, 0.09) 0%, transparent 45%),
                radial-gradient(circle at 50% 75%, rgba(16, 185, 129, 0.06) 0%, transparent 50%);
            background-attachment: fixed;
        }

        .glass-panel {
            background: rgba(12, 45, 35, 0.68);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(16, 185, 129, 0.22);
        }

        .glass-card {
            background: rgba(8, 35, 27, 0.75);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .glass-card:hover {
            border-color: rgba(250, 204, 21, 0.55);
            box-shadow: 0 12px 35px -8px rgba(250, 204, 21, 0.2), 0 0 20px rgba(16, 185, 129, 0.2);
            transform: translateY(-4px);
        }

        .btn-gold {
            background: linear-gradient(135deg, #FACC15 0%, #F59E0B 100%);
            color: #041611;
            font-weight: 700;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 18px rgba(250, 204, 21, 0.35);
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, #FEF08A 0%, #FACC15 100%);
            box-shadow: 0 6px 24px rgba(250, 204, 21, 0.55);
            transform: scale(1.02);
        }

        .btn-emerald {
            background: rgba(16, 185, 129, 0.15);
            color: #A7F3D0;
            border: 1px solid rgba(16, 185, 129, 0.45);
            transition: all 0.2s ease;
        }

        .btn-emerald:hover {
            background: rgba(16, 185, 129, 0.3);
            border-color: #10B981;
            color: #FFFFFF;
        }

        /* Pulsing Online Indicator */
        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .pulse-online {
            animation: pulse-ring 2s infinite cubic-bezier(0.4, 0, 0.6, 1);
        }

        /* Subtle sacred aura */
        .gold-text-glow {
            text-shadow: 0 0 15px rgba(250, 204, 21, 0.4);
        }

        .emerald-text-glow {
            text-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-astro-gold selection:text-astro-base">

    <!-- TOP ANNOUNCEMENT BAR (AUSPICIOUS SUBA NERAM TICKER) -->
    <div class="bg-gradient-to-r from-astro-emeraldDark via-[#08291F] to-astro-surface border-b border-astro-emerald/20 px-4 py-2 text-xs">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-astro-gold">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 animate-spin"></i>
                <span class="font-bold tracking-wide">இன்றைய சுப முகூர்த்த நேரம்:</span>
                <span class="text-white/90">காலை 09:15 AM - 10:15 AM | கௌரி நல்ல நேரம்: 01:30 PM - 02:30 PM</span>
            </div>
            <div class="flex items-center gap-4 text-white/80">
                <span class="hidden md:inline-flex items-center gap-1 text-astro-textMint">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-astro-gold"></i> 100% ரகசியத்தன்மை & பாதுகாப்பான சேவை
                </span>
                <a href="#astrologers" class="text-astro-gold hover:underline font-semibold flex items-center gap-1">
                    இப்போதே ஆலோசனை பெறுக <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN NAVIGATION BAR -->
    <header class="sticky top-0 z-40 glass-panel border-b border-astro-emerald/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo & Name -->
            <a href="index.php" class="flex items-center gap-3.5 group">
                <div class="relative w-12 h-12 rounded-xl overflow-hidden border-2 border-astro-gold shadow-glow-gold/40 group-hover:scale-105 transition duration-300">
                    <img src="assets/logo.jpg" alt="AstroSubam Logo" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-cinzel text-2xl font-bold tracking-wider text-astro-gold gold-text-glow">ASTROSUBAM</span>
                        <span class="bg-astro-gold/20 text-astro-gold text-[10px] font-bold px-2 py-0.5 rounded-full border border-astro-gold/40">VEDIC</span>
                    </div>
                    <p class="text-xs text-astro-textMint font-tamil tracking-wide">நல்வழி காட்டும் வேத ஜோதிட வழிகாட்டல்</p>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden lg:flex items-center gap-7 text-sm font-medium">
                <a href="#astrologers" class="text-white/90 hover:text-astro-gold transition flex items-center gap-1.5">
                    <i data-lucide="users" class="w-4 h-4 text-astro-emerald"></i> ஜோதிடர்கள் (Astrologers)
                </a>
                <a href="#panchangam" class="text-white/90 hover:text-astro-gold transition flex items-center gap-1.5">
                    <i data-lucide="sun" class="w-4 h-4 text-astro-gold"></i> பஞ்சாங்கம் & ராசிபலன்
                </a>
                <a href="#services" class="text-white/90 hover:text-astro-gold transition flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-4 h-4 text-astro-emerald"></i> சேவைகள்
                </a>
                <a href="admin/settings.php" target="_blank" class="text-white/60 hover:text-astro-gold transition text-xs flex items-center gap-1">
                    <i data-lucide="settings" class="w-3.5 h-3.5"></i> Admin
                </a>
            </nav>

            <!-- Wallet & User Actions -->
            <div class="flex items-center gap-3">
                <!-- Live Wallet Chip -->
                <button onclick="openWalletModal()" class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-astro-surface border border-astro-gold/40 hover:border-astro-gold transition shadow-sm hover:shadow-glow-gold/30">
                    <i data-lucide="wallet" class="w-4 h-4 text-astro-gold"></i>
                    <div class="text-left text-xs">
                        <span class="text-white/60 text-[10px] block leading-none">Wallet</span>
                        <span id="nav-wallet-balance" class="text-astro-gold font-bold text-sm">₹500.00</span>
                    </div>
                    <span class="w-5 h-5 rounded-full bg-astro-gold/20 flex items-center justify-center text-astro-gold text-xs font-bold ml-1">+</span>
                </button>

                <!-- Auth / Login Button -->
                <button onclick="openAuthModal()" id="btn-user-auth" class="btn-gold px-4 py-2 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-1.5">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span id="user-display-name">உள்நுழைக</span>
                </button>

                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-lg bg-astro-surface border border-astro-emerald/30 text-white">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-astro-emerald/20 bg-astro-surface/95 px-6 py-4 space-y-3">
            <a href="#astrologers" onclick="toggleMobileMenu()" class="block py-2 text-white font-medium hover:text-astro-gold">🌟 ஜோதிடர்கள் பட்டியல்</a>
            <a href="#panchangam" onclick="toggleMobileMenu()" class="block py-2 text-white font-medium hover:text-astro-gold">📅 இன்றைய பஞ்சாங்கம் & ராசிபலன்</a>
            <a href="#services" onclick="toggleMobileMenu()" class="block py-2 text-white font-medium hover:text-astro-gold">🔮 ஆலோசனைகள் & பரிகாரங்கள்</a>
            <a href="admin/settings.php" class="block py-2 text-white/60 hover:text-astro-gold text-xs">⚙️ Super Admin Settings</a>
        </div>
    </header>

    <!-- HERO SECTION: SACRED EMERALD & SOLAR GOLD FUSION -->
    <section class="relative overflow-hidden pt-10 pb-16 lg:py-20">
        <!-- Celestial Aura Blobs -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-astro-emerald/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute bottom-10 right-1/4 w-96 h-96 bg-astro-gold/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Column: Copy & CTAs -->
                <div class="lg:col-span-7 space-y-6">
                    <!-- Trust Pill -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-astro-surface border border-astro-gold/40 text-astro-gold text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-astro-emerald pulse-online"></span>
                        <span>100% அங்கீகரிக்கப்பட்ட வேத குருமார்கள் ஆன்லைனில் உள்ளனர்</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="font-cinzel text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
                        நேரலை <span class="text-transparent bg-clip-text bg-gradient-to-r from-astro-gold via-astro-goldLight to-astro-goldAmber">வேத ஜோதிட</span> ஆலோசனை
                    </h1>

                    <p class="text-astro-textMint/90 text-base sm:text-lg max-w-2xl font-tamil leading-relaxed">
                        திருமணம், தொழில், செல்வம், கல்வி, ஜாதக தோஷங்கள் மற்றும் குடும்ப அமைதிக்கான துல்லியமான தீர்வுகள். 
                        நேரலை <b>Call, Video & Chat</b> மூலம் அனுபவமிக்க ஜோதிடர்களிடம் உடனடி வழிகாட்டல் பெறுங்கள்.
                    </p>

                    <!-- Quick Consultation Rate Badges -->
                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-astro-surface border border-astro-emerald/30 text-xs">
                            <i data-lucide="phone-call" class="w-4 h-4 text-astro-gold"></i>
                            <div>
                                <span class="text-white/60 block text-[10px]">ஆடியோ அழைப்பு</span>
                                <span class="text-astro-gold font-bold">₹15 - ₹20 / நிமிடம்</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-astro-surface border border-astro-emerald/30 text-xs">
                            <i data-lucide="video" class="w-4 h-4 text-astro-emerald"></i>
                            <div>
                                <span class="text-white/60 block text-[10px]">வீடியோ தரிசனம்</span>
                                <span class="text-astro-gold font-bold">₹25 - ₹35 / நிமிடம்</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-astro-surface border border-astro-emerald/30 text-xs">
                            <i data-lucide="message-square" class="w-4 h-4 text-astro-gold"></i>
                            <div>
                                <span class="text-white/60 block text-[10px]">நேரலை சாட்</span>
                                <span class="text-astro-gold font-bold">₹10 - ₹12 / நிமிடம்</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Action Buttons -->
                    <div class="flex flex-wrap items-center gap-4 pt-4">
                        <a href="#astrologers" class="btn-gold px-8 py-3.5 rounded-xl font-bold flex items-center gap-2 text-sm sm:text-base">
                            <i data-lucide="sparkles" class="w-5 h-5"></i> இப்போதே பேசுக (Consult Now)
                        </a>
                        <a href="#panchangam" class="btn-emerald px-6 py-3.5 rounded-xl font-semibold flex items-center gap-2 text-sm sm:text-base">
                            <i data-lucide="compass" class="w-5 h-5 text-astro-gold"></i> இன்றைய ராசிபலன் பார்க்க
                        </a>
                    </div>

                    <!-- Metric Counters -->
                    <div class="grid grid-cols-3 gap-4 pt-6 border-t border-astro-emerald/20">
                        <div>
                            <span class="font-cinzel text-2xl sm:text-3xl font-extrabold text-astro-gold">50+</span>
                            <p class="text-xs text-white/70 font-tamil">அங்கீகரிக்கப்பட்ட குருக்கள்</p>
                        </div>
                        <div>
                            <span class="font-cinzel text-2xl sm:text-3xl font-extrabold text-astro-emeraldLight">1,00,000+</span>
                            <p class="text-xs text-white/70 font-tamil">மகிழ்ச்சியான பக்தர்கள்</p>
                        </div>
                        <div>
                            <span class="font-cinzel text-2xl sm:text-3xl font-extrabold text-astro-gold">4.95 ★</span>
                            <p class="text-xs text-white/70 font-tamil">வாடிக்கையாளர் திருப்தி</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Featured Astrologer Showcase Card -->
                <div class="lg:col-span-5">
                    <div class="glass-card rounded-3xl p-6 sm:p-8 relative border border-astro-gold/30 shadow-card-elevate">
                        <!-- Top Tag -->
                        <div class="flex items-center justify-between mb-4">
                            <span class="bg-astro-gold/20 text-astro-gold text-xs font-bold px-3 py-1 rounded-full border border-astro-gold/40 flex items-center gap-1.5">
                                <i data-lucide="award" class="w-3.5 h-3.5"></i> சிறப்பு முதன்மை ஜோதிடர்
                            </span>
                            <div class="flex items-center gap-1 text-xs text-astro-emeraldLight font-semibold">
                                <span class="w-2.5 h-2.5 rounded-full bg-astro-emerald pulse-online"></span>
                                ஆன்லைனில் உள்ளார்
                            </div>
                        </div>

                        <!-- Guruji Profile -->
                        <div class="flex items-center gap-4 mb-5">
                            <div class="relative w-20 h-20 rounded-2xl overflow-hidden border-2 border-astro-gold shadow-glow-gold/40 flex-shrink-0">
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80" alt="Guruji" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-white font-cinzel">Guruji S. Sundaramurthy</h3>
                                <p class="text-xs text-astro-gold font-medium">வேத ஜோதிடம் & KP சிஸ்டம் நிபுணர்</p>
                                <p class="text-xs text-white/60">22 ஆண்டுகள் அனுபவம் • தமிழ், English</p>
                                <div class="flex items-center gap-1 text-astro-gold text-xs font-bold mt-1">
                                    <span>4.95 ★</span>
                                    <span class="text-white/40">(450+ ஆலோசனைகள்)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Specialization Badges -->
                        <div class="flex flex-wrap gap-1.5 mb-5 text-[11px]">
                            <span class="px-2.5 py-1 rounded-lg bg-astro-surface border border-astro-emerald/30 text-astro-textMint">திருமண பொருத்தம்</span>
                            <span class="px-2.5 py-1 rounded-lg bg-astro-surface border border-astro-emerald/30 text-astro-textMint">தொழில் யோகம்</span>
                            <span class="px-2.5 py-1 rounded-lg bg-astro-surface border border-astro-emerald/30 text-astro-textMint">தோஷ நிவர்த்தி</span>
                            <span class="px-2.5 py-1 rounded-lg bg-astro-surface border border-astro-emerald/30 text-astro-textMint">KP முறை</span>
                        </div>

                        <!-- Instant Action CTAs -->
                        <div class="grid grid-cols-3 gap-2 pt-2">
                            <button onclick="startConsultation(1, 'Chat', 12)" class="btn-emerald py-2.5 rounded-xl font-bold text-xs flex flex-col items-center justify-center gap-1">
                                <i data-lucide="message-square" class="w-4 h-4 text-astro-gold"></i>
                                <span>Chat ₹12/m</span>
                            </button>
                            <button onclick="startConsultation(1, 'Audio', 20)" class="btn-gold py-2.5 rounded-xl font-bold text-xs flex flex-col items-center justify-center gap-1">
                                <i data-lucide="phone-call" class="w-4 h-4"></i>
                                <span>Call ₹20/m</span>
                            </button>
                            <button onclick="startConsultation(1, 'Video', 35)" class="btn-emerald py-2.5 rounded-xl font-bold text-xs flex flex-col items-center justify-center gap-1">
                                <i data-lucide="video" class="w-4 h-4 text-astro-gold"></i>
                                <span>Video ₹35/m</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- QUICK CATEGORIES BAR -->
    <section class="border-y border-astro-emerald/20 bg-astro-surface/60 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <a href="#astrologers" class="glass-card p-3.5 rounded-2xl flex flex-col items-center text-center gap-2 group">
                    <div class="w-11 h-11 rounded-xl bg-astro-emerald/20 flex items-center justify-center text-astro-emerald group-hover:bg-astro-gold group-hover:text-astro-base transition duration-300">
                        <i data-lucide="phone" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-semibold text-white/90">நேரலை அழைப்பு</span>
                </a>
                <a href="#astrologers" class="glass-card p-3.5 rounded-2xl flex flex-col items-center text-center gap-2 group">
                    <div class="w-11 h-11 rounded-xl bg-astro-gold/20 flex items-center justify-center text-astro-gold group-hover:bg-astro-gold group-hover:text-astro-base transition duration-300">
                        <i data-lucide="video" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-semibold text-white/90">வீடியோ தரிசனம்</span>
                </a>
                <a href="#astrologers" class="glass-card p-3.5 rounded-2xl flex flex-col items-center text-center gap-2 group">
                    <div class="w-11 h-11 rounded-xl bg-astro-emerald/20 flex items-center justify-center text-astro-emerald group-hover:bg-astro-gold group-hover:text-astro-base transition duration-300">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-semibold text-white/90">சாட் ஆலோசனை</span>
                </a>
                <a href="#panchangam" class="glass-card p-3.5 rounded-2xl flex flex-col items-center text-center gap-2 group">
                    <div class="w-11 h-11 rounded-xl bg-astro-gold/20 flex items-center justify-center text-astro-gold group-hover:bg-astro-gold group-hover:text-astro-base transition duration-300">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-semibold text-white/90">இன்றைய பஞ்சாங்கம்</span>
                </a>
                <a href="#panchangam" class="glass-card p-3.5 rounded-2xl flex flex-col items-center text-center gap-2 group">
                    <div class="w-11 h-11 rounded-xl bg-astro-emerald/20 flex items-center justify-center text-astro-emerald group-hover:bg-astro-gold group-hover:text-astro-base transition duration-300">
                        <i data-lucide="heart" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-semibold text-white/90">திருமண பொருத்தம்</span>
                </a>
                <a href="#panchangam" class="glass-card p-3.5 rounded-2xl flex flex-col items-center text-center gap-2 group">
                    <div class="w-11 h-11 rounded-xl bg-astro-gold/20 flex items-center justify-center text-astro-gold group-hover:bg-astro-gold group-hover:text-astro-base transition duration-300">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xs font-semibold text-white/90">ஜாதக கணிப்பு</span>
                </a>
            </div>
        </div>
    </section>

    <!-- LIVE ASTROLOGERS MARKETPLACE SECTION -->
    <section id="astrologers" class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 text-astro-gold text-xs font-bold tracking-widest uppercase mb-2">
                        <i data-lucide="stars" class="w-4 h-4"></i> VERIFIED VEDIC ASTROLOGERS
                    </div>
                    <h2 class="font-cinzel text-2xl sm:text-4xl font-extrabold text-white">
                        அனுபவமிக்க <span class="text-transparent bg-clip-text bg-gradient-to-r from-astro-gold to-astro-goldAmber">ஜோதிட நிபுணர்கள்</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-astro-textMint mt-1 font-tamil">
                        உங்கள் வாழ்க்கைப் பிரச்னைகளுக்கு உரிய எளிய பரிகாரங்கள் & சரியான வழிகாட்டல்
                    </p>
                </div>

                <!-- Filter Pills -->
                <div class="flex flex-wrap items-center gap-2">
                    <button onclick="filterAstrologers('all')" class="astro-filter-btn px-4 py-1.5 rounded-full text-xs font-bold bg-astro-gold text-astro-base transition">அனைத்தும்</button>
                    <button onclick="filterAstrologers('vedic')" class="astro-filter-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-astro-surface border border-astro-emerald/30 text-white/80 hover:border-astro-gold transition">வேத ஜோதிடம்</button>
                    <button onclick="filterAstrologers('kp')" class="astro-filter-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-astro-surface border border-astro-emerald/30 text-white/80 hover:border-astro-gold transition">KP முறை</button>
                    <button onclick="filterAstrologers('nadi')" class="astro-filter-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-astro-surface border border-astro-emerald/30 text-white/80 hover:border-astro-gold transition">நாடி ஜோதிடம்</button>
                    <button onclick="filterAstrologers('prasanna')" class="astro-filter-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-astro-surface border border-astro-emerald/30 text-white/80 hover:border-astro-gold transition">பிரசன்னம்</button>
                </div>
            </div>

            <!-- Astrologers Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="astrologers-container">
                <?php foreach ($astrologers as $astro): 
                    $name = htmlspecialchars($astro['name']);
                    $specialities = htmlspecialchars($astro['specialities'] ?? 'Vedic Astrology');
                    $languages = htmlspecialchars($astro['languages'] ?? 'Tamil, English');
                    $exp = (int)($astro['experience_years'] ?? 10);
                    $rating = number_format((float)($astro['rating'] ?? 4.9), 2);
                    $reviews = (int)($astro['total_reviews'] ?? 200);
                    $audioRate = (int)($astro['audio_rate_min'] ?? 20);
                    $videoRate = (int)($astro['video_rate_min'] ?? 30);
                    $chatRate = (int)($astro['chat_rate_min'] ?? 10);
                    $avatar = htmlspecialchars($astro['avatar'] ?? 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80');
                    $status = $astro['call_status'] ?? 'available';
                    $id = (int)$astro['id'];
                ?>
                <div class="glass-card rounded-2xl p-5 relative border border-astro-emerald/20 flex flex-col justify-between group" data-category="<?= strtolower($specialities) ?>">
                    <!-- Card Header: Avatar & Info -->
                    <div>
                        <div class="flex items-start gap-4 mb-4">
                            <!-- Avatar with Online Dot -->
                            <div class="relative flex-shrink-0">
                                <img src="<?= $avatar ?>" alt="<?= $name ?>" class="w-16 h-16 rounded-2xl object-cover border-2 border-astro-gold/60 group-hover:border-astro-gold transition">
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full <?= $status === 'available' ? 'bg-astro-emerald pulse-online' : 'bg-amber-500' ?> border-2 border-astro-card"></span>
                            </div>

                            <!-- Astrologer Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-base text-white truncate font-cinzel"><?= $name ?></h3>
                                </div>
                                <p class="text-xs text-astro-gold font-medium truncate mt-0.5"><?= $specialities ?></p>
                                <p class="text-xs text-white/60 mt-0.5"><?= $exp ?> ஆண்டுகள் • <?= $languages ?></p>
                                
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="flex items-center gap-1 text-xs text-astro-gold font-bold bg-astro-gold/10 px-2 py-0.5 rounded-md border border-astro-gold/30">
                                        <i data-lucide="star" class="w-3 h-3 fill-astro-gold"></i> <?= $rating ?>
                                    </span>
                                    <span class="text-[11px] text-white/50"><?= $reviews ?>+ ஆலோசனைகள்</span>
                                </div>
                            </div>
                        </div>

                        <!-- Highlights Tags -->
                        <div class="flex flex-wrap gap-1.5 my-3 text-[11px]">
                            <span class="px-2 py-0.5 rounded-md bg-astro-base/80 border border-astro-emerald/25 text-astro-textMint">துல்லிய கணிப்பு</span>
                            <span class="px-2 py-0.5 rounded-md bg-astro-base/80 border border-astro-emerald/25 text-astro-textMint">நேரடி தீர்வு</span>
                            <span class="px-2 py-0.5 rounded-md bg-astro-base/80 border border-astro-emerald/25 text-astro-textMint">100% ரகசியம்</span>
                        </div>
                    </div>

                    <!-- Card Footer: Rate Cards & Action Buttons -->
                    <div class="pt-4 border-t border-astro-emerald/15 mt-2">
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Chat Button -->
                            <button onclick="startConsultation(<?= $id ?>, 'Chat', <?= $chatRate ?>, '<?= addslashes($name) ?>')" class="btn-emerald py-2 rounded-xl text-xs font-bold flex flex-col items-center justify-center gap-0.5">
                                <i data-lucide="message-square" class="w-3.5 h-3.5 text-astro-gold"></i>
                                <span>சாட்</span>
                                <span class="text-[10px] text-astro-gold font-semibold">₹<?= $chatRate ?>/நிமி</span>
                            </button>

                            <!-- Audio Call Button -->
                            <button onclick="startConsultation(<?= $id ?>, 'Audio', <?= $audioRate ?>, '<?= addslashes($name) ?>')" class="btn-gold py-2 rounded-xl text-xs font-bold flex flex-col items-center justify-center gap-0.5">
                                <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                                <span>அழைப்பு</span>
                                <span class="text-[10px] text-astro-base font-extrabold">₹<?= $audioRate ?>/நிமி</span>
                            </button>

                            <!-- Video Call Button -->
                            <button onclick="startConsultation(<?= $id ?>, 'Video', <?= $videoRate ?>, '<?= addslashes($name) ?>')" class="btn-emerald py-2 rounded-xl text-xs font-bold flex flex-col items-center justify-center gap-0.5">
                                <i data-lucide="video" class="w-3.5 h-3.5 text-astro-gold"></i>
                                <span>வீடியோ</span>
                                <span class="text-[10px] text-astro-gold font-semibold">₹<?= $videoRate ?>/நிமி</span>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- DAILY PANCHANGAM & KUNDLI / RASI PALAN SECTION -->
    <section id="panchangam" class="py-16 bg-astro-surface/40 border-y border-astro-emerald/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-12">
                <div class="inline-flex items-center gap-2 text-astro-gold text-xs font-bold tracking-widest uppercase mb-2">
                    <i data-lucide="sun" class="w-4 h-4"></i> DAILY VEDIC PANCHANGAM & RASIPALAN
                </div>
                <h2 class="font-cinzel text-2xl sm:text-4xl font-extrabold text-white">
                    இன்றைய <span class="text-transparent bg-clip-text bg-gradient-to-r from-astro-gold to-astro-goldAmber">சுப நேரங்கள் & ராசிபலன்</span>
                </h2>
                <p class="text-xs sm:text-sm text-astro-textMint mt-2 font-tamil">
                    நாளைய நற்காரியங்களை தொடங்குவதற்கு உகந்த சுப நேரம் மற்றும் தினசரி கிரக சஞ்சார பலன்கள்
                </p>
            </div>

            <!-- Panchangam Grid Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
                <div class="glass-card p-4 rounded-2xl border-l-4 border-l-astro-emerald">
                    <span class="text-xs text-astro-textMint font-semibold flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-4 h-4 text-astro-emerald"></i> நல்ல நேரம் (Nalla Neram)
                    </span>
                    <p class="text-base font-bold text-astro-gold mt-1">காலை: 09:15 AM - 10:15 AM</p>
                    <p class="text-xs text-white/70">மாலை: 04:45 PM - 05:45 PM</p>
                </div>

                <div class="glass-card p-4 rounded-2xl border-l-4 border-l-astro-gold">
                    <span class="text-xs text-astro-textMint font-semibold flex items-center gap-1.5">
                        <i data-lucide="sunrise" class="w-4 h-4 text-astro-gold"></i> கௌரி நல்ல நேரம்
                    </span>
                    <p class="text-base font-bold text-astro-gold mt-1">பிற்பகல்: 01:30 PM - 02:30 PM</p>
                    <p class="text-xs text-white/70">இரவு: 09:30 PM - 10:30 PM</p>
                </div>

                <div class="glass-card p-4 rounded-2xl border-l-4 border-l-red-500">
                    <span class="text-xs text-red-300 font-semibold flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-400"></i> ராகு காலம் (Rahu Kalam)
                    </span>
                    <p class="text-base font-bold text-red-400 mt-1">பிற்பகல்: 03:00 PM - 04:30 PM</p>
                    <p class="text-xs text-white/60">சுப காரியங்களை தவிர்க்கவும்</p>
                </div>

                <div class="glass-card p-4 rounded-2xl border-l-4 border-l-amber-500">
                    <span class="text-xs text-amber-300 font-semibold flex items-center gap-1.5">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-amber-400"></i> எமகண்டம் & குளிகை
                    </span>
                    <p class="text-base font-bold text-amber-300 mt-1">குளிகை: 12:00 PM - 01:30 PM</p>
                    <p class="text-xs text-white/60">எமகண்டம்: 09:00 AM - 10:30 AM</p>
                </div>
            </div>

            <!-- 12 Rasis Forecast Tabs / Grid -->
            <div class="glass-panel p-6 sm:p-8 rounded-3xl border border-astro-emerald/30">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-cinzel text-lg sm:text-xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-5 h-5 text-astro-gold"></i> 12 ராசிகளின் இன்றைய பலன்கள்
                    </h3>
                    <span class="text-xs text-astro-gold font-semibold">நவக்கிரக ஆசி பெற்ற பலன்கள்</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs font-tamil">
                    <div class="p-3.5 rounded-xl bg-astro-base/70 border border-astro-emerald/20 hover:border-astro-gold/50 transition">
                        <span class="text-astro-gold font-bold text-sm block mb-1">♈ மேஷம் (Aries)</span>
                        <p class="text-white/80">தொழில் முயற்சிகளில் பண வரவு உண்டு. புது முடிவுகள் எடுப்பதற்கு மிக அனுகூலமான நாள்.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-astro-base/70 border border-astro-emerald/20 hover:border-astro-gold/50 transition">
                        <span class="text-astro-gold font-bold text-sm block mb-1">♉ ரிஷபம் (Taurus)</span>
                        <p class="text-white/80">குடும்பத்தில் மகிழ்ச்சியும் நிம்மதியும் நிலவும். பிரயாணங்கள் நல்ல காரிய வெற்றியைத் தரும்.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-astro-base/70 border border-astro-emerald/20 hover:border-astro-gold/50 transition">
                        <span class="text-astro-gold font-bold text-sm block mb-1">♊ மிதுனம் (Gemini)</span>
                        <p class="text-white/80">உடல் ஆரோக்கியத்தில் கவனம் தேவை. ஆன்மீக வழிபாடுகள் மற்றும் பரிகாரங்களுக்கு உகந்தது.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-astro-base/70 border border-astro-emerald/20 hover:border-astro-gold/50 transition">
                        <span class="text-astro-gold font-bold text-sm block mb-1">♋ கடகம் (Cancer)</span>
                        <p class="text-white/80">பெரியோர்களின் ஆதரவு கிடைக்கும். வியாபார கூட்டு முயற்சிகளில் நல்ல லாபம் காண இயலும்.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-astro-base/70 border border-astro-emerald/20 hover:border-astro-gold/50 transition">
                        <span class="text-astro-gold font-bold text-sm block mb-1">♌ சிம்மம் (Leo)</span>
                        <p class="text-white/80">தலைமைப் பண்பு பிரகாசிக்கும். திருமண பேச்சுவார்த்தைகள் தடையின்றி நல்ல முடிவுக்கு வரும்.</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-astro-base/70 border border-astro-emerald/20 hover:border-astro-gold/50 transition">
                        <span class="text-astro-gold font-bold text-sm block mb-1">♍ கன்னி (Virgo)</span>
                        <p class="text-white/80">படைப்புத்திறன் சார்ந்த பணிகள் வெற்றி தரும். தெய்வீக அனுகிரகம் உங்கள் முதலீடுகளைக் காக்கும்.</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- WHY CHOOSE ASTROSUBAM TRUST BADGES -->
    <section id="services" class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-astro-gold text-xs font-bold uppercase tracking-widest">WHY CHOOSE ASTROSUBAM</span>
                <h2 class="font-cinzel text-2xl sm:text-3xl font-extrabold text-white mt-1">
                    ஏன் எங்களின் <span class="text-astro-gold">ஆஸ்ட்ரோசுபம்</span> ஆலோசனை?
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass-card p-6 rounded-3xl text-center flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-astro-emerald/20 flex items-center justify-center text-astro-emerald mb-4 border border-astro-emerald/40">
                        <i data-lucide="shield-check" class="w-7 h-7"></i>
                    </div>
                    <h3 class="font-bold text-base text-white mb-2 font-cinzel">100% ரகசியத்தன்மை & பாதுகாப்பு</h3>
                    <p class="text-xs text-white/70 leading-relaxed font-tamil">
                        உங்கள் ஜாதக விபரங்கள், ஆடியோ/வீடியோ உரையாடல்கள் அனைத்தும் அதியுயர் Agora RTC எண்ட்-டு-எண்ட் மறைகுறியாக்கத்துடன் பாதுகாக்கப்படுகிறது.
                    </p>
                </div>

                <div class="glass-card p-6 rounded-3xl text-center flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-astro-gold/20 flex items-center justify-center text-astro-gold mb-4 border border-astro-gold/40">
                        <i data-lucide="award" class="w-7 h-7"></i>
                    </div>
                    <h3 class="font-bold text-base text-white mb-2 font-cinzel">அங்கீகரிக்கப்பட்ட வேத வித்வான்கள்</h3>
                    <p class="text-xs text-white/70 leading-relaxed font-tamil">
                        10+ ஆண்டுகளுக்கு மேலான அனுபவம் கொண்ட தகுதியான ஜோதிடர்கள் மட்டுமே கடுமையான தேர்வு முறைக்கு பின் இணைக்கப்படுகின்றனர்.
                    </p>
                </div>

                <div class="glass-card p-6 rounded-3xl text-center flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-astro-emerald/20 flex items-center justify-center text-astro-emerald mb-4 border border-astro-emerald/40">
                        <i data-lucide="zap" class="w-7 h-7"></i>
                    </div>
                    <h3 class="font-bold text-base text-white mb-2 font-cinzel">நிமிடத்திற்கு கட்டணம் & வாலட் சேமிப்பு</h3>
                    <p class="text-xs text-white/70 leading-relaxed font-tamil">
                        நீங்கள் பேசும் வினாடிகளுக்கு மட்டுமே கட்டணம். மீதமுள்ள தொகை உங்கள் வாலட்டிலேயே பாதுகாப்பாக இருக்கும்.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="mt-auto border-t border-astro-emerald/20 bg-astro-base/90 pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-8 border-b border-astro-emerald/15">
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <img src="assets/logo.jpg" alt="Logo" class="w-8 h-8 rounded-lg border border-astro-gold">
                        <span class="font-cinzel text-lg font-bold text-astro-gold">ASTROSUBAM</span>
                    </div>
                    <p class="text-xs text-white/60 font-tamil leading-relaxed">
                        வேத ஜோதிடம், KP கணிதம் மற்றும் நாடி சாஸ்திரத்தின் மூலம் துல்லியமான எதிர்கால வழிகாட்டல்.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-astro-gold mb-3">ஆலோசனைகள்</h4>
                    <ul class="space-y-2 text-xs text-white/70">
                        <li><a href="#astrologers" class="hover:text-astro-gold">திருமண பொருத்தம்</a></li>
                        <li><a href="#astrologers" class="hover:text-astro-gold">வேலை & தொழில் மேன்மை</a></li>
                        <li><a href="#astrologers" class="hover:text-astro-gold">தோஷ நிவர்த்தி பரிகாரம்</a></li>
                        <li><a href="#astrologers" class="hover:text-astro-gold">குழந்தை பாக்கிய கணிப்பு</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-astro-gold mb-3">இணைப்புகள்</h4>
                    <ul class="space-y-2 text-xs text-white/70">
                        <li><a href="#panchangam" class="hover:text-astro-gold">இன்றைய பஞ்சாங்கம்</a></li>
                        <li><button onclick="openWalletModal()" class="hover:text-astro-gold">வாலட் ரீசார்ஜ்</button></li>
                        <li><a href="admin/settings.php" target="_blank" class="hover:text-astro-gold">Super Admin Panel</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-astro-gold mb-3">உதவி & ஆதரவு</h4>
                    <p class="text-xs text-white/60 mb-2">24/7 வாடிக்கையாளர் உதவி மையம்</p>
                    <p class="text-xs text-astro-gold font-bold">support@astrosubam.com</p>
                    <p class="text-xs text-white/70 mt-1">+91 99988 87770</p>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-white/50 gap-2">
                <p>© 2026 AstroSubam.com. All Rights Reserved. Auspicious Vedic Jyotish Consultation.</p>
                <p class="text-astro-gold/70">Sacred Green & Solar Gold Edition</p>
            </div>
        </div>
    </footer>

    <!-- ============================================== -->
    <!-- INTERACTIVE MODALS (CONSULTATION, CHAT, WALLET, AUTH) -->
    <!-- ============================================== -->

    <!-- 1. LIVE CALL / VIDEO CONSULTATION SIMULATOR MODAL (AGORA RTC ENGINE) -->
    <div id="call-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
        <div class="glass-panel w-full max-w-md rounded-3xl p-6 border-2 border-astro-gold/50 shadow-glow-gold relative flex flex-col items-center text-center">
            
            <!-- Close Modal -->
            <button onclick="closeCallModal()" class="absolute top-4 right-4 p-2 text-white/60 hover:text-white rounded-full bg-astro-surface">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Call Badge -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/20 text-red-400 text-xs font-bold border border-red-500/30 mb-4">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                <span id="call-type-title">Agora RTC Live Call</span> • <span id="call-timer">00:00</span>
            </div>

            <!-- Video Frame Container (when type === 'Video') -->
            <div id="call-video-container" class="hidden relative w-full h-56 rounded-2xl overflow-hidden border-2 border-astro-gold shadow-glow-gold/40 mb-3 bg-black">
                <img id="call-video-astro-img" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=600&q=80" alt="Video Feed" class="w-full h-full object-cover">
                <!-- PiP Self Camera -->
                <div class="absolute top-2 right-2 w-20 h-28 rounded-xl overflow-hidden border-2 border-astro-emerald shadow-md bg-astro-surface flex flex-col items-center justify-center text-white">
                    <i data-lucide="user" class="w-8 h-8 text-astro-gold"></i>
                    <span class="text-[9px] text-astro-textMint font-bold mt-1">You (Self)</span>
                </div>
                <div class="absolute bottom-2 left-2 px-2.5 py-1 rounded-lg bg-black/60 text-[10px] text-white flex items-center gap-1.5 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-astro-emerald animate-pulse"></span>
                    <span id="call-video-astro-name">Guruji S. Sundaramurthy</span>
                </div>
            </div>

            <!-- Audio Avatar Container (when type === 'Audio') -->
            <div id="call-audio-container" class="relative w-28 h-28 rounded-full overflow-hidden border-4 border-astro-gold shadow-glow-gold/50 mb-4 pulse-online">
                <img id="call-astro-img" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80" alt="Astro" class="w-full h-full object-cover">
            </div>

            <h3 id="call-astro-name" class="font-cinzel text-xl font-bold text-white">Guruji S. Sundaramurthy</h3>
            <p id="call-rate-desc" class="text-xs text-astro-gold mt-1 font-semibold">₹20 / நிமிடம் • Agora RTC Encrypted Channel</p>

            <!-- Live Billing Bar -->
            <div class="w-full bg-astro-surface/90 border border-astro-emerald/30 rounded-2xl p-3 my-5 text-xs flex items-center justify-between">
                <div>
                    <span class="text-white/60 block text-[10px]">பிடிக்கப்பட்ட தொகை</span>
                    <span id="call-deducted-amt" class="text-white font-bold text-sm">₹0.00</span>
                </div>
                <div class="text-right">
                    <span class="text-white/60 block text-[10px]">மீதமுள்ள வாலட்</span>
                    <span id="call-wallet-bal" class="text-astro-gold font-bold text-sm">₹500.00</span>
                </div>
            </div>

            <!-- Call Controls (Mute, Video Toggle, End, Speaker) -->
            <div class="flex items-center justify-center gap-4 sm:gap-6 mt-2">
                <button onclick="toggleCallMute(this)" id="btn-call-mic" title="Microphone Mute" class="w-12 h-12 rounded-full bg-astro-surface border border-astro-emerald/40 text-white flex items-center justify-center hover:bg-astro-card">
                    <i data-lucide="mic" class="w-5 h-5"></i>
                </button>

                <button onclick="toggleVideoFeed(this)" id="btn-call-video" title="Video Camera Toggle" class="hidden w-12 h-12 rounded-full bg-astro-surface border border-astro-gold/40 text-astro-gold flex items-center justify-center hover:bg-astro-card">
                    <i data-lucide="video" class="w-5 h-5"></i>
                </button>

                <button onclick="endLiveCall()" title="End Call" class="w-16 h-16 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-lg shadow-red-600/40 transform hover:scale-105 transition">
                    <i data-lucide="phone-off" class="w-7 h-7"></i>
                </button>

                <button onclick="toggleCallSpeaker(this)" id="btn-call-speaker" title="Speaker" class="w-12 h-12 rounded-full bg-astro-surface border border-astro-emerald/40 text-white flex items-center justify-center hover:bg-astro-card">
                    <i data-lucide="volume-2" class="w-5 h-5"></i>
                </button>
            </div>

            <p class="text-[11px] text-white/50 mt-5">60-வினாடி நேரடி தானியங்கி வாலட் பிடித்தம் செயலில் உள்ளது</p>
        </div>
    </div>

    <!-- 2. LIVE CHAT CONSULTATION MODAL -->
    <div id="chat-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md">
        <div class="glass-panel w-full max-w-lg h-[600px] rounded-3xl border border-astro-gold/40 shadow-glow-gold flex flex-col overflow-hidden">
            
            <!-- Chat Header -->
            <div class="bg-astro-surface px-5 py-3.5 border-b border-astro-emerald/20 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <img id="chat-astro-img" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80" alt="Astro" class="w-10 h-10 rounded-xl object-cover border border-astro-gold">
                    <div>
                        <h4 id="chat-astro-name" class="font-bold text-sm text-white font-cinzel">Guruji S. Sundaramurthy</h4>
                        <div class="flex items-center gap-1.5 text-[11px] text-astro-emeraldLight">
                            <span class="w-2 h-2 rounded-full bg-astro-emerald animate-pulse"></span>
                            நேரலை ஆலோசனை • ₹<span id="chat-rate-val">12</span>/நிமி
                        </div>
                    </div>
                </div>
                <button onclick="closeChatModal()" class="btn-emerald px-3 py-1.5 rounded-lg text-xs font-bold text-red-300 border-red-500/30 hover:bg-red-500/20">
                    முடிக்க (End)
                </button>
            </div>

            <!-- Chat Messages Container -->
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-astro-base/70">
                <!-- Astrologer initial message -->
                <div class="flex items-start gap-2 max-w-[85%]">
                    <div class="w-7 h-7 rounded-lg bg-astro-surface border border-astro-gold/40 flex items-center justify-center text-astro-gold text-xs font-bold flex-shrink-0">
                        ॐ
                    </div>
                    <div class="bg-astro-surface border border-astro-emerald/30 p-3 rounded-2xl rounded-tl-none text-xs text-white/90 leading-relaxed font-tamil">
                        வணக்கம்! நான் குருஜி. தங்களின் பிறப்பு தேதி (DOB), பிறந்த நேரம் (Time) மற்றும் பிறந்த ஊர் (Place of birth) பகிருங்கள். ஜாதகத்தை கணித்து விடை தருகிறேன்.
                    </div>
                </div>
            </div>

            <!-- Chat Input Box -->
            <form onsubmit="sendChatMessage(event)" class="bg-astro-surface p-3 border-t border-astro-emerald/20 flex items-center gap-2 flex-shrink-0">
                <input type="text" id="chat-input" placeholder="உங்கள் கேள்விகள் அல்லது பிறப்பு விவரங்களை தட்டச்சு செய்க..." class="flex-1 bg-astro-base border border-astro-emerald/30 rounded-xl px-4 py-2.5 text-xs text-white placeholder-white/40 focus:outline-none focus:border-astro-gold">
                <button type="submit" class="btn-gold p-2.5 rounded-xl flex items-center justify-center">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- 3. WALLET RECHARGE MODAL -->
    <div id="wallet-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
        <div class="glass-panel w-full max-w-md rounded-3xl p-6 border border-astro-gold/40 shadow-glow-gold relative">
            <button onclick="closeWalletModal()" class="absolute top-4 right-4 p-2 text-white/60 hover:text-white rounded-full bg-astro-surface">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-2xl bg-astro-gold/20 flex items-center justify-center text-astro-gold mx-auto mb-2 border border-astro-gold/40">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
                <h3 class="font-cinzel text-xl font-bold text-white">வாலட் ரீசார்ஜ் (Wallet Recharge)</h3>
                <p class="text-xs text-white/60 mt-1">குறைந்தபட்சம் 5 நிமிட ஆலோசனைக்கு ரீசார்ஜ் செய்க</p>
                <div class="mt-3 p-3 rounded-xl bg-astro-surface border border-astro-gold/30">
                    <span class="text-xs text-white/60 block">தற்போதைய இருப்பு:</span>
                    <span id="modal-wallet-balance" class="text-astro-gold font-bold text-2xl">₹500.00</span>
                </div>
            </div>

            <!-- Instant Packs Grid -->
            <div class="grid grid-cols-3 gap-2.5 mb-6">
                <button onclick="rechargeWallet(100)" class="p-3 rounded-xl bg-astro-surface border border-astro-emerald/30 hover:border-astro-gold text-center transition">
                    <span class="text-xs text-white/60 block">தொடக்க பேக்</span>
                    <span class="text-base font-bold text-white">₹100</span>
                </button>
                <button onclick="rechargeWallet(200)" class="p-3 rounded-xl bg-astro-surface border border-astro-emerald/30 hover:border-astro-gold text-center transition">
                    <span class="text-xs text-white/60 block">பிரபல பேக்</span>
                    <span class="text-base font-bold text-white">₹200</span>
                </button>
                <button onclick="rechargeWallet(500)" class="p-3 rounded-xl bg-astro-surface border-2 border-astro-gold text-center transition relative overflow-hidden">
                    <span class="absolute top-0 right-0 bg-astro-gold text-astro-base text-[9px] font-bold px-1.5 rounded-bl">Best</span>
                    <span class="text-xs text-astro-gold block font-semibold">+₹50 போனஸ்</span>
                    <span class="text-base font-bold text-astro-gold">₹500</span>
                </button>
                <button onclick="rechargeWallet(1000)" class="p-3 rounded-xl bg-astro-surface border border-astro-emerald/30 hover:border-astro-gold text-center transition">
                    <span class="text-xs text-astro-emeraldLight block">+₹150 போனஸ்</span>
                    <span class="text-base font-bold text-white">₹1,000</span>
                </button>
                <button onclick="rechargeWallet(2000)" class="p-3 rounded-xl bg-astro-surface border border-astro-emerald/30 hover:border-astro-gold text-center transition">
                    <span class="text-xs text-astro-emeraldLight block">+₹350 போனஸ்</span>
                    <span class="text-base font-bold text-white">₹2,000</span>
                </button>
                <button onclick="rechargeWallet(5000)" class="p-3 rounded-xl bg-astro-surface border border-astro-emerald/30 hover:border-astro-gold text-center transition">
                    <span class="text-xs text-astro-gold block font-bold">VIP அர்ச்சனை</span>
                    <span class="text-base font-bold text-white">₹5,000</span>
                </button>
            </div>

            <!-- Instant UPI Simulation Button -->
            <button onclick="rechargeWallet(500)" class="btn-gold w-full py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2">
                <i data-lucide="zap" class="w-4 h-4"></i> உடனடி ரீசார்ஜ் (UPI / GPay / PhonePe)
            </button>
        </div>
    </div>

    <!-- 4. CLIENT AUTH MODAL (LOGIN / REGISTER WITH ₹100 BONUS) -->
    <div id="auth-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
        <div class="glass-panel w-full max-w-md rounded-3xl p-6 border border-astro-gold/40 shadow-glow-gold relative">
            <button onclick="closeAuthModal()" class="absolute top-4 right-4 p-2 text-white/60 hover:text-white rounded-full bg-astro-surface">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-2xl bg-astro-emerald/20 flex items-center justify-center text-astro-emerald mx-auto mb-2 border border-astro-emerald/40">
                    <i data-lucide="user-check" class="w-6 h-6"></i>
                </div>
                <h3 class="font-cinzel text-xl font-bold text-white">பக்தர் உள்நுழைவு (Login)</h3>
                <p class="text-xs text-astro-gold mt-1 font-semibold">🎁 புதிய பயனர்களுக்கு ₹100 இலவச போனஸ்</p>
            </div>

            <form onsubmit="handleAuthSubmit(event)" class="space-y-4">
                <div>
                    <label class="block text-xs text-white/70 mb-1">மொபைல் எண் (Mobile Number)</label>
                    <input type="tel" id="auth-phone" value="9998887770" required class="w-full bg-astro-surface border border-astro-emerald/30 rounded-xl px-4 py-2.5 text-xs text-white placeholder-white/30 focus:outline-none focus:border-astro-gold">
                </div>
                <div>
                    <label class="block text-xs text-white/70 mb-1">கடவுச்சொல் (Password)</label>
                    <input type="password" id="auth-password" value="client123" required class="w-full bg-astro-surface border border-astro-emerald/30 rounded-xl px-4 py-2.5 text-xs text-white placeholder-white/30 focus:outline-none focus:border-astro-gold">
                </div>
                <button type="submit" class="btn-gold w-full py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i> உள்நுழைக & ஆலோசனை பெறுக
                </button>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- JAVASCRIPT: LOGIC, AGORA SIMULATOR, BILLING -->
    <!-- ============================================== -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Local State
        const AppState = {
            walletBalance: parseFloat(localStorage.getItem('astro_wallet') || '500.00'),
            userName: localStorage.getItem('astro_user') || 'Devotee User',
            isLoggedIn: true,
            activeCall: {
                timer: null,
                billingTimer: null,
                seconds: 0,
                deducted: 0.0,
                ratePerMin: 20.0,
                astroName: '',
                type: 'Audio'
            }
        };

        // Update Wallet UI
        function updateWalletUI() {
            const formatted = '₹' + AppState.walletBalance.toFixed(2);
            document.getElementById('nav-wallet-balance').innerText = formatted;
            document.getElementById('modal-wallet-balance').innerText = formatted;
            document.getElementById('call-wallet-bal').innerText = formatted;
            localStorage.setItem('astro_wallet', AppState.walletBalance.toString());
        }
        updateWalletUI();

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Filter Astrologers
        function filterAstrologers(cat) {
            document.querySelectorAll('.astro-filter-btn').forEach(btn => {
                btn.classList.remove('bg-astro-gold', 'text-astro-base');
                btn.classList.add('bg-astro-surface', 'text-white/80');
            });
            event.target.classList.add('bg-astro-gold', 'text-astro-base');
            event.target.classList.remove('bg-astro-surface', 'text-white/80');

            const cards = document.querySelectorAll('#astrologers-container > div');
            cards.forEach(card => {
                if (cat === 'all') {
                    card.style.display = 'flex';
                } else {
                    const cardCat = card.getAttribute('data-category') || '';
                    card.style.display = cardCat.includes(cat) ? 'flex' : 'none';
                }
            });
        }

        // ==============================================
        // CONSULTATION LOGIC & 5-MIN SAFEGUARD
        // ==============================================
        async function startConsultation(astroId, type, ratePerMin, name = 'Guruji') {
            const minRequired = ratePerMin * 5; // 5-minute safeguard threshold
            if (AppState.walletBalance < minRequired) {
                alert(`⚠️ குறைந்தபட்ச இருப்பு எச்சரிக்கை!\n\n5 நிமிட ஆலோசனைக்கு குறைந்தபட்சம் ₹${minRequired} இருப்பு தேவை.\nஉங்கள் தற்போதைய வாலட் இருப்பு: ₹${AppState.walletBalance.toFixed(0)}.\n\nஉடனடியாக வாலட் ரீசார்ஜ் செய்யவும்.`);
                openWalletModal();
                return;
            }

            // Real backend consultation request
            try {
                const reqRes = await fetch('api/sessions/request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        user_id: 2,
                        astrologer_id: astroId,
                        type: (type || 'audio').toLowerCase(),
                        user_name: AppState.userName || 'பக்தர் (Web User)',
                        user_phone: '8000000001'
                    })
                });
                const reqData = await reqRes.json();
                if (reqData.ok && reqData.data && reqData.data.session_id) {
                    const sid = reqData.data.session_id;
                    if (type === 'Chat') {
                        openChatModal(name, ratePerMin, sid, astroId);
                    } else {
                        openCallModal(name, type, ratePerMin, sid, astroId);
                    }
                    return;
                } else {
                    alert(reqData.message || 'ஜோதிடரை இணைக்க முடியவில்லை. சிறிது நேரம் கழித்து முயற்சிக்கவும்.');
                    return;
                }
            } catch (e) {
                console.warn('Backend call initiate error:', e);
                if (type === 'Chat') {
                    openChatModal(name, ratePerMin);
                } else {
                    openCallModal(name, type, ratePerMin);
                }
            }
        }

        // ==============================================
        // CALL LIFECYCLE WITH REAL 2-WAY HANDSHAKE & BILLING
        // ==============================================
        let callPollTimer = null;
        let currentCallSessionId = null;

        function openCallModal(name, type, rate, sessionId = null, astroId = 1) {
            currentCallSessionId = sessionId;
            AppState.activeCall.astroName = name;
            AppState.activeCall.type = type;
            AppState.activeCall.ratePerMin = rate;
            AppState.activeCall.seconds = 0;
            AppState.activeCall.deducted = 0.0;

            document.getElementById('call-astro-name').innerText = name;
            document.getElementById('call-type-title').innerText = sessionId ? `Ringing Guruji... (${type} Call)` : `Agora RTC Live ${type} Call`;
            document.getElementById('call-rate-desc').innerText = `₹${rate} / நிமிடம் • Agora RTC Encrypted Channel`;
            document.getElementById('call-deducted-amt').innerText = '₹0.00';
            document.getElementById('call-timer').innerText = '00:00';

            if (type === 'Video') {
                document.getElementById('call-video-container').classList.remove('hidden');
                document.getElementById('call-audio-container').classList.add('hidden');
                document.getElementById('btn-call-video').classList.remove('hidden');
                document.getElementById('call-video-astro-name').innerText = name;
            } else {
                document.getElementById('call-video-container').classList.add('hidden');
                document.getElementById('call-audio-container').classList.remove('hidden');
                document.getElementById('btn-call-video').classList.add('hidden');
            }

            updateWalletUI();
            document.getElementById('call-modal').classList.remove('hidden');

            if (callPollTimer) clearInterval(callPollTimer);
            if (AppState.activeCall.timer) clearInterval(AppState.activeCall.timer);
            if (AppState.activeCall.billingTimer) clearInterval(AppState.activeCall.billingTimer);

            // If connected via real backend session: Poll for astrologer acceptance
            if (sessionId) {
                callPollTimer = setInterval(async () => {
                    try {
                        const statusRes = await fetch(`api/sessions/check_status.php?session_id=${sessionId}`);
                        const statusData = await statusRes.json();
                        if (statusData.ok && statusData.data) {
                            const curStatus = statusData.data.status;
                            if (curStatus === 'ringing_user') {
                                // Astrologer accepted! User auto-accepts to start live room!
                                clearInterval(callPollTimer);
                                callPollTimer = null;

                                await fetch('api/sessions/user_accept.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ session_id: sessionId, user_id: 2 })
                                });

                                document.getElementById('call-type-title').innerText = `Live Connected (${type} Call)`;
                                startCallLiveTimers(sessionId);
                            } else if (curStatus === 'active') {
                                clearInterval(callPollTimer);
                                callPollTimer = null;
                                document.getElementById('call-type-title').innerText = `Live Connected (${type} Call)`;
                                startCallLiveTimers(sessionId);
                            } else if (curStatus === 'cancelled') {
                                clearInterval(callPollTimer);
                                callPollTimer = null;
                                document.getElementById('call-modal').classList.add('hidden');
                                alert('ஜோதிடர் தற்போது அழைப்பை ஏற்க இயலவில்லை அல்லது ரத்து செய்யப்பட்டது (Astrologer declined or timed out).');
                            }
                        }
                    } catch (e) {}
                }, 1200);
            } else {
                // Local simulation fallback
                startCallLiveTimers(null);
            }
        }

        function startCallLiveTimers(sessionId) {
            // 1-second display timer
            AppState.activeCall.timer = setInterval(() => {
                AppState.activeCall.seconds++;
                const mins = String(Math.floor(AppState.activeCall.seconds / 60)).padStart(2, '0');
                const secs = String(AppState.activeCall.seconds % 60).padStart(2, '0');
                document.getElementById('call-timer').innerText = `${mins}:${secs}`;
            }, 1000);

            // 60-second real-time wallet deduction heartbeat
            AppState.activeCall.billingTimer = setInterval(async () => {
                if (AppState.walletBalance >= AppState.activeCall.ratePerMin) {
                    AppState.walletBalance -= AppState.activeCall.ratePerMin;
                    AppState.activeCall.deducted += AppState.activeCall.ratePerMin;
                    document.getElementById('call-deducted-amt').innerText = '₹' + AppState.activeCall.deducted.toFixed(2);
                    updateWalletUI();

                    if (sessionId) {
                        try {
                            const hbRes = await fetch('api/sessions/heartbeat.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ session_id: sessionId })
                            });
                            const hbData = await hbRes.json();
                            if (hbData.data && hbData.data.terminate) {
                                alert('⚠️ வாலட் இருப்பு முடிந்துவிட்டது! ஆலோசனை தானாக நிறைவு பெறுகிறது.');
                                endLiveCall();
                            }
                        } catch (e) {}
                    }
                } else {
                    alert('⚠️ வாலட் இருப்பு முடிந்துவிட்டது! ஆலோசனை தானாக நிறைவு பெறுகிறது.');
                    endLiveCall();
                }
            }, 60000);
        }

        async function endLiveCall() {
            if (callPollTimer) clearInterval(callPollTimer);
            clearInterval(AppState.activeCall.timer);
            clearInterval(AppState.activeCall.billingTimer);

            if (currentCallSessionId) {
                try {
                    await fetch('api/sessions/end.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ session_id: currentCallSessionId, rating: 5 })
                    });
                } catch (e) {}
                currentCallSessionId = null;
            }

            document.getElementById('call-modal').classList.add('hidden');
            alert(`✅ ஆலோசனை நிறைவடைந்தது!\n\nகால அளவு: ${AppState.activeCall.seconds} விநாடிகள்\nபிடிக்கப்பட்ட கட்டணம்: ₹${AppState.activeCall.deducted.toFixed(2)}\nமீதமுள்ள வாலட் இருப்பு: ₹${AppState.walletBalance.toFixed(2)}\n\nகுருஜியின் தெய்வீக அருள் நிலைக்கட்டும்! 🌟`);
        }

        function closeCallModal() {
            if (confirm('ஆலோசனையை நிறைவு செய்ய விரும்புகிறீர்களா?')) {
                endLiveCall();
            }
        }

        function toggleCallMute(btn) {
            btn.classList.toggle('bg-red-500/30');
            btn.classList.toggle('text-red-400');
        }

        function toggleCallSpeaker(btn) {
            btn.classList.toggle('text-astro-gold');
        }

        function toggleVideoFeed(btn) {
            btn.classList.toggle('bg-red-500/30');
            btn.classList.toggle('text-red-400');
            const videoImg = document.getElementById('call-video-astro-img');
            if (videoImg) {
                videoImg.classList.toggle('opacity-30');
            }
        }

        // ==============================================
        // CHAT CONSULTATION MODAL LOGIC - REAL 2-WAY SYNC
        // ==============================================
        let currentChatSessionId = null;
        let chatPollTimer = null;
        let chatStatusPollTimer = null;
        let lastWebChatMsgId = 0;

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function openChatModal(name, rate, sessionId = null, astroId = 1) {
            currentChatSessionId = sessionId;
            lastWebChatMsgId = 0;
            document.getElementById('chat-astro-name').innerText = name;
            document.getElementById('chat-rate-val').innerText = rate;

            // Clean chat container with astrologer welcome
            const container = document.getElementById('chat-messages');
            container.innerHTML = `
                <div class="flex items-start gap-2 max-w-[85%]">
                    <div class="w-7 h-7 rounded-lg bg-astro-surface border border-astro-gold/40 flex items-center justify-center text-astro-gold text-xs font-bold flex-shrink-0">
                        ॐ
                    </div>
                    <div class="bg-astro-surface border border-astro-emerald/30 p-3 rounded-2xl rounded-tl-none text-xs text-white/90 leading-relaxed font-tamil">
                        வணக்கம்! நான் ${escapeHtml(name)}. தங்களின் பிறப்பு தேதி (DOB), பிறந்த நேரம் (Time) மற்றும் பிறந்த ஊர் (Place of birth) பகிருங்கள். ஜாதகத்தை கணித்து விடை தருகிறேன்.
                    </div>
                </div>
            `;
            document.getElementById('chat-modal').classList.remove('hidden');

            if (chatPollTimer) clearInterval(chatPollTimer);
            if (chatStatusPollTimer) clearInterval(chatStatusPollTimer);

            if (sessionId) {
                // Poll session handshake
                chatStatusPollTimer = setInterval(async () => {
                    try {
                        const sRes = await fetch(`api/sessions/check_status.php?session_id=${sessionId}`);
                        const sData = await sRes.json();
                        if (sData.ok && sData.data) {
                            const status = sData.data.status;
                            if (status === 'ringing_user') {
                                await fetch('api/sessions/user_accept.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ session_id: sessionId, user_id: 2 })
                                });
                            }
                        }
                    } catch (e) {}
                }, 1200);

                // Incremental chat messages poller
                chatPollTimer = setInterval(async () => {
                    try {
                        const res = await fetch(`api/chat/messages.php?session_id=${encodeURIComponent(sessionId)}&last_id=${lastWebChatMsgId}`);
                        const data = await res.json();
                        if (data.ok && data.data && data.data.messages && data.data.messages.length > 0) {
                            data.data.messages.forEach(msg => {
                                const id = parseInt(msg.id);
                                if (id > lastWebChatMsgId) {
                                    lastWebChatMsgId = id;
                                }
                                if (msg.sender_type === 'astrologer') {
                                    appendAstrologerChatBubble(msg.content);
                                }
                            });
                        }
                    } catch (e) {}
                }, 1200);
            }
        }

        function appendAstrologerChatBubble(text) {
            const container = document.getElementById('chat-messages');
            if (!container) return;
            const reply = document.createElement('div');
            reply.className = 'flex items-start gap-2 max-w-[85%]';
            reply.innerHTML = `
                <div class="w-7 h-7 rounded-lg bg-astro-surface border border-astro-gold/40 flex items-center justify-center text-astro-gold text-xs font-bold flex-shrink-0">
                    ॐ
                </div>
                <div class="bg-astro-surface border border-astro-emerald/30 p-3 rounded-2xl rounded-tl-none text-xs text-white/90 leading-relaxed font-tamil">
                    ${escapeHtml(text)}
                </div>
            `;
            container.appendChild(reply);
            container.scrollTop = container.scrollHeight;
        }

        function closeChatModal() {
            if (chatPollTimer) clearInterval(chatPollTimer);
            if (chatStatusPollTimer) clearInterval(chatStatusPollTimer);
            if (currentChatSessionId) {
                fetch('api/sessions/end.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: currentChatSessionId, rating: 5 })
                }).catch(() => {});
                currentChatSessionId = null;
            }
            document.getElementById('chat-modal').classList.add('hidden');
        }

        async function sendChatMessage(e) {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const text = input.value.trim();
            if (!text) return;

            const container = document.getElementById('chat-messages');

            // Append user message immediately
            const userMsg = document.createElement('div');
            userMsg.className = 'flex items-start justify-end gap-2';
            userMsg.innerHTML = `
                <div class="bg-astro-gold text-astro-base font-medium p-3 rounded-2xl rounded-tr-none text-xs max-w-[85%] leading-relaxed font-tamil">
                    ${escapeHtml(text)}
                </div>
            `;
            container.appendChild(userMsg);
            input.value = '';
            container.scrollTop = container.scrollHeight;

            if (currentChatSessionId) {
                try {
                    await fetch('api/chat/send.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            session_id: currentChatSessionId,
                            sender_type: 'user',
                            sender_id: 2,
                            content: text
                        })
                    });
                } catch (e) {
                    console.error('Error sending user chat:', e);
                }
            }
        }

        // ==============================================
        // WALLET RECHARGE LOGIC
        // ==============================================
        function openWalletModal() {
            updateWalletUI();
            document.getElementById('wallet-modal').classList.remove('hidden');
        }

        function closeWalletModal() {
            document.getElementById('wallet-modal').classList.add('hidden');
        }

        function rechargeWallet(amount) {
            AppState.walletBalance += amount;
            updateWalletUI();
            alert(`🎉 ரீசார்ஜ் வெற்றி!\n\n₹${amount} வாலட்டில் வெற்றிகரமாக சேர்க்கப்பட்டது.\nபுதிய இருப்பு: ₹${AppState.walletBalance.toFixed(2)}`);
            closeWalletModal();
        }

        // ==============================================
        // AUTH MODAL LOGIC
        // ==============================================
        function openAuthModal() {
            document.getElementById('auth-modal').classList.remove('hidden');
        }

        function closeAuthModal() {
            document.getElementById('auth-modal').classList.add('hidden');
        }

        function handleAuthSubmit(e) {
            e.preventDefault();
            const phone = document.getElementById('auth-phone').value;
            AppState.userName = 'பக்தர் ' + phone.substring(6);
            AppState.isLoggedIn = true;
            document.getElementById('user-display-name').innerText = AppState.userName;
            localStorage.setItem('astro_user', AppState.userName);
            closeAuthModal();
            alert(`✅ வெற்றிகரமாக உள்நுழைந்துள்ளீர்கள்!\nவரவேற்கிறோம், ${AppState.userName}!`);
        }
    </script>
</body>
</html>

