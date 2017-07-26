<?php
require_once '../includes/utils-login.php';
require_once '../includes/fb-setup.php';
require_once '../includes/db-io.php';

$helper = $fb->getRedirectLoginHelper();

try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (!isset($accessToken)) {
  if ($helper->getError()) {
    //User denied the request
    /*header('HTTP/1.0 401 Unauthorized');
    echo "Error: " . $helper->getError() . "\n";
    echo "Error Code: " . $helper->getErrorCode() . "\n";
    echo "Error Reason: " . $helper->getErrorReason() . "\n";
    echo "Error Description: " . $helper->getErrorDescription() . "\n";*/
    header('Location: /');
  } else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Bad request';
  }
  exit;
}

// Success, FB connected

/*echo '<h3>Access Token</h3><pre>';
var_dump($accessToken->getValue());
echo '</pre>';*/

// The OAuth 2.0 client handler helps us manage access tokens
$oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
/*$tokenMetadata = $oAuth2Client->debugToken($accessToken);
echo '<h3>Metadata</h3><pre>';
var_dump($tokenMetadata);
echo '</pre>';

// Validation (these will throw FacebookSDKException's when they fail)
try {
  $tokenMetadata->validateAppId('1616269921948808');
  // If you know the user ID this access token belongs to, you can validate it here
  //$tokenMetadata->validateUserId('123');
  $tokenMetadata->validateExpiration();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Validation failed, Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}*/

if (!$accessToken->isLongLived()) {
  // Exchanges a short-lived access token for a long-lived one
  try {
    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  } catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
    exit;
  }

  /*echo '<h3>Long-lived</h3><pre>';
  var_dump($accessToken->getValue());
  echo '</pre>';*/
} /*else {
  echo '<em>Already long-lived.</em>';
}*/

// User is logged in with a long-lived access token.

$fb->setDefaultAccessToken($accessToken);

