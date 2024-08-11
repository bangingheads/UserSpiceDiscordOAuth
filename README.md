# UserSpice Discord Login Plugin

This plugin allows you to use Discord OAuth for logging into UserSpice.

Userspice can be downloaded from their [website](https://userspice.com/) or on [GitHub](https://github.com/mudmin/UserSpice5)

## Setting Up

1. Copy the discord_login plugin folder into /usersc/plugins/
2. Open UserSpice Admin Panel and install plugin.
3. Generate Client ID and Client Secret from Discord
4. Configure plugin with Client Information

## Generating Discord Client Authorization

Go to [https://discord.com/developers/](https://discord.com/developers/) and create/login to Discord

If you haven't already made an app, press the `New Application` button. You can name your Application as you choose, this name will be seen when users authorize their account.

Open your application, you can see your Client ID and Secret (by pressing reveal) on this General Information tab. Go to the OAuth2 tab. Press Add Redirect.

For the OAuth Redirect URL, you can copy the automatically generated URL from the plugin configuration, or it is `YOUR_URL/usersc/plugins/discord_login/assets/oauth_success.php` replacing `YOUR_URL` with the location of your UserSpice install.

Make sure to press Save Changes when completed.

## Plugin Configuration

Setting up the plugin is simple using the information from Discord.

Discord Client ID: Your Discord Application's Client ID

Discord Client Secret: Your Discord Application's Client Secret

Discord Callback URL (Full URL Path): This is automatically generated on install. If this is wrong it should be replaced by `YOUR_URL/usersc/plugins/discord_login/assets/oauth_success.php` replacing `YOUR_URL` with the location of your UserSpice install.

Redirect After Discord Login (Full URL Path): Enter the full path of the URL where you would like users to be redirected after logging in.

## Integration

Once you have logged into discord OAuth the user information will be added to users data.

You will have access to the following (With an example of my account BangingHeads):

disc_id: The Discord ID (173649211282292736)

disc_uname: The Discord Username (BangingHeads)

You can access them through the \$user variable. For example `$user->data()->disc_uname`

Need to run custom code after login or signup? Use the `usersc/includes/oauth_success_redirect.php` This file is included before the redirect and any code will be ran.

## Questions

Any issues? Feel free to open an issue on Github or make a Pull Request.

Need help? Add me on Discord: BangingHeads.

Any help with UserSpice can be asked in their [Discord](https://discord.gg/j25FeHu)
