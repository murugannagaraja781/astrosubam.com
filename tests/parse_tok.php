<?php
require __DIR__ . '/../helpers/AgoraToken.php';
// AccessToken2 is included via AgoraToken.php

$uToken = '007eJxTYEhMblm7fU1j6zVxJXvLvhsBjceXh61Ji3wdpvN/y3JvkzkKDGmmKWmGZhbJlsnmFiaWqWkWlkbJKcaWFibGKYZJiSZG271/ZAnwMTBoVh5kYARCFiAG8ZnAJDOYZAGTsgyJxSVF+cWlSYm58Y7B8YbmliZmpuZGBkbxhoaWZmwMhgZAYAQAANInzA==';
$aToken = '007eJxTYFi06WvvvG1quTI6beJdE5OCThxROXXmU5/U9SKhtS83FHkoMKSZpqQZmlkkWyabW5hYpqZZWBolpxhbWpgYpxgmJZoYbff+kSXAx8Bw79xvJkYGRgYWIAbxmcAkM5hkAZOyDInFJUX5xaVJibnxjsHxhuaWJmam5kYGRvGGhpZmbAxGBkBgCACgFimR';

$aUser = new AccessToken2();
$aUser->parse($uToken);
$sUser = $aUser->getServices(ServiceRtc::SERVICE_TYPE)[0];

$aAstro = new AccessToken2();
$aAstro->parse($aToken);
$sAstro = $aAstro->getServices(ServiceRtc::SERVICE_TYPE)[0];

echo "User: UID={$sUser->uid} CH={$sUser->channelName}\n";
echo "Astro: UID={$sAstro->uid} CH={$sAstro->channelName}\n";
