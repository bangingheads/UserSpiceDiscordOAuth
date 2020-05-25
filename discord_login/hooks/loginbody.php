<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
require_once '../users/init.php';
global $settings, $user, $db, $authUrl, $us_url_root, $abS_us_root;
if($settings->twlogin==1 && !$user->isLoggedIn()){
    require_once $abs_us_root.$us_url_root.'usersc/plugins/twitch_login/assets/twitch_oauth.php';
  }
?>
