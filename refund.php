<?php
// refund.php - AstroSubam Refund & Cancellation Policy (Google Play Store & Payment Gateway Compliant)
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="ta-IN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>பணத்தைத் திரும்பப்பெறுதல் & ரத்து கொள்கை | Refund Policy - AstroSubam</title>
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
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-4xl mx-auto px-4 sm:px-6 py-10 w-full">
        <div class="bg-astro-surface/90 border border-astro-border rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl">

            <!-- Document Header -->
            <div class="border-b border-astro-border pb-6 mb-8 text-center sm:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-astro-emerald/15 border border-astro-emerald/40 text-astro-textMint text-xs font-semibold mb-3">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Customer Assurance & Refund Policy
                </div>
                <h1 class="font-cinzel text-2xl sm:text-3xl font-bold text-astro-gold tracking-wide">Refund & Cancellation Policy</h1>
                <p class="font-tamil text-sm text-astro-textMint mt-1 font-semibold">பணத்தைத் திரும்பப்பெறுதல் மற்றும் ரத்து கொள்கை</p>
                <div class="flex flex-wrap items-center gap-4 text-xs text-white/50 mt-4">
                    <span>📅 <strong>Effective Date:</strong> January 1, 2026</span>
                    <span>•</span>
                    <span>🔄 <strong>Last Updated:</strong> November 15, 2026</span>
                </div>
            </div>

            <!-- Content -->
            <div class="prose-gold space-y-6">

                <section>
                    <h2><i data-lucide="check" class="w-5 h-5 text-astro-gold"></i> 1. Overview (பொது பார்வை)</h2>
                    <p>
                        At AstroSubam, we are dedicated to providing the highest quality astrological consultations. We recognize that occasional technical disruptions (such as poor network connectivity or call drops) can occur. This policy outlines our refund and adjustment process.
                    </p>
                </section>

                <section>
                    <h2><i data-lucide="phone-missed" class="w-5 h-5 text-astro-gold"></i> 2. Technical Call Drops & Disconnections</h2>
                    <ul>
                        <li><strong>Calls Disconnected Under 60 Seconds:</strong> If an audio or video call disconnects within the first 60 seconds due to an Agora RTC connectivity or server error, any deducted amount for that session will be automatically credited back to your AstroSubam wallet.</li>
                        <li><strong>Astrologer Unresponsiveness:</strong> If an astrologer does not answer or cannot be heard due to technical failure, you will not be billed for that duration.</li>
                    </ul>
                </section>

                <section>
                    <h2><i data-lucide="credit-card" class="w-5 h-5 text-astro-gold"></i> 3. Wallet Recharge Refunds</h2>
                    <ul>
                        <li>Wallet recharges are typically non-refundable once utilized for active consultations with astrologers.</li>
                        <li><strong>Accidental Duplicate Recharge:</strong> If your bank account or card was debited multiple times due to payment gateway lag, please email us within 48 hours with your transaction reference numbers. Duplicate debits will be refunded to your original payment source within 5 to 7 working days.</li>
                    </ul>
                </section>

                <section>
                    <h2><i data-lucide="help-circle" class="w-5 h-5 text-astro-gold"></i> 4. How to Request a Refund / Support</h2>
                    <p>
                        To request assistance regarding a disputed consultation deduction or recharge issue, raise a ticket via the AstroSubam Customer Support screen in the app or contact our team:
                    </p>
                    <div class="p-4 rounded-2xl bg-astro-card border border-astro-border text-xs space-y-1">
                        <p><strong class="text-astro-gold">Email:</strong> <a href="mailto:support@astrosubam.com?subject=Refund%20Inquiry" class="text-astro-emerald underline">support@astrosubam.com</a></p>
                        <p><strong class="text-astro-gold">Helpline:</strong> +91 99447 00648 / +91 99988 87770</p>
                        <p><strong class="text-astro-gold">Resolution Turnaround:</strong> 24 to 48 business hours</p>
                    </div>
                </section>

            </div>

            <!-- Footer Navigation Links -->
            <div class="border-t border-astro-border pt-6 mt-10 flex flex-wrap items-center justify-between gap-4 text-xs text-white/60">
                <div class="flex items-center gap-4">
                    <a href="privacy.php" class="hover:text-astro-gold transition">Privacy Policy</a>
                    <span>•</span>
                    <a href="terms.php" class="hover:text-astro-gold transition">Terms & Conditions</a>
                    <span>•</span>
                    <a href="delete_account.php" class="hover:text-red-400 transition">Delete Account</a>
                </div>
                <p>© 2026 AstroSubam. All Rights Reserved.</p>
            </div>

        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>
</html>
