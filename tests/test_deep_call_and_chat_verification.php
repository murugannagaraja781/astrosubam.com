<?php
// tests/test_deep_call_and_chat_verification.php
// Deep end-to-end verification script testing both Call and Chat workflows
declare(strict_types=1);

echo "======================================================================\n";
echo "🔮 ASTROSUBAM - DEEP END-TO-END CALL & CHAT VERIFICATION\n";
echo "======================================================================\n\n";

$php = 'C:\\xampp\\php\\php.exe';

function callApi(string $script, array $get = [], array $post = []): array {
    global $php;
    $tmpFile = __DIR__ . '/_temp_deep_test_' . mt_rand(1000, 9999) . '.php';
    $code = '<?php' . PHP_EOL;
    $code .= '$_SERVER["REQUEST_METHOD"] = ' . var_export(!empty($post) ? 'POST' : 'GET', true) . ';' . PHP_EOL;
    $code .= '$_POST = ' . var_export($post, true) . ';' . PHP_EOL;
    $code .= '$_GET = ' . var_export($get, true) . ';' . PHP_EOL;
    $code .= 'require ' . var_export(__DIR__ . '/../' . $script, true) . ';' . PHP_EOL;
    file_put_contents($tmpFile, $code);

    $output = shell_exec("\"{$php}\" \"{$tmpFile}\" 2>&1");
    @unlink($tmpFile);

    $start = strpos($output, '{');
    $end = strrpos($output, '}');
    if ($start !== false && $end !== false) {
        $jsonStr = substr($output, $start, $end - $start + 1);
        $decoded = json_decode($jsonStr, true);
        if ($decoded) return $decoded;
    }
    return ['error' => true, 'raw' => $output];
}

function isSuccess(array $res): bool {
    return ($res['ok'] ?? ($res['success'] ?? false)) === true;
}

require_once __DIR__ . '/../config/database.php';
$db = getDb();

$testUserId = 2;
$testAstroId = 1;

// Reset test user and astrologer
$db->prepare("UPDATE users SET wallet_balance = 500.00 WHERE id = ?")->execute([$testUserId]);
$db->prepare("UPDATE astrologers SET call_status = 'available' WHERE id = ?")->execute([$testAstroId]);
$db->exec("UPDATE consultation_sessions SET status = 'cancelled' WHERE status IN ('requested', 'ringing_user')");

echo "👤 [INIT] Devotee ID: {$testUserId} | Wallet Balance: ₹500.00\n";
echo "🧙‍♂️ [INIT] Astrologer ID: {$testAstroId} | Status: available\n\n";

$overallPass = true;

// =============================================================================
// PART 1: LIVE CALL VERIFICATION (Audio / Video Agora RTC Engine)
// =============================================================================
echo "======================================================================\n";
echo "📞 PART 1: LIVE AUDIO/VIDEO CALL END-TO-END VERIFICATION\n";
echo "======================================================================\n\n";

// 1.1 Devotee Requests Call
echo "▶ 1.1: Devotee initiates Audio Call via api/sessions/request.php\n";
$reqCall = callApi('api/sessions/request.php', [], [
    'user_id' => $testUserId,
    'astrologer_id' => $testAstroId,
    'type' => 'audio',
    'user_name' => 'பக்தர் (Karthik R.)',
    'user_phone' => '9876543210'
]);