try {
  $response = $fb->get('/me?fields=id,name,first_name,last_name,email,birthday,gender,picture,location');
  $graphUser = $response->getGraphUser();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$exists = user_exists($graphUser->getProperty('id'),true);

if(isLoggedIn()){
  $user = get_user();
  if($user['fb_id'] && $graphUser->getProperty('id')!==$user['fb_id']){
    header('HTTP/1.0 400 Bad Request');
    die('Error: The Facebook account connected to the current tktpass account does not match that currently logged in on Facebook. Please log out and try again.');
  }
  if($exists && $exists!==$user['id']){
    header('HTTP/1.0 400 Bad Request');
    die('Error: The Facebook account you are trying to connect is already connected to a different tktpass account. Please log out and try again.');
  }
} else $_SESSION['user'] = [];
if($exists) $_SESSION['user']['id'] = $exists;
$_SESSION['user']['fb_access_token'] = $accessToken->getValue();
$_SESSION['user']['fb_access_expires'] = $accessToken->getExpiresAt()->format('Y-m-d H:i:s');

$propsNames = $graphUser->getPropertyNames();
foreach ($propsNames as $property) {
  if($property === 'id')
    $_SESSION['user']['fb_id'] = $graphUser->getProperty($property);
  else if(in_array($property,array("first_name","last_name","email")))
    $_SESSION['user'][$property] = $_SESSION['user'][$property] ? $_SESSION['user'][$property] : $graphUser->getProperty($property);
  else
    $_SESSION['user'][$property] = $graphUser->getProperty($property);
}
$_SESSION['user']['picture'] = $_SESSION['user']['picture'] && $_SESSION['user']['picture']['url'] ? $_SESSION['user']['picture']['url'] : $_SESSION['user']['picture'];
if(isset($_SESSION['user']['birthday']) && $_SESSION['user']['birthday'] instanceof DateTime)
  $_SESSION['user']['birthday'] = $_SESSION['user']['birthday']->format('Y-m-d');
if(isset($_SESSION['user']['gender']))
  $_SESSION['user']['gender']= $_SESSION['user']['gender'] === 'male' ? 0 : 1;
if(isset($_SESSION['user']['location'])){
  $loc_id = $_SESSION['user']['location']['id'];
  try {
    $response = $fb->get('/'.$loc_id.'?fields=location');
    $page = $response->getGraphPage();
  } catch(Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
  }
  $_SESSION['user']['city'] = $page->getLocation()->getCity();
  $_SESSION['user']['country'] = $page->getLocation()->getCountry();
  unset($_SESSION['user']['location']);
  //$countrycodes = array('AF'=>'Afghanistan','AX'=>'Åland Islands','AL'=>'Albania','DZ'=>'Algeria','AS'=>'American Samoa','AD'=>'Andorra','AO'=>'Angola','AI'=>'Anguilla','AQ'=>'Antarctica','AG'=>'Antigua and Barbuda','AR'=>'Argentina','AU'=>'Australia','AT'=>'Austria','AZ'=>'Azerbaijan','BS'=>'Bahamas','BH'=>'Bahrain','BD'=>'Bangladesh','BB'=>'Barbados','BY'=>'Belarus','BE'=>'Belgium','BZ'=>'Belize','BJ'=>'Benin','BM'=>'Bermuda','BT'=>'Bhutan','BO'=>'Bolivia','BA'=>'Bosnia and Herzegovina','BW'=>'Botswana','BV'=>'Bouvet Island','BR'=>'Brazil','IO'=>'British Indian Ocean Territory','BN'=>'Brunei Darussalam','BG'=>'Bulgaria','BF'=>'Burkina Faso','BI'=>'Burundi','KH'=>'Cambodia','CM'=>'Cameroon','CA'=>'Canada','CV'=>'Cape Verde','KY'=>'Cayman Islands','CF'=>'Central African Republic','TD'=>'Chad','CL'=>'Chile','CN'=>'China','CX'=>'Christmas Island','CC'=>'Cocos (Keeling) Islands','CO'=>'Colombia','KM'=>'Comoros','CG'=>'Congo','CD'=>'Zaire','CD'=>'Democratic Republic of Congo','CK'=>'Cook Islands','CR'=>'Costa Rica','CI'=>'Côte D\'Ivoire','HR'=>'Croatia','CU'=>'Cuba','CY'=>'Cyprus','CZ'=>'Czech Republic','DK'=>'Denmark','DJ'=>'Djibouti','DM'=>'Dominica','DO'=>'Dominican Republic','EC'=>'Ecuador','EG'=>'Egypt','SV'=>'El Salvador','GQ'=>'Equatorial Guinea','ER'=>'Eritrea','EE'=>'Estonia','ET'=>'Ethiopia','FK'=>'Falkland Islands (Malvinas)','FO'=>'Faroe Islands','FJ'=>'Fiji','FI'=>'Finland','FR'=>'France','GF'=>'French Guiana','PF'=>'French Polynesia','TF'=>'French Southern Territories','GA'=>'Gabon','GM'=>'Gambia','GE'=>'Georgia','DE'=>'Germany','GH'=>'Ghana','GI'=>'Gibraltar','GR'=>'Greece','GL'=>'Greenland','GD'=>'Grenada','GP'=>'Guadeloupe','GU'=>'Guam','GT'=>'Guatemala','GG'=>'Guernsey','GN'=>'Guinea','GW'=>'Guinea-Bissau','GY'=>'Guyana','HT'=>'Haiti','HM'=>'Heard Island and Mcdonald Islands','VA'=>'Vatican City State','HN'=>'Honduras','HK'=>'Hong Kong','HU'=>'Hungary','IS'=>'Iceland','IN'=>'India','ID'=>'Indonesia','IR'=>'Iran,Islamic Republic of','IQ'=>'Iraq','IE'=>'Ireland','IM'=>'Isle of Man','IL'=>'Israel','IT'=>'Italy','JM'=>'Jamaica','JP'=>'Japan','JE'=>'Jersey','JO'=>'Jordan','KZ'=>'Kazakhstan','KE'=>'KENYA','KI'=>'Kiribati','KP'=>'Korea,Democratic People\'s Republic of','KR'=>'Korea,Republic of','KW'=>'Kuwait','KG'=>'Kyrgyzstan','LA'=>'Lao People\'s Democratic Republic','LV'=>'Latvia','LB'=>'Lebanon','LS'=>'Lesotho','LR'=>'Liberia','LY'=>'Libyan Arab Jamahiriya','LI'=>'Liechtenstein','LT'=>'Lithuania','LU'=>'Luxembourg','MO'=>'Macao','MK'=>'Macedonia,the Former Yugoslav Republic of','MG'=>'Madagascar','MW'=>'Malawi','MY'=>'Malaysia','MV'=>'Maldives','ML'=>'Mali','MT'=>'Malta','MH'=>'Marshall Islands','MQ'=>'Martinique','MR'=>'Mauritania','MU'=>'Mauritius','YT'=>'Mayotte','MX'=>'Mexico','FM'=>'Micronesia,Federated States of','MD'=>'Moldova,Republic of','MC'=>'Monaco','MN'=>'Mongolia','ME'=>'Montenegro','MS'=>'Montserrat','MA'=>'Morocco','MZ'=>'Mozambique','MM'=>'Myanmar','NA'=>'Namibia','NR'=>'Nauru','NP'=>'Nepal','NL'=>'Netherlands','AN'=>'Netherlands Antilles','NC'=>'New Caledonia','NZ'=>'New Zealand','NI'=>'Nicaragua','NE'=>'Niger','NG'=>'Nigeria','NU'=>'Niue','NF'=>'Norfolk Island','MP'=>'Northern Mariana Islands','NO'=>'Norway','OM'=>'Oman','PK'=>'Pakistan','PW'=>'Palau','PS'=>'Palestinian Territory,Occupied','PA'=>'Panama','PG'=>'Papua New Guinea','PY'=>'Paraguay','PE'=>'Peru','PH'=>'Philippines','PN'=>'Pitcairn','PL'=>'Poland','PT'=>'Portugal','PR'=>'Puerto Rico','QA'=>'Qatar','RE'=>'Réunion','RO'=>'Romania','RU'=>'Russian Federation','RW'=>'Rwanda','SH'=>'Saint Helena','KN'=>'Saint Kitts and Nevis','LC'=>'Saint Lucia','PM'=>'Saint Pierre and Miquelon','VC'=>'Saint Vincent and the Grenadines','WS'=>'Samoa','SM'=>'San Marino','ST'=>'Sao Tome and Principe','SA'=>'Saudi Arabia','SN'=>'Senegal','RS'=>'Serbia','SC'=>'Seychelles','SL'=>'Sierra Leone','SG'=>'Singapore','SK'=>'Slovakia','SI'=>'Slovenia','SB'=>'Solomon Islands','SO'=>'Somalia','ZA'=>'South Africa','GS'=>'South Georgia and the South Sandwich Islands','ES'=>'Spain','LK'=>'Sri Lanka','SD'=>'Sudan','SR'=>'Suriname','SJ'=>'Svalbard and Jan Mayen','SZ'=>'Swaziland','SE'=>'Sweden','CH'=>'Switzerland','SY'=>'Syrian Arab Republic','TW'=>'Taiwan,Province of China','TJ'=>'Tajikistan','TZ'=>'Tanzania,United Republic of','TH'=>'Thailand','TL'=>'Timor-Leste','TG'=>'Togo','TK'=>'Tokelau','TO'=>'Tonga','TT'=>'Trinidad and Tobago','TN'=>'Tunisia','TR'=>'Turkey','TM'=>'Turkmenistan','TC'=>'Turks and Caicos Islands','TV'=>'Tuvalu','UG'=>'Uganda','UA'=>'Ukraine','AE'=>'United Arab Emirates','GB'=>'United Kingdom','US'=>'United States','UM'=>'United States Minor Outlying Islands','UY'=>'Uruguay','UZ'=>'Uzbekistan','VU'=>'Vanuatu','VE'=>'Venezuela','VN'=>'Viet Nam','VG'=>'Virgin Islands,British','VI'=>'Virgin Islands,U.S.','WF'=>'Wallis and Futuna','EH'=>'Western Sahara','YE'=>'Yemen','ZM'=>'Zambia','ZW'=>'Zimbabwe');
}

// Three scenarios
// 1) New user to register, 2) Logging in new FB connect, 3) Logging in and already connected

if($exists){
  //Update and log in that user if not already
  if(!$user) $user = get_user($exists);
  $data = array();
  if(!$user['first_name'])
    $data['first_name'] = $_SESSION['user']['first_name'];
  if(!$user['last_name'])
    $data['last_name'] = $_SESSION['user']['last_name'];
  if(!$user['email'])
    $data['email'] = $_SESSION['user']['email'];
  $data['fb_access_token'] = $_SESSION['user']['fb_access_token'];
  $data['fb_access_expires'] = $_SESSION['user']['fb_access_expires'];
  if(isset($_SESSION['user']['birthday']))
    $data['birthday'] = $_SESSION['user']['birthday'];
  if(isset($_SESSION['user']['gender']))
    $data['gender'] = $_SESSION['user']['gender'];
  if(isset($_SESSION['user']['city']))
    $data['city'] = $_SESSION['user']['city'];
  if(isset($_SESSION['user']['country']))
    $data['country']= $_SESSION['user']['country'];
  try{
    update_user($exists,$data);
  } catch(Exception $e){
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die("Couldn't update user, an error occurred\n\n".$e->getMessage()."\n\nPlease try again later.");
  }
} else if(isLoggedIn()){
  //Add FB ID to this user
  $data = array();
  if(!$user['first_name'])
    $data['first_name'] = $_SESSION['user']['first_name'];
  if(!$user['last_name'])
    $data['last_name'] = $_SESSION['user']['last_name'];
  if(!$user['email'])
    $data['email'] = $_SESSION['user']['email'];
  $data['fb_id'] = $_SESSION['user']['fb_id'];
  $data['fb_access_token'] = $_SESSION['user']['fb_access_token'];
  $data['fb_access_expires'] = $_SESSION['user']['fb_access_expires'];
  if(isset($_SESSION['user']['birthday']))
    $data['birthday'] = $_SESSION['user']['birthday'];
  if(isset($_SESSION['user']['gender']))
    $data['gender'] = $_SESSION['user']['gender'];
  if(isset($_SESSION['user']['city']))
    $data['city'] = $_SESSION['user']['city'];
  if(isset($_SESSION['user']['country']))
    $data['country']= $_SESSION['user']['country'];
  try{
    $user = update_user($user['id'],$data);
    if(!$user || $user['err']){
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
      die("Couldn't update user, an error occurred\n\n".$user['err']."\n\nPlease try again later.");
    }
  } catch(Exception $e){
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die("Couldn't update user, an error occurred\n\n".$e->getMessage()."\n\nPlease try again later.");
  }
} else {
  //Register
  try{
    $id = insert_user($_SESSION['user']);
    $_SESSION['user']['id'] = $id;
  } catch(Exception $e){
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die("Couldn't create new user, an error occurred\n\n".$e->getMessage()."\n\nPlease try again later.");
  }
}
if(isset($_GET['next']) &&
   (is_null(parse_url($_GET['next'],PHP_URL_HOST)) || substr(parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST),-11)=='tktpass.com')
  )
  $next = $_GET['next'];
/* else if(isset($_SERVER['HTTP_REFERER']) && substr(parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST),-11)=='tktpass.com')
  $next = $_SERVER['HTTP_REFERER'];*/
else $next = '/';
header('Location: '.$next);