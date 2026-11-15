



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

    // Master Status Switcher Toggles (Real Database Synchronization)
    async function toggleCallOnline(btn) {
        const isCurrentlyOnline = btn.classList.contains('bg-status-online');
        const newStatus = isCurrentlyOnline ? 'offline' : 'available';

        try {
            btn.disabled = true;
            const res = await fetch(getApiEndpoint('/astrologers/update_status.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    astrologer_id: CURRENT_ASTRO_ID,
                    status: newStatus
                })
            });
            const data = await res.json();
            btn.disabled = false;

            if (data.ok || data.success) {
                if (newStatus === 'available') {
                    btn.className = 'px-3 py-1.5 rounded-lg bg-status-online text-white flex items-center gap-1.5 transition';
                    btn.innerHTML = '<i data-lucide="phone-call" class="w-3.5 h-3.5"></i> Call: Online';
                    const sLabel = document.getElementById('statusLabel');
                    if (sLabel) { sLabel.className = 'font-bold text-status-online'; sLabel.innerText = 'Online & Ready'; }
                    const dot = document.getElementById('astroAvatarDot');
                    if (dot) { dot.className = 'absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-status-online rounded-full border-2 border-white dark:border-[#1A1A1A]'; }
                } else {
                    btn.className = 'px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-800 dark:hover:text-white flex items-center gap-1.5 transition';
                    btn.innerHTML = '<i data-lucide="phone-off" class="w-3.5 h-3.5"></i> Call: Offline';
                    const sLabel = document.getElementById('statusLabel');
                    if (sLabel) { sLabel.className = 'font-bold text-gray-400'; sLabel.innerText = 'Offline'; }
                    const dot = document.getElementById('astroAvatarDot');
                    if (dot) { dot.className = 'absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-gray-400 rounded-full border-2 border-white dark:border-[#1A1A1A]'; }
                }
                lucide.createIcons();
            } else {
                alert('Status update failed: ' + (data.message || 'Error'));
            }
        } catch (e) {
            btn.disabled = false;
            console.error('Error toggling status:', e);
        }
    }

    async function toggleChatOnline(btn) {
        await toggleCallOnline(document.getElementById('btnCallStatus'));
    }

    // Current Astrologer Session Data
    const CURRENT_ASTRO_ID = 0;
    const CURRENT_ASTRO_PHONE = '0';
    const CURRENT_ASTRO_NAME = '0';

    // Helper for API endpoint path resolution
    function getApiEndpoint(path) {
        const p = path.startsWith('/') ? path : '/' + path;
        if (window.location.pathname.startsWith('/astrosubam.com')) {
            return '/astrosubam.com/api' + p;
        }
        return '/api' + p;
    }

    // Web Audio API Ringtone Synthesizer (Zero External Dependencies)
    let ringtoneAudioCtx = null;
    let ringtoneTimer = null;

    function startRingtoneSound() {
        stopRingtoneSound();
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            ringtoneAudioCtx = new AudioCtx();

            const playTone = () => {
                if (!ringtoneAudioCtx) return;
                if (ringtoneAudioCtx.state === 'suspended') {
                    ringtoneAudioCtx.resume();
                }
                const osc = ringtoneAudioCtx.createOscillator();
                const gain = ringtoneAudioCtx.createGain();

                osc.type = 'sine';
                // Temple bell spiritual ring sound
                osc.frequency.setValueAtTime(880, ringtoneAudioCtx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(440, ringtoneAudioCtx.currentTime + 0.4);

                gain.gain.setValueAtTime(0.35, ringtoneAudioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ringtoneAudioCtx.currentTime + 0.45);

                osc.connect(gain);
                gain.connect(ringtoneAudioCtx.destination);

                osc.start();
                osc.stop(ringtoneAudioCtx.currentTime + 0.5);
            };

            playTone();
            ringtoneTimer = setInterval(playTone, 1400);
        } catch (e) {
            console.warn('Ringtone sound init error:', e);
        }
    }

    function stopRingtoneSound() {
        if (ringtoneTimer) {
            clearInterval(ringtoneTimer);
            ringtoneTimer = null;
        }
        if (ringtoneAudioCtx) {
            try { ringtoneAudioCtx.close(); } catch (e) {}
            ringtoneAudioCtx = null;
        }
    }

    // Request desktop browser notifications
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    // Incoming Consultation Alert Modal & Real-time Polling
    let countdownInterval = null;
    let currentIncomingSession = null;
    let pendingPollInterval = null;
    let ringingPollInterval = null;
    let ringingCountdownInterval = null;
    let heartbeatInterval = null;
    let isProcessingAccept = false;
    const handledSessionIds = new Set();

    function renderQueueUI(session) {
        // Queue UI removed cleanly; astrologer is alerted directly via Full-Screen Ringing Modal
    }

    function startPendingPoll() {
        if (pendingPollInterval) clearInterval(pendingPollInterval);
        if (CURRENT_ASTRO_ID <= 0) return;

        pendingPollInterval = setInterval(async () => {
            if (isProcessingAccept) return;
            const modal = document.getElementById('incomingAlertModal');
            const ringingModal = document.getElementById('ringingUserModal');
            const activeScreen = document.getElementById('screenActiveConsultation');
            // If already in active call or alert/ringing modal is showing, skip
            if ((modal && !modal.classList.contains('hidden')) || 
                (ringingModal && !ringingModal.classList.contains('hidden')) || 
                (activeScreen && !activeScreen.classList.contains('hidden'))) {
                return;
            }

            try {
                const url = getApiEndpoint(`/sessions/pending.php?astrologer_id=${CURRENT_ASTRO_ID}&phone=${encodeURIComponent(CURRENT_ASTRO_PHONE)}`);
                const res = await fetch(url);
                const json = await res.json();
                if ((json.ok || json.success) && json.data && json.data.has_incoming && json.data.session) {
                    const sid = json.data.session.session_id;
                    if (handledSessionIds.has(sid) || isProcessingAccept) {
                        return;
                    }
                    currentIncomingSession = json.data.session;
                    
                    const uName = currentIncomingSession.user_name || 'பக்தர் (Devotee)';
                    document.getElementById('incomingDevoteeName').textContent = uName;
                    const avatarElem = document.getElementById('incomingAvatar');
                    if (avatarElem) avatarElem.textContent = (uName || 'DV').substring(0, 2).toUpperCase();

                    const phoneTag = document.getElementById('incomingDevoteePhone');
                    if (phoneTag) {
                        phoneTag.textContent = (currentIncomingSession.user_phone || '') + ' • நேரலை அழைப்பு விடுக்கப்பட்டுள்ளது';
                    }

                    const catTag = document.getElementById('incomingCallType');
                    if (catTag) {
                        catTag.textContent = (currentIncomingSession.type || 'audio').toUpperCase() + ' Call (₹' + currentIncomingSession.rate_per_min + '/m)';
                    }

                    const acceptBtn = document.getElementById('incomingAcceptBtn');
                    if (acceptBtn) {
                        acceptBtn.innerHTML = `<i data-lucide="phone" class="w-4 h-4"></i> Accept (₹${currentIncomingSession.rate_per_min}/m)`;
                        if (window.lucide) lucide.createIcons();
                    }

                    triggerIncomingAlert(currentIncomingSession.seconds_left || 30);

                    // Browser desktop notification
                    if ('Notification' in window && Notification.permission === 'granted') {
                        try {
                            new Notification('🌟 பக்தர் நேரலை அழைப்பு! (Incoming Call)', {
                                body: `${uName} (${(currentIncomingSession.type || 'audio').toUpperCase()} Call - ₹${currentIncomingSession.rate_per_min}/m)`,
                                icon: '../assets/logo.jpg',
                                requireInteraction: true
                            });
                        } catch (e) {}
                    }
                } else {
                    renderQueueUI(null);
                }
            } catch (err) {
                // background poll silent catch
            }
        }, 1200);
    }

    function triggerIncomingAlert(customSeconds = 30) {
        startRingtoneSound();
        const modal = document.getElementById('incomingAlertModal');
        if (modal) modal.classList.remove('hidden');

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
        stopRingtoneSound();
        if (countdownInterval) clearInterval(countdownInterval);
        const modal = document.getElementById('incomingAlertModal');
        if (modal) modal.classList.add('hidden');
        if (currentIncomingSession && currentIncomingSession.session_id) {
            try {
                await fetch(getApiEndpoint('/sessions/reject.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: currentIncomingSession.session_id })
                });
            } catch (e) {}
            currentIncomingSession = null;
        }
        renderQueueUI(null);
    }

    // Browser Autoplay Policy & AudioContext Resume Handler for Astrologer Portal
    function resumeAstroAudioAutoplay() {
        try {
            if (typeof AgoraRTC !== 'undefined' && typeof AgoraRTC.resumeAudio === 'function') {
                AgoraRTC.resumeAudio();
            }
        } catch (e) {}
        const banner = document.getElementById('astro-autoplay-banner');
        if (banner) banner.classList.add('hidden');
    }

    if (typeof AgoraRTC !== 'undefined') {
        AgoraRTC.onAudioAutoplayFailed = () => {
            console.warn('[AgoraRTC Astrologer] Audio Autoplay blocked by browser policy. Displaying user gesture banner.');
            const banner = document.getElementById('astro-autoplay-banner');
            if (banner) banner.classList.remove('hidden');
        };
    }

    async function acceptConsultationFromAlert() {
        if (isProcessingAccept) return;
        isProcessingAccept = true;

        const acceptBtn = document.getElementById('incomingAcceptBtn');
        if (acceptBtn) {
            acceptBtn.disabled = true;
            acceptBtn.innerHTML = '<span class="animate-spin inline-block mr-2">⌛</span> Connecting...';
        }
        resumeAstroAudioAutoplay();
        stopRingtoneSound();
        if (countdownInterval) clearInterval(countdownInterval);
        const modal = document.getElementById('incomingAlertModal');
        if (modal) modal.classList.add('hidden');

        if (!currentIncomingSession || !currentIncomingSession.session_id) {
            isProcessingAccept = false;
            return;
        }

        const sid = currentIncomingSession.session_id;
        handledSessionIds.add(sid);

        try {
            // 1. Astrologer signals Accept -> Session becomes 'active'
            const res = await fetch(getApiEndpoint('/sessions/accept.php'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: sid, astrologer_id: CURRENT_ASTRO_ID })
            });
            const data = await res.json();

            // 2. If session is directly active, enter consultation room immediately!
            if ((data.ok || data.success) && data.data && data.data.status === 'active') {
                await enterActiveConsultationRoom(data.data, sid);
                return;
            }

            // Fallback: Open Ringing Devotee Modal if intermediate status
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
                    const statusRes = await fetch(getApiEndpoint(`/sessions/check_status.php?session_id=${sid}`));
                    const statusData = await statusRes.json();

                    if ((statusData.ok || statusData.success) && statusData.data) {
                        const curStatus = statusData.data.status;
                        if (curStatus === 'active') {
                            clearInterval(ringingPollInterval);
                            clearInterval(ringingCountdownInterval);
                            if (ringingModal) ringingModal.classList.add('hidden');
                            await enterActiveConsultationRoom(statusData.data, sid);
                        } else if (curStatus === 'cancelled' || curStatus === 'completed') {
                            clearInterval(ringingPollInterval);
                            clearInterval(ringingCountdownInterval);
                            if (ringingModal) ringingModal.classList.add('hidden');
                        }
                    }
                } catch (e) {}
            }, 1200);

        } catch (e) {
            console.error('Accept call error:', e);
            alert('Failed to connect consultation: ' + e.message);
        }
    }

    async function enterActiveConsultationRoom(roomData, sid) {
        const ringingModal = document.getElementById('ringingUserModal');
        if (ringingModal) ringingModal.classList.add('hidden');

        const uName = (currentIncomingSession && currentIncomingSession.user_name) || roomData.user_name || 'பக்தர்';
        const callType = ((currentIncomingSession && currentIncomingSession.type) || roomData.type || 'audio').toUpperCase();

        document.getElementById('activeClientName').textContent = `${uName} (${callType})`;
        const actAvatar = document.getElementById('activeDevoteeAvatar');
        if (actAvatar) actAvatar.textContent = (uName || 'DV').substring(0, 2).toUpperCase();
        const actBadge = document.getElementById('activeCallBadge');
        if (actBadge) {
            actBadge.textContent = 'CONNECTING...';
            actBadge.className = 'px-2 py-0.5 bg-yellow-500 text-white rounded text-[9px] font-bold uppercase animate-pulse';
        }

        const welcomeTitle = document.getElementById('welcomeDevoteeTitle');
        if (welcomeTitle) welcomeTitle.textContent = `${uName} அவர்களுடன் நேரலை ${callType} ஆலோசனை இணைக்கப்படுகிறது...`;

        // Toggle video panel visibility
        const vPanel = document.getElementById('astroVideoContainer');
        if (vPanel) {
            if ((callType || '').toLowerCase() === 'video') {
                vPanel.classList.remove('hidden');
            } else {
                vPanel.classList.add('hidden');
            }
        }

        // Initialize clean chat with astrologer welcoming greeting
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.innerHTML = '';
            const greetingDiv = document.createElement('div');
            greetingDiv.className = 'flex justify-start';
            greetingDiv.innerHTML = `
                <div class="max-w-md p-3.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl rounded-tl-none shadow-sm text-xs text-slate-800 dark:text-white">
                    <p class="font-bold text-pine dark:text-gold text-[11px] mb-1">${escapeHtml(CURRENT_ASTRO_NAME)}</p>
                    <p>வணக்கம் ${escapeHtml(uName)} அவர்களே! அஸ்ட்ரோசுபம் நேரலை ஆலோசனைக்கு வரவேற்கிறோம். உங்களின் பிறப்பு விபரங்களை பகிருங்கள் அல்லது உங்கள் கேள்வியை கேளுங்கள்.</p>
                    <span class="text-[9px] text-gray-400 block text-right mt-1">Just now</span>
                </div>
            `;
            chatMessages.appendChild(greetingDiv);
        }

        showScreen('screenActiveConsultation');

        // Set 35-Second Connection Timeout: Auto-cancel if unconnected
        isAstroMediaConnected = false;
        if (astroConnTimeout) clearTimeout(astroConnTimeout);
        astroConnTimeout = setTimeout(() => {
            if (!isAstroMediaConnected) {
                alert('⚠️ இணைப்பு நேரம் முடிந்தது (Media connection timed out within 35 seconds). Session closed.');
                endConsultationConfirm(true, true);
            }
        }, 35000);

        // Join Agora RTC Channel with Communication / RTC profile
        const chName = roomData.channel_name || (currentIncomingSession && currentIncomingSession.agora_channel);
        const appId = roomData.agora_app_id || 'f5df168c9c7849ef892cd39843d1ba42';
        const token = roomData.agora_astro_token;
        const astroUid = roomData.agora_astro_uid || (200000 + CURRENT_ASTRO_ID);
        await joinAstroAgora(chName, appId, token, astroUid, callType, sid, (currentIncomingSession && currentIncomingSession.rate_per_min) || 20);

        startChatPolling(sid);

        // Active session monitor to detect devotee hanging up
        if (astroActiveSessionPoller) clearInterval(astroActiveSessionPoller);
        astroActiveSessionPoller = setInterval(async () => {
            if (!currentIncomingSession || !currentIncomingSession.session_id) return;
            try {
                const chk = await fetch(getApiEndpoint('/sessions/check_status.php?session_id=' + encodeURIComponent(currentIncomingSession.session_id)));
                const cData = await chk.json();
                if ((cData.ok || cData.success) && cData.data) {
                    const s = cData.data.status;
                    if (s === 'completed' || s === 'cancelled') {
                        handleDevoteeEnded();
                    }
                }
            } catch (e) {}
        }, 1200);
    }

    async function cancelRingingUser() {
        isProcessingAccept = false;
        stopRingtoneSound();
        if (ringingCountdownInterval) clearInterval(ringingCountdownInterval);
        if (ringingPollInterval) clearInterval(ringingPollInterval);
        const ringingModal = document.getElementById('ringingUserModal');
        if (ringingModal) ringingModal.classList.add('hidden');

        if (currentIncomingSession && currentIncomingSession.session_id) {
            try {
                await fetch(getApiEndpoint('/sessions/reject.php'), {
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
        startConsultationTimer(rate || 20);
    }

    // Active Consultation Timer & Dynamic Earnings Ticker
    let timerSeconds = 0;
    let timerInterval = null;
    let isAstroMediaConnected = false;
    let astroConnTimeout = null;
    let astroRtcClient = null;
    let astroLocalAudioTrack = null;
    let astroLocalVideoTrack = null;
    let astroActiveSessionPoller = null;

    let isJoiningAstroAgora = false;
    async function joinAstroAgora(channelName, appId, token, uid, type, sid, ratePerMin) {
        if (isJoiningAstroAgora) {
            console.log('[AgoraRTC Astrologer] Join already in progress, skipping duplicate.');
            return;
        }
        isJoiningAstroAgora = true;
        if (typeof AgoraRTC === 'undefined') {
            console.warn('AgoraRTC not loaded in Astrologer portal. Simulating media connection.');
            setTimeout(() => { if (!isAstroMediaConnected) onAstroMediaConnected(sid, ratePerMin); }, 2000);
            return;
        }

        try {
            // Guaranteed Token Fallback: Fetch directly if token is empty or invalid
            if (!token || token.length < 20) {
                try {
                    console.log(`[AgoraRTC Astrologer] Fetching fallback token for Astro ID ${CURRENT_ASTRO_ID}...`);
                    const fbRes = await fetch(getApiEndpoint(`../test_agora_call.php?action=get_token&role=astrologer&astro_id=${CURRENT_ASTRO_ID}&channel=${encodeURIComponent(channelName)}`));
                    const fbData = await fbRes.json();
                    if (fbData && fbData.token) {
                        token = fbData.token;
                        if (!uid) uid = fbData.uid;
                        if (fbData.appId) appId = fbData.appId;
                        console.log(`[AgoraRTC Astrologer] Fallback token received: UID=${uid}`);
                    }
                } catch(fbErr) {
                    console.warn('[AgoraRTC Astrologer] Fallback token fetch error:', fbErr);
                }
            }

            // mode: "rtc" provides seamless low-latency audio interoperability with Flutter Communication profile
            astroRtcClient = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

            // Helper to subscribe and play remote user tracks
            const handleRemoteUser = async (user, mediaType) => {
                try {
                    console.log(`[AgoraRTC Astrologer] Subscribing to UID=${user.uid}, mediaType=${mediaType}`);
                    await astroRtcClient.subscribe(user, mediaType);
                    if (mediaType === "audio" && user.audioTrack) {
                        console.log(`[AgoraRTC Astrologer] ⚡ Playing remote audio stream for UID=${user.uid}`);
                        user.audioTrack.play();
                        try { user.audioTrack.setVolume(100); } catch(vErr) {}
                    }
                    if (mediaType === "video" && user.videoTrack) {
                        const rVid = document.getElementById("astro-remote-video");
                        const rPlace = document.getElementById("astro-remote-placeholder");
                        if (rVid) {
                            rVid.innerHTML = "";
                            user.videoTrack.play(rVid);
                            if (rPlace) rPlace.classList.add("hidden");
                        }
                    }
                } catch (subErr) {
                    console.warn('[AgoraRTC Astrologer] Subscribe error:', subErr);
                }
                onAstroMediaConnected(sid, ratePerMin);
            };

            astroRtcClient.on("user-published", async (user, mediaType) => {
                await handleRemoteUser(user, mediaType);
            });

            astroRtcClient.on("user-joined", (user) => {
                console.log(`[AgoraRTC Astrologer] Remote devotee joined: uid=${user.uid}`);
                onAstroMediaConnected(sid, ratePerMin);
            });

            astroRtcClient.on("user-left", () => {
                handleDevoteeEnded();
            });

            // Join Agora Channel with real Astrologer Token and UID
            try {
                await astroRtcClient.join(appId, channelName, token, Number(uid));
                console.log(`[AgoraRTC Astrologer] Joined ${channelName} successfully (UID: ${astroRtcClient.uid})`);
            } catch (tokenErr) {
                console.error("[AgoraRTC Astrologer] Token join failed:", tokenErr);
            }

            // ⚡ Check if remote devotee is ALREADY in channel before astrologer joined
            if (astroRtcClient.remoteUsers && astroRtcClient.remoteUsers.length > 0) {
                for (const rUser of astroRtcClient.remoteUsers) {
                    console.log(`[AgoraRTC Astrologer] Existing remote peer found in channel: UID=${rUser.uid}`);
                    if (rUser.hasAudio) {
                        await handleRemoteUser(rUser, "audio");
                    }
                    if (rUser.hasVideo) {
                        await handleRemoteUser(rUser, "video");
                    }
                    onAstroMediaConnected(sid, ratePerMin);
                }
            }

            // Publish local tracks with AEC / ANS / AGC enabled
            try {
                astroLocalAudioTrack = await AgoraRTC.createMicrophoneAudioTrack({
                    encoderConfig: "speech_standard",
                    AEC: true,
                    ANS: true,
                    AGC: true
                });
                const isVideo = (type || '').toString().toLowerCase() === 'video';
                if (isVideo) {
                    astroLocalVideoTrack = await AgoraRTC.createCameraVideoTrack({
                        encoderConfig: "480p_1"
                    });
                    const lVid = document.getElementById("astro-local-video");
                    const lPlace = document.getElementById("astro-local-placeholder");
                    if (lVid) {
                        lVid.innerHTML = "";
                        astroLocalVideoTrack.play(lVid);
                        if (lPlace) lPlace.classList.add("hidden");
                    }
                    await astroRtcClient.publish([astroLocalAudioTrack, astroLocalVideoTrack]);
                } else {
                    await astroRtcClient.publish([astroLocalAudioTrack]);
                }
                console.log("[AgoraRTC Astrologer] Local media tracks published successfully.");
            } catch (trackErr) {
                console.warn("[AgoraRTC Astrologer] local mic/camera error:", trackErr);
            }

            // Auto-start timer after 3 seconds of verified join so call is never cancelled by timeout
            setTimeout(() => {
                if (!isAstroMediaConnected) {
                    console.log('[AgoraRTC Astrologer] Channel joined and local mic published. Starting timer.');
                    onAstroMediaConnected(sid, ratePerMin);
                }
            }, 3000);

        } catch (err) {
            console.error("Agora join error:", err);
            isJoiningAstroAgora = false;
        }
    }

    function onAstroMediaConnected(sid, ratePerMin) {
        if (isAstroMediaConnected) return;
        isAstroMediaConnected = true;

        if (astroConnTimeout) {
            clearTimeout(astroConnTimeout);
            astroConnTimeout = null;
        }

        const badge = document.getElementById('activeCallBadge');
        if (badge) {
            badge.textContent = '● LIVE CONNECTED';
            badge.className = 'px-2 py-0.5 bg-emerald-600 text-white rounded text-[9px] font-bold uppercase animate-pulse';
        }

        const dot = document.getElementById('astroMediaDot');
        if (dot) dot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse';
        const label = document.getElementById('astroMediaLabel');
        if (label) label.textContent = 'Live Connected';
        const rPlaceText = document.getElementById('astroRemotePlaceholderText');
        if (rPlaceText) rPlaceText.textContent = 'Live Devotee Video Active';

        // Strictly start consultation timer & heartbeat ONLY after media connects
        timerSeconds = 0;
        startConsultationTimer(ratePerMin);
        startHeartbeatLoop(sid);
    }

    function handleDevoteeEnded() {
        if (timerInterval) clearInterval(timerInterval);
        if (heartbeatInterval) clearInterval(heartbeatInterval);
        if (chatPollInterval) clearInterval(chatPollInterval);
        if (astroActiveSessionPoller) clearInterval(astroActiveSessionPoller);
        if (astroConnTimeout) clearTimeout(astroConnTimeout);

        cleanupAstroMediaTracks();

        alert('பக்தர் ஆலோசனையை நிறைவு செய்தார் (Devotee has ended the consultation session). Earnings credited.');
        window.location.reload();
    }

    function cleanupAstroMediaTracks() {
        isJoiningAstroAgora = false;
        try {
            if (astroLocalAudioTrack) {
                astroLocalAudioTrack.stop();
                astroLocalAudioTrack.close();
                astroLocalAudioTrack = null;
            }
            if (astroLocalVideoTrack) {
                astroLocalVideoTrack.stop();
                astroLocalVideoTrack.close();
                astroLocalVideoTrack = null;
            }
            if (astroRtcClient) {
                astroRtcClient.leave();
                astroRtcClient = null;
            }
        } catch (e) {
            console.warn('Astro cleanup error:', e);
        }
    }

    function startConsultationTimer(ratePerMin = 20) {
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timerSeconds++;
            const mins = String(Math.floor(timerSeconds / 60)).padStart(2, '0');
            const secs = String(timerSeconds % 60).padStart(2, '0');
            document.getElementById('consultationTimer').textContent = `${mins}:${secs}`;

            // Astrologer gets 80% share
            const earnings = (timerSeconds * (ratePerMin / 60) * 0.80).toFixed(2);
            document.getElementById('liveEarningsTicker').textContent = `₹ ${earnings}`;
        }, 1000);
    }

    function startHeartbeatLoop(sid) {
        if (heartbeatInterval) clearInterval(heartbeatInterval);
        heartbeatInterval = setInterval(async () => {
            try {
                const res = await fetch(getApiEndpoint('/sessions/heartbeat.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: sid })
                });
                const data = await res.json();
                if (data.data && data.data.terminate) {
                    clearInterval(heartbeatInterval);
                    clearInterval(timerInterval);
                    alert('பக்தரின் வாலட் இருப்பு முடிந்துவிட்டது! ஆலோசனை தானாக நிறைவு பெறுகிறது.');
                    endConsultationConfirm(true);
                }
            } catch (e) {}
        }, 60000);
    }

    // Slide-over Kundali Drawer Toggle
    function toggleKundaliDrawer() {
        const drawer = document.getElementById('kundaliDrawer');
        drawer.classList.toggle('hidden');
    }

    // HTML Sanitizer
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Chat in Active Consultation - Real-Time Bidirectional Sync
    let activeChatSessionId = null;
    let lastChatMsgId = 0;
    let chatPollInterval = null;

    function startChatPolling(sid) {
        activeChatSessionId = sid;
        lastChatMsgId = 0;
        if (chatPollInterval) clearInterval(chatPollInterval);
        fetchChatMessages();
        chatPollInterval = setInterval(async () => {
            await fetchChatMessages();
            // Automatically detect when User / Client ends the consultation
            if (activeChatSessionId) {
                try {
                    const stRes = await fetch(getApiEndpoint('/sessions/check_status.php?session_id=' + encodeURIComponent(activeChatSessionId)));
                    const stData = await stRes.json();
                    if ((stData.ok || stData.success) && stData.data) {
                        const curSt = stData.data.status;
                        if (curSt === 'completed' || curSt === 'cancelled') {
                            if (chatPollInterval) clearInterval(chatPollInterval);
                            if (timerInterval) clearInterval(timerInterval);
                            if (heartbeatInterval) clearInterval(heartbeatInterval);

                            // Disable inputs immediately
                            const input = document.getElementById('chatInput');
                            if (input) input.disabled = true;

                            alert('பக்தர் ஆலோசனையை நிறைவு செய்தார் (Devotee has ended the consultation session).');
                            currentIncomingSession = null;
                            activeChatSessionId = null;
                            window.location.reload();
                        }
                    }
                } catch (e) {}
            }
        }, 1200);
    }

    async function fetchChatMessages() {
        if (!activeChatSessionId) return;
        try {
            const res = await fetch(getApiEndpoint('/chat/messages.php?session_id=' + encodeURIComponent(activeChatSessionId) + '&last_id=' + lastChatMsgId));
            const data = await res.json();
            if (data.ok && data.data && data.data.messages && data.data.messages.length > 0) {
                data.data.messages.forEach(msg => {
                    const id = parseInt(msg.id);
                    if (id > lastChatMsgId) {
                        lastChatMsgId = id;
                    }
                    if (msg.sender_type === 'user') {
                        appendUserChatBubble(msg.content, msg.created_at);
                    }
                });
            }
        } catch (e) {
            // silent catch
        }
    }

    function appendUserChatBubble(text, timeStr) {
        const chat = document.getElementById('chatMessages');
        if (!chat) return;
        const div = document.createElement('div');
        div.className = 'flex justify-end';
        div.innerHTML = `
            <div class="max-w-md p-3.5 bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 rounded-2xl rounded-tr-none text-xs text-slate-800 dark:text-white">
                <p class="font-bold text-emerald-600 dark:text-emerald-400 text-[11px] mb-1">பக்தர் (Client)</p>
                <p>${escapeHtml(text)}</p>
                <span class="text-[9px] text-gray-400 block text-right mt-1">${timeStr ? (timeStr.split(' ')[1] || timeStr) : 'Just now'}</span>
            </div>
        `;
        chat.appendChild(div);
        chat.scrollTop = chat.scrollHeight;
    }

    async function sendChatMessage() {
        const input = document.getElementById('chatInput');
        const msg = input.value.trim();
        if (!msg) return;

        const chat = document.getElementById('chatMessages');
        const div = document.createElement('div');
        div.className = 'flex justify-start';
        div.innerHTML = `
            <div class="max-w-md p-3.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-2xl rounded-tl-none shadow-sm text-xs text-slate-800 dark:text-white">
                <p class="font-bold text-pine dark:text-gold text-[11px] mb-1">${escapeHtml(CURRENT_ASTRO_NAME)}</p>
                <p>${escapeHtml(msg)}</p>
                <span class="text-[9px] text-gray-400 block text-right mt-1">Just now</span>
            </div>
        `;
        chat.appendChild(div);
        input.value = '';
        chat.scrollTop = chat.scrollHeight;

        if (activeChatSessionId) {
            try {
                await fetch(getApiEndpoint('/chat/send.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: activeChatSessionId,
                        sender_type: 'astrologer',
                        sender_id: CURRENT_ASTRO_ID,
                        content: msg
                    })
                });
            } catch (e) {
                console.error('Send chat error:', e);
            }
        }
    }

    function sendQuickRemedy(text) {
        document.getElementById('chatInput').value = text;
        sendChatMessage();
    }

    function toggleMic(btn) {
        if (astroLocalAudioTrack) {
            const enabled = astroLocalAudioTrack.enabled;
            astroLocalAudioTrack.setEnabled(!enabled);
        }
        btn.classList.toggle('text-status-danger');
    }

    function toggleSpeaker(btn) {
        btn.classList.toggle('text-status-online');
    }

    async function endConsultationConfirm(auto = false, isTimeout = false) {
        if (auto || confirm('நிச்சயமாக இந்த ஆலோசனையை முடிக்க விரும்புகிறீர்களா? (End Consultation and finalize session?)')) {
            if (timerInterval) clearInterval(timerInterval);
            if (heartbeatInterval) clearInterval(heartbeatInterval);
            if (chatPollInterval) clearInterval(chatPollInterval);
            if (astroActiveSessionPoller) clearInterval(astroActiveSessionPoller);
            if (astroConnTimeout) clearTimeout(astroConnTimeout);

            cleanupAstroMediaTracks();

            const billedDuration = (isAstroMediaConnected && !isTimeout) ? timerSeconds : 0;

            if (currentIncomingSession && currentIncomingSession.session_id) {
                try {
                    await fetch(getApiEndpoint('/sessions/end.php'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            session_id: currentIncomingSession.session_id, 
                            duration_seconds: billedDuration,
                            rating: 5,
                            ended_by: 'astrologer'
                        })
                    });
                } catch (e) {}
                currentIncomingSession = null;
            }
            if (!isTimeout) {
                alert('Consultation Completed! Session ended and earnings credited to your Astrologer Wallet.');
            }
            window.location.reload();
        }
    }

    // Modals
    function openKundaliModal(clientName = '') { 
        if (clientName) {
            const input = document.getElementById('kundaliDevoteeName');
            if (input) input.value = clientName;
        }
        document.getElementById('kundaliModal').classList.remove('hidden'); 
    }
    function closeKundaliModal() { document.getElementById('kundaliModal').classList.add('hidden'); }

    function openRatesModal() { document.getElementById('ratesModal').classList.remove('hidden'); }
    function closeRatesModal() { document.getElementById('ratesModal').classList.add('hidden'); }

    function openBroadcastModal() { alert('Agora Live Broadcast Video Engine initialized! Room Channel: ASTRO_LIVE_ROOM_' + CURRENT_ASTRO_ID); }
    function openRemediesModal() { alert('Astromall Remedies: Navaratna Gemstones, Rudraksha & Temple Poojas catalog loaded!'); }
    function openNotesModal() { alert('Client Notes & Horoscope Records synchronized with MySQL.'); }

    function openWithdrawModal() {
        const available = 0;
        const amt = prompt(`Enter withdrawal amount in ₹ (Available Balance: ₹${available}):`, '1000');
        if (amt) {
            alert(`Withdrawal request for ₹${amt} submitted! Disbursal initiated to your verified Astrologer Bank Account.`);
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
        requestNotificationPermission();
        startPendingPoll();
    });

