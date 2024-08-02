<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)) {
    $db = DB::getInstance();
    include "plugin_info.php";

    if(!function_exists("socialLogin")) {
        $db->update('us_plugins', ['plugin', '=', $plugin_name], ['status' => 'uninstalled']);
        $usplugins[$plugin_name] = 2;
        write_php_ini($usplugins, $abs_us_root . $us_url_root . 'usersc/plugins/plugins.ini.php');
        usError("socialLogin function required please update UserSpice to at least 5.7.0.");
        Redirect::to('admin.php?view=plugins');
        die();
    }

    //all actions should be performed here.
    $check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?", array($plugin_name))->count();
    if ($check > 0) {
        err($plugin_name.' has already been installed!');
    } else {
        $db->query("ALTER TABLE settings ADD disclogin BOOLEAN");
        $db->query("ALTER TABLE settings ADD discclientid varchar(255)");
        $db->query("ALTER TABLE settings ADD discclientsecret varchar(255)");
        $db->query("ALTER TABLE settings ADD disccallback varchar(255)");
		$db->query("ALTER TABLE settings ADD discserverreq BOOLEAN");
		$db->query("ALTER TABLE settings ADD discserverid varchar(255)");
        $db->query("ALTER TABLE users ADD disc_uid varchar(255)");
        $db->query("ALTER TABLE users ADD disc_uname varchar(255)");

    
        $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $path = explode("users/", $full_url);
        $url_path = $path[0] . "usersc/plugins/$plugin_name/assets/oauth_success.php";
    
        $db->update('settings', 1, ["disccallback"=>$url_path,"disclogin"=>0,"discserverreq"=>0]);

        $db->query("DELETE FROM plg_social_logins WHERE plugin = ?;", [$plugin_name]);
        $db->insert("plg_social_logins", ["plugin"=>$plugin_name, "provider"=>"Discord", "enabledsetting"=>"disclogin", "image"=>"assets/discord.png", "link"=>"assets/discord_oauth.php"]);
    
        $fields = array(
            'plugin'=>$plugin_name,
            'status'=>'installed',
        );
        $db->insert('us_plugins', $fields);
        if (!$db->error()) {
            err($plugin_name.' installed');
            logger($user->data()->id, "USPlugins", $plugin_name." installed");
        } else {
            err($plugin_name.' was not installed');
            logger($user->data()->id, "USPlugins", "Failed to to install plugin, Error: ".$db->errorString());
        }
    }

    //do you want to inject your plugin in the middle of core UserSpice pages?
    $hooks = [];

    //The format is $hooks['userspicepage.php']['position'] = path to filename to include
    //Note you can include the same filename on multiple pages if that makes sense;
    //postion options are post,body,form,bottom
    //See documentation for more information

    registerHooks($hooks, $plugin_name);
} //do not perform actions outside of this statement
