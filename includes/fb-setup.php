<?php
/**
 * @file fb-setup.php
 * File used to initalise the global `$fb` Facebook\Facebook instance (php SDK v5) used to handle all communication with Facebook.
 */

define('FACEBOOK_SDK_V5_SRC_DIR', '/opt/facebook-php-sdk-v5');
require_once FACEBOOK_SDK_V5_SRC_DIR.'/autoload.php';

require_once 'utils-login.php';

/**
 * @global array An associative array containg the settings described in config.ini
 */
$config = parse_ini_file('config.ini',true);

/**
* @global Facebook\\Facebook This is the global instance of <a href="https://developers.facebook.com/docs/php/gettingstarted" target="_blank">Facebook's php SDK v5</a>, used to handle all communication with Facebook.
* @see <a href="https://developers.facebook.com/docs/php/gettingstarted" target="_blank">The Facebook SDK for PHP</a>
* @see <a href="https://github.com/facebook/php-graph-sdk" target="_blank">Facebook SDK for PHP (v5) on Github</a>
*/
$fb = new Facebook\Facebook([
  'app_id' => $config['fb']['app_id'],
  'app_secret' => $config['fb']['app_secret'],
  'default_graph_version' => $config['fb']['default_graph_version'],
]);

/**
* @cond trycatchifelse
*/
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(isLoggedIn() && isFbConnected()){
    //$accessToken = $_SESSION['user']['fb_access_token'];
    $accessToken = new Facebook\Authentication\AccessToken($_SESSION['user']['fb_access_token'], $_SESSION['user']['fb_access_expires']);
    $fb->setDefaultAccessToken($accessToken);
    //Test token
    try {
      $response = $fb->get('/me?fields=name,first_name,last_name,email,birthday,gender,picture,location');
      $user = $response->getGraphUser();
      $propsNames = $user->getPropertyNames();
      foreach ($propsNames as $property) {
        if($property == 'id') $_SESSION['user']['fb_id'] = $user->getProperty($property);
        else $_SESSION['user'][$property] = $user->getProperty($property);
      }
      $_SESSION['user']['picture'] = $_SESSION['user']['picture']['url'];
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error (invalid token?)
      // Try update token? But for now
      logout();
    }
} else {
  $accessToken = $fb->getApp()->getAccessToken();
  $fb->setDefaultAccessToken($accessToken);
}
/**
* @endcond
*/

/**
 * @global array This global array defines the list of Facebook login permissions to ask for when a user first authorises the tktpass app.
 * @see <a href="https://developers.facebook.com/docs/facebook-login/permissions" target="_blank">Permissions Reference - Facebook Login</a>
 */
$fb_permissions = ['email','public_profile','user_friends','user_birthday','user_location','user_events','rsvp_event'];