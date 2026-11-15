<?php
// test_agora_call.php - Standalone Agora RTC Two-Way Voice Testing Tool
declare(strict_types=1);

require_once __DIR__ . '/helpers/AgoraToken.php';
require_once __DIR__ . '/helpers/Settings.php';

// Handle AJAX token generation request
if (isset($_GET['action']) && $_GET['action'] === 'get_token') {
    header('Content-Type: application/json');
    $config = require __DIR__ . '/config/agora.php';
    $appId = $config['app_id'];
    $role = $_GET['role'] ?? 'user';
    $channel = trim($_GET['channel'] ?? 'astrosubam_voice_test');
    if (empty($channel)) $channel = 'astrosubam_voice_test';

    if ($role === 'astrologer') {
        $astroId = (int)($_GET['astro_id'] ?? 1);
        $uid = AgoraToken::getAstroUid($astroId);
        $token = AgoraToken::generateAstroToken($channel, $astroId);
        $name = 'Guruji S. Sundaramurthy (Astrologer)';
    } else {
        $userId = (int)($_GET['user_id'] ?? 2);
        $uid = AgoraToken::getUserUid($userId);
        $token = AgoraToken::generateUserToken($channel, $userId);
        $name = 'Devotee (User 2)';
    }

    echo json_encode([
        'ok'      => !empty($token),
        'appId'   => $appId,
        'channel' => $channel,
        'role'    => $role,
        'uid'     => $uid,
        'token'   => $token,
        'name'    => $name
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ta" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroSubam - Agora RTC Live Voice Call Tester</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.22.0.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
    </style>
</head>
<body class="bg-[#0A1612] text-slate-100 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-2xl bg-[#0F221B] border border-amber-500/30 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-white/10 pb-5 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-600 to-yellow-400 p-0.5 flex items-center justify-center shadow-lg">
                    <div class="w-full h-full bg-[#0A1612] rounded-[14px] flex items-center justify-center text-amber-400 font-bold">
                        <i data-lucide="phone-call" class="w-6 h-6"></i>
                    </div>
                </div>
                <div>
                    <h1 class="font-cinzel text-xl sm:text-2xl font-bold text-amber-400">AstroSubam RTC Voice Lab</h1>
                    <p class="text-xs text-emerald-300/70">Real-Time Two-Way Agora Voice Testing Portal</p>
                </div>
            </div>
            <span id="callStatusPill" class="px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-gray-400 border border-white/10 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-gray-400"></span> Disconnected
            </span>
        </div>

        <!-- Controls Form -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-xs font-bold text-amber-300/80 mb-1.5 uppercase tracking-wider">Select Your Role</label>
                <select id="roleSelect" class="w-full bg-[#0A1612] border border-white/15 rounded-xl px-4 py-3 text-sm font-semibold text-white focus:outline-none focus:border-amber-400">
                    <option value="astrologer">Astrologer (Guruji • UID: 200001)</option>
                    <option value="user">Devotee (User 2 • UID: 100002)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-amber-300/80 mb-1.5 uppercase tracking-wider">Channel Name</label>
                <input type="text" id="channelInput" value="astrosubam_voice_test" class="w-full bg-[#0A1612] border border-white/15 rounded-xl px-4 py-3 text-sm font-semibold text-white focus:outline-none focus:border-amber-400">
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 mb-6 text-xs text-amber-200/90 leading-relaxed">
            <p class="font-bold mb-1 flex items-center gap-1.5 text-amber-400">
                <i data-lucide="info" class="w-4 h-4"></i> எப்படி டெஸ்ட் செய்வது (How to Test):
            </p>
            <ul class="list-disc list-inside space-y-1 text-slate-300">
                <li>இந்த பக்கத்தை <strong>இரண்டு Browser Tab</strong>-களில் திறக்கவும் (அல்லது கணினி மற்றும் மொபைலில் திறக்கவும்).</li>
                <li>ஒரு Tab-ல் <strong>Astrologer</strong> தேர்வு செய்யவும், மற்றொரு Tab-ல் <strong>Devotee</strong> தேர்வு செய்யவும்.</li>
                <li>இரண்டிலும் <strong>"Join & Speak"</strong> அழுத்தினால், இருபுறமும் குரல் தெளிவாக கேட்கும்!</li>
            </ul>
        </div>

        <!-- Visual Meters & Call Stats -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <!-- Local Mic Volume -->
            <div class="bg-[#0A1612] border border-white/10 rounded-2xl p-4">
                <div class="flex items-center justify-between text-xs mb-2">
                    <span class="text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="mic" class="w-3.5 h-3.5 text-emerald-400"></i> Your Mic Volume
                    </span>
                    <span id="localVolText" class="font-bold text-emerald-400">0%</span>
                </div>
                <div class="w-full bg-white/10 h-3 rounded-full overflow-hidden">
                    <div id="localVolBar" class="bg-emerald-500 h-full w-0 transition-all duration-100"></div>
                </div>
            </div>

            <!-- Remote Audio Volume -->
            <div class="bg-[#0A1612] border border-white/10 rounded-2xl p-4">
                <div class="flex items-center justify-between text-xs mb-2">
                    <span class="text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="volume-2" class="w-3.5 h-3.5 text-amber-400"></i> Peer Voice Volume
                    </span>
                    <span id="remoteVolText" class="font-bold text-amber-400">0%</span>
                </div>
                <div class="w-full bg-white/10 h-3 rounded-full overflow-hidden">
                    <div id="remoteVolBar" class="bg-amber-500 h-full w-0 transition-all duration-100"></div>
                </div>
            </div>
        </div>

        <!-- Timer & Call Action Buttons -->
        <div class="flex items-center justify-between bg-[#0A1612] border border-white/10 rounded-2xl p-4 mb-6">
            <div>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">Call Duration</p>
                <p id="timerDisplay" class="font-cinzel text-2xl font-bold text-amber-400">00:00</p>
            </div>

            <div class="flex items-center gap-2">
                <button id="muteBtn" onclick="toggleMute()" disabled class="p-3 bg-white/10 text-white rounded-xl hover:bg-white/15 disabled:opacity-40 transition">
                    <i data-lucide="mic" id="muteIcon" class="w-5 h-5"></i>
                </button>
                <button id="joinBtn" onclick="toggleCall()" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                    <i data-lucide="phone" class="w-5 h-5"></i> <span id="joinBtnText">Join & Speak</span>
                </button>
            </div>
        </div>

        <!-- Live Diagnostics Log -->
        <div class="bg-[#0A1612] border border-white/10 rounded-2xl p-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <i data-lucide="terminal" class="w-3.5 h-3.5 text-amber-400"></i> RTC Event Stream
            </p>
            <div id="logArea" class="h-32 overflow-y-auto text-[11px] font-mono text-emerald-300/80 space-y-1">
                <p class="text-slate-500">Ready to initiate live voice test. Click "Join & Speak".</p>
            </div>
        </div>

    </div>

    <script>
        lucide.createIcons();

        let client = null;
        let localAudioTrack = null;
        let isJoined = false;
        let isMuted = false;
        let timerInterval = null;
        let timerSeconds = 0;

        function log(msg, color = 'text-emerald-300/90') {
            const area = document.getElementById('logArea');
            const p = document.createElement('p');
            p.className = color;
            p.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
            area.appendChild(p);
            area.scrollTop = area.scrollHeight;
        }

        async function toggleCall() {
            if (isJoined) {
                await leaveCall();
            } else {
                await joinCall();
            }
        }

        async function joinCall() {
            const role = document.getElementById('roleSelect').value;
            const channel = document.getElementById('channelInput').value.trim() || 'astrosubam_voice_test';
            const btn = document.getElementById('joinBtn');
            const btnText = document.getElementById('joinBtnText');
            btn.disabled = true;
            btnText.textContent = 'Connecting...';

            log(`Fetching token for role: ${role.toUpperCase()}, channel: ${channel}...`, 'text-amber-300');

            try {
                // 1. Fetch official Agora token from backend
                const tokenRes = await fetch(`test_agora_call.php?action=get_token&role=${role}&channel=${encodeURIComponent(channel)}`);
                const tokenData = await tokenRes.json();

                if (!tokenData.ok || !tokenData.token) {
                    throw new Error('Token generation failed on server');
                }

                log(`Token received! UID: ${tokenData.uid}, AppID: ${tokenData.appId.substring(0, 8)}...`, 'text-emerald-400');

                // 2. Initialize Agora RTC Client in Communication / RTC mode
                client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

                // 3. Register remote user event listeners
                client.on("user-published", async (user, mediaType) => {
                    log(`Remote user published: UID=${user.uid}, media=${mediaType}`, 'text-amber-400 font-bold');
                    await client.subscribe(user, mediaType);
                    if (mediaType === "audio") {
                        user.audioTrack.play();
                        user.audioTrack.setVolume(100);
                        log(`Playing remote audio for UID=${user.uid}!`, 'text-emerald-400 font-bold');
                    }
                });

                client.on("user-joined", (user) => {
                    log(`Remote peer joined channel: UID=${user.uid}`, 'text-amber-300');
                    updateStatus(true, `Connected with UID ${user.uid}`);
                });

                client.on("user-left", (user) => {
                    log(`Remote peer left: UID=${user.uid}`, 'text-rose-400');
                });

                // Enable Volume Indicator
                AgoraRTC.enableLogUpload();
                client.enableAudioVolumeIndicator();
                client.on("volume-indicator", (volumes) => {
                    volumes.forEach((vol) => {
                        if (vol.uid === tokenData.uid) {
                            const pct = Math.min(100, Math.round((vol.level / 100) * 100));
                            document.getElementById('localVolBar').style.width = pct + '%';
                            document.getElementById('localVolText').textContent = pct + '%';
                        } else {
                            const pct = Math.min(100, Math.round((vol.level / 100) * 100));
                            document.getElementById('remoteVolBar').style.width = pct + '%';
                            document.getElementById('remoteVolText').textContent = pct + '%';
                        }
                    });
                });

                // 4. Join channel
                await client.join(tokenData.appId, tokenData.channel, tokenData.token, Number(tokenData.uid));
                log(`Joined channel ${tokenData.channel} successfully!`, 'text-emerald-400 font-bold');

                // 5. Create local microphone audio track with noise suppression & AEC
                localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack({
                    encoderConfig: "speech_standard",
                    AEC: true,
                    ANS: true,
                    AGC: true
                });

                // 6. Publish track
                await client.publish([localAudioTrack]);
                log(`Microphone published! Speaking is now live.`, 'text-emerald-400 font-bold');

                isJoined = true;
                btn.disabled = false;
                btn.className = "px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2";
                btnText.textContent = "Leave Call";
                document.getElementById('muteBtn').disabled = false;
                document.getElementById('roleSelect').disabled = true;
                document.getElementById('channelInput').disabled = true;

                updateStatus(true, "In Call (Live)");
                startTimer();

            } catch (err) {
                log(`Error: ${err.message}`, 'text-rose-400 font-bold');
                btn.disabled = false;
                btnText.textContent = "Join & Speak";
                alert('Join failed: ' + err.message);
            }
        }

        async function leaveCall() {
            if (timerInterval) clearInterval(timerInterval);
            timerSeconds = 0;
            document.getElementById('timerDisplay').textContent = "00:00";

            if (localAudioTrack) {
                localAudioTrack.stop();
                localAudioTrack.close();
                localAudioTrack = null;
            }

            if (client) {
                await client.leave();
                client = null;
            }

            isJoined = false;
            document.getElementById('localVolBar').style.width = '0%';
            document.getElementById('localVolText').textContent = '0%';
            document.getElementById('remoteVolBar').style.width = '0%';
            document.getElementById('remoteVolText').textContent = '0%';

            const btn = document.getElementById('joinBtn');
            btn.className = "px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2";
            document.getElementById('joinBtnText').textContent = "Join & Speak";
            document.getElementById('muteBtn').disabled = true;
            document.getElementById('roleSelect').disabled = false;
            document.getElementById('channelInput').disabled = false;

            updateStatus(false, "Disconnected");
            log('Left channel and cleaned up media.', 'text-slate-400');
        }

        function toggleMute() {
            if (!localAudioTrack) return;
            isMuted = !isMuted;
            localAudioTrack.setEnabled(!isMuted);
            document.getElementById('muteIcon').setAttribute('data-lucide', isMuted ? 'mic-off' : 'mic');
            document.getElementById('muteBtn').className = isMuted 
                ? 'p-3 bg-rose-600/40 border border-rose-500 text-rose-300 rounded-xl transition'
                : 'p-3 bg-white/10 text-white rounded-xl hover:bg-white/15 transition';
            lucide.createIcons();
            log(`Microphone ${isMuted ? 'MUTED' : 'UNMUTED'}.`, 'text-amber-300');
        }

        function startTimer() {
            if (timerInterval) clearInterval(timerInterval);
            timerSeconds = 0;
            timerInterval = setInterval(() => {
                timerSeconds++;
                const mins = String(Math.floor(timerSeconds / 60)).padStart(2, '0');
                const secs = String(timerSeconds % 60).padStart(2, '0');
                document.getElementById('timerDisplay').textContent = `${mins}:${secs}`;
            }, 1000);
        }

        function updateStatus(active, text) {
            const pill = document.getElementById('callStatusPill');
            if (active) {
                pill.className = "px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center gap-1.5 animate-pulse";
                pill.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-400"></span> ${text}`;
            } else {
                pill.className = "px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-gray-400 border border-white/10 flex items-center gap-1.5";
                pill.innerHTML = `<span class="w-2 h-2 rounded-full bg-gray-400"></span> ${text}`;
            }
        }
    </script>
</body>
</html>

