<?php if (!in_array($user->data()->id, $master_account)) {
    Redirect::to($us_url_root.'users/admin.php');
} //only allow master accounts to manage plugins!?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if (!empty($_POST['plugin_discord_login'])) {
    $token = $_POST['csrf'];
    if (!Token::check($token)) {
        include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
}
$token = Token::generate();
?>
<div class="content mt-3">
  <div class="row">
    <div class="col-6 offset-3">
      <h2>Discord Login Settings</h2>
<strong>Please note:</strong> You must generate Discord Client Tokens to use Discord OAuth. More information can be found on the plugin Github README <a href="https://github.com/bangingheads/UserSpiceDiscordOAuth" target="_blank"><font color="blue">here.</font></a><br><br>


<!-- left -->
<div class="form-group">
  <label for="disclogin">Enable Discord Login</label>
  <span style="float:right;">
    <label class="switch switch-text switch-success">
                <input id="disclogin" type="checkbox" class="switch-input toggle" data-desc="Discord Login" <?php if ($settings->disclogin==1) {
    echo 'checked="true"';
} ?>>
                <span data-on="Yes" data-off="No" class="switch-label"></span>
                <span class="switch-handle"></span>
              </label>
            </span>
          </div>

          <div class="form-group">
            <label for="discid">Discord Client ID</label>
            <input type="password" class="form-control ajxtxt" data-desc="Twitch Client ID" name="discclientid" id="discclientid" value="<?=$settings->discclientid?>">
          </div>

          <div class="form-group">
            <label for="discsecret">Discord Client Secret</label>
            <input type="password" class="form-control ajxtxt" data-desc="Discord Client Secret" name="twclientsecret" id="discclientsecret" value="<?=$settings->discclientsecret?>">
          </div>

          <div class="form-group">
            <label for="disccallback">Discord Callback URL</label>
            <input type="text" class="form-control ajxtxt" data-desc="Discord Callback URL" name="disccallback" id="disccallback" value="<?=$settings->disccallback?>">
          </div>

  		<div class="form-group">
            <label for="finalredir">Redirect After Discord Login (Final Redirect)</label>
            <input type="text" class="form-control ajxtxt" data-desc="Final Redirect" name="finalredir" id="finalredir" value="<?=$settings->finalredir?>">
          </div>

  		</div>
  		</div>