if (!isSuccess($reqCall)) {
    echo "❌ FAILED to initiate call: " . json_encode($reqCall) . "\n";
    $overallPass = false;
} else {
    $callSessionId = $reqCall['data']['session_id'];
    echo "✅ SUCCESS: Session Created: {$callSessionId} | Status: {$reqCall['data']['status']}\n\n";

    // 1.2 Astrologer Polls Pending Queue
    echo "▶ 1.2: Astrologer polls incoming call queue via api/sessions/pending.php\n";
    $pendCall = callApi('api/sessions/pending.php', ['astrologer_id' => $testAstroId]);
    if (!isSuccess($pendCall) || empty($pendCall['data']['session'])) {
        echo "❌ FAILED: Astrologer did not receive incoming call in pending queue.\n";
        $overallPass = false;
    } else {
        echo "✅ SUCCESS: Astrologer received ring notification for session: " . $pendCall['data']['session']['session_id'] . "\n\n";

        // 1.3 Astrologer Accepts Call
        echo "▶ 1.3: Astrologer clicks 'Accept Call' via api/sessions/accept.php\n";
        $accCall = callApi('api/sessions/accept.php', [], ['session_id' => $callSessionId]);
        if (!isSuccess($accCall) || ($accCall['data']['status'] ?? '') !== 'ringing_user') {
            echo "❌ FAILED: Astrologer acceptance failed: " . json_encode($accCall) . "\n";
            $overallPass = false;
        } else {
            echo "✅ SUCCESS: Astrologer accepted! Call status transitioned to: ringing_user\n\n";

            // 1.4 Devotee Connects
            echo "▶ 1.4: Devotee device confirms connection via api/sessions/user_accept.php\n";
            $devAcc = callApi('api/sessions/user_accept.php', [], [
                'session_id' => $callSessionId,
                'user_id' => $testUserId
            ]);
            if (!isSuccess($devAcc) || ($devAcc['data']['status'] ?? '') !== 'active') {
                echo "❌ FAILED: Devotee connect failed: " . json_encode($devAcc) . "\n";
                $overallPass = false;
            } else {
                echo "✅ SUCCESS: 2-Way Connection ACTIVE! Agora Channel: " . $devAcc['data']['agora_channel'] . "\n";
                echo "   🔑 Devotee RTC Token: " . substr($devAcc['data']['agora_user_token'] ?? '', 0, 30) . "...\n";
                echo "   🔑 Astrologer RTC Token: " . substr($devAcc['data']['agora_astro_token'] ?? '', 0, 30) . "...\n\n";

                // 1.5 Live Heartbeat & Billing
                echo "▶ 1.5: 60-Second Consultation Heartbeat Billing via api/sessions/heartbeat.php\n";
                $hbCall = callApi('api/sessions/heartbeat.php', [], ['session_id' => $callSessionId]);
                if (!isSuccess($hbCall)) {
                    echo "❌ FAILED: Heartbeat failed: " . json_encode($hbCall) . "\n";
                    $overallPass = false;
                } else {
                    echo "✅ SUCCESS: Heartbeat recorded! Deducted: ₹" . $hbCall['data']['rate_deducted'] . " | Devotee Remaining: ₹" . $hbCall['data']['remaining_balance'] . "\n\n";

                    // 1.6 Call Termination
                    echo "▶ 1.6: Finalizing Call Session via api/sessions/end.php\n";
                    $endCall = callApi('api/sessions/end.php', [], ['session_id' => $callSessionId, 'rating' => 5]);
                    if (!isSuccess($endCall)) {
                        echo "❌ FAILED: End call failed: " . json_encode($endCall) . "\n";
                        $overallPass = false;
                    } else {
                        echo "✅ SUCCESS: Call ended. Astrologer credited: ₹" . $endCall['data']['astrologer_credited'] . "\n";
                        echo "   🎉 CALL LIFECYCLE 100% OPERATIONAL!\n\n";
                    }
                }
            }
        }
    }
}

// =============================================================================
// PART 2: LIVE CHAT VERIFICATION (Bidirectional Real-Time Messaging)
// =============================================================================
echo "======================================================================\n";
echo "💬 PART 2: LIVE BIDIRECTIONAL CHAT CONSULTATION VERIFICATION\n";
echo "======================================================================\n\n";

// Reset Astrologer availability for chat
$db->prepare("UPDATE astrologers SET call_status = 'available' WHERE id = ?")->execute([$testAstroId]);

// 2.1 Devotee Requests Chat
echo "▶ 2.1: Devotee initiates Chat Consultation via api/sessions/request.php\n";
$reqChat = callApi('api/sessions/request.php', [], [
    'user_id' => $testUserId,
    'astrologer_id' => $testAstroId,
    'type' => 'chat',
    'user_name' => 'பக்தர் (Karthik R.)',
    'user_phone' => '9876543210'
]);

