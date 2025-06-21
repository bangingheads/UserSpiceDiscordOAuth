<?php
require_once '../../../../users/init.php';

$clientId=$settings->discclientid;
$secret=$settings->discclientsecret;
$callback=$settings->disccallback;

require __DIR__ . '/vendor/autoload.php';
use RestCord\DiscordClient;

$provider = new \Wohali\OAuth2\Client\Provider\Discord([
  'clientId' => $clientId,
  'clientSecret' => $secret,
  'redirectUri' => $callback
]);

if (empty(Input::get('code'))) {
    Redirect::to($us_url_root.'users/login.php');
    die();
}

if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['discordstate'])) {
    unset($_SESSION['discordstate']);
    Redirect::to($us_url_root.'users/login.php');
    die();
}
try {
    $accessToken = $provider->getAccessToken('authorization_code', [
    'code' => Input::get('code')
]);
    $resourceOwner = $provider->getResourceOwner($accessToken);
    $oauthUser = $resourceOwner->toArray();
    $discUsername = $oauthUser['username'];
    $discId = $oauthUser['id'];
} catch (Exception $e) {
    unset($_SESSION['discordstate']);
    Redirect::to($us_url_root.'users/login.php');
    die();
}
if($settings->discserverreq) {
	$discord = new DiscordClient(['token' => $accessToken->getToken(), 'tokenType' => 'OAuth']); // Token is required
	$guilds = $discord->user->getCurrentUserGuilds();
	$inGuilds = array();
	foreach($guilds as $guild) {
		array_push($inGuilds, $guild->id);
	}
	if (!in_array($settings->discserverid, $inGuilds)) {
		Redirect::to($us_url_root.'users/login.php');
        die();
	}
}
if ($oauthUser['verified']) {
    $discEmail = $oauthUser['email'];
    $checkExistingQ = $db->query("SELECT * FROM users WHERE email = ?", array($discEmail));
    $CEQCount = $checkExistingQ->count();
} else {
    $discEmail = ""; //User does not have a verified discord email address
    $CEQCount = 0;
}
$fields = [
    "disc_uid" => $discId,
    "disc_uname" => $discUsername,
];
if (!empty($discEmail)) $fields["email"] = $discEmail;

socialLogin($discEmail, $discUsername, ["disc_uid"=>$discId], $fields, ', "discord");