<?php
/**
 * @file API Index
 * File that builds the entire public-faceing API endpoints and their functionaility.
 * 
 * @todo Break this file up into lots of smaller files and simply include them here. More manageable.
 *
 * @defgroup api API Functions
 * @brief Functions used to build the API functionaility.
 * @{
 */

if (session_status() == PHP_SESSION_NONE)
    session_start();

// Loads Slim Framework (and its dependencies such as psr, a psr-7 php interface library, and pimple dependency container) and Stripe's PHP SDK.
require 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once '../includes/utils-login.php';
require_once '../includes/db-io.php';
require_once '../includes/fb-setup.php';
require_once '../includes/stripe-setup.php';
require_once '../includes/sendmail.php';

$config = parse_ini_file('../includes/config.ini',true);
$config['displayErrorDetails'] = true;

$app = new \Slim\App(["settings" => $config]);

/*$container = $app->getContainer();
$container['db'] = function ($c) {
    $db_config = $c['settings']['db'];
    $db = new PDO("mysql:host=" . $db_config['host'] . ";dbname=" . $db_config['dbname'],
        $db_config['user'], $db_config['pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $db;
};*/

/*$app->add(function (Request $request, Response $response, callable $next) {
    $response = $response->withHeader('Content-type', 'application/json; charset=utf-8');
    return $next($request, $response);
});*/

// PSR-7 Middleware that determines the client IP address and stores it as an ServerRequest attribute, loaded through composer
$checkProxyHeaders = true;
$trustedProxies = ['10.0.0.1', '10.0.0.2', '127.0.0.1', '169.254.0.0','10.0.0.0', '172.16.0.0', '192.168.0.0'];
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));

/* ==================================================================================== *
   Utils
 * ==================================================================================== */
/**
 * @defgroup api-utils Utility Functions
 * @brief Utility functions to ease building the API functionaility.
 * @{
 */

/**
* Check whether a datetime string passed to the API is in the right format, namely `d/m/y H:i`
*
*
* @author  Alex Taylor <alex@taylrr.co.uk>
*
* @since 1.0
*
* @param string $dateString A datetime string
*
* @return boolean Whether the given string is in `d/m/y H:i` format or not.
*/
function isValidDate($dateString){
    return (bool)DateTime::createFromFormat('d/m/y H\:i', $dateString);
}

/**
* Transforms a `d/m/y H:i` datestring into a `Y-m-d H:i` formatted one
*
*
* @author  Alex Taylor <alex@taylrr.co.uk>
*
* @since 1.0
*
* @param string $dateString A `d/m/y H:i` format datetime string
*
* @return string The eqivalent datetime string in `Y-m-d H:i` format
*/
function toSqlDate($dateString){
    $date = DateTime::createFromFormat('d/m/y H\:i', $dateString);
    return $date ? $date->format('Y-m-d H:i') : false;
}

/**
* Checks whether the given email address is structually valid and also checks DNS for any MX records at the given domain
*
*
* @author  Alex Taylor <alex@taylrr.co.uk>
*
* @since 1.0
*
* @param string $email A proposed email address
*
* @return boolean `true` if the given email address appears legitimate, `false` if not.
*/
function isValidEmail($email){
    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
        $isValid = false;
    } else {
        $domain    = substr($email, $atIndex + 1);
        $local     = substr($email, 0, $atIndex);
        $localLen  = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $isValid = false;
        } else if ($domainLen < 4 || $domainLen > 255) {
            // domain part length exceeded
            $isValid = false;
        } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
            // local part starts or ends with '.'
            $isValid = false;
        }  else if ($domain[0] == '.' || $domain[$domainLen - 1] == '.' || $domain[0] == '-' || $domain[$domainLen - 1] == '-') {
            // domain part starts or ends with '.' or '-'
            $isValid = false;
        } else if (!preg_match('/^[a-zA-Z0-9\\.!#$%&’\'*+\/=?^_`{|}~\\-]+$/', $local)) {
            // character not valid in local part
            $isValid = false;
        }  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $isValid = false;
        } else if (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $isValid = false;
        } else if (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $isValid = false;
        }  else if (is_bool(strrpos($domain, ".")) && !strrpos($domain, ".")) {
            // domain part has no TLD
            $isValid = false;
        } /*else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
            // character not valid in local part unless 
            // local part is quoted
            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                $isValid = false;
            }
        }*/
        if($isValid && !checkdnsrr($domain, "MX")){ //checkdnsrr($domain, "A")
            // domain not found in DNS
            $isValid = false;
        }
    }
    return $isValid;
}

/**
* Checks whether there is a file at the given URL and that file is accessable.
*
*
* @author  Alex Taylor <alex@taylrr.co.uk>
*
* @since 1.0
*
* @param string $url Proposed URL to a file
*
* @return boolean `true` if the URL resolves and the file is accessable, `false` if not.
*/
function remoteFileExists($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    // don't download content
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(curl_exec($ch)!==FALSE){
        return true;
    }
    else{
        return false;
    }
}

/**
* Checks whether there is a file at the given URL and that file is accessable.
*
*
* @author  Alex Taylor <alex@taylrr.co.uk>
*
* @since 1.0
*
* @param string $url Proposed URL to a file
*
* @return boolean `true` if the URL resolves and the file is accessable, `false` if not.
*/
function add401AndJson($res){
    $res = $res->withHeader('WWW-Authenticate', 'Cookie realm="tktpass" form-action="/login" cookie-name=PHPSESSID auth-param=email auth-param=password');
    $res = $res->withJson(array("err" => "User not logged in"),401);
    return $res;
}
/**
 * @}
 */

/* ==================================================================================== *
   Facebook
 * ==================================================================================== */

$app->get('/{route:me/fb-events|user/fb-events|fb-events}[/]', function (Request $req, Response $res) {
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!isFbConnected()){
      $res = $res->withJson(array("err" => "User not connected with Facebook"),400);
      return $res;
    }

    function FB_GetUserEvents($fields="id") {
        global $fb;
        $events = array();
        $offset = 0;
        $limit = 500;
        $now = time();

        $data = $fb->get("/me/events?since=$now&limit=$limit&offset=$offset&fields=$fields")->getDecodedBody();
        $events = array_merge($events, $data["data"]);
        while(in_array("paging", $data) && array_key_exists("next", $data["paging"])) {
            $offset += $limit;
            $data = $fb->get("/me/events?limit=$limit&offset=$offset&fields=$fields");
            $events = array_merge($events, $data["data"]);
        }
        return $events;
    }
    function FB_FilterEventsAdminOnly($events) {
        global $fb;
        $myevents=array();
        $fields = "is_viewer_admin,cover,name";
        $offset = 0;
        $limit = 500;
        foreach ($events as $event){
            $data = $fb->get("/".$event["id"]."?limit=$limit&offset=$offset&fields=$fields")->getDecodedBody();
            if($data["is_viewer_admin"])
                array_push($myevents, array_merge($event,$data));
        }
        return $myevents;
    }

    try {
        $events = FB_GetUserEvents();
        $myevents = FB_FilterEventsAdminOnly($events);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      $res = $res->withJson(array("err" => $e->getMessage()),400);
      return $res;
    }

    $res = $res->withJson($myevents,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
    return $res;
});

$app->get('/fb-events/{id:\d{8,16}}[/]', function (Request $req, Response $res, $args) {
    global $fb;
    try {
      $event = $fb->get('/'.$args['id'].'?fields=id,name,description,cover,start_time,end_time,place,type')->getDecodedBody();
      $event["admin"] = ($fb->get('/'.$args['id'].'/admins')->getDecodedBody())["data"][0];
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      $res = $res->withJson(array("err" => $e->getMessage()),400);
      return $res;
    }
    $res = $res->withJson($event,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
    return $res;
});

/* ==================================================================================== *
   Database Users
 * ==================================================================================== */

function checkUserAllowed($POST){
    //Allowed
    $allowed_keys = array("id", "first_name", "last_name", "email", "hash", "joined", "plan", "referral", "customer_id", "mobile", "fb_id", "fb_access_token", "fb_access_expires", "birthday", "gender", "city", "country", "mailing_list", "last_active", "account_id", "account_secret", "account_publishable", "verified", "newPassword", "newPassword2");
    $keys = array_keys($POST);
    foreach ($keys as $key) {
      if(!in_array($key, $allowed_keys)){
        return array("err"=>"Unrecognised key: ".$key." in users","status"=>400);
      }
    }
    return array("err"=>false,"status"=>200);
}

function isFacebookUserId($id){
    $url = "https://www.facebook.com/app_scoped_user_id/".$id;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
    curl_setopt($ch,  CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch,  CURLOPT_MAXREDIRS, 2);
    /* Get the HTML or whatever is linked in $url. */
    $response = curl_exec($ch);
    /* Check for 404 (file not found). */
    $httpCode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE))+0;
    curl_close($ch);
    return $httpCode < 400;
}

function validateUserFields($POST){
    //Valid checks
    if(isset($POST["id"]) && !preg_match("/^\d+$/", $POST["id"]))
        return array("err"=>"Malformed id given","status"=>400);
    if(isset($POST["first_name"]) && strlen(trim($POST["first_name"])) < 2)
        return array("err"=>"first_name must be at least 2 charchters","status"=>400);
    if(isset($POST["last_name"]) && strlen(trim($POST["last_name"])) < 2)
        return array("err"=>"last_name must be at least 2 charchters","status"=>400);
    if(isset($POST["email"]) && !isValidEmail($POST["email"]))
        return array("err"=>"email not valid","status"=>400);
    if(isset($POST["gender"]) && !in_array($POST["gender"], array('','0','1')))
        return array("err"=>"Unrecognised value for gender, should be 0, 1 or empty string","status"=>400);
    if(isset($POST["hash"]))
        return array("err"=>"Cannot edit hash value","status"=>400);
    if(isset($POST["joined"]) && !isValidDate($POST["joined"]))
        return array("err"=>"Could not parse joined as date string","status"=>400);
    if(isset($POST["fb_id"]) && !isFacebookUserId($POST["fb_id"]))
        return array("err"=>"fb_id ".$POST["fb_id"]." not recognised","status"=>400);
    if(isset($POST["city"]) && strlen(trim($POST["city"])) < 3)
        return array("err"=>"city must be at least 3 charchters","status"=>400);
    if(isset($POST["country"]) && strlen(trim($POST["country"])) < 3)
        return array("err"=>"country must be at least 3 charchters","status"=>400);
    if(isset($POST["mailing_list"]) && !in_array($POST["mailing_list"], array('0','1')))
        return array("err"=>"Unrecognised value for mailing_list, should be 0 or 1","status"=>400);
    if(isset($POST["last_active"]) && !isValidDate($POST["last_active"]))
        return array("err"=>"Could not parse last_active as date string","status"=>400);
    if(isset($POST["verified"]) && !in_array($POST["verified"], array('0','1')))
        return array("err"=>"Unrecognised value for verified, should be 0 or 1","status"=>400);
    if(isset($POST["newPassword"])){
        if(strlen($POST["newPassword"]) < 6)
          return array("err"=>"New password must be at least 6 characters","status"=>400);
        if($POST["newPassword"] !== $POST["newPassword2"])
          return array("err"=>"Passwords do not match","status"=>400);
    }
    //END Valid checks
    return array("err"=>false,"status"=>200);
}

