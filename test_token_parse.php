<?php
require_once __DIR__ . '/helpers/AgoraToken.php';
require_once __DIR__ . '/helpers/AccessToken2.php';

$userTok = AgoraToken::generateUserToken('test_channel_123', 2);
$astroTok = AgoraToken::generateAstroToken('test_channel_123', 2);

$aUser = new AccessToken2();
$aUser->parse($userTok);
$sUser = $aUser->getServices(ServiceRtc::SERVICE_TYPE)[0];

$aAstro = new AccessToken2();
$aAstro->parse($astroTok);
$sAstro = $aAstro->getServices(ServiceRtc::SERVICE_TYPE)[0];

echo "User Token UID: [" . $sUser->uid . "] Expected: [100002] Channel: [" . $sUser->channelName . "]\n";
echo "Astro Token UID: [" . $sAstro->uid . "] Expected: [200002] Channel: [" . $sAstro->channelName . "]\n";
echo "User Privileges: " . json_encode($sUser->privileges) . "\n";
echo "Astro Privileges: " . json_encode($sAstro->privileges) . "\n";
