<?php
/*
UserSpice 5
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
?>

<?php

$db=DB::getInstance();

$settingsQ=$db->query("SELECT * FROM settings");
$settings=$settingsQ->first();

$clientId=$settings->discclientid;
$secret=$settings->discclientsecret;
$callback=$settings->disccallback;

if (!isset($_SESSION)) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';

$provider = new \Wohali\OAuth2\Client\Provider\Discord([
  'clientId' => $clientId,
  'clientSecret' => $secret,
  'redirectUri' => $callback
]);
if($settings->discserverreq) {
$options = [
  'scope' => ['identify', 'email', 'guilds']
];
} else {
$options = [
  'scope' => ['identify', 'email']
];
}

$link = $provider->getAuthorizationUrl($options);
$_SESSION['discordstate'] = $provider->getState();
?>