function sanitiseUserFields($POST){
    if(isset($POST["first_name"]))
        $POST["first_name"] = trim($POST["first_name"]);
    if(isset($POST["last_name"]))
        $POST["last_name"] = trim($POST["last_name"]);
    if(isset($POST["city"]))
        $POST["city"] = trim($POST["city"]);
    if(isset($POST["country"]))
        $POST["country"] = trim($POST["country"]);
    if(isset($POST["joined"]))
        $POST["joined"] = toSqlDate($POST["joined"]);
    if(isset($POST["last_active"]))
        $POST["last_active"] = toSqlDate($POST["last_active"]);
    if(isset($POST["gender"]) && $POST["gender"]==='')
        $POST["gender"] = null;
    if(isset($POST["newPassword"])){
        $POST["hash"] = password_hash($POST["newPassword"],PASSWORD_DEFAULT);
        unset($POST["newPassword"]);
        unset($POST["newPassword2"]);
    }
    return $POST;
}

function get_user_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!$args['id'])
        $args['id'] = $_SESSION['user']['id'];
    if(!user_exists($args['id'])){
        $res = $res->withJson(array("err" => "user id ".$args['id']." not recognised"),404);
        return $res;
    }
    $result = get_user($args["id"]);
    // Respond
    if($result && !$result["err"]){
      unset($result['hash']);
      unset($result['account_secret']);
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
}

$app->get('/{route:user|me}[/]','get_user_handler');

//$app->get('/{route:user|me}/{id:\d+}[/]',get_user_handler);

function post_user_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if($args['id']){
        if(!user_exists($args['id'])){
            $res = $res->withJson(array("err" => "user id ".$args['id']." not recognised"),404);
            return $res;
        }
        if($args['id'] !== $_SESSION['user']['id']){
            $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to edit details of user ".$args['id']),403);
            return $res;
        }
    } else $args['id'] = $_SESSION['user']['id'];

    $POST = $req->getParsedBody();

    $allowed = checkUserAllowed($POST);
    if($allowed["err"]){
        $res = $res->withJson(array("err" => $allowed["err"]),$allowed["status"]);
        return $res;
    }

    //Disallowed
    $disallowed_keys = array("id", "hash", "joined", "plan", "referral", "customer_id", "fb_id", "fb_access_token", "fb_access_expires", "last_active", "account_id", "account_secret", "account_publishable", "verified");
    $user = get_user($args['id']);
    if($user['fb_id'])
      array_push($disallowed_keys, "birthday", "gender");
    $keys = array_keys($POST);
    foreach($keys as $key){
       if(in_array($key, $disallowed_keys)){
        $res = $res->withJson(array("err" => "Cannot edit ".$key." value"),403);
        return $res;
       }
    }

    //Valid Checks
    $valid = validateUserFields($POST);
    if($valid["err"]){
        $res = $res->withJson(array("err" => $valid["err"]),$valid["status"]);
        return $res;
    }

    $POST = sanitiseUserFields($POST);

    $result = update_user($args["id"],$POST);
    // Respond
    if($result && !$result["err"]){
      unset($result['hash']);
      unset($result['account_secret']);
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
}

$app->post('/{route:user|me}[/]','post_user_handler');

//$app->post('/users/{id:\d+}[/]',post_user_handler);

/*$app->delete('/users/{id:\d{8,16}}[/]',function(Request $req, Response $res, $args){
    if(!user_exists($args['id'])){
        $res = $res->withJson(array("err" => "user id ".$args['id']." not recognised"),404);
        return $res;
    }

    $result = delete_user($args["id"]);
    // Respond
    if($result && !$result["err"]){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
});*/

$app->post('/register[/]',function(Request $req, Response $res, $args){
    if(isLoggedIn()){
      $res = $res->withJson(array("err" => "Already logged in"),400);
      return $res;
    }
    $POST = $req->getParsedBody();
    $ids = false;
    if(!trim($POST['first_name']) || strlen(trim($POST['first_name']))<2)
        $ids = $ids?$ids+', #inputFname':'#inputFname';
    if(!trim($POST['last_name']) || strlen(trim($POST['last_name']))<2)
        $ids = $ids?$ids+', #inputLname':'#inputLname';
    if(!trim($POST['email']) || !isValidEmail($POST['email']))
        $ids = $ids?$ids+', #inputEmail':'#inputEmail';
    if(!$POST['password'] || strlen($POST['password'])<6)
        $ids = $ids?$ids+', #inputPassword':'#inputPassword';
    if($ids){
      $res = $res->withJson(array("err" => true,"ids"=>$ids),400);
      return $res;
    }
    if(get_user_by_email($POST['email'])) {
      $res = $res->withJson(array("err" => "User with email ".$POST['email']." already exists"),400);
      return $res;
    }
    $POST['hash'] = password_hash($POST['password'],PASSWORD_DEFAULT);
    unset($POST['password']);
    $user = insert_user($POST);
    if($user && !$user["err"]){
      unset($user['hash']);
      unset($user['account_secret']);
      $res = $res->withJson($user,201);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $user["err"]),500);
      return $res;
    }
});

$app->post('/login[/]',function(Request $req, Response $res, $args){
    if(isLoggedIn()){
        $res = $res->withJson(array("err" => "Already logged in"),400);
        return $res;
    }
    $POST = $req->getParsedBody();
    if(!$POST['email']){
        $res = $res->withJson(array("err" => "User email not provided"),400);
        return $res;
    }
    if(!$POST['password']){
        $res = $res->withJson(array("err" => "User password not provided"),400);
        return $res;
    }
    $user = get_user_by_email($POST['email']);
    if(!$user || isset($user["err"])){
        $res = $res->withJson(array("err" => $user["err"]),400);
        return $res;
    }
    if(password_verify($POST['password'], $user['hash'])){
      //logout();
      session_regenerate_id();
      unset($user['hash']);
      unset($user['account_secret']);
      $_SESSION['user'] = array();
      $keys = array_keys($user);
      foreach ($keys as $key) {
        if(!is_null($user[$key]))
          $_SESSION['user'][$key] = $user[$key];
      }
      $res = $res->withJson($user,200);
      return $res;
    } else {
      $res = $res->withJson(array("err" => "Email and password do not match"),400);
      return $res;
    }
});

$app->post('/forgot[/]',function(Request $req, Response $res, $args){
    if(isLoggedIn()){
      $res = $res->withJson(array("err" => "Already logged in"),400);
      return $res;
    }
    $POST = $req->getParsedBody();
    if(!$POST['email']){
      $res = $res->withJson(array("err" => "User email not provided"),400);
      return $res;
    }
    $user = get_user_by_email($POST['email']);
    if(!$user || $user["err"] || !$user["email"]){
      $res = $res->withJson(array(),204);
      return $res;
    }
    $selector = bin2hex(random_bytes(8));
    $bytes = random_bytes(32);
    $inserted = insert_user_recovery(array("user_id"=>$user['id'],"selector"=>$selector,"hash"=>hash('sha256',$bytes)));
    if(!$inserted || $inserted['err']){
      $res = $res->withJson(array("err" => "Database insert failed: ".$inserted['err']),500);
      return $res;
    }
    /*$urlToEmail = 'https://tktpass.com/reset.php?'.http_build_query([
        'selector' => $selector,
        'validator' => bin2hex($bytes)
    ]);*/
    $sent = sendResetEmail($user['email'],$user['first_name'],$selector,bin2hex($bytes));
    if($sent){
        $res = $res->withJson(array(),204);
        return $res;
    } else {
        $res = $res->withJson(array("err" => "Delivering email to ".$POST['email']." failed"),500);
        return $res;
    }
});

$app->post('/reset[/]',function(Request $req, Response $res, $args){
    // if(isLoggedIn()){
    //   $res = $res->withJson(array("err" => "Cannot reset password if already logged in"),400);
    //   return $res;
    // }
    $POST = $req->getParsedBody();
    if(!$POST['password']){
      $res = $res->withJson(array("err" => "New password not provided"),400);
      return $res;
    }
    if(strlen($POST['password'])<6){
      $res = $res->withJson(array("err" => "New password must be at least 6 characters"),400);
      return $res;
    }
    if(!$POST['selector']){
      $res = $res->withJson(array("err" => "Selector value not provided"),400);
      return $res;
    }
    if(!$POST['validator']){
      $res = $res->withJson(array("err" => "Validator value not provided"),400);
      return $res;
    }
    if(!$POST['token']){
      $res = $res->withJson(array("err" => "Token value not provided"),400);
      return $res;
    }
    $row = validate_user_recovery($POST['selector'], $POST['validator'], $POST['token']);
    if(!$row || $row['err']){
      $res = $res->withJson(array("err" => "Could not validate reset credentials"),400);
      return $res;
    }
    if(!user_exists($row['user_id'])){
      $res = $res->withJson(array("err"=>"Could not find user with ID ".$row['user_id']),500);
      return $res;
    }
    $hash = password_hash($POST['password'],PASSWORD_DEFAULT);
    $updated = update_user($row['user_id'],array("hash"=>$hash));
    if($updated && !$updated["err"]){
      delete_user_recovery($row['id']);
      unset($updated['hash']);
      unset($updated['account_secret']);
      $res = $res->withJson($updated,200);
      return $res;
    } else {
      $res = $res->withJson(array("err"=>$updated["err"]),500);
      return $res;
    }
});

function checkAccountAllowed($POST){
    //Top Allowed
    $allowed_keys = array("business_logo","business_name","business_primary_color","business_url","country","decline_charge_on","default_currency","email","external_account","legal_entity","metadata","payout_schedule","product_description","statement_descriptor","support_email","support_phone","support_url","timezone","tos_acceptance","transfer_schedule","verification");
    $keys = array_keys($POST);
    foreach ($keys as $key) {
      if(!in_array($key, $allowed_keys)){
        return array("err"=>"Unrecognised key for account: ".$key,"status"=>400);
      }
    }
    //decline_charge_on Allowed
    if(isset($POST['decline_charge_on'])){
        if(!is_array($POST['decline_charge_on']))
            return array("err"=>"decline_charge_on must be an array of key-value pairs","status"=>400);
        $allowed_keys = array("avs_failure","cvc_failure");
        $keys = array_keys($POST['decline_charge_on']);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key for decline_charge_on: ".$key,"status"=>400);
          }
        }
    }
    //External Account Allowed
    /* Commented out as we use Stripe.js token instead
    if(isset($POST['external_account'])){
        if(!is_array($POST['external_account']))
            return array("err"=>"external_account must be an array of key-value pairs","status"=>400);
        $allowed_keys = array("object","account_number","country","currency","account_holder_name","account_holder_type","routing_number");
        $keys = array_keys($POST['external_account']);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key for external_account: ".$key,"status"=>400);
          }
        }
    }*/
    //Legal Entity Allowed
    if(isset($POST['legal_entity'])){
        if(!is_array($POST['legal_entity']))
            return array("err"=>"legal_entity must be an array of key-value pairs","status"=>400);
        $allowed_keys = array("additional_owners","address","business_name","business_tax_id","business_vat_id","dob","first_name","gender","last_name","personal_address","personal_id_number","phone_number","type","verification");
        $keys = array_keys($POST['legal_entity']);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key for legal_entity: ".$key,"status"=>400);
          }
        }
    }
    //Address Allowed
    $allowed_keys = array("city","country","line1","line2","postal_code","state");
    if(is_array($POST['legal_entity']) && isset($POST['legal_entity']['address'])){
        if(!is_array($POST['legal_entity']['address']))
            return array("err"=>"legal_entity['address'] must be an array of key-value pairs","status"=>400);
        $keys = array_keys($POST['legal_entity']['address']);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key for legal_entity['address']: ".$key,"status"=>400);
          }
        }
    }
    if(isset($POST['legal_entity']['personal_address'])){
        if(!is_array($POST['legal_entity']['personal_address']))
            return array("err"=>"legal_entity['personal_address'] must be an array of key-value pairs","status"=>400);
        $keys = array_keys($POST['legal_entity']['personal_address']);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key for legal_entity['personal_address']: ".$key,"status"=>400);
          }
        }
    }
    //DOB Allowed
    if(is_array($POST['legal_entity']) && isset($POST['legal_entity']['dob'])){
        if(!is_array($POST['legal_entity']['dob']))
            return array("err"=>"legal_entity['dob'] must be an array of key-value pairs","status"=>400);
        $allowed_keys = array("day","month","year");
        $keys = array_keys($POST['legal_entity']['dob']);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key for legal_entity['dob']: ".$key,"status"=>400);
          }
        }
    }
    //Verification Allowed
    if(is_array($POST['legal_entity']) && isset($POST['legal_entity']['verification'])){
        if(!is_array($POST['legal_entity']['verification']))
            return array("err"=>"legal_entity['verification'] must be an array of key-value pairs","status"=>400);
        $allowed_keys = array("document");
        $keys = array_keys($POST['legal_entity']['verification']);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key for legal_entity['verification']: ".$key,"status"=>400);
          }
        }
    }
    //TOS Allowed
    /*Commented outas now value is just true, and info is gathered on the backend
    if(isset($POST['tos_acceptance'])){
        if(!is_array($POST['tos_acceptance']))
            return array("err"=>"tos_acceptance must be an array of key-value pairs","status"=>400);
        $allowed_keys = array("date","ip","user_agent");
        $keys = array_keys($POST['tos_acceptance']);
        foreach ($keys as $key) {
          if(!in_array($key, $allowed_keys)){
            return array("err"=>"Unrecognised key for tos_acceptance: ".$key,"status"=>400);
          }
        }
    }*/

    return array("err"=>false,"status"=>200);
}

