<?php

require_once 'paths.php';
require_once ROOT.'vendor/autoload.php';

define('DEV', false);
define('DEF_NUM_EVENTS', 10);
define('FB_APP_TOKEN', '1616269921948808|oQroCtlreok9RV5HUMIPeiI89oQ');
//define('STRIPE_PUBLIC', (DEV ? "pk_test_6e2JAHrqsSfADzDHDMeAssBG": "pk_live_069WEiJbyNNYNvB1tkuJgifp"));
//define('STRIPE_PRIVATE', (DEV ? "sk_test_7hKBarIyyojeNOkds9jSUBcQ": "sk_live_xMRQkPtVYoyInwfKCYlgayeN"));

define('STRIPE_PUBLIC', ("pk_live_069WEiJbyNNYNvB1tkuJgifp"));
define('STRIPE_PRIVATE', ("sk_live_xMRQkPtVYoyInwfKCYlgayeN"));
// define('STRIPE_PUBLIC', "pk_test_6e2JAHrqsSfADzDHDMeAssBG");
// define('STRIPE_PRIVATE', "sk_test_7hKBarIyyojeNOkds9jSUBcQ");


if (DEV) error_reporting(E_ALL);

date_default_timezone_set("Europe/London");

//$fbConf = array('app_id' => '1616269921948808', 'app_secret' => '4777a3cc9fb9f3a0b53faabc250464c9', 'default_graph_version' => 'v2.5');
$fb = new Facebook\Facebook([
  'app_id' => '1616269921948808',
  'app_secret' => '4777a3cc9fb9f3a0b53faabc250464c9',
  'default_graph_version' => 'v2.5',
]);

$DB = array("host"=>"localhost", "username"=>"corona", "password"=>"TKTPASS15!", "DB"=>"tktpass");

$mysqli = mysqli_connect($DB['host'], $DB['username'], $DB['password'], $DB['DB']);
$mysqli->set_charset("utf8");
if (mysqli_connect_errno())
{
	die ("Failed to connect to MySQL: " . mysqli_connect_error());
}


?>