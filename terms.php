<?php
// terms.php - AstroSubam Terms & Conditions (Google Play Store Compliant)
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="ta-IN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>விதிமுறைகள் & நிபந்தனைகள் | Terms & Conditions - AstroSubam</title>
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
        .prose-gold h2 { color: #FACC15; font-family: 'Cinzel', serif; font-size: 1.25rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem; }
        .prose-gold h3 { color: #34D399; font-size: 1rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.5rem; }
        .prose-gold p, .prose-gold li { color: rgba(255, 255, 255, 0.82); font-size: 0.875rem; line-height: 1.7; margin-bottom: 0.75rem; }
        .prose-gold ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
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
                    <i data-lucide="shield" class="w-3.5 h-3.5"></i> தனியுரிமை (Privacy)
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-4xl mx-auto px-4 sm:px-6 py-10 w-full">
        <div class="bg-astro-surface/90 border border-astro-border rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl">

            <!-- Document Header -->
            <div class="border-b border-astro-border pb-6 mb-8 text-center sm:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-astro-gold/15 border border-astro-gold/40 text-astro-gold text-xs font-semibold mb-3">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i> User Agreement & Service Terms
                </div>
                <h1 class="font-cinzel text-2xl sm:text-3xl font-bold text-astro-gold tracking-wide">Terms & Conditions</h1>
                <p class="font-tamil text-sm text-astro-textMint mt-1 font-semibold">பயன்பாட்டு விதிமுறைகள் மற்றும் சேவை நிபந்தனைகள்</p>
                <div class="flex flex-wrap items-center gap-4 text-xs text-white/50 mt-4">
                    <span>📅 <strong>Effective Date:</strong> January 1, 2026</span>
                    <span>•</span>
                    <span>🔄 <strong>Last Updated:</strong> November 15, 2026</span>
                </div>
            </div>

            <!-- Terms Body -->
            <div class="prose-gold space-y-6">

                <section>
                    <h2><i data-lucide="check-circle" class="w-5 h-5 text-astro-gold"></i> 1. Acceptance of Terms (விதிமுறைகளை ஏற்றுக்கொள்ளுதல்)</h2>
                    <p>
                        By downloading, installing, or accessing the <strong>AstroSubam Mobile Application</strong> (available on Google Play) or accessing <strong>https://subam.navanagari.com</strong>, you agree to be bound by these Terms and Conditions and our Privacy Policy. If you do not agree, please do not use the services.
                    </p>
                </section>

                <section>
                    <h2><i data-lucide="compass" class="w-5 h-5 text-astro-gold"></i> 2. Nature of Astrological Services & Disclaimers</h2>
                    <p>
                        AstroSubam connects users with qualified Vedic astrologers, numerologists, and KP system practitioners for spiritual counseling, horoscopic analysis, and astrological guidance.
                    </p>
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 my-3 text-xs leading-relaxed">
                        <strong class="text-amber-400 block mb-1">⚠️ Important Guidance Disclaimer:</strong>
                        Astrological predictions are based on ancient traditional Vedic Jyotish interpretations, mathematical planetary positions, and personal birth details provided by you. Astrology is an advisory science intended for spiritual enrichment, guidance, and peace of mind. Consultations are <strong>NOT a substitute for professional medical diagnosis, psychiatric care, legal representation, or financial investment advice</strong>.
                    </div>
                </section>

                <section>
                    <h2><i data-lucide="user-check" class="w-5 h-5 text-astro-gold"></i> 3. User Registration & Account Security</h2>
                    <ul>
                        <li>You must be at least 18 years old to create an account and access paid consultations.</li>
                        <li>Account login is secured via Mobile OTP (One-Time Password). You are responsible for maintaining the confidentiality of your mobile device and OTP.</li>
                        <li>You agree to provide accurate birth details (date, time, and place) to enable precise horoscopic computations.</li>
                    </ul>
                </section>

                <section>
                    <h2><i data-lucide="wallet" class="w-5 h-5 text-astro-gold"></i> 4. Wallet, Consultation Billing & Pricing</h2>
                    <ul>
                        <li>Consultation services are offered via real-time <strong>Audio Call, Video Call, and Two-Way Live Chat</strong>.</li>
                        <li>Each astrologer displays their per-minute rate transparently (e.g., ₹15 - ₹35 per minute).</li>
                        <li><strong>5-Minute Safeguard Balance:</strong> To ensure consultation continuity, an initial minimum balance equivalent to 5 minutes of consultation is required before initiating a call or chat.</li>
                        <li>Billing occurs per minute during active session duration via our automated heartbeat system. When wallet balance is depleted, sessions conclude gracefully.</li>
                    </ul>
                </section>

                <section>
                    <h2><i data-lucide="phone-call" class="w-5 h-5 text-astro-gold"></i> 5. Agora RTC Communications & User Conduct</h2>
                    <p>
                        Live consultations are delivered in real-time via Agora RTC. You agree to treat astrologers with mutual respect and dignity. The following behaviors will lead to immediate session termination and account suspension:
                    </p>
                    <ul>
                        <li>Abusive, obscene, harassing, or defamatory language.</li>
                        <li>Inappropriate video conduct during video consultations.</li>
                        <li>Attempting to solicit personal off-platform contact information or payments outside AstroSubam.</li>
                    </ul>
                </section>

                <section>
                    <h2><i data-lucide="shield-off" class="w-5 h-5 text-astro-gold"></i> 6. Account Deletion (Google Play Compliance)</h2>
                    <p>
                        You have the right to terminate your account and request complete erasure of your personal data at any time via our web resource at <a href="delete_account.php" class="text-astro-emerald underline">https://subam.navanagari.com/delete_account.php</a> or directly from the in-app profile screen.
                    </p>
                </section>

                <section>
                    <h2><i data-lucide="scale" class="w-5 h-5 text-astro-gold"></i> 7. Governing Law & Jurisdiction</h2>
                    <p>
                        These Terms and Conditions shall be governed by and construed in accordance with the laws of India. Any disputes arising out of or related to these terms shall be subject to the exclusive jurisdiction of the competent courts in Tamil Nadu, India.
                    </p>
                </section>

                <section>
                    <h2><i data-lucide="mail" class="w-5 h-5 text-astro-gold"></i> 8. Contact & Customer Support</h2>
                    <div class="p-4 rounded-2xl bg-astro-card border border-astro-border text-xs space-y-1.5">
                        <p><strong class="text-astro-gold">Entity:</strong> AstroSubam Platform</p>
                        <p><strong class="text-astro-gold">Customer Care Email:</strong> <a href="mailto:support@astrosubam.com" class="text-astro-emerald hover:underline">support@astrosubam.com</a></p>
                        <p><strong class="text-astro-gold">Helpline:</strong> +91 99447 00648 / +91 99988 87770</p>
                    </div>
                </section>

            </div>

            <!-- Footer Navigation Links -->
            <div class="border-t border-astro-border pt-6 mt-10 flex flex-wrap items-center justify-between gap-4 text-xs text-white/60">
                <div class="flex items-center gap-4">
                    <a href="privacy.php" class="hover:text-astro-gold transition">Privacy Policy</a>
                    <span>•</span>
                    <a href="delete_account.php" class="hover:text-red-400 transition">Delete Account</a>
                    <span>•</span>
                    <a href="refund.php" class="hover:text-astro-gold transition">Refund Policy</a>
                </div>
                <p>© 2026 AstroSubam. All Rights Reserved.</p>
            </div>

        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