function validateAccountFields($POST){
    //Valid checks
    if(isset($POST["email"]) && !isValidEmail($POST['email']))
        return array("err"=>"Invalid email provided","status"=>400);

    $supportedCountries = array("AU","CA","DK","FI","FR","IE","NO","SE","GB","US","AT","BE","DE","HK","IT","JP","LU","NL","PT","SG","ES","BR","MX","NZ","CH");
    if(isset($POST["country"]) && !in_array($POST["country"], $supportedCountries))
        return array("err"=>"Country code '".$POST["country"]."' not supported","status"=>400);

    $supportedCurrencies = array("AUD","CAD","USD","EUR","GBP","DKK","NOK","SEK","JPY","SGD","BRL","MXN","CHF","HKD");
    if(is_array($POST['external_account'])){
        if($POST['external_account']['object'] !== 'bank_account')
            return array("err"=>"external_account['object'] must equal 'bank_account'","status"=>400);

        if(isset($POST['external_account']['account_number']) && !preg_match("/^(?:\d ?){4,}$/", $POST['external_account']['account_number']))
            return array("err"=>"external_account['account_number'] not numeric or long enough","status"=>400);

        if(!in_array($POST['external_account']["country"], $supportedCountries))
            return array("err"=>"Not supported country code '".$POST['external_account']["country"]."' in external_account","status"=>400);

        if(!in_array($POST['external_account']["currency"], $supportedCurrencies))
            return array("err"=>"Not supported currency code '".$POST['external_account']["currency"]."' in external_account","status"=>400);

        if(isset($POST['external_account']["routing_number"]) && !preg_match("/^(?:\d[ -]?){4,}$/", $POST['external_account']['routing_number']))
            return array("err"=>"external_account['routing_number'] not numeric or long enough","status"=>400);
    }

    if(isset($POST['legal_entity'])){
        if(!is_array($POST['legal_entity']))
            return array("err"=>"legal_entity must be an array of key-value pairs","status"=>400);

        if(isset($POST['legal_entity']["address"])){
            if(!is_array($POST['legal_entity']["address"]))
                return array("err"=>"legal_entity['address'] must be an array of key-value pairs","status"=>400);

            if(isset($POST['legal_entity']["address"]['country']) && !in_array($POST['legal_entity']["address"]['country'], $supportedCountries))
                return array("err"=>"Not supported country code '".$POST['legal_entity']["address"]['country']."' in legal_entity['address']['country']","status"=>400);
        }

        if(isset($POST['legal_entity']['dob'])){
            if(!is_array($POST['legal_entity']['dob']))
                return array("err"=>"legal_entity['dob'] must be an array of key-value pairs","status"=>400);

            if(!isset($POST['legal_entity']['dob']['day']) || intval($POST['legal_entity']['dob']['day'])<1 || intval($POST['legal_entity']['dob']['day'])>31)
                return array("err"=>"legal_entity['dob']['day'] must be an integer between 1 and 31","status"=>400);

            if(!isset($POST['legal_entity']['dob']['month']) || intval($POST['legal_entity']['dob']['month'])<1 || intval($POST['legal_entity']['dob']['month'])>12)
                return array("err"=>"legal_entity['dob']['month'] must be an integer between 1 and 12","status"=>400);

            if(!isset($POST['legal_entity']['dob']['year']) || intval($POST['legal_entity']['dob']['year'])<1900 || intval($POST['legal_entity']['dob']['year'])>2010)
                return array("err"=>"legal_entity['dob']['year'] must be an integer between 1900 and 2010","status"=>400);
        }

        if(isset($POST['legal_entity']['verification'])){
            if(!is_array($POST['legal_entity']['verification']))
                return array("err"=>"legal_entity['verification'] must be an array of key-value pairs","status"=>400);

            if(!isset($POST['legal_entity']['verification']['document']))
                return array("err"=>"legal_entity['verification']['document'] must be set if setting legal_entity['verification']","status"=>400);
        }

        if(isset($POST['legal_entity']['address'])){
            if(!is_array($POST['legal_entity']['address']))
                return array("err"=>"legal_entity['address'] must be an array of key-value pairs","status"=>400);

            if(isset($POST['legal_entity']['address']["country"]) && !in_array($POST['legal_entity']['address']["country"], $supportedCountries))
                return array("err"=>"legal_entity['address']['country'] '".$POST['legal_entity']['address']["country"]."' must be a supported 2-letter country code","status"=>400);
        }

        if(isset($POST['legal_entity']['gender']) && !in_array($POST['legal_entity']['gender'], array("male","female")))
                return array("err"=>"legal_entity['gender'] must be one of 'male' or 'female'","status"=>400);

        if(isset($POST['legal_entity']['personal_address'])){
            if(!is_array($POST['legal_entity']['personal_address']))
                return array("err"=>"legal_entity['personal_address'] must be an array of key-value pairs","status"=>400);

            if(isset($POST['legal_entity']['personal_address']["country"]) && !in_array($POST['legal_entity']['personal_address']["country"], $supportedCountries))
                return array("err"=>"legal_entity['personal_address']['country'] '".$POST['legal_entity']['personal_address']["country"]."' must be a supported 2-letter country code","status"=>400);
        }

        if(!empty($POST['legal_entity']['phone_number']) && !preg_match("/^\\+?[\\d\\-\\(\\) ]{6,}$/", $POST['legal_entity']['phone_number']))
            return array("err"=>"legal_entity['phone_number'] does not look like a valid phone number","status"=>400);

        if(isset($POST['legal_entity']['type']) && !in_array($POST['legal_entity']['type'], array("individual","company")))
                return array("err"=>"legal_entity['type'] must be one of 'individual' or 'company'","status"=>400);
    }

    if(isset($POST['metadata']) && !is_array($POST['metadata']))
        return array("err"=>"metadata must be an array of key-value pairs","status"=>400);

    /*if(isset($POST['tos_acceptance'])){
        if(!is_array($POST['tos_acceptance']))
            return array("err"=>"tos_acceptance must be an array of key-value pairs","status"=>400);
        function isValidTimeStamp($timestamp){
            return ((string) (int) $timestamp === $timestamp) 
                && ($timestamp <= PHP_INT_MAX)
                && ($timestamp >= ~PHP_INT_MAX);
        }
        if(isset($POST['tos_acceptance']['date']) && !isValidTimeStamp($POST['tos_acceptance']['date']))
            return array("err"=>"tos_acceptance['date'] must be a valid unix timestamp","status"=>400);
        if(isset($POST['tos_acceptance']['ip']) && !filter_var($POST['tos_acceptance']['ip'], FILTER_VALIDATE_IP))
            return array("err"=>"tos_acceptance['ip'] must be a valid IP address","status"=>400);
    }*/

    if(isset($POST['tos_acceptance']) && !in_array($POST['tos_acceptance'],array(true,"true","1",false,"false","0")))
        return array("err"=>"tos_acceptance must be a boolean value (true/false)","status"=>400);
    //END Valid checks

    return array("err"=>false,"status"=>200);
}

function sanitiseAccountFields($POST){
    if(isset($POST['legal_entity']) && isset($POST['legal_entity']['dob'])){
        $POST['legal_entity']['dob']['day'] = intval($POST['legal_entity']['dob']['day']);
        $POST['legal_entity']['dob']['month'] = intval($POST['legal_entity']['dob']['month']);
        $POST['legal_entity']['dob']['year'] = intval($POST['legal_entity']['dob']['year']);
    }
    /*if(isset($POST['tos_acceptance']) && isset($POST['tos_acceptance']['date'])){
        $POST['tos_acceptance']['date'] = (string) (int) $POST['tos_acceptance']['date'];
    }*/
    if(isset($POST['tos_acceptance']) && $POST['tos_acceptance'])
        $POST['tos_acceptance'] = array("date" => time());

    return $POST;
}

