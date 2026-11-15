<?php
// privacy.php - AstroSubam Official Privacy Policy (Google Play Store Compliant)
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="ta-IN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>தனியுரிமைக் கொள்கை | Privacy Policy - AstroSubam</title>
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
                <a href="delete_account.php" class="px-3 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-300 transition flex items-center gap-1.5">
                    <i data-lucide="user-x" class="w-3.5 h-3.5"></i> கணக்கு நீக்கம் (Delete Account)
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-4xl mx-auto px-4 sm:px-6 py-10 w-full">
        <div class="bg-astro-surface/90 border border-astro-border rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl">
            
            <!-- Document Header -->
            <div class="border-b border-astro-border pb-6 mb-8 text-center sm:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-astro-emerald/15 border border-astro-emerald/40 text-astro-textMint text-xs font-semibold mb-3">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-astro-emerald"></i> Google Play Store & User Data Compliance
                </div>
                <h1 class="font-cinzel text-2xl sm:text-3xl font-bold text-astro-gold tracking-wide">AstroSubam - Privacy Policy</h1>
                <p class="font-tamil text-sm text-astro-textMint mt-1 font-semibold">தனியுரிமைக் கொள்கை மற்றும் தரவு பாதுகாப்பு அறிக்கை</p>
                <div class="flex flex-wrap items-center gap-4 text-xs text-white/50 mt-4">
                    <span>📅 <strong>Effective Date:</strong> January 1, 2026</span>
                    <span>•</span>
                    <span>🔄 <strong>Last Updated:</strong> November 15, 2026</span>
                    <span>•</span>
                    <span>📱 <strong>App:</strong> AstroSubam (Android & Web)</span>
                </div>
            </div>

            <!-- Policy Body -->
            <div class="prose-gold space-y-6">
                
                <section>
                    <h2><i data-lucide="info" class="w-5 h-5 text-astro-gold"></i> 1. Introduction (அறிமுகம்)</h2>
                    <p>
                        Welcome to <strong>AstroSubam</strong> ("we", "our", "us", or "the Platform"), operated via the website <strong>https://subam.navanagari.com</strong> and the <strong>AstroSubam Mobile Application</strong> available on Google Play. We are committed to protecting your privacy and personal data.
                    </p>
                    <p class="font-tamil text-white/70">
                        ஆஸ்ட்ரோசுபம் (AstroSubam) ஆன்மீகம் மற்றும் வேத ஜோதிட ஆலோசனை சேவைகளை வழங்கும் ஒரு தளமாகும். எங்கள் இணையதளம் மற்றும் மொபைல் செயலியைப் பயன்படுத்தும் போது உங்கள் தனிப்பட்ட விவரங்கள் எவ்வாறு சேகரிக்கப்பட்டு, பாதுகாக்கப்படுகின்றன என்பதை இக்கொள்கை விளக்குகிறது.
                    </p>
                </section>

                <section>
                    <h2><i data-lucide="database" class="w-5 h-5 text-astro-gold"></i> 2. Information We Collect (நாங்கள் சேகரிக்கும் தகவல்கள்)</h2>
                    <p>To provide accurate Vedic astrological consultations and enable secure real-time communications, we collect the following types of information:</p>
                    <ul>
                        <li><strong>Account & Contact Information:</strong> Mobile Phone Number (for OTP verification and account identification), Full Name, and optional Email Address.</li>
                        <li><strong>Astrological / Birth Details:</strong> Date of Birth, Time of Birth, Place of Birth, Gender, and Zodiac / Rashi / Nakshatra details provided voluntarily by you to generate your birth chart (Kundli) and enable astrologers to offer accurate readings.</li>
                        <li><strong>Consultation Data:</strong> Chat messages exchanged with astrologers during active sessions, call duration logs, and consultation history. Real-time audio and video streams are transmitted securely via Agora RTC and are <strong>NOT recorded</strong> or stored on our servers without your prior explicit consent.</li>
                        <li><strong>Wallet & Transaction Records:</strong> In-app wallet recharge transaction IDs, recharge amounts, consultation rate deductions, and balance ledger. We do not store raw credit/debit card numbers or UPI PINs; all payments are processed through secure, RBI-licensed payment gateways.</li>
                    </ul>
                </section>

                <section>
                    <h2><i data-lucide="smartphone" class="w-5 h-5 text-astro-gold"></i> 3. Device Permissions Used (சாதன அனுமதிகள்)</h2>
                    <p>The AstroSubam Android mobile application requests the following runtime permissions strictly to provide core consultation features:</p>
                    <ul>
                        <li><strong>CAMERA (`android.permission.CAMERA`):</strong> Required exclusively when you initiate or accept a live one-on-one video consultation with a certified astrologer. Video is streamed in real-time through Agora RTC and is not captured when video is off.</li>
                        <li><strong>MICROPHONE / AUDIO (`android.permission.RECORD_AUDIO`):</strong> Required for two-way voice communication during audio and video consultations with astrologers.</li>
                        <li><strong>NOTIFICATIONS (`android.permission.POST_NOTIFICATIONS`):</strong> Required on Android 13+ to notify you of incoming calls from astrologers, replies to your chat messages, and wallet recharge confirmations.</li>
                        <li><strong>INTERNET & NETWORK STATE (`android.permission.INTERNET`, `ACCESS_NETWORK_STATE`):</strong> Required to connect to our secure backend API and Agora RTC communication servers.</li>
                    </ul>
                </section>

                <section>
                    <h2><i data-lucide="layers" class="w-5 h-5 text-astro-gold"></i> 4. Third-Party Service Providers (மூன்றாம் தரப்பு சேவைகள்)</h2>
                    <p>We work with trusted third-party technology providers to power specific app functionalities:</p>
                    <ul>
                        <li><strong>Agora.io (Agora RTC SDK):</strong> Facilitates end-to-end encrypted, low-latency live audio and video streaming between devotees and astrologers.</li>
                        <li><strong>Ping4SMS:</strong> Sends transactional SMS containing one-time passwords (OTP) for secure login verification.</li>
                        <li><strong>Payment Gateways (Razorpay / UPI / NetBanking):</strong> Process payment transactions securely in compliance with PCI-DSS guidelines.</li>
                        <li><strong>Google Play Services:</strong> Core app distribution, safety attestation, and push notification delivery.</li>
                    </ul>
                </section>

                <section>
                    <h2><i data-lucide="lock" class="w-5 h-5 text-astro-gold"></i> 5. Data Security & Storage (தரவு பாதுகாப்பு)</h2>
                    <p>
                        All data transmitted between the AstroSubam app, website, and our servers is encrypted using industry-standard **Transport Layer Security (TLS 1.2 / TLS 1.3 / HTTPS)**. We employ firewall protections, hashed passwords, session timeouts, and restricted database access to safeguard your personal information against unauthorized access, loss, or alteration.
                    </p>
                </section>

                <section>
                    <h2><i data-lucide="user-x" class="w-5 h-5 text-astro-gold"></i> 6. Account Deletion & Data Retention (Google Play Policy Compliance)</h2>
                    <p>
                        In strict compliance with Google Play's <strong>Account Deletion and Data Safety Policy</strong>, we provide both in-app and web-based mechanisms for users to request the permanent deletion of their account and associated personal data.
                    </p>
                    <div class="p-4 rounded-2xl bg-astro-card border border-astro-emerald/30 my-3">
                        <p class="font-bold text-astro-gold text-xs uppercase tracking-wider mb-1">🔗 Direct Account Deletion Web Resource:</p>
                        <p class="text-xs text-white/80 mb-2">You can request account deletion at any time without needing the app installed by visiting:</p>
                        <a href="delete_account.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 border border-red-500/40 text-red-300 text-xs font-semibold transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> https://subam.navanagari.com/delete_account.php
                        </a>
                    </div>
                    <p>
                        <strong>Upon receiving an account deletion request:</strong>
                    </p>
                    <ul>
                        <li>Your account profile, name, phone number, saved birth charts, and chat conversations will be deleted or permanently anonymized.</li>
                        <li>Financial billing receipts and tax transaction records are retained for the legally mandated period required by Indian accounting and taxation laws, after which they are purged.</li>
                    </ul>
                </section>

                <section>
                    <h2><i data-lucide="users" class="w-5 h-5 text-astro-gold"></i> 7. Children's Privacy (குழந்தைகள் தனியுரிமை)</h2>
                    <p>
                        AstroSubam is intended for individuals aged 18 and older. We do not knowingly collect personal information from children under 13. If you believe a minor has registered on our platform without parental consent, please contact us immediately to have the account removed.
                    </p>
                </section>

                <section>
                    <h2><i data-lucide="mail" class="w-5 h-5 text-astro-gold"></i> 8. Grievance Officer & Contact Us (தொடர்புக்கு)</h2>
                    <p>If you have any questions, feedback, or concerns regarding this Privacy Policy or your data, please contact our Grievance Officer:</p>
                    <div class="p-4 rounded-2xl bg-astro-card border border-astro-border text-xs space-y-1.5">
                        <p><strong class="text-astro-gold">Platform:</strong> AstroSubam - Vedic Astrology & Agora Consultation</p>
                        <p><strong class="text-astro-gold">Email:</strong> <a href="mailto:support@astrosubam.com" class="text-astro-emerald hover:underline">support@astrosubam.com</a></p>
                        <p><strong class="text-astro-gold">Customer Care Phone:</strong> +91 99447 00648 / +91 99988 87770</p>
                        <p><strong class="text-astro-gold">Official Website:</strong> <a href="https://subam.navanagari.com" class="text-astro-emerald hover:underline">https://subam.navanagari.com</a></p>
                        <p><strong class="text-astro-gold">Country of Operation:</strong> Tamil Nadu, India</p>
                    </div>
                </section>

            </div>

            <!-- Footer Navigation Links -->
            <div class="border-t border-astro-border pt-6 mt-10 flex flex-wrap items-center justify-between gap-4 text-xs text-white/60">
                <div class="flex items-center gap-4">
                    <a href="terms.php" class="hover:text-astro-gold transition">Terms & Conditions</a>
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
