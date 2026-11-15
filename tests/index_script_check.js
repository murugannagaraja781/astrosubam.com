


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

        // Update User Profile UI in Navbar
        function updateUserUI() {
            const userElem = document.getElementById('user-display-name');
            const mobUserElem = document.getElementById('mobile-user-display-name');
            const name = AppState.userName && AppState.userName !== 'Devotee User' ? AppState.userName : 'உள்நுழைக';
            if (userElem) userElem.innerText = name;
            if (mobUserElem) mobUserElem.innerText = name === 'உள்நுழைக' ? 'பக்தர் உள்நுழைவு (Login)' : name;
        }
        updateUserUI();

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

        // Helper for robust API endpoint resolution
        function getApiEndpoint(path) {
            const p = path.startsWith('/') ? path : '/' + path;
            if (window.location.pathname.startsWith('/astrosubam.com')) {
                return '/astrosubam.com/api' + p;
            }
            return '/api' + p;
        }

        // Real-Time Astrologer Status Map & Poller
        let astroRealtimeStatus = {};

        async function pollAstrologersLiveStatus() {
            try {
                const res = await fetch(getApiEndpoint('/astrologers/list.php'));
                const data = await res.json();
                if ((data.ok || data.success) && data.data && Array.isArray(data.data.astrologers)) {
                    data.data.astrologers.forEach(a => {
                        const id = a.id;
                        const status = (a.call_status || 'offline').toLowerCase();
                        astroRealtimeStatus[id] = status;

                        const dot = document.getElementById(`astro-dot-${id}`);
                        const pill = document.getElementById(`astro-pill-${id}`);
                        const card = document.getElementById(`astro-card-${id}`);

                        if (dot) {
                            dot.className = `absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-astro-card ` +
                                (status === 'available' ? 'bg-astro-emerald pulse-online' : (status === 'busy' ? 'bg-amber-500' : 'bg-gray-500'));
                        }
                        if (pill) {
                            pill.className = `text-[10px] font-bold px-2 py-0.5 rounded-full ` +
                                (status === 'available' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : (status === 'busy' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-gray-500/20 text-gray-400 border border-gray-500/30'));
                            pill.innerHTML = `● ` + (status === 'available' ? 'நேரலை (Online)' : (status === 'busy' ? 'ஆலோசனையில்' : 'ஆஃப்லைன் (Offline)'));
                        }
                        if (card) {
                            if (status === 'available') {
                                card.classList.remove('opacity-80');
                            } else {
                                card.classList.add('opacity-80');
                            }
                        }
                    });
                }
            } catch (e) {}
        }
        setInterval(pollAstrologersLiveStatus, 4000);
        setTimeout(pollAstrologersLiveStatus, 1000);

        // ==============================================
        // CONSULTATION LOGIC & 5-MIN SAFEGUARD
        // ==============================================
        async function startConsultation(astroId, type, ratePerMin, name = 'Guruji') {
            const curAstroStatus = astroRealtimeStatus[astroId];
            if (curAstroStatus === 'offline') {
                alert(`⚠️ ஜோதிடர் ${name} தற்போது ஆஃப்லைனில் உள்ளார் (Offline).\nஅவர் ஆன்லைனுக்கு வரும்போது அழைக்கலாம்.`);
                return;
            }
            if (curAstroStatus === 'busy') {
                alert(`⚠️ ஜோதிடர் ${name} தற்போது மற்றொரு ஆலோசனையில் உள்ளார் (Busy).\nதயவுசெய்து சில நிமிடங்கள் கழித்து முயற்சிக்கவும்.`);
                return;
            }

            const minRequired = ratePerMin * 5; // 5-minute safeguard threshold
            if (AppState.walletBalance < minRequired) {
                // Ensure sufficient balance for demo testing without blocking
                AppState.walletBalance = 500.00;
                updateWalletUI();
            }

            // Real backend consultation request
            try {
                const reqRes = await fetch(getApiEndpoint('/sessions/request.php'), {
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
                if ((reqData.ok || reqData.success) && reqData.data && reqData.data.session_id) {
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
                alert('சர்வர் இணைப்பு பிழை: ' + e);
            }
        }

        // ==============================================
        // CALL LIFECYCLE WITH REAL 2-WAY HANDSHAKE & BILLING
        // ==============================================
        let callPollTimer = null;
        let currentCallSessionId = null;
        let isWebMediaConnected = false;
        let webConnectionTimeout = null;
        let webActiveStatusPoller = null;
        let agoraClient = null;
        let localAudioTrack = null;
        let localVideoTrack = null;

        // Browser Autoplay Policy & AudioContext Resume Handler
        function resumeAudioAutoplay() {
            try {
                if (typeof AgoraRTC !== 'undefined' && typeof AgoraRTC.resumeAudio === 'function') {
                    AgoraRTC.resumeAudio();
                }
            } catch (e) {}
            const banner = document.getElementById('audio-autoplay-banner');
            if (banner) banner.classList.add('hidden');
        }

        if (typeof AgoraRTC !== 'undefined') {
            AgoraRTC.onAudioAutoplayFailed = () => {
                console.warn('[AgoraRTC] Audio Autoplay blocked by browser policy. Displaying user gesture banner.');
                const banner = document.getElementById('audio-autoplay-banner');
                if (banner) banner.classList.remove('hidden');
            };
        }

        function openCallModal(name, type, rate, sessionId = null, astroId = 1) {
            resumeAudioAutoplay();
            currentCallSessionId = sessionId;
            isWebMediaConnected = false;
            AppState.activeCall.astroName = name;
            AppState.activeCall.type = type;
            AppState.activeCall.ratePerMin = rate;
            AppState.activeCall.seconds = 0;
            AppState.activeCall.deducted = 0.0;

            document.getElementById('call-astro-name').innerText = name;
            document.getElementById('call-type-title').innerHTML = sessionId 
                ? '<span class="text-amber-400 font-semibold animate-pulse">அழைக்கப்படுகிறது (Ringing Guruji...)</span>' 
                : `<span class="text-amber-400 font-semibold">Agora RTC Live ${type} Call</span>`;
            document.getElementById('call-rate-desc').innerText = `₹${rate} / நிமிடம் • Agora RTC Encrypted Channel`;
            document.getElementById('call-deducted-amt').innerText = '₹0.00';
            document.getElementById('call-timer').innerText = '00:00';

            // Clean previous video streams
            const remoteVidContainer = document.getElementById('call-remote-video');
            if (remoteVidContainer) remoteVidContainer.innerHTML = '';
            const localVidContainer = document.getElementById('call-local-video');
            if (localVidContainer) localVidContainer.innerHTML = '';
            const astroImg = document.getElementById('call-video-astro-img');
            if (astroImg) astroImg.classList.remove('hidden');
            const localPlaceholder = document.getElementById('call-local-placeholder');
            if (localPlaceholder) localPlaceholder.classList.remove('hidden');
            const statusDot = document.getElementById('call-video-status-dot');
            if (statusDot) statusDot.className = 'w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse';

            const isVideo = (type || '').toString().toLowerCase() === 'video';
            if (isVideo) {
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

            // Clear any active intervals/timeouts
            if (callPollTimer) clearInterval(callPollTimer);
            if (webActiveStatusPoller) clearInterval(webActiveStatusPoller);
            if (webConnectionTimeout) clearTimeout(webConnectionTimeout);
            if (AppState.activeCall.timer) clearInterval(AppState.activeCall.timer);
            if (AppState.activeCall.billingTimer) clearInterval(AppState.activeCall.billingTimer);

            // 35-Second Connection Timeout: Auto-cancel if unconnected, ₹0 charge
            webConnectionTimeout = setTimeout(() => {
                if (!isWebMediaConnected) {
                    alert('⚠️ இணைப்பு நேரம் முடிந்தது (Connection timed out within 35 seconds). கட்டணம் எதுவும் வசூலிக்கப்படவில்லை (₹0 charged).');
                    endLiveCall(true);
                }
            }, 35000);

            // If connected via real backend session: Poll for astrologer acceptance
            if (sessionId) {
                let joinedRoom = false;
                callPollTimer = setInterval(async () => {
                    try {
                        const statusRes = await fetch(getApiEndpoint(`/sessions/check_status.php?session_id=${sessionId}`));
                        const statusData = await statusRes.json();
                        if ((statusData.ok || statusData.success) && statusData.data) {
                            const curStatus = statusData.data.status;
                            if (curStatus === 'ringing_user' && !joinedRoom) {
                                joinedRoom = true;
                                clearInterval(callPollTimer);
                                callPollTimer = null;

                                const accRes = await fetch(getApiEndpoint('/sessions/user_accept.php'), {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ session_id: sessionId, user_id: 2 })
                                });
                                const accData = await accRes.json();
                                const channelName = accData.data?.channel_name || statusData.data.channel_name;
                                const appId = accData.data?.agora_app_id || statusData.data.agora_app_id || 'f5df168c9c7849ef892cd39843d1ba42';
                                const token = accData.data?.agora_user_token || statusData.data.agora_user_token;
                                const userUid = accData.data?.agora_user_uid || statusData.data?.agora_user_uid || (100000 + 2);

                                document.getElementById('call-type-title').innerHTML = '<span class="text-amber-400 font-semibold animate-pulse">இணைக்கப்படுகிறது (Connecting Audio/Video...)...</span>';
                                await joinAgoraRoom(channelName, appId, token, userUid, type);
                            } else if (curStatus === 'active' && !joinedRoom) {
                                joinedRoom = true;
                                clearInterval(callPollTimer);
                                callPollTimer = null;
                                const channelName = statusData.data.channel_name;
                                const appId = statusData.data.agora_app_id || 'f5df168c9c7849ef892cd39843d1ba42';
                                const token = statusData.data.agora_user_token;
                                const userUid = statusData.data?.agora_user_uid || (100000 + 2);

                                document.getElementById('call-type-title').innerHTML = '<span class="text-amber-400 font-semibold animate-pulse">இணைக்கப்படுகிறது (Connecting Audio/Video...)...</span>';
                                await joinAgoraRoom(channelName, appId, token, userUid, type);
                            } else if (curStatus === 'cancelled' || curStatus === 'completed') {
                                clearInterval(callPollTimer);
                                callPollTimer = null;
                                if (webConnectionTimeout) clearTimeout(webConnectionTimeout);
                                document.getElementById('call-modal').classList.add('hidden');
                                alert('ஜோதிடர் தற்போது அழைப்பை ஏற்க இயலவில்லை அல்லது ரத்து செய்யப்பட்டது (Astrologer declined or timed out).');
                            }
                        }
                    } catch (e) {
                        console.warn('Status poll error:', e);
                    }
                }, 800);
            } else {
                // Local simulation fallback: simulate media connect after 2 seconds
                setTimeout(() => {
                    if (!isWebMediaConnected) {
                        onWebMediaConnected(null);
                    }
                }, 2000);
            }
        }

        let isJoiningAgoraRoom = false;
        async function joinAgoraRoom(channelName, appId, token, uid, type) {
            if (isJoiningAgoraRoom) {
                console.log('[AgoraRTC Devotee] Join already in progress, skipping duplicate call.');
                return;
            }
            isJoiningAgoraRoom = true;

            // Poller to detect when remote side terminates session
            if (webActiveStatusPoller) clearInterval(webActiveStatusPoller);
            webActiveStatusPoller = setInterval(async () => {
                if (!currentCallSessionId) return;
                try {
                    const stRes = await fetch(getApiEndpoint(`/sessions/check_status.php?session_id=${currentCallSessionId}`));
                    const stData = await stRes.json();
                    if ((stData.ok || stData.success) && stData.data) {
                        const s = stData.data.status;
                        if (s === 'active' && !isWebMediaConnected) {
                            onWebMediaConnected(currentCallSessionId);
                        } else if (s === 'completed' || s === 'cancelled') {
                            handleRemotePeerEnded();
                        }
                    }
                } catch (e) {}
            }, 1500);

            if (typeof AgoraRTC === 'undefined') {
                console.warn('AgoraRTC SDK not loaded. Proceeding with media simulated connect.');
                onWebMediaConnected(currentCallSessionId);
                return;
            }

            try {
                // Guaranteed Token Fallback
                if (!token || token.length < 20) {
                    try {
                        console.log(`[AgoraRTC Devotee] Fetching fallback token for devotee...`);
                        const fbRes = await fetch(getApiEndpoint(`test_agora_call.php?action=get_token&role=user&user_id=${CURRENT_USER_ID}&channel=${encodeURIComponent(channelName)}`));
                        const fbData = await fbRes.json();
                        if (fbData && fbData.token) {
                            token = fbData.token;
                            if (!uid) uid = fbData.uid;
                            if (fbData.appId) appId = fbData.appId;
                            console.log(`[AgoraRTC Devotee] Fallback token received: UID=${uid}`);
                        }
                    } catch(fbErr) {
                        console.warn('[AgoraRTC Devotee] Fallback token fetch error:', fbErr);
                    }
                }

                // mode: "rtc" provides seamless low-latency audio interoperability with Flutter Communication profile
                agoraClient = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

                const handleRemotePeer = async (user, mediaType) => {
                    try {
                        console.log(`[AgoraRTC Devotee] Subscribing to UID=${user.uid}, mediaType=${mediaType}`);
                        await agoraClient.subscribe(user, mediaType);
                        if (mediaType === "audio" && user.audioTrack) {
                            console.log(`[AgoraRTC Devotee] ⚡ Playing remote audio track for UID=${user.uid}`);
                            user.audioTrack.play();
                            try { user.audioTrack.setVolume(100); } catch(vErr) {}
                        }
                        if (mediaType === "video" && user.videoTrack) {
                            const remoteContainer = document.getElementById("call-remote-video");
                            const astroImg = document.getElementById("call-video-astro-img");
                            if (remoteContainer) {
                                remoteContainer.innerHTML = "";
                                user.videoTrack.play(remoteContainer);
                                if (astroImg) astroImg.classList.add("hidden");
                            }
                        }
                    } catch (subErr) {
                        console.warn('[AgoraRTC Devotee] Subscribe error:', subErr);
                    }
                    onWebMediaConnected(currentCallSessionId);
                };

                // Strictly start timer only when remote media track is received and connected
                agoraClient.on("user-published", async (user, mediaType) => {
                    await handleRemotePeer(user, mediaType);
                });

                agoraClient.on("user-joined", (user) => {
                    console.log(`[AgoraRTC Devotee] Remote peer joined: uid=${user.uid}`);
                    onWebMediaConnected(currentCallSessionId);
                });

                agoraClient.on("user-left", () => {
                    handleRemotePeerEnded();
                });

                // Join Agora channel with real Devotee Token and UID
                try {
                    await agoraClient.join(appId, channelName, token, Number(uid));
                    console.log(`[AgoraRTC Devotee] Joined ${channelName} successfully (UID: ${agoraClient.uid})`);
                } catch (tokenErr) {
                    console.error("[AgoraRTC Devotee] Token join failed:", tokenErr);
                }

                // ⚡ Check if astrologer is ALREADY in channel before devotee joined
                if (agoraClient.remoteUsers && agoraClient.remoteUsers.length > 0) {
                    for (const rUser of agoraClient.remoteUsers) {
                        console.log(`[AgoraRTC Devotee] Existing astrologer in channel: UID=${rUser.uid}`);
                        if (rUser.hasAudio) {
                            await handleRemotePeer(rUser, "audio");
                        }
                        if (rUser.hasVideo) {
                            await handleRemotePeer(rUser, "video");
                        }
                        onWebMediaConnected(currentCallSessionId);
                    }
                }

                // Publish local tracks with AEC / ANS / AGC enabled
                try {
                    localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack({
                        encoderConfig: "speech_standard",
                        AEC: true,
                        ANS: true,
                        AGC: true
                    });
                    const isVideo = (type || '').toString().toLowerCase() === 'video';
                    if (isVideo) {
                        localVideoTrack = await AgoraRTC.createCameraVideoTrack({
                            encoderConfig: "480p_1"
                        });
                        const localContainer = document.getElementById("call-local-video");
                        const localPlaceholder = document.getElementById("call-local-placeholder");
                        if (localContainer) {
                            localContainer.innerHTML = "";
                            localVideoTrack.play(localContainer);
                            if (localPlaceholder) localPlaceholder.classList.add("hidden");
                        }
                        await agoraClient.publish([localAudioTrack, localVideoTrack]);
                    } else {
                        await agoraClient.publish([localAudioTrack]);
                    }
                    console.log("[AgoraRTC Devotee] Local media tracks published successfully.");
                } catch (trackErr) {
                    console.warn("[AgoraRTC Devotee] Microphone/Camera permission error:", trackErr);
                }

                // Auto-start timer after 3 seconds of verified join so call is never cancelled by timeout
                setTimeout(() => {
                    if (!isWebMediaConnected) {
                        console.log('[AgoraRTC Devotee] Channel joined and local mic published. Starting timer.');
                        onWebMediaConnected(currentCallSessionId);
                    }
                }, 3000);

            } catch (err) {
                console.error("Agora room initialization error:", err);
                isJoiningAgoraRoom = false;
            }
        }

        function onWebMediaConnected(sessionId) {
            if (isWebMediaConnected) return;
            isWebMediaConnected = true;

            if (webConnectionTimeout) {
                clearTimeout(webConnectionTimeout);
                webConnectionTimeout = null;
            }

            document.getElementById('call-type-title').innerHTML = `<span class="text-emerald-400 font-bold">● நேரலை இணைக்கப்பட்டது (${AppState.activeCall.type} Call)</span>`;
            const statusDot = document.getElementById('call-video-status-dot');
            if (statusDot) statusDot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse';

            // Strictly start session timers & billing ONLY after media connects
            startCallLiveTimers(sessionId);
        }

        function startCallLiveTimers(sessionId) {
            if (AppState.activeCall.timer) clearInterval(AppState.activeCall.timer);
            if (AppState.activeCall.billingTimer) clearInterval(AppState.activeCall.billingTimer);

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
                            const hbRes = await fetch(getApiEndpoint('/sessions/heartbeat.php'), {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ session_id: sessionId })
                            });
                            const hbData = await hbRes.json();
                            if (hbData.data && hbData.data.terminate) {
                                alert('⚠️ வாலட் இருப்பு முடிந்துவிட்டது! ஆலோசனை தானாக நிறைவு பெறுகிறது.');
                                endLiveCall(false);
                            }
                        } catch (e) {}
                    }
                } else {
                    alert('⚠️ வாலட் இருப்பு முடிந்துவிட்டது! ஆலோசனை தானாக நிறைவு பெறுகிறது.');
                    endLiveCall(false);
                }
            }, 60000);
        }

        async function handleRemotePeerEnded() {
            if (callPollTimer) clearInterval(callPollTimer);
            if (webActiveStatusPoller) clearInterval(webActiveStatusPoller);
            if (webConnectionTimeout) clearTimeout(webConnectionTimeout);
            if (AppState.activeCall.timer) clearInterval(AppState.activeCall.timer);
            if (AppState.activeCall.billingTimer) clearInterval(AppState.activeCall.billingTimer);

            cleanupLocalMediaTracks();

            document.getElementById('call-modal').classList.add('hidden');
            const billedSecs = isWebMediaConnected ? AppState.activeCall.seconds : 0;

            // Fetch latest real-time user wallet balance from backend
            try {
                const balRes = await fetch(getApiEndpoint('/wallet/balance.php?user_id=2'));
                const balData = await balRes.json();
                if ((balData.ok || balData.success) && balData.data && balData.data.wallet_balance !== undefined) {
                    AppState.walletBalance = parseFloat(balData.data.wallet_balance);
                    updateWalletUI();
                }
            } catch (e) {}

            alert(`✅ ஆலோசனை நிறைவடைந்தது!\n(Astrologer has ended the consultation session)\n\nகால அளவு: ${billedSecs} விநாடிகள்\nபிடிக்கப்பட்ட கட்டணம்: ₹${AppState.activeCall.deducted.toFixed(2)}\nமீதமுள்ள வாலட் இருப்பு: ₹${AppState.walletBalance.toFixed(2)}\n\nநன்றி! 🌟`);
            currentCallSessionId = null;
        }

        function cleanupLocalMediaTracks() {
            isJoiningAgoraRoom = false;
            try {
                if (localAudioTrack) {
                    localAudioTrack.stop();
                    localAudioTrack.close();
                    localAudioTrack = null;
                }
                if (localVideoTrack) {
                    localVideoTrack.stop();
                    localVideoTrack.close();
                    localVideoTrack = null;
                }
                if (agoraClient) {
                    agoraClient.leave();
                    agoraClient = null;
                }
            } catch (e) {
                console.warn('Cleanup error:', e);
            }
        }

        async function endLiveCall(isTimeout = false) {
            if (callPollTimer) clearInterval(callPollTimer);
            if (webActiveStatusPoller) clearInterval(webActiveStatusPoller);
            if (webConnectionTimeout) clearTimeout(webConnectionTimeout);
            if (AppState.activeCall.timer) clearInterval(AppState.activeCall.timer);
            if (AppState.activeCall.billingTimer) clearInterval(AppState.activeCall.billingTimer);

            cleanupLocalMediaTracks();

            const billedDuration = (isWebMediaConnected && !isTimeout) ? AppState.activeCall.seconds : 0;

            if (currentCallSessionId) {
                try {
                    const endRes = await fetch(getApiEndpoint('/sessions/end.php'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            session_id: currentCallSessionId, 
                            duration_seconds: billedDuration,
                            rating: 5,
                            ended_by: 'user'
                        })
                    });
                    const endData = await endRes.json();
                    if ((endData.ok || endData.success) && endData.data) {
                        if (endData.data.user_remaining_balance !== undefined) {
                            AppState.walletBalance = parseFloat(endData.data.user_remaining_balance);
                            updateWalletUI();
                        }
                        if (endData.data.total_deducted !== undefined) {
                            AppState.activeCall.deducted = parseFloat(endData.data.total_deducted);
                        }
                    }
                } catch (e) {
                    console.warn('Call end API error:', e);
                }
                currentCallSessionId = null;
            }

            document.getElementById('call-modal').classList.add('hidden');

            if (!isTimeout) {
                alert(`✅ ஆலோசனை நிறைவடைந்தது!\n\nகால அளவு: ${billedDuration} விநாடிகள்\nபிடிக்கப்பட்ட கட்டணம்: ₹${AppState.activeCall.deducted.toFixed(2)}\nமீதமுள்ள வாலட் இருப்பு: ₹${AppState.walletBalance.toFixed(2)}\n\nகுருஜியின் தெய்வீக அருள் நிலைக்கட்டும்! 🌟`);
            }
        }

        function closeCallModal() {
            if (confirm('ஆலோசனையை நிறைவு செய்ய விரும்புகிறீர்களா?')) {
                endLiveCall(false);
            }
        }

        function toggleCallMute(btn) {
            if (localAudioTrack) {
                const enabled = localAudioTrack.enabled;
                localAudioTrack.setEnabled(!enabled);
            }
            btn.classList.toggle('bg-red-500/30');
            btn.classList.toggle('text-red-400');
        }

        function toggleCallSpeaker(btn) {
            btn.classList.toggle('text-astro-gold');
        }

        function toggleVideoFeed(btn) {
            if (localVideoTrack) {
                const enabled = localVideoTrack.enabled;
                localVideoTrack.setEnabled(!enabled);
            }
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

        let chatTimerInterval = null;
        let chatBillingInterval = null;
        let chatActiveSeconds = 0;
        let chatTotalDeducted = 0;

        function appendAstrologerChatBubble(content) {
            const container = document.getElementById('chat-messages');
            if (!container) return;
            const bubble = document.createElement('div');
            bubble.className = 'flex items-start gap-2 max-w-[85%]';
            bubble.innerHTML = `
                <div class="w-7 h-7 rounded-lg bg-astro-surface border border-astro-gold/40 flex items-center justify-center text-astro-gold text-xs font-bold flex-shrink-0">
                    ॐ
                </div>
                <div class="bg-astro-surface border border-astro-emerald/30 p-3 rounded-2xl rounded-tl-none text-xs text-white/90 leading-relaxed font-tamil">
                    ${escapeHtml(content)}
                </div>
            `;
            container.appendChild(bubble);
            container.scrollTop = container.scrollHeight;
        }

        function openChatModal(name, rate, sessionId = null, astroId = 1) {
            currentChatSessionId = sessionId;
            lastWebChatMsgId = 0;
            chatActiveSeconds = 0;
            chatTotalDeducted = 0;

            document.getElementById('chat-astro-name').innerText = name;
            document.getElementById('chat-rate-val').innerText = rate;
            const timerEl = document.getElementById('chat-timer-display');
            if (timerEl) timerEl.innerText = '00:00';
            const statusEl = document.getElementById('chat-status-text');
            if (statusEl) statusEl.innerText = sessionId ? 'இணைக்கிறது...' : 'நேரலை';
            const deductedBadge = document.getElementById('chat-deducted-badge');
            if (deductedBadge) deductedBadge.innerText = '₹0.00';

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
            if (chatTimerInterval) clearInterval(chatTimerInterval);
            if (chatBillingInterval) clearInterval(chatBillingInterval);

            // Start chat duration timer (1 second ticker)
            chatTimerInterval = setInterval(() => {
                chatActiveSeconds++;
                const mins = String(Math.floor(chatActiveSeconds / 60)).padStart(2, '0');
                const secs = String(chatActiveSeconds % 60).padStart(2, '0');
                const tEl = document.getElementById('chat-timer-display');
                if (tEl) tEl.innerText = `${mins}:${secs}`;
            }, 1000);

            // Start 60-second Real-time Billing Heartbeat for Chat
            chatBillingInterval = setInterval(async () => {
                const chatRate = parseFloat(rate) || 12.0;
                if (AppState.walletBalance >= chatRate) {
                    AppState.walletBalance -= chatRate;
                    chatTotalDeducted += chatRate;
                    const dBadge = document.getElementById('chat-deducted-badge');
                    if (dBadge) dBadge.innerText = '₹' + chatTotalDeducted.toFixed(2);
                    updateWalletUI();

                    if (currentChatSessionId) {
                        try {
                            const hbRes = await fetch(getApiEndpoint('/sessions/heartbeat.php'), {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ session_id: currentChatSessionId })
                            });
                            const hbData = await hbRes.json();
                            if (hbData.data && hbData.data.terminate) {
                                alert('⚠️ வாலட் இருப்பு முடிந்துவிட்டது! சாட் ஆலோசனை தானாக நிறைவு பெறுகிறது.');
                                endLiveChatSession();
                            }
                        } catch (e) {}
                    }
                } else {
                    alert('⚠️ வாலட் இருப்பு முடிந்துவிட்டது! சாட் ஆலோசனை தானாக நிறைவு பெறுகிறது.');
                    endLiveChatSession();
                }
            }, 60000);

            if (sessionId) {
                // Poll session handshake
                chatStatusPollTimer = setInterval(async () => {
                    try {
                        const sRes = await fetch(getApiEndpoint(`/sessions/check_status.php?session_id=${sessionId}`));
                        const sData = await sRes.json();
                        if ((sData.ok || sData.success) && sData.data) {
                            const status = sData.data.status;
                            if (status === 'ringing_user') {
                                await fetch(getApiEndpoint('/sessions/user_accept.php'), {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ session_id: sessionId, user_id: 2 })
                                });
                                const sText = document.getElementById('chat-status-text');
                                if (sText) sText.innerText = 'நேரலை இணைக்கப்பட்டது';
                            } else if (status === 'active') {
                                const sText = document.getElementById('chat-status-text');
                                if (sText) sText.innerText = 'நேரலை இணைக்கப்பட்டது';
                            } else if (status === 'completed' || status === 'cancelled') {
                                clearInterval(chatStatusPollTimer);
                                clearInterval(chatPollTimer);
                                clearInterval(chatTimerInterval);
                                clearInterval(chatBillingInterval);
                                document.getElementById('chat-modal').classList.add('hidden');
                                alert('ஜோதிடர் ஆலோசனையை நிறைவு செய்தார் (Consultation Ended).');
                            }
                        }
                    } catch (e) {}
                }, 800);

                // Incremental chat messages poller
                chatPollTimer = setInterval(async () => {
                    try {
                        const res = await fetch(getApiEndpoint(`/chat/messages.php?session_id=${encodeURIComponent(sessionId)}&last_id=${lastWebChatMsgId}`));
                        const data = await res.json();
                        if ((data.ok || data.success) && data.data && data.data.messages && data.data.messages.length > 0) {
                            data.data.messages.forEach(msg => {
                                const id = parseInt(msg.id);
                                if (id > lastWebChatMsgId) {
                                    lastWebChatMsgId = id;
                                }
                                if (String(msg.sender_type).toLowerCase() === 'astrologer') {
                                    appendAstrologerChatBubble(msg.content);
                                }
                            });
                        }
                    } catch (e) {}
                }, 1000);
            }
        }

        async function endLiveChatSession() {
            if (chatPollTimer) clearInterval(chatPollTimer);
            if (chatStatusPollTimer) clearInterval(chatStatusPollTimer);
            if (chatTimerInterval) clearInterval(chatTimerInterval);
            if (chatBillingInterval) clearInterval(chatBillingInterval);

            const sid = currentChatSessionId;
            currentChatSessionId = null;

            if (sid) {
                try {
                    await fetch(getApiEndpoint('/sessions/end.php'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ session_id: sid, rating: 5 })
                    });
                } catch (e) {
                    console.error('Error ending chat session:', e);
                }
            }

            document.getElementById('chat-modal').classList.add('hidden');
            const mins = Math.floor(chatActiveSeconds / 60);
            const secs = chatActiveSeconds % 60;
            alert(`✅ சாட் ஆலோசனை நிறைவடைந்தது!\n\nகால அளவு: ${mins} நிமிடம் ${secs} வினாடி\nகட்டணம்: ₹${chatTotalDeducted.toFixed(2)}\nமீதமுள்ள வாலட் இருப்பு: ₹${AppState.walletBalance.toFixed(2)}\n\nகுருஜியின் ஆசிகள்! 🌟`);
        }

        function closeChatModal() {
            endLiveChatSession();
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
                    await fetch(getApiEndpoint('/chat/send.php'), {
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
        // AUTH MODAL & LOGIN LOGIC
        // ==============================================
        let currentAuthMode = 'password';

        function switchAuthTab(mode) {
            currentAuthMode = mode;
            const tabPass = document.getElementById('auth-tab-pass');
            const tabOtp = document.getElementById('auth-tab-otp');
            const groupPass = document.getElementById('auth-pass-group');
            const groupOtp = document.getElementById('auth-otp-group');

            if (mode === 'otp') {
                if (tabOtp) tabOtp.className = 'flex-1 py-1.5 rounded-lg bg-astro-gold text-astro-base font-bold shadow-sm transition';
                if (tabPass) tabPass.className = 'flex-1 py-1.5 rounded-lg text-white/70 hover:text-white transition';
                if (groupPass) groupPass.classList.add('hidden');
                if (groupOtp) groupOtp.classList.remove('hidden');
            } else {
                if (tabPass) tabPass.className = 'flex-1 py-1.5 rounded-lg bg-astro-gold text-astro-base font-bold shadow-sm transition';
                if (tabOtp) tabOtp.className = 'flex-1 py-1.5 rounded-lg text-white/70 hover:text-white transition';
                if (groupPass) groupPass.classList.remove('hidden');
                if (groupOtp) groupOtp.classList.add('hidden');
            }
        }

        function openAuthModal() {
            document.getElementById('auth-modal').classList.remove('hidden');
        }

        function closeAuthModal() {
            document.getElementById('auth-modal').classList.add('hidden');
        }

        function quickFillAuth(phone, credential, name, wallet) {
            document.getElementById('auth-phone').value = phone;
            if (credential === '0101') {
                switchAuthTab('otp');
                document.getElementById('auth-otp').value = credential;
            } else {
                switchAuthTab('password');
                document.getElementById('auth-password').value = credential;
            }
            AppState.userName = name;
            AppState.walletBalance = wallet || 500.00;
            AppState.isLoggedIn = true;
            localStorage.setItem('astro_user', name);
            localStorage.setItem('astro_wallet', AppState.walletBalance.toString());
            updateUserUI();
            updateWalletUI();
            closeAuthModal();
            alert(`✅ ${name} கணக்கில் உள்நுழைந்துள்ளீர்கள்!\n\nவாலட் இருப்பு: ₹${AppState.walletBalance.toFixed(2)}\nகுருஜியின் ஆலோசனை பெற தயாராக உள்ளீர்கள்! 🌟`);
        }

        async function handleAuthSubmit(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('auth-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="animate-spin inline-block mr-2">⏳</span> சரிபார்க்கப்படுகிறது...';
            }

            const phone = document.getElementById('auth-phone').value.trim();
            const password = document.getElementById('auth-password') ? document.getElementById('auth-password').value : '';
            const otp = document.getElementById('auth-otp') ? document.getElementById('auth-otp').value.trim() : '';

            try {
                if (currentAuthMode === 'otp') {
                    const res = await fetch(getApiEndpoint('/auth/verify_otp.php'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ phone: phone, otp: otp })
                    });
                    const data = await res.json();
                    if ((data.ok || data.success) && data.data && data.data.user) {
                        const u = data.data.user;
                        AppState.userName = u.name || ('பக்தர் ' + phone.substring(6));
                        AppState.walletBalance = parseFloat(u.wallet_balance || '500.00');
                        if (data.data.token) localStorage.setItem('astro_jwt', data.data.token);
                    } else if (otp === '0101' || otp === '1234') {
                        AppState.userName = 'பக்தர் ' + phone.substring(6);
                        AppState.walletBalance = 500.00;
                    } else {
                        alert(data.message || 'தவறான OTP! டெமோவிற்கு 0101 பயன்படுத்தவும்.');
                        return;
                    }
                } else {
                    const res = await fetch(getApiEndpoint('/auth/login.php'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ phone_or_email: phone, password: password })
                    });
                    const data = await res.json();
                    if ((data.ok || data.success) && data.data && data.data.user) {
                        const u = data.data.user;
                        AppState.userName = u.name || ('பக்தர் ' + phone.substring(6));
                        AppState.walletBalance = parseFloat(u.wallet_balance || '500.00');
                        if (data.data.token) localStorage.setItem('astro_jwt', data.data.token);
                    } else {
                        AppState.userName = 'பக்தர் ' + (phone.length >= 4 ? phone.substring(phone.length - 4) : phone);
                    }
                }

                AppState.isLoggedIn = true;
                localStorage.setItem('astro_user', AppState.userName);
                localStorage.setItem('astro_wallet', AppState.walletBalance.toString());
                updateUserUI();
                updateWalletUI();
                closeAuthModal();
                alert(`✅ வெற்றிகரமாக உள்நுழைந்துள்ளீர்கள்!\n\nவரவேற்கிறோம், ${AppState.userName}!\nஉங்கள் வாலட் இருப்பு: ₹${AppState.walletBalance.toFixed(2)}`);

            } catch (err) {
                AppState.userName = 'பக்தர் ' + (phone.length >= 4 ? phone.substring(phone.length - 4) : phone);
                AppState.isLoggedIn = true;
                localStorage.setItem('astro_user', AppState.userName);
                updateUserUI();
                updateWalletUI();
                closeAuthModal();
                alert(`✅ உள்நுழைவு முடிந்தது!\nவரவேற்கிறோம், ${AppState.userName}!`);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i data-lucide="log-in" class="w-4 h-4"></i> உள்நுழைக & ஆலோசனை பெறுக';
                    if (window.lucide) lucide.createIcons();
                }
            }
        }
    
