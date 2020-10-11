<?php
require_once '../../../../users/init.php';

$db=DB::getInstance();

$settingsQ=$db->query("SELECT * FROM settings");
$settings=$settingsQ->first();

if (!isset($_SESSION)) {
    session_start();
}

$clientId=$settings->discclientid;
$secret=$settings->discclientsecret;
$callback=$settings->disccallback;
$whereNext=$settings->finalredir;

require __DIR__ . '/vendor/autoload.php';
use RestCord\DiscordClient;

$provider = new \Wohali\OAuth2\Client\Provider\Discord([
  'clientId' => $clientId,
  'clientSecret' => $secret,
  'redirectUri' => $callback
]);



if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['discordstate'])) {
    unset($_SESSION['discordstate']);
    exit('Invalid state');
}
try {
    $accessToken = $provider->getAccessToken('authorization_code', [
    'code' => Input::get('code')
]);
    $resourceOwner = $provider->getResourceOwner($accessToken);
    $oauthUser = $resourceOwner->toArray();
    $discUsername = $oauthUser['username'];
    $discDiscriminator = $oauthUser['discriminator'];
    $discId = $oauthUser['id'];
} catch (Exception $e) {
    unset($_SESSION['discordstate']);
    exit($e->getMessage());
}
if($settings->discserverreq) {
	$discord = new DiscordClient(['token' => $accessToken->getToken(), 'tokenType' => 'OAuth']); // Token is required
	$guilds = $discord->user->getCurrentUserGuilds();
	$inGuilds = array();
	foreach($guilds as $guild) {
		array_push($inGuilds, $guild->id);
	}
	if (!in_array($settings->discserverid, $inGuilds)) {
		Redirect::To($us_url_root.'users/login.php');
	}
}
if ($oauthUser['verified']) {
    $discEmail = $oauthUser['email'];
    $checkExistingQ = $db->query("SELECT * FROM users WHERE email = ?", array($discEmail));
    $CEQCount = $checkExistingQ->count();
    $verified = 1;
} else {
    $discEmail = ""; //User does not have a verified discord email address
    $CEQCount = 0;
    $verified = 0;
}

//Existing UserSpice User Found
if ($CEQCount>0) {
    $checkExisting = $checkExistingQ->first();
    $newLoginCount = $checkExisting->logins+1;
    $newLastLogin = date("Y-m-d H:i:s");

    $fields=array('disc_uid'=>$discId, 'disc_uname'=>$discUsername, 'disc_discriminator'=>$discDiscriminator, 'logins'=>$newLoginCount, 'last_login'=>$newLastLogin);

    $db->update('users', $checkExisting->id, $fields);
    $sessionName = Config::get('session/session_name');
    Session::put($sessionName, $checkExisting->id);

    $hooks = getMyHooks(['page'=>'loginSuccess']);
    includeHook($hooks,'body');
    $ip = ipCheck();
    $q = $db->query("SELECT id FROM us_ip_list WHERE ip = ?", array($ip));
    $c = $q->count();
    if ($c < 1) {
        $db->insert('us_ip_list', array(
      'user_id' => $checkExisting->id,
      'ip' => $ip,
    ));
    } else {
        $f = $q->first();
        $db->update('us_ip_list', $f->id, array(
      'user_id' => $checkExisting->id,
      'ip' => $ip,
    ));
    }
    include($abs_us_root.$us_url_root.'usersc/includes/oauth_success_redirect.php');
    Redirect::to($whereNext);
} else {
    if ($settings->registration==0) {
        session_destroy();
        Redirect::to($us_url_root.'users/join.php');
        die();
    } else {
        // //No Existing UserSpice User Found
        $date = date("Y-m-d H:i:s");

        $preQCount = $db->query("SELECT username FROM users WHERE username = ?", array($discUsername))->count();
        if ($preQCount == 0) {
            $username = $discUsername;
        } else {
            $username = $discUsername.$discDiscriminator;
        }
    
        $fields=array('email'=>$discEmail, 'username'=>$username, 'fname'=>$discUsername, 'lname'=>$discDiscriminator, 'permissions'=>1,'logins'=>1,'company'=>'none','join_date'=>$date,'last_login'=>$date,'email_verified'=>$verified,'password'=>null,'disc_uid'=>$discId,'disc_uname'=>$discUsername,'disc_discriminator'=>$discDiscriminator);

        $db->insert('users', $fields);
        $lastID = $db->lastId();

        $insert2 = $db->query("INSERT INTO user_permission_matches SET user_id = $lastID, permission_id = 1");

        $theNewId=$lastID;
        include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');

        $sessionName = Config::get('session/session_name');
        Session::put($sessionName, $lastID);
        include($abs_us_root.$us_url_root.'usersc/includes/oauth_success_redirect.php');
        Redirect::to($whereNext);
    }
}