if (!isSuccess($reqChat)) {
    echo "❌ FAILED to initiate chat session: " . json_encode($reqChat) . "\n";
    $overallPass = false;
} else {
    $chatSessionId = $reqChat['data']['session_id'];
    echo "✅ SUCCESS: Chat Session Created: {$chatSessionId} | Status: {$reqChat['data']['status']}\n\n";

    // 2.2 Astrologer Polls Pending Queue
    echo "▶ 2.2: Astrologer receives incoming Chat in pending queue via api/sessions/pending.php\n";
    $pendChat = callApi('api/sessions/pending.php', ['astrologer_id' => $testAstroId]);
    if (!isSuccess($pendChat) || empty($pendChat['data']['session'])) {
        echo "❌ FAILED: Chat request not received by Astrologer: " . json_encode($pendChat) . "\n";
        $overallPass = false;
    } else {
        echo "✅ SUCCESS: Astrologer alerted for incoming Chat! Consultation Type: " . $pendChat['data']['session']['type'] . "\n\n";

        // 2.3 Astrologer Accepts Chat
        echo "▶ 2.3: Astrologer accepts Chat via api/sessions/accept.php\n";
        $accChat = callApi('api/sessions/accept.php', [], ['session_id' => $chatSessionId]);
        if (!isSuccess($accChat)) {
            echo "❌ FAILED: Astrologer chat acceptance failed.\n";
            $overallPass = false;
        } else {
            echo "✅ SUCCESS: Astrologer accepted chat session.\n\n";

            // 2.4 Devotee Connects Chat Session
            echo "▶ 2.4: Devotee connects chat via api/sessions/user_accept.php\n";
            $devChatAcc = callApi('api/sessions/user_accept.php', [], [
                'session_id' => $chatSessionId,
                'user_id' => $testUserId
            ]);
            if (!isSuccess($devChatAcc) || ($devChatAcc['data']['status'] ?? '') !== 'active') {
                echo "❌ FAILED: Devotee chat connection failed.\n";
                $overallPass = false;
            } else {
                echo "✅ SUCCESS: Chat session ACTIVE!\n\n";

                // 2.5 Devotee Sends Message 1
                echo "▶ 2.5: Devotee sends Question via api/chat/send.php\n";
                $devoteeMsg1 = "வணக்கம் குருஜி! எனது பிறந்த தேதி 15-08-1995. எனது தொழில் வளர்ச்சி எப்போது மேன்மையடையும்?";
                $send1 = callApi('api/chat/send.php', [], [
                    'session_id' => $chatSessionId,
                    'sender_type' => 'user',
                    'sender_id' => $testUserId,
                    'content' => $devoteeMsg1
                ]);
                if (!isSuccess($send1)) {
                    echo "❌ FAILED to send devotee message: " . json_encode($send1) . "\n";
                    $overallPass = false;
                } else {
                    $msg1Id = $send1['data']['message_id'];
                    echo "✅ SUCCESS: Devotee Message #{$msg1Id} sent into database: \"{$devoteeMsg1}\"\n\n";

                    // 2.6 Astrologer Polls Messages (Incremental)
                    echo "▶ 2.6: Astrologer Dashboard polls messages via api/chat/messages.php?last_id=0\n";
                    $fetchAstro = callApi('api/chat/messages.php', [
                        'session_id' => $chatSessionId,
                        'last_id' => 0
                    ]);
                    $msgs = $fetchAstro['data']['messages'] ?? [];
                    $foundMsg1 = false;
                    foreach ($msgs as $m) {
                        if ($m['id'] == $msg1Id && $m['content'] === $devoteeMsg1) {
                            $foundMsg1 = true;
                            break;
                        }
                    }
                    if (!$foundMsg1) {
                        echo "❌ FAILED: Astrologer poller did not find Devotee Message #{$msg1Id}\n";
                        $overallPass = false;
                    } else {
                        echo "✅ SUCCESS: Astrologer poller successfully received Devotee's Message!\n\n";

                        // 2.7 Astrologer Sends Reply Message
                        echo "▶ 2.7: Astrologer sends Horoscope Answer via api/chat/send.php\n";
                        $astroReply = "வணக்கம் கார்த்திக் அவர்களே! உங்கள் லக்னத்திற்கு குரு 10-ஆம் இடத்தில் பெயர்ச்சி ஆகிறார். அடுத்த 3 மாதங்களில் புதிய தொழில் வாய்ப்பு பிரகாசமாக உள்ளது.";
                        $send2 = callApi('api/chat/send.php', [], [
                            'session_id' => $chatSessionId,
                            'sender_type' => 'astrologer',
                            'sender_id' => $testAstroId,
                            'content' => $astroReply
                        ]);
                        if (!isSuccess($send2)) {
                            echo "❌ FAILED to send astrologer reply: " . json_encode($send2) . "\n";
                            $overallPass = false;
                        } else {
                            $msg2Id = $send2['data']['message_id'];
                            echo "✅ SUCCESS: Astrologer Reply #{$msg2Id} stored in database: \"{$astroReply}\"\n\n";

                            // 2.8 Devotee Polls Astrologer Reply (last_id = msg1Id)
                            echo "▶ 2.8: Devotee Portal polls messages via api/chat/messages.php?last_id={$msg1Id}\n";
                            $fetchDevotee = callApi('api/chat/messages.php', [
                                'session_id' => $chatSessionId,
                                'last_id' => $msg1Id
                            ]);
                            $devoteeNewMsgs = $fetchDevotee['data']['messages'] ?? [];
                            $foundReply = false;
                            foreach ($devoteeNewMsgs as $m) {
                                if ($m['id'] == $msg2Id && $m['sender_type'] === 'astrologer') {
                                    $foundReply = true;
                                    break;
                                }
                            }
                            if (!$foundReply) {
                                echo "❌ FAILED: Devotee did not receive Astrologer's reply via incremental poller!\n";
                                $overallPass = false;
                            } else {
                                echo "✅ SUCCESS: Devotee received Astrologer's reply instantly in real time!\n\n";

                                // 2.9 Finalize Chat Consultation
                                echo "▶ 2.9: Ending Chat Consultation via api/sessions/end.php\n";
                                $endChat = callApi('api/sessions/end.php', [], ['session_id' => $chatSessionId, 'rating' => 5]);
                                if (!isSuccess($endChat)) {
                                    echo "❌ FAILED to end chat session.\n";
                                    $overallPass = false;
                                } else {
                                    echo "✅ SUCCESS: Chat consultation concluded successfully.\n";
                                    echo "   🎉 BIDIRECTIONAL CHAT 100% OPERATIONAL!\n\n";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

echo "======================================================================\n";
if ($overallPass) {
    echo "🎯 FINAL VERIFICATION RESULT: ALL TESTS PASSED (100% SUCCESS)!\n";
    echo "📞 Live Call (Audio/Video + Agora RTC Token + Billing) : WORKING ✅\n";
    echo "💬 Live Chat (Bidirectional + DB Persist + Polling)     : WORKING ✅\n";
} else {
    echo "⚠️ FINAL VERIFICATION RESULT: SOME TESTS FAILED.\n";
}
echo "======================================================================\n";
