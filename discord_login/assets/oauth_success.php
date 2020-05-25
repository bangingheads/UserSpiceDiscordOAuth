<?php
require_once '../../../../users/init.php';

$db=DB::getInstance();

$settingsQ=$db->query("SELECT * FROM settings");
$settings=$settingsQ->first();

if(!isset($_SESSION)){session_start();}

$clientId=$settings->twclientid;
$secret=$settings->twclientsecret;
$callback=$settings->twcallback;
$whereNext=$settings->finalredir;

require_once($abs_us_root.$us_url_root."usersc/plugins/twitch_login/assets/twitch.php");
$provider = new TwitchProvider([
    'clientId'                => $clientId,     // The client ID assigned when you created your application
    'clientSecret'            => $secret, // The client secret assigned when you created your application
    'redirectUri'             => $callback,  // Your redirect URL you specified when you created your application
    'scopes'                  => ['user:read:email']  // The scopes you would like to request
]);

if (empty(Input::get('state')) || (isset($_SESSION['oauth2state']) && Input::get('state') !== $_SESSION['oauth2state'])) {
    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }
    exit('Invalid state');
}
try {
$accessToken = $provider->getAccessToken('authorization_code', [
	'code' => Input::get('code')
]);
$resourceOwner = $provider->getResourceOwner($accessToken);
$twuser = $resourceOwner->toArray();
$twUsername = $twuser['data'][0]['login'];
$twId = $twuser['data'][0]['id'];

}catch (Exception $e) {
	unset($_SESSION['oauth2state']);
	exit($e->getMessage());
}

if($twuser['data'][0]['email'] !== NULL) {
$twEmail = $twuser['data'][0]['email'];
$checkExistingQ = $db->query("SELECT * FROM users WHERE email = ?",array ($twEmail));

$CEQCount = $checkExistingQ->count();
}else {
	$twEmail = ""; //User does not have a verified twitch email address
	$CEQCount = 0;
}

//Existing UserSpice User Found
if ($CEQCount>0){
$checkExisting = $checkExistingQ->first();
$newLoginCount = $checkExisting->logins+1;
$newLastLogin = date("Y-m-d H:i:s");

$fields=array('tw_uid'=>$twId, 'tw_uname'=>$twUsername, 'logins'=>$newLoginCount, 'last_login'=>$newLastLogin);

$db->update('users',$checkExisting->id,$fields);
$sessionName = Config::get('session/session_name');
Session::put($sessionName, $checkExisting->id);

$twoQ = $db->query("select twoKey from users where id = ? and twoEnabled = 1",[$checkExisting->id]);
if($twoQ->count()>0) {
  $_SESSION['twofa']=1;
    $page=encodeURIComponent(Input::get('redirect'));
    logger($user->data()->id,"Two FA","Two FA being requested.");
    Redirect::To($us_url_root.'users/twofa.php');
  }
  $ip = ipCheck();
  $q = $db->query("SELECT id FROM us_ip_list WHERE ip = ?",array($ip));
  $c = $q->count();
  if($c < 1){
    $db->insert('us_ip_list', array(
      'user_id' => $checkExisting->id,
      'ip' => $ip,
    ));
  }else{
    $f = $q->first();
    $db->update('us_ip_list',$f->id, array(
      'user_id' => $checkExisting->id,
      'ip' => $ip,
    ));
  }

Redirect::to($whereNext);
}else{
  if($settings->registration==0) {
    session_destroy();
    Redirect::to($us_url_root.'users/join.php');
    die();
  } else {
    // //No Existing UserSpice User Found
    $date = date("Y-m-d H:i:s");

	$preQCount = $db->query("SELECT username FROM users WHERE username = ?",array($twUsername))->count();
	if($preQCount == 0) {
		$username = $twUsername;
	}else {
		for($i=0;$i<999;$i++) {
			$preQCount = $db->query("SELECT username FROM users WHERE username = ?",array($twUsername.$i))->count();
			if($preQCount == 0) {
				$username = $twUsername.$i;
				break;
			}
		}
	}
	
    $fields=array('email'=>$twEmail,'username'=>$username,'permissions'=>1,'logins'=>1,'company'=>'none','join_date'=>$date,'last_login'=>$date,'email_verified'=>1,'password'=>NULL,'tw_uid'=>$twId,'tw_uname'=>$twUsername);

    $db->insert('users',$fields);
    $lastID = $db->lastId();

    $insert2 = $db->query("INSERT INTO user_permission_matches SET user_id = $lastID, permission_id = 1");

    $theNewId=$lastID;
    include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');

	$sessionName = Config::get('session/session_name');
	Session::put($sessionName, $lastID);
    Redirect::to($whereNext);
  }
}

?>