function post_user_account_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if($args['id']){
        if(!user_exists($args['id'])){
            $res = $res->withJson(array("err" => "user id ".$args['id']." not recognised"),404);
            return $res;
        }
        if($args['id'] !== $_SESSION['user']['id']){
            $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to edit account details of user ".$args['id']),403);
            return $res;
        }
    } else $args['id'] = $_SESSION['user']['id'];

    $POST = $req->getParsedBody();

    //Disallowed
    $disallowed_keys = array("id","object","charges_enabled","debit_negative_balances","default_currency","details_submitted","display_name","payout_statement_descriptor","type");
    $keys = array_keys($POST);
    foreach($keys as $key){
       if(in_array($key, $disallowed_keys)){
        $res = $res->withJson(array("err" => "Cannot edit '".$key."' value"),403);
        return $res;
       }
    }

    $allowed = checkAccountAllowed($POST);
    if($allowed["err"]){
        $res = $res->withJson(array("err" => $allowed["err"]),$allowed["status"]);
        return $res;
    }

    //Valid Checks
    $valid = validateAccountFields($POST);
    if($valid["err"]){
        $res = $res->withJson(array("err" => $valid["err"]),$valid["status"]);
        return $res;
    }

    $POST = sanitiseAccountFields($POST);
    if(isset($POST['tos_acceptance']) && $POST['tos_acceptance']){
        $POST['tos_acceptance'] = array();
        $POST['tos_acceptance']['ip'] = $req->getAttribute('ip_address');
        $POST['tos_acceptance']['user_agent'] = $req->getHeader('User-Agent')[0];
    }

    $account = null;
    $user = get_user($args['id']);
    if(!$user['account_id']){
        //Top Required for creation
        $required_keys = array("external_account","legal_entity","tos_acceptance");
        $keys = array_keys($POST);
        $oneOf = false;
        foreach($required_keys as $required_key){
            /*if(!in_array($required_key, $keys) || empty($POST[$required_key])){
                $res = $res->withJson(array("err" => "Missing required key '".$required_key),400);
                return $res;
            }*/
            if(in_array($required_key, $keys) && !empty($POST[$required_key])){
                $oneOf = true;
                break;
            }
        }
        if(!$oneOf){
            $res = $res->withJson(array("err" => "One of \"external_account\", \"legal_entity\", or \"tos_acceptance\" required for account creation"),400);
            return $res;
        }
        //legal_entity Required for creation
        if(isset($POST["legal_entity"])){
            $required_keys = array("dob","first_name","last_name","type","address");
            $keys = array_keys($POST['legal_entity']);
            foreach($required_keys as $required_key){
                if(!in_array($required_key, $keys) || empty($POST['legal_entity'][$required_key])){
                    $res = $res->withJson(array("err" => "Missing required key '".$required_key."' for legal_entity"),400);
                    return $res;
                }
            }
            //legal_entity Required for creation of company
            if($POST['legal_entity']['type'] === 'company'){
                $required_keys = array("business_name","business_tax_id","business_vat_id","personal_address");
                $keys = array_keys($POST['legal_entity']);
                foreach($required_keys as $required_key){
                    if(!in_array($required_key, $keys) || empty($POST['legal_entity'][$required_key])){
                        $res = $res->withJson(array("err" => "Missing required key '".$required_key."' for legal_entity"),400);
                        return $res;
                    }
                }
            }
        }
        //TODO: upgrade Stripe version
        //Final edits for creation
        $POST['type'] = 'custom';
        //Create account with Stripe
        try {
            $account = \Stripe\Account::create($POST);
        } catch (\Stripe\Error\Base $e) {
            $res = $res->withJson(array("err"=>"Stripe Base error occured: ".$e->getMessage()),$e->getHttpStatus());
            return $res;
        }  catch(Exception $e){
            $res = $res->withJson(array("err" => "An error occurred within PHP: ".$e->getMessage()),500);
            return $res;
        }
        //Save to DB
        $result = update_user($args["id"],array("account_id"=>$account->id,"account_secret"=>$account->keys->secret,"account_publishable"=>$account->keys->publishable));
        if(!$result || $result["err"]){
            $res = $res->withJson(array("err" => "Account ceated with ID ".$account->id.", secret ".$account->keys->secret.", and publishable ".$account->keys->publishable." (note these down!) but saving to database failed: ".$result["err"]),$result["status"]);
            return $res;
        }
    } else {
        //Update account within Stripe
        $account = \Stripe\Account::retrieve($user['account_id']);
        function &getObjRef(&$obj,$prop) {
            return $obj->{$prop};
        }
        function updateObjFromArray(&$obj,$array){
            foreach ($array as $key=>$value) {
                if(!is_array($array[$key]))
                    $obj->{$key} = $array[$key]!==''?$array[$key]:null;
                else{
                    $ref = getObjRef($obj,$key);
                    if(!$ref){
                        $obj->{$key} = new \stdClass;
                        $ref = getObjRef($obj,$key);
                    }
                    updateObjFromArray($ref,$array[$key]);
                }
            }
        }
        updateObjFromArray($account,$POST);
        $account->external_account = (array)$account->external_account;
        try {
            $account->save();
        } catch (\Stripe\Error\Base $e) {
            $res = $res->withJson(array("err"=>"Stripe Base error occured here: ".$e->getMessage()),$e->getHttpStatus());
            return $res;
        }  catch(Exception $e){
            $res = $res->withJson(array("err" => "An error occurred within PHP here: ".$e->getMessage()),500);
            return $res;
        }
    }
    // Respond
    $res = $res->withJson($account,$user['account_id'] ? 200 : 201,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
    return $res;
}

$app->post('/{route:user/|me/|}account[/]','post_user_account_handler');

//$app->post('/users/{id:\d+}/account[/]',post_user_account_handler);

function get_user_account_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if($args['id']){
        if(!user_exists($args['id'])){
            $res = $res->withJson(array("err" => "user id ".$args['id']." not recognised"),404);
            return $res;
        }
        if($args['id'] !== $_SESSION['user']['id']){
            $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to get account details of user ".$args['id']),403);
            return $res;
        }
    } else $args['id'] = $_SESSION['user']['id'];
    $user = get_user($args['id']);
    if($user['account_id']){
        $account = \Stripe\Account::retrieve($user['account_id']);
        $res = $res->withJson($account,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
        return $res;
    } else{
        $res = $res->withJson(array("err" => "No account for user ".$args['id']),400);
        return $res;
    }
}

$app->get('/{route:user/|me/|}account[/]','get_user_account_handler');

function delete_user_account_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if($args['id']){
        if(!user_exists($args['id'])){
            $res = $res->withJson(array("err" => "user id ".$args['id']." not recognised"),404);
            return $res;
        }
        if($args['id'] !== $_SESSION['user']['id']){
            $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to get account details of user ".$args['id']),403);
            return $res;
        }
    } else $args['id'] = $_SESSION['user']['id'];

    $POST = $req->getParsedBody();
    if(!isset($POST['account_id']) || !isset($POST['account_secret'])){
        $res = $res->withJson(array("err" => "Must pass your account_id and account_secret to confirm deletion of an account"),400);
        return $res;
    }

    $user = get_user($args['id']);

    if($user['account_id']){
        if($POST['account_id'] !== $user['account_id'] || $POST['account_secret'] !== $user['account_secret']){
            $res = $res->withJson(array("err" => "account_id or account_secret do not match account"),400);
            return $res;
        }
        $account = \Stripe\Account::retrieve($user['account_id']);
        try {
            $result = $account->delete();
            if($result->deleted){
                $result2 = update_user($args["id"],array("account_id"=>null,"account_secret"=>"","account_publishable"=>""));
                if(!$result2["err"]){
                    $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
                    return $res;
                } else {
                    $res = $res->withJson(array("deleted"=>true,"err"=>"Database update failed: ".$result2["err"]),500,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
                    return $res;
                }
            } else {
                $res = $res->withJson($result,500,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
                return $res;
            }
        } catch(Exception $e){
            $res = $res->withJson(array("err"=>"Delete failed: ".$e->getMessage()),$e->getHttpStatus());
            return $res;
        }
    } else{
        $res = $res->withJson(array("err" => "No account for user ".$args['id']),400);
        return $res;
    }
}

$app->post('/{route:user/|me/|}account/delete[/]','delete_user_account_handler');

$app->delete('/{route:user/|me/|}account[/]','delete_user_account_handler');

//$app->delete('/users/{id:\d+}/account[/]',delete_user_account_handler);

/* ==================================================================================== *
   Database Events
 * ==================================================================================== */

function checkEventAllowed($POST){
    //Allowed
    $allowed_keys = array("id", "name", "host", "start", "venue", "address_1", "address_2", "city", "postcode", "end", "description", "image", "private", "fb_id", "user_id","created");
    $keys = array_keys($POST);
    foreach ($keys as $key) {
      if(!in_array($key, $allowed_keys)){
        return array("err"=>"Unrecognised key: ".$key,"status"=>400);
      }
    }
    return array("err"=>false,"status"=>200);
}

function isFacebookEvent($id){
    /*$url = "https://www.facebook.com/events/".$id;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch,  CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch,  CURLOPT_MAXREDIRS, 1);
    /* Get the HTML or whatever is linked in $url. *
    $response = curl_exec($ch);
    /* Check for 404 (file not found). *
    $httpCode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE))+0;
    curl_close($ch);
    return $httpCode < 400;*/
    global $fb;
    $event = false;
    try {
        $event = $fb->get('/'.$id.'?fields=start_time')->getDecodedBody();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      $event = false;
    }
    return ($event && $event['start_time']) ? true : false;
}

function validateEventFields($POST){
    //Valid checks
    if(isset($POST["id"]) && !preg_match("/^\d{9,11}$/", $POST["id"]))
        return array("err"=>"Malformed id given","status"=>400);
    if(isset($POST["name"]) && strlen($POST["name"]) < 2)
        return array("err"=>"name must be at least 2 charchters","status"=>400);
    if(isset($POST["host"]) && strlen($POST["host"]) < 2)
        return array("err"=>"host must be at least 2 charchters","status"=>400);
    if(isset($POST["start"]) && !isValidDate($POST["start"]))
        return array("err"=>"Could not parse start as date string","status"=>400);
    if(isset($POST["end"]) && !is_null($POST['end']) && !isValidDate($POST["end"]))
        return array("err"=>"Could not parse end as date string","status"=>400);
    if(isset($POST["address_1"]) && strlen($POST["address_1"]) < 3)
        return array("err"=>"Address line 1 must be at least 3 characters","status"=>400);
    if(isset($POST["address_2"]) && strlen($POST["address_2"]) < 4)
        return array("err"=>"Address line 2 must be at least 4 characters","status"=>400);
    if(isset($POST["description"]) && strlen($POST["description"]) < 32)
        return array("err"=>"description must be at least 32 characters","status"=>400);
    if(isset($POST["user_id"])){
        if(intval($POST["user_id"]) < 1)
            return array("err"=>"user_id must be an integer greater than 0","status"=>400);
        if(!user_exists(intval($POST["user_id"])))
            return array("err"=>"user_id ".intval($POST["user_id"])." not recognised","status"=>404);
    }

    if(isset($POST['image']) && !empty($POST['image'])){
        if(!remoteFileExists($POST['image']))
            return array("err"=>"image ".$POST['image']." not resolvable","status"=>400);
        try {
            $info = getimagesize($POST['image']);
            if($info[0]<320 || $info[1]<240)
                return array("err"=>"image dimensions of ".$info[0]."x".$info[1]." smaller than 320x240","status"=>400);
            $imageTypeArray = array(
                0=>'UNKNOWN',
                1=>'GIF',
                2=>'JPEG',
                3=>'PNG',
                4=>'SWF',
                5=>'PSD',
                6=>'BMP',
                7=>'TIFF_II',
                8=>'TIFF_MM',
                9=>'JPC',
                10=>'JP2',
                11=>'JPX',
                12=>'JB2',
                13=>'SWC',
                14=>'IFF',
                15=>'WBMP',
                16=>'XBM',
                17=>'ICO',
                18=>'COUNT'
            );
            if($info[2] !== 2 && $info[2] !== 3)
                return array("err"=>"Image filetype ".$imageTypeArray[$info[2]]." not allowed","status"=>400);
            if(!in_array($info["mime"],array(image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_JPEG))))
                return array("err"=>"Image MIME type ".$info['mime']." not allowed","status"=>400);
        } catch(Exception $e){
            return array("err"=>"Error reading image at ".$POST['image'].": ".$e->getMessage(),"status"=>400);
        }
        /*function save_to_file($url,$dir){
             // create new directory with 744 permissions if it does not exist yet
             // owner will be the user/group the PHP script is run under
             if (!file_exists($dir)){
                 $oldmask = umask(0);  // helpful when used in linux server  
                 mkdir($dir, 0744);
             }
            // create a new cURL resource
            $ch = curl_init();

            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_HEADER, 0);

            // grab URL and pass it to the browser
            $out = curl_exec($ch);

            // close cURL resource, and free up system resources
            curl_close($ch);

            $ext = preg_replace("/\\?[^\\?]*?$/i",'',strtolower(pathinfo($url, PATHINFO_EXTENSION)));

            $filepath = $dir.(substr($dir, -1)==='/'?'':'/').md5($url).".".$ext;
            $fp = fopen($filepath, 'w');
            fwrite($fp, $out);
            fclose($fp);
            return $filepath;
        }
        if(copy($POST['image'],'/var/www/img/tmp/'.md5($POST['image']).".".preg_replace("/\\?[^\\?]*?$/i",'',strtolower(pathinfo($url, PATHINFO_EXTENSION)))))
            die("success!");
        else
            die("failure!");
        $file_output = shell_exec("file '".$tmp."'");
        die($file_output);
        if(strpos($file_output, 'PNG') === false &&
           strpos($file_output, 'JPEG') === false
        ) {
            $res = $res->withJson(array("err" => "Filetype check on image failed"),400);
            return $res;
        }
        shell_exec("rm '".$tmp."'");*/
    }
    if(isset($POST["private"]) && !in_array($POST["private"], array(0,1,'0','1',true,false)))
        return array("err"=>"Unrecognised value for private, should be 0 (false) or 1 (true)","status"=>400);
    if(isset($POST["fb_id"]) && !isFacebookEvent($POST["fb_id"]))
        return array("err"=>"Invalid Facebook event id","status"=>400);
    if(isset($POST["created"]) && !isValidDate($POST["created"]))
        return array("err"=>"Could not parse created as date string","status"=>400);
    //END Valid checks
    return array("err"=>false,"status"=>200);
}

function clean_html($html){
    function DOMinnerHTML(DOMNode $element){ 
        $innerHTML = ""; 
        $children  = $element->childNodes;
        foreach ($children as $child) { 
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return $innerHTML; 
    } 
    function clean_node($node,$doc){
        $allowed_tags = array("strong","em","blockquote","q","del","ins","a","b","u","i","span","p","br","ul","ol","li","table","tbody","tr","th","td","tfoot");
        $remove_content_tags = array("script","applet","style","link","iframe","frame","frameset");

        if(in_array($node->nodeType,array(2,8))){
            $node->parentNode->removeChild($node);
            return;
        } else if($node->nodeType === 3)
            return;
        if(in_array($node->tagName,$remove_content_tags)){
            $node->parentNode->removeChild($node);
            return;
        }
        if($node->childNodes){
          foreach ($node->childNodes as $child)
            clean_node($child,$doc);
        }
        if(!in_array($node->tagName,$allowed_tags)){
            $frag = $doc->createDocumentFragment();
            $frag->appendXML(DOMinnerHTML($node));
            $node->parentNode->replaceChild($frag,$node);
        } else {
            if($node->tagName === 'a'){
              $href = $node->getAttribute('href');
              if(!preg_match("/^\s*(http:|https:)?\\/\\//i", $href)){
                $href = null;
              }
            }
            $attributes = $node->attributes;
            while ($attributes->length) {
                $node->removeAttribute($attributes->item(0)->name);
            }
            if($href){
                $node->setAttribute("href",$href);
                $node->setAttribute("target","_blank");
                $node->setAttribute("rel","nofollow");
                $href = null;
            }
        }
    }
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->loadHTML($html);
    $doc->normalizeDocument();
    $body = $doc->getElementsByTagName('body')[0];
    foreach ($body->childNodes as $node) {
      clean_node($node,$doc);
    }
    return DOMinnerHTML($body);
}

function sanitiseEventFields($POST){
    if(isset($POST["start"]))
        $POST["start"] = toSqlDate($POST["start"]);
    if(isset($POST["end"]))
        $POST["end"] = toSqlDate($POST["end"]);
    if(isset($POST["description"]))
        $POST["description"] = clean_html($POST["description"]);
    if(isset($POST["user_id"]))
        $POST["user_id"] = intval($POST["user_id"]);
    if(isset($POST["private"]))
        $POST["private"] = in_array($POST["private"], array(1,'1',true)) ? 1 : 0;
    if(isset($POST["created"]))
        $POST["created"] = toSqlDate($POST["created"]);
    return $POST;
}

$app->post('/events[/]', function (Request $req, Response $res) {
    if(!isLoggedIn())
      return add401AndJson($res);

    $POST = $req->getParsedBody();
    
    $allowed = checkEventAllowed($POST);
    if(!$allowed || $allowed["err"]){
        $res = $res->withJson(array("err" => $allowed["err"]),$allowed["status"]);
        return $res;
    }

    //Disallowed
    if(isset($POST["id"])){
        $res = $res->withJson(array("err" => "Cannot create event with id"),400);
        return $res;
    }
    if(isset($POST["user_id"]) && $POST["user_id"] !== $_SESSION['user']['id']){
        $res = $res->withJson(array("err" => "User ID mismatch, cannot create as a different user"),400);
        return $res;
    }
    if(isset($POST["created"])){
        $res = $res->withJson(array("err" => "Cannot edit created value"),400);
        return $res;
    }

    //Check required
    $keys = array("name","host","start","address_1","description");
    foreach ($keys as $key) {
      if(empty($POST[$key])){
        $res = $res->withJson(array("err" => "Missing required: ".$key),400);
        return $res;
      }
    }

    //Valid checks
    $valid = validateEventFields($POST);
    if(!$valid || $valid["err"]){
        $res = $res->withJson(array("err" => $valid["err"]),$valid["status"]);
        return $res;
    }

    // Time common sense checks
    if( isset($POST['end']) && !is_null($POST['end']) ){
        $start = DateTime::createFromFormat('d/m/y H\:i', $POST['start']);
        $end = DateTime::createFromFormat('d/m/y H\:i', $POST['end']);
        if($start > $end){
            $res = $res->withJson(array("err" => "Start time '".$POST['start']."' cannot be greater than end time '".$POST['end']."'"),400);
            return $res;
        }
    }

    $POST = sanitiseEventFields($POST);
    if(!$POST['user_id'])
        $POST['user_id'] = $_SESSION['user']['id'];

    $result = insert_event($POST);

    // Respond
    if($result && !$result["err"]){
      $res = $res->withJson($result,201,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
});

function get_event_handler(Request $req, Response $res, $args){
    if(!event_exists($args['id'])){
        $res = $res->withJson(array("err" => "event id ".$args['id']." not recognised"),404);
        return $res;
    }
    $result = get_event($args["id"]);

    // Respond
    if($result && !$result["err"]){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
}

$app->get('/events/{id:\d{8,16}}[/]','get_event_handler');

$app->get('/event/{id:\d{8,16}}[/]','get_event_handler');

$app->get('/events[/]',function(Request $req, Response $res, $args){
    $result = get_events();
    // Respond
    if(is_array($result) && !$result["err"]){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
});

$app->get('/{route:me|user}/events[/]',function(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    $result = get_user_upcoming_events();
    // Respond
    if(is_array($result)){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
});

function post_event_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!event_exists($args['id'])){
        $res = $res->withJson(array("err" => "event id ".$args['id']." not recognised"),404);
        return $res;
    }
    $event = get_event($args['id']);
    if($_SESSION['user']['id'] !== $event['user_id']){
      $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to edit user ".$event['user_id']."'s event."),403);
      return $res;
    }

    $POST = $req->getParsedBody();

    // Adds ability to set end time to null. EDIT: Should be null anyway if set to null.
    // if(isset($POST['end']) && in_array($POST['end'],array('null','NULL')))
    //     $POST['end'] = null;

    $allowed = checkEventAllowed($POST);
    if($allowed["err"]){
        $res = $res->withJson(array("err" => $allowed["err"]),$allowed["status"]);
        return $res;
    }

    //Disallowed
    $disallowed_keys = array("id","created");
    $keys = array_keys($POST);
    foreach($keys as $key){
       if(in_array($key, $disallowed_keys)){
        $res = $res->withJson(array("err" => "Cannot edit ".$key." value"),403);
        return $res;
       }
    }

    //Valid Checks
    $valid = validateEventFields($POST);
    if($valid["err"]){
        $res = $res->withJson(array("err" => $valid["err"]),$valid["status"]);
        return $res;
    }

    // Time common sense checks
    if( (isset($POST['start']) && ($POST['end'] || $event['end']))
          ||
        (isset($POST['end']) && !is_null($POST['end']))
    ){
        if(isset($POST['start']))
            $start = DateTime::createFromFormat('d/m/y H\:i', $POST['start']);
        else
            $start = DateTime::createFromFormat('Y-m-d H:i:s', $event['start']);

        if(isset($POST['end']))
            $end = DateTime::createFromFormat('d/m/y H\:i', $POST['end']);
        else
            $end = DateTime::createFromFormat('Y-m-d H:i:s', $event['end']);

        if($start > $end){
            $res = $res->withJson(array("err" => "Start time '".($start->format('d/m/y H\:i'))."' cannot be greater than end time '".($end->format('d/m/y H\:i'))."'"),400);
            return $res;
        }
    }

    $POST = sanitiseEventFields($POST);

    $result = update_event($args["id"],$POST);
    // Respond
    if($result && !$result["err"]){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
}
$app->post('/events/{id:\d{8,16}}[/]','post_event_handler');
$app->post('/event/{id:\d{8,16}}[/]','post_event_handler');

function get_event_image_handler(Request $req, Response $res, $args){
    if(!event_exists($args['id'])){
        $res = $res->withJson(array("err" => "event id ".$args['id']." not recognised"),404);
        return $res;
    }
    $event = get_event($args["id"]);
    if($event['image']){
        $ext = strtolower(explode('?',pathinfo($event['image'], PATHINFO_EXTENSION))[0]);
        $mime = '';
        switch($ext){
            case 'png':  $mime = image_type_to_mime_type(IMAGETYPE_PNG);
                         break;
            case 'jpg': 
            case 'jpeg': $mime = image_type_to_mime_type(IMAGETYPE_JPEG);
                         break;
        }
        $res = $res->withHeader('Content-type', $mime);
        $host = parse_url($event['image'],PHP_URL_HOST);
        $res->getBody()->write(file_get_contents(($host ? '' : '/var/www').$event['image']));
        return $res;
    } else {
        $res = $res->withJson(array("err" => "no image for event ".$args['id']),400);
        return $res;
    }
}
$app->get('/events/{id:\d{8,16}}/image[/]','get_event_image_handler');
$app->get('/event/{id:\d{8,16}}/image[/]','get_event_image_handler');

function post_event_image_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!event_exists($args['id'])){
        $res = $res->withJson(array("err" => "event id ".$args['id']." not recognised"),404);
        return $res;
    }
    $event = get_event($args["id"]);
    if($_SESSION['user']['id'] !== $event['user_id']){
      $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to edit user ".$event['user_id']."'s event image."),403);
      return $res;
    }

    $POST = $req->getParsedBody();

    // Allowed
    $allowed_keys = array("image");
    $keys = array_keys($POST);
    foreach ($keys as $key) {
      if(!in_array($key, $allowed_keys)){
        $res = $res->withJson(array("err"=>"Unrecognised key: ".$key." at /event/".$args["id"]."/image"),400);
        return $res;
      }
    }

    if(isset($POST['image']) && !empty($POST['image'])){
        if(!remoteFileExists($POST['image'])){
            $res = $res->withJson(array("err"=>"image ".$POST['image']." not resolvable"),400);
            return $res;
        }
        $result = update_event($args["id"],array("image"=>$POST['image']));
        // Respond
        if($result && ! $result["err"]){
          $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
          return $res;
        } else {
          $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
          return $res;
        }
    } else {
        $FILES = $req->getUploadedFiles();
        if (isset($FILES['image']) && $FILES['image'] instanceof \Psr\Http\Message\UploadedFileInterface) {
            // not OK yet, we only know the field is set
            $image = $FILES['image'];
            if ($image->getError() === \UPLOAD_ERR_OK) {
                // Now we know the file is uploaded succesfully
                $ext = strtolower(pathinfo($image->getClientFilename(), PATHINFO_EXTENSION));
                switch($ext){
                    case 'png':
                    case 'jpg':
                    case 'jpeg': break;
                    default: $res = $res->withJson(array("err" => "File extension .".$ext." not allowed"),400);
                             return $res;
                }
                $mime_type = $image->getClientMediaType();
                switch($mime_type){
                    case image_type_to_mime_type(IMAGETYPE_PNG):
                    case image_type_to_mime_type(IMAGETYPE_JPEG): break;
                    default: $res = $res->withJson(array("err" => "MIME type ".$mime_type." not allowed"),400);
                             return $res;
                }
                if( ($ext == 'png') && ($mime_type !== image_type_to_mime_type(IMAGETYPE_PNG)) ||
                    ($ext == 'jpg' || $ext == 'jpeg') && ($mime_type !== image_type_to_mime_type(IMAGETYPE_JPEG))
                ){
                  $res = $res->withJson(array("err" => "File extenstion/MIME type mismatch"),400);
                  return $res;
                }
                if($image->getSize() > 500000){
                  $res = $res->withJson(array("err" => "image exceeds max files size of 500kb"),400);
                  return $res;
                }
                $upload_dir = '/var/www/img/event/';
                $filename = $args['id'].'.'.$ext;

                try {
                    $image->moveTo($upload_dir.$image->getClientFilename());
                    /*if(move_uploaded_file($image->$file,$upload_dir.$filename)){
                      $res = $res->withJson(array("err" => "move_uploaded_file succeeded"),400);
                      return $res;
                    } else {
                      $res = $res->withJson(array("err" => "move_uploaded_file failed"),400);
                      return $res;
                    }*/
                    $file_output = shell_exec("file '".$upload_dir.$image->getClientFilename()."'");
                    if(strpos($file_output, 'PNG') === false &&
                       strpos($file_output, 'JPEG') === false
                    ) {
                        shell_exec("rm '".$upload_dir.$image->getClientFilename()."'");
                        $res = $res->withJson(array("err" => "Filetype check on image failed"),400);
                        return $res;
                    }
                    rename($upload_dir.$image->getClientFilename(), $upload_dir.$filename);
                } catch(Exception $e){
                    $res = $res->withJson(array("err" => "Caught: ".$e->getMessage()),500);
                    return $res;
                }
                $image_url = '/img/event/'.$filename;
                $result = update_event($args["id"],array("image"=>$image_url));

                // Respond
                if($result && ! $result["err"]){
                  $GET = $req->getQueryParams();
                  if(isset($GET['iframe']) && $GET['iframe']){
                    $res = $res->withHeader('Content-type', 'text/html; charset=utf-8');
                    $string = '<script type="text/javascript">document.domain="tktpass.com";window.top.window[\''.$GET['iframe'].'\'](';
                    $string .= json_encode($result,JSON_PARTIAL_OUTPUT_ON_ERROR);
                    $string .= ');</script>';
                    $res->getBody()->write($string);
                    return $res;
                  } else {
                    $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
                    return $res;
                  }
                } else {
                  $res = $res->withJson(array("err" => "Upload success, however update failed: ".$result["err"]),$result["status"]);
                  return $res;
                }
       
            } else {
                $res = $res->withJson(array("err" => "Upload error"),400);
                return $res;
            }
        } else {
            $res = $res->withJson(array("err" => "No upload with name 'image' present"),400);
            return $res;
        }
    }
}
$app->map(['POST', 'PUT'], '/events/{id:\d{8,16}}/image[/]','post_event_image_handler');
$app->map(['POST', 'PUT'], '/event/{id:\d{8,16}}/image[/]','post_event_image_handler');

function delete_event_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!event_exists($args['id'])){
        $res = $res->withJson(array("err" => "event id ".$args['id']." not recognised"),404);
        return $res;
    }
    $event = get_event($args["id"]);
    if($_SESSION['user']['id'] !== $event['user_id']){
      $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to edit user ".$event['user_id']."'s event."),403);
      return $res;
    }

    $result = delete_event($args["id"]);
    // Respond
    if($result && !$result["err"]){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
}
$app->post('/events/{id:\d{8,16}}/delete[/]','delete_event_handler');
$app->delete('/events/{id:\d{8,16}}[/]','delete_event_handler');

$app->get('/events/{id:\d{8,16}}/activity[/]',function(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!event_exists($args['id'])){
        $res = $res->withJson(array("err" => "event id ".$args['id']." not recognised"),404);
        return $res;
    }
    $event = get_event($args["id"]);
    if($_SESSION['user']['id'] !== $event['user_id']){
      $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to see the activity of user ".$event['user_id']."'s event."),403);
      return $res;
    }

    $activity = get_event_activity($args["id"]);
    // Respond
    if($activity && !$activity["err"]){
      $res = $res->withJson($activity,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
});

$app->get('/events/{id:\d{8,16}}/stats[/]',function(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!event_exists($args['id'])){
        $res = $res->withJson(array("err" => "event id ".$args['id']." not recognised"),404);
        return $res;
    }
    $event = get_event($args["id"]);
    if($_SESSION['user']['id'] !== $event['user_id']){
      $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to see the stats of user ".$event['user_id']."'s event."),403);
      return $res;
    }

    $stats = get_event_stats($args["id"]);
    
    // Respond
    if($stats && !$stats["err"]){
      $res = $res->withJson($stats,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
});

/* ==================================================================================== *
   Database Ticket Types
 * ==================================================================================== */

function checkTicketTypeAllowed($ticket){
    //Allowed
    $allowed_keys = array("id", "event_id", "type", "name", "price", "quantity","sold","added");
    $keys = array_keys($ticket);
    foreach ($keys as $key) {
      if(!in_array($key, $allowed_keys)){
        return array("err"=>"Unrecognised key for ticket type: ".$key,"status"=>400);
      }
    }
    return array("err"=>false,"status"=>200);
}

function validateTicketTypeFields($ticket){
    //Valid checks
    if(isset($ticket["id"]) && !ticket_type_exists($ticket["id"]))
        return array("err"=>"id ".$ticket["id"]." not recognised","status"=>404);
    if(isset($ticket["event_id"]) && !event_exists($ticket["event_id"]))
        return array("err"=>"event_id ".$ticket["event_id"]." not recognised","status"=>404);
    if(isset($ticket["type"]) && (intval($ticket["type"]) < 0 || intval($ticket["type"]) > 2))
        return array("err"=>"ticket type should be 0 (free), 1 (paid) or 2 (donation)","status"=>400);
    if(isset($ticket["name"]) && strlen($ticket["name"]) < 3)
        return array("err"=>"name must be at least 3 charchters","status"=>400);
    if(isset($ticket["price"]) && (!is_numeric($ticket["price"]) || intval($ticket["price"]) < 0))
        return array("err"=>"price must be an integer greater than or equal to 0","status"=>400);
    if(isset($ticket["quantity"]) && (!is_numeric($ticket["quantity"]) || intval($ticket["quantity"]) <= 0))
        return array("err"=>"quantity must be an integer greater than 0","status"=>400);
    if(isset($ticket["sold"])){
        if(!is_numeric($ticket["sold"]) || intval($ticket["sold"]) < 0)
            return array("err"=>"sold must be an integer greater than or equal to 0","status"=>400);
        if($ticket["sold"] > $ticket["quantity"])
            return array("err"=>"sold must be an integer less than or equal to the quantity available","status"=>400);
    }
    if(isset($ticket["added"]) && !isValidDate($ticket["added"]))
        return array("err"=>"Could not parse added as date string","status"=>400);
    //END Valid checks
    return array("err"=>false,"status"=>200);
}

function sanitiseTicketTypeFields($ticket){
    if(isset($ticket["type"]))
        $ticket["type"] = intval($ticket["type"]);
    if(isset($ticket["price"]))
        $ticket["price"] = intval($ticket["price"]);
    if(isset($ticket["quantity"]))
        $ticket["quantity"] = intval($ticket["quantity"]);
    if(isset($ticket["sold"]))
        $ticket["sold"] = intval($ticket["sold"]);
    if(isset($ticket["added"]))
        $ticket["added"] = toSqlDate($ticket["added"]);
    return $ticket;
}

$app->put('/events/{event_id:\d{8,16}}/tickets[/]', function (Request $req, Response $res, $args) {
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!event_exists($args['event_id'])){
        $res = $res->withJson(array("err" => "event id ".$args['event_id']." not recognised"),404);
        return $res;
    }
    $event = get_event($args['event_id']);
    if($_SESSION['user']['id'] !== $event['user_id']){
        $res = $res->withJson(array("err" => "user ".$_SESSION['user']['id']." does not have permission to edit tickets on this event"),403);
        return $res;
    }

    $PUT = $req->getParsedBody();
    if(!isset($PUT["tickets"])){
        $res = $res->withJson(array("err" => "tickets key not set"),400);
        return $res;
    }
    $tickets = $PUT["tickets"];
    if(!$tickets || !is_array($tickets) || !count($tickets)){
        $res = $res->withJson(array("err" => "tickets not valid array or is empty"),400);
        return $res;
    }
    $tickets = array_values($tickets);

    foreach ($tickets as $i=>$ticket){
    
        $allowed = checkTicketTypeAllowed($ticket);
        if($allowed["err"]){
            $res = $res->withJson(array("err" => $allowed["err"]." at tickets index ".$i), $allowed["status"]);
            return $res;
        }

        //Disallowed
        if(isset($ticket["event_id"]) && ($ticket["event_id"] !== $args['event_id'])){
            $res = $res->withJson(array("err" => "Event ID mismatch, event_id set at tickets index ".$i." does not match url target"),400);
            return $res;
        }
        if(isset($ticket["added"])){
            $res = $res->withJson(array("err" => "Cannot edit added date, at tickets index ".$i),400);
            return $res;
        }

        //Check required
        $keys = array("type","name","price","quantity");
        foreach ($keys as $key) {
          if(empty($ticket[$key]) && !($key === "type" && $ticket["type"]==="0") && !($key === "price" && ($ticket["type"]==="0" || $ticket["type"]==="2"))){
            $res = $res->withJson(array("err" => "Missing required: ".$key.", at tickets index ".$i),400);
            return $res;
          }
        }

        //Valid checks
        $valid = validateTicketTypeFields($ticket);
        if($valid["err"]){
            $res = $res->withJson(array("err" => $valid["err"].", at tickets index ".$i),$valid["status"]);
            return $res;
        }

        $tickets[$i] = sanitiseTicketTypeFields($ticket);
        $tickets[$i]['event_id'] = $args['event_id'];
    }
    $result = put_event_ticket_types($args['event_id'], $tickets);
    // Respond
    if(is_array($result) && !$result["err"]){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
});

$app->get('/events/{event_id:\d{8,16}}/tickets[/]', function (Request $req, Response $res, $args) {
    $result = get_event_ticket_types($args['event_id']);
    foreach($result as $i=>$ticket){
        $result[$i]['sold'] = get_ticket_type_sold($ticket["id"]);
    }
    // Respond
    if(is_array($result) && !$result["err"]){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result['status']);
      return $res;
    }
});

/* ==================================================================================== *
   Database Tickets
 * ==================================================================================== */

function getBookingFee($total, $plan){
    if($plan === "tktpass_freedom")
        return 0;
    if($plan === "tktpass_flexible")
        return max(round(0.07*$total),30);
    else
        return max(round(0.1*$total),30);
}

function checkOrderAllowed($post){
    //Allowed
    $allowed_keys = array("order","source");
    $keys = array_keys($post);
    foreach ($keys as $key) {
      if(!in_array($key, $allowed_keys)){
        return array("err"=>"Unrecognised key for order request: ".$key,"status"=>400);
      }
    }
    $allowed_keys = array("id","event_id","type","name","price","quantity","added","available","saving");
    foreach ($post['order'] as $i=>$ticket) {
      $keys = array_keys($ticket);
      foreach ($keys as $key) {
        if(!in_array($key, $allowed_keys)){
          return array("err"=>"Unrecognised key for ticket at index ".$i." in order request: ".$key,"status"=>400);
        }
      }
    }
    return array("err"=>false,"status"=>200);
}

function checkOrderRequired($order){
    //Allowed
    $required_keys = array("order");
    foreach ($required_keys as $key) {
      if(!array_key_exists($key, $order) && $order[$key]){
        return array("err"=>"Missing required key '".$key."' for order request","status"=>400);
      }
    }
    $required_keys = array("quantity","id","event_id");
    foreach ($post['order'] as $i=>$ticket) {
      foreach ($required_keys as $key) {
        if(!in_array($key, $ticket)){
          return array("err"=>"Missing required key '".$key."' for ticket at index ".$i." in order request","status"=>400);
        }
      }
    }
    return array("err"=>false,"status"=>200);
}

$app->post('/order[/]', function (Request $req, Response $res, $args) {
    if(!isLoggedIn())
      return add401AndJson($res);
    $POST = $req->getParsedBody();
    $allowed = checkOrderAllowed($POST);
    if(!$allowed || $allowed["err"]){
        $res = $res->withJson(array("err" => $allowed["err"]),$allowed["status"]);
        return $res;
    }
    $required = checkOrderRequired($POST);
    if(!$required || $required["err"]){
        $res = $res->withJson(array("err" => $required["err"]),$required["status"]);
        return $res;
    }
    $user= get_user();
    $order = array();
    $total = 0;
    $addPlan = false;
    foreach ($POST['order'] as $i=>$ticket){
        $ticket['quantity'] = intval($ticket["quantity"]);
        if($ticket['quantity'] > 0){
            if(ticket_type_exists($ticket['id'])){
                $ticket_db = get_ticket_type($ticket['id']);
                if($ticket['event_id'] !== $ticket_db['event_id']){
                    $res = $res->withJson(array("err" => "Incorrect event_id given on ticket with id ".$ticket['id']),400);
                    return $res;
                }
                $sold = get_ticket_type_sold($ticket_db['id']);
                if($ticket['quantity'] > $ticket_db['quantity']-$sold){
                    //TODO: Return a more user-friendly message here, ticket names rather than IDs
                    if($ticket_db['quantity']-$sold<1)
                        $res = $res->withJson(array("err" => "Ticket ".$ticket['event_id'].'-'.$ticket['id']." sold out"),400);
                    else
                        $res = $res->withJson(array("err" => "Only ".($ticket_db['quantity']-$sold).' '.$ticket['event_id'].'-'.$ticket['id']." tickets left, ".$ticket['quantity']." requested"),400);
                    return $res;
                }
                $ticket_db["total_for_sale"] = $ticket_db["quantity"];
                $ticket_db["quantity"] = $ticket["quantity"];
                $ticket = array_merge($ticket,$ticket_db);
                $total += $ticket['quantity']*$ticket["price"];
                array_push($order, $ticket);
            } else {
                if($ticket['id'] === 'tktpass_flexible_promo')
                    $addPlan = 'tktpass_flexible';
                if($ticket['id'] === 'tktpass_freedom_promo')
                    $addPlan = 'tktpass_freedom';
            }
        }
    }
    if(!count($order)){
        $res = $res->withJson(array("err" => "Empty order"),400);
        return $res;
    }
    $eventId = $order[0]["event_id"];
    foreach ($order as $ticket) {
        if($ticket["event_id"] !== $eventId){
            $res = $res->withJson(array("err" => "Multiple event purchasing not currently available"),400);
            return $res;
        }
    }
    $event = get_event($eventId);
    // If we're purchasing one or more not-free tickets
    if($total){
        $customerId = get_customer_id($user['id']);
        $customer = null;
        // If already connected with Stripe, get customer object
        if($customerId){
            if(is_array($customerId) && $customerId["err"]){
                $res = $res->withJson(array("err" => $customerId["err"]),$customerId["status"]);
                return $res;
            }
            try{
                $customer = \Stripe\Customer::retrieve($customerId);
            } catch(Exception $e){
                $res = $res->withJson(array("err" => "Invalid customer ID"),500);
                return $res;
            }
        }
        $token = null;
        if(isset($POST["source"])){
            try{
                $token = \Stripe\Token::retrieve($POST['source']);
            } catch(Exception $e){
                $res = $res->withJson(array("err" => "Provided invalid card details token"),400);
                return $res;
            }
        }
        if($token){
            // If not connected with Stripe, create new customer now
            if(!$customer){
                $customerDeets = array(
                  "description" => $user['first_name'].' '.$user['last_name']." (".$user['email'].")",
                  "email" => $user['email'],
                  "metadata" => array("user_id" => $user['id']),
                  "source" => $token->id
                );
                if($user['mobile'])
                    $customerDeets["shipping"] = array("phone"=>$user['mobile']);
                $customer = \Stripe\Customer::create($customerDeets);
                update_user($_SESSION['user']['id'],array("customer_id"=>$customer->id));
                $_SESSION['user']['customer_id'] = $customer->id;
            } else {
                try {
                    $customer->source = $token;
                    $customer->save();
                } catch(\Stripe\Error\Card $e) {
                    // If it's a decline, \Stripe\Error\Card will be caught
                    $body = $e->getJsonBody();
                    $err  = $body['error'];
                    $res = $res->withJson(array("err" => $err['message'], "type" => $err['type'], "code" => $err['code'], "param" => $err['param']),$e->getHttpStatus());
                    return $res;
                } catch (\Stripe\Error\RateLimit $e) {
                    $res = $res->withJson(array("err"=>"Too many requests made to Stripe API too quickly"),$e->getHttpStatus());
                    return $res;
                } catch (\Stripe\Error\InvalidRequest $e) {
                    $res = $res->withJson(array("err"=>"Invalid parameters were supplied to Stripe\'s API: ".$e->getMessage()),$e->getHttpStatus());
                    return $res;
                } catch (\Stripe\Error\Authentication $e) {
                    $res = $res->withJson(array("err"=>"Authentication with Stripe\'s API failed: ".$e->getMessage()),$e->getHttpStatus());
                    return $res;
                  // (maybe you changed API keys recently)
                } catch (\Stripe\Error\ApiConnection $e) {
                    $res = $res->withJson(array("err"=>"Network communication with Stripe failed: ".$e->getMessage()),$e->getHttpStatus());
                    return $res;
                } catch (\Stripe\Error\Base $e) {
                    $res = $res->withJson(array("err"=>"Stripe Base error occured: ".$e->getMessage()),$e->getHttpStatus());
                    return $res;
                  // Display a very generic error to the user, and maybe send
                  // yourself an email
                } catch (Exception $e) {
                  // Something else happened, completely unrelated to Stripe
                    $res = $res->withJson(array("err"=>"An error occured within PHP: ".$e->getMessage()),$e->getHttpStatus());
                    return $res;
                }
            }
        }
        // If no source token, must have connected with Stripe and paid with a source before
        else if(!$customer || !$customer->sources->total_count){
            $res = $res->withJson(array("err" => "No payment details found, customer ID is ".$customerId." source is ".$POST['source']),400);
            return $res;
        }

        $bookingFee = getBookingFee($total,$addPlan ? $addPlan : ($customer->subscriptions->total_count?$customer->subscriptions->data[0]->id:null));
        $description = "";
        $orderMeta = "[";
        //$cachedEvents = array();
        $len = count($order);
        foreach ($order as $i=>$ticket) {
            /*if(!$cachedEvents[$ticket['event_id']])
                $cachedEvents[$ticket['event_id']] = get_event($ticket['event_id']);
            $event_i = $cachedEvents[$ticket['event_id']];*/
            $description .= $ticket['quantity']."x ".$ticket['name'];
            $orderMeta .= '{"id":"'.$ticket['event_id'].'-'.$ticket['id'].'","quantity":'.$ticket['quantity'].'}';
            if ($i !== $len-1){
                $description .= ", ";
                $orderMeta .= ",";
            } else {
                $description .= " for ".$event["name"];
                $orderMeta .= "]";
            }
        }
        $organiser = get_user($event['user_id']);
        $account = \Stripe\Account::retrieve($organiser['account_id']);

        try {
            //TODO: New Stripe version compatable? Specifically, application_fee & destination?
            //TODO: Properly design "capture if verified" model
            $charge = \Stripe\Charge::create(array(
              "amount" => $total+$bookingFee,
              "customer" => $customer->id,
              "description" => $description,
              "metadata" => array("order"=>$orderMeta),
              "destination" => $account->id,
              "application_fee"=>$bookingFee,
              "currency"=>"GBP",
              "capture"=>($organiser["verified"] ? true : false)
            ));//, array("idempotency_key" => "oHO9hM0zlolNhzej")
        } catch(Stripe\Error\Card $e) {
          // If declined \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
            $err  = $body['error'];
            $res = $res->withJson(array("err" => $err['message'], "type" => $err['type'], "code" => $err['code'], "param" => $err['param']),$e->getHttpStatus());
            return $res;
        } catch (\Stripe\Error\RateLimit $e) {
            $res = $res->withJson(array("err"=>"Too many requests made to Stripe API too quickly"),$e->getHttpStatus());
            return $res;
        } catch (\Stripe\Error\InvalidRequest $e) {
            $res = $res->withJson(array("err"=>"Invalid parameters were supplied to Stripe\'s API: ".$e->getMessage()),$e->getHttpStatus());
            return $res;
        } catch (\Stripe\Error\Authentication $e) {
            $res = $res->withJson(array("err"=>"Authentication with Stripe\'s API failed: ".$e->getMessage()),$e->getHttpStatus());
            return $res;
          // (maybe you changed API keys recently)
        } catch (\Stripe\Error\ApiConnection $e) {
            $res = $res->withJson(array("err"=>"Network communication with Stripe failed: ".$e->getMessage()),$e->getHttpStatus());
            return $res;
        } catch (\Stripe\Error\Base $e) {
            $res = $res->withJson(array("err"=>"Stripe Base error occured: ".$e->getMessage()),$e->getHttpStatus());
            return $res;
          // Display a very generic error to the user, and maybe send
          // yourself an email
        } catch (Exception $e) {
          // Something else happened, completely unrelated to Stripe
            $res = $res->withJson(array("err"=>"An error occured within PHP: ".$e->getMessage()),$e->getHttpStatus());
            return $res;
        }
    }
    // If we've successfully paid, or are only ordering free tickets
    if(($charge && $charge->paid) || !$total){
        $tickets = array();
        $time = (new DateTime())->format('Y-m-d H:i');
        foreach ($order as $ticket) {
            $tryResell = true;
            for($i=0;$i<intval($ticket['quantity']);$i++) {
                $fields = array(
                    "event_ticket_type_id" => $ticket["id"],
                    "user_id" => $_SESSION['user']['id'],
                    "time" => $time
                );
                if($charge)
                  $fields["charge_id"] = $charge->id;
                $newTicket = insert_ticket($fields);
                //Check resell
                if($tryResell && ($soldTicket = get_reselling($ticket["id"]))){
                    if($ticket['price'] && $soldTicket["selling_price"]){
                        //Nice to combine refunds but would have to check for same user
                        try {
                          //TODO: Stripe upgrade compatiable?
                          $refund = \Stripe\Refund::create(array(
                              "charge" => $soldTicket["charge_id"],
                              "amount" => $soldTicket["selling_price"],
                              "reason" => "requested_by_customer",
                              "refund_application_fee" => false,
                              "reverse_transfer" => true
                          ));
                        } /*catch(Stripe\Error\Card $e) {
                            // If declined \Stripe\Error\Card will be caught
                            $body = $e->getJsonBody();
                            $err  = $body['error'];
                            $res = $res->withJson(array("err" => $err['message'], "type" => $err['type'], "code" => $err['code'], "param" => $err['param']),$e->getHttpStatus());
                            return $res;
                        } catch (\Stripe\Error\RateLimit $e) {
                            $res = $res->withJson(array("err"=>"Too many requests made to Stripe API too quickly"),$e->getHttpStatus());
                            return $res;
                        } catch (\Stripe\Error\InvalidRequest $e) {
                            $res = $res->withJson(array("err"=>"Invalid parameters were supplied to Stripe\'s API: ".$e->getMessage()),$e->getHttpStatus());
                            return $res;
                        } catch (\Stripe\Error\Authentication $e) {
                            $res = $res->withJson(array("err"=>"Authentication with Stripe\'s API failed: ".$e->getMessage()),$e->getHttpStatus());
                            return $res;
                          // (maybe you changed API keys recently)
                        } catch (\Stripe\Error\ApiConnection $e) {
                            $res = $res->withJson(array("err"=>"Network communication with Stripe failed: ".$e->getMessage()),$e->getHttpStatus());
                            return $res;
                        } catch (\Stripe\Error\Base $e) {
                            $res = $res->withJson(array("err"=>"Stripe Base error occured: ".$e->getMessage()),$e->getHttpStatus());
                            return $res;
                          // Display a very generic error to the user, and maybe send
                          // yourself an email
                        }*/ catch (Exception $e) {
                          sendAdminEmail("Failed to refund ticket ".$soldTicket["id"]." for new ticket ".$newTicket['id'].", an error was thrown.", array(
                            "refund" => $refund,
                            "exception" => $e
                          ));
                        }
                        if(in_array($refund->status, array("failed", "cancelled"))){
                          sendAdminEmail("Failed to refund ticket ".$soldTicket["id"]." for new ticket ".$newTicket['id'].", status returned as ".($refund->status).".", array(
                            "refund" => $refund
                          ));
                        }
                    }
                    //TODO: Remove data redundacy from database design
                    $fields = array("sold_ticket"=>$newTicket["id"]);
                    //TODO: Really okay to overwrite charge_id here? Not want to add a column so can keep both charge_id and refund_id?
                    if($refund)
                      $fields["charge_id"] =$refund->id;
                    $result = update_ticket($soldTicket["id"],$fields);
                    if(!$result || $result["err"]){
                        sendAdminEmail("Error updating resold ticket with ID ".$soldTicket['id']." to link to new ticket with ID ".$newTicket["id"].($result["err"]?". Error says: ".$result["err"]:"."), array(
                          "soldTicket" => $soldTicket,
                          "newTicket" => $newTicket,
                          "result" => $result
                        ));
                    } else $soldTicket = $result;
                    $result = update_ticket($newTicket["id"],array("bought_ticket"=>$soldTicket["id"]));
                    if(!$result || $result["err"]){
                        sendAdminEmail("Error updating new ticket with ID ".$newTicket['id']." to link to resold old ticket with ID ".$soldTicket["id"].($result["err"]?". Error says: ".$result["err"]:"."), array(
                          "soldTicket" => $soldTicket,
                          "newTicket" => $newTicket,
                          "result" => $result
                        ));
                    } else $newTicket = $result;
                } else $tryResell = false;
                array_push($tickets, $newTicket);
            }
        }
        if(!sendBookingEmail($user,$event,$order,$tickets)){
            sendAdminEmail("User ".$user['id']." successfully bought tickets but their booking email failed to send.", array(
              "user" => $user,
              "event" => $event,
              "order" => $order,
              "tickets" => $tickets
            ));
        }
        if($addPlan){
            //TODO: Stripe upgrade compatiable?
            try {
              if($customer->subscriptions && $customer->subscriptions->total_count){
                $subscription = \Stripe\Subscription::retrieve($customer->subscriptions->data[0]->id);
                $subscription->plan = $addPlan;
                $subscription->save();
              } else {
                $subscription = \Stripe\Subscription::create(array(
                  "customer" => $customer->id,
                  "plan" => $addPlan
                ));
                $subscription->save();
              }
            } catch (Exception $e) {
                sendAdminEmail("User ".$user['id']." successfully bought tickets and email sent, but changing their plan to '".$addPlan."' failed.", array(
                  "user" => $user,
                  "customer" => $customer,
                  "addPlan" => $addPlan,
                  "exception" => $e,
                  "trace" => $e->getTraceAsString(),
                  "msg"=> $e->getMessage()
                ));
            }
        }
        $res = $res->withJson($tickets,201);
        return $res;
    } else {
        sendAdminEmail("User ".$user['id']." tried to buy tickets but the charge failed for some reason.", array(
          "user" => $user,
          "event" => $event,
          "order" => $order,
          "charge" => $charge
        ));
        $res = $res->withJson(array("err"=>"Unknown error occured making charge. Try again later.","id"=>$charge->id),500);
        return $res;
    }
});

function get_ticket_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!ticket_exists($args['id'])){
        $res = $res->withJson(array("err" => "Ticket id ".$args['id']." not recognised"),404);
        return $res;
    }
    $ticket = get_ticket($args["id"]);
    if(!$ticket || $ticket["err"]){
      $res = $res->withJson(array("err" => $ticket["err"]),$ticket["status"]);
      return $res;
    }
    if($ticket['user_id'] !== $_SESSION['user']['id']){
      $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to view ticket with id ".$args['id']),400);
      return $res;
    }
    $type = get_ticket_type($ticket['event_ticket_type_id']);
    $event = get_event($type['event_id']);
    $event["event_ticket_type_id"] = $type["id"];
    $event["event_ticket_type_name"] = $type["name"];
    $event["event_ticket_type_price"] = $type["price"];
    $event["event_id"] = $event["id"];
    $event["id"] = $ticket["id"];
    $res = $res->withJson($event,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
    return $res;
}

$app->get('/tickets/{id:\w{8}}[/]','get_ticket_handler');

$app->get('/ticket/{id:\w{8}}[/]','get_ticket_handler');

function get_tickets_handler(Request $req, Response $res, $args){
    if(!isLoggedIn())
      return add401AndJson($res);
    $ids = array();
    $params = $req->getQueryParams();
    if(!$params || !(array_key_exists("ids",$params) || array_key_exists("id",$params))){
        $res = $res->withJson(array("err" => "No ticket ID(s) provided"),400);
        return $res;
    }
    if(array_key_exists("ids",$params) && $params["ids"]){
        $ids = explode(',',$params["ids"]);
    }
    if(array_key_exists("id",$params) && $params["id"]){
        $ids = array_merge($ids, explode(',',$params["id"]));
    }
    if(empty($ids)){
        $res = $res->withJson(array("err" => "No ticket ID(s) provided"),400);
        return $res;
    }
    $exists = array();
    foreach($ids as $i=>$id){
      if(ticket_exists($id)){
        array_push($exists, $id);
      }
    }
    if(empty($exists)){
        $res = $res->withJson(array("err" => "All ticket IDs provided are not recognised"),404);
        return $res;
    }
    $result = get_tickets($exists);
    if(!$result || $result["err"]){
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
    $tickets = array();
    foreach($result as $i=>$ticket){
        if($ticket['user_id'] === $_SESSION['user']['id']) array_push($tickets, $ticket);
    }
    if(!$tickets){
      $res = $res->withJson(array("err" => "All ticket IDs provided are not recognised"),404);
      return $res;
    }
    $types = array();
    foreach($tickets as $i=>$ticket){
      if(!array_key_exists($ticket['event_ticket_type_id'], $types)){
        $types[$ticket['event_ticket_type_id']] = get_ticket_type($ticket['event_ticket_type_id']);
      }
    }
    $events = array();
    $result = array();
    foreach($tickets as $ticket){
      $type = $types[$ticket['event_ticket_type_id']];
      if(!array_key_exists($type['event_id'], $events)){
        $events[$type['event_id']] = get_event($type['event_id']);
      }
      if(!array_key_exists($type['id'], $result)){
        $result[$type['id']] = $events[$type['event_id']];
        $result[$type['id']]["event_ticket_type_id"] = $type["id"];
        $result[$type['id']]["event_ticket_type_name"] = $type["name"];
        $result[$type['id']]["event_ticket_type_price"] = $type["price"];
        $result[$type['id']]["quantity"] = 1;
        $result[$type['id']]["ids"] = $ticket["id"];
      } else {
        $result[$type['id']]["quantity"] += 1;
        $result[$type['id']]["ids"] .= ",".$ticket["id"];
      }
    }
    $result = array_values($result);
    $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
    return $res;
}

$app->get('/tickets[/]','get_tickets_handler');

$app->get('/{route:me/|user/|}tickets[/]',function(Request $req, Response $res, $args){
    $result = get_user_tickets();
    // Respond
    if($result && !$result["err"]){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
});

$app->get('/{route:me/|user/|}tickets/upcoming[/]',function(Request $req, Response $res, $args){
    $result = get_user_upcoming_tickets();
    // Respond
    if(is_array($result)){
      $res = $res->withJson($result,200,$req->isXhr()?JSON_PARTIAL_OUTPUT_ON_ERROR:JSON_PRETTY_PRINT);
      return $res;
    } else {
      $res = $res->withJson(array("err" => $result["err"]),$result["status"]);
      return $res;
    }
});

$app->post('/resell/{id:[\w\d]{6,16}}[/]', function (Request $req, Response $res, $args) {
    if(!isLoggedIn())
      return add401AndJson($res);
    if(!ticket_exists($args['id'])) {
      $res = $res->withJson(array("err" => "Ticket id ".$args['id']." not recognised"),404);
      return $res;
    }
    $ticket = get_ticket($args['id']);
    if($ticket['user_id'] !== $_SESSION['user']['id']){
      $res = $res->withJson(array("err" => "User ".$_SESSION['user']['id']." does not have permission to resell ticket with id ".$args['id']),403);
      return $res;
    }
    $POST = $req->getParsedBody();

    // Adds ability to set price to null. EDIT: Should be null anyway if set to null.
    // if(isset($POST['price']) && in_array($POST['price'],array('null','NULL')))
    //     $POST['price'] = null;

    if( !is_null($POST['price']) && $POST['price']!==0 && (!$POST['price'] || !is_numeric($POST['price']) || intval($POST['price'])<0) ){
      $res = $res->withJson(array("err" => "Invalid price '".$POST['price']."' provided"),400);
      return $res;
    }
    if(!is_null($POST['price']))
        $POST['price'] = intval($POST['price']);
    $ticket_type = get_ticket_type($ticket["event_ticket_type_id"]);
    if($POST['price'] && $POST['price'] > $ticket_type["price"])
        $POST['price'] = $ticket_type["price"];
    $updated = update_ticket($ticket['id'],array(
        "selling_time" => is_null($POST['price']) ? null : (new DateTime())->format('Y-m-d H:i:s'),
        "selling_price" => $POST['price'])
    );
    if($updated && !$updated["err"]){
        $res = $res->withJson($updated,200);
        return $res;
    } else {
        $res = $res->withJson(array("err"=>$updated["err"]?$updated["err"]:'Unknown error'),500);
        return $res;
    }
});

$app->run();