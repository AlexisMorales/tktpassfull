<?php

session_start();	
	
require_once 'api/paths.php';
require_once API.'config.php';
require_once ROOT.'/vendor/autoload.php';

global $fb;

$next = $_GET['next'] ? $_GET['next'] : URL;

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

if (isset($accessToken)) {
  // Logged in!
	$_SESSION['facebook_access_token'] = (string) $accessToken;
  
	$reqPerms = array();
    $perms = $fb->get("/me/permissions", $_SESSION['facebook_access_token'] );
    
	$perms = $perms->getDecodedBody();
	
	foreach($perms['data'] AS $perm) {
    	if($perm['status']=='declined') $reqPerms[] = $perm['permission'];
    }
    
    if (count($reqPerms))
    {
	    $helper = $fb->getRedirectLoginHelper();
		$loginUrl = $helper->getLoginUrl(URL.'/fb-callback', $reqPerms);
		header( "Location: " . $loginUrl."&auth_type=rerequest");
		exit;
	}
	  
	try {
	  $response = $fb->get('/me?fields=id,name,first_name,last_name,email,birthday,gender,picture', $_SESSION['facebook_access_token'] );
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  echo 'Graph returned an error: ' . $e->getMessage();
	  exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  exit;
	}
	
	$user = $response->getGraphUser();
	$propsNames = $user->getPropertyNames();	
	
	foreach ($propsNames as $property) {
		$_SESSION['user'][$property] = $user->getProperty($property);
	}
	
	$_SESSION['user']['fb_id'] = $_SESSION['user']['id'];
	$_SESSION['user']['picture'] = $_SESSION['user']['picture']['url'];
	
	global $mysqli;
	$query = "SELECT id FROM users WHERE (fb_id = ?)";
	$stmt = $mysqli -> prepare($query);
	$stmt -> bind_param("s", $_SESSION['user']['fb_id']);
	$stmt -> execute();
	$stmt->store_result();

	if ($stmt->num_rows)
	{
		$stmt->bind_result($id);
		$stmt->fetch();
		$_SESSION['user']['id'] = $id;
		$stmt->close();

		$query = "UPDATE users SET lastLoggedIn = UNIX_TIMESTAMP() WHERE id = ?";
		$stmt = $mysqli -> prepare($query);
		$stmt -> bind_param("s", $_SESSION['user']['id']);
		$stmt -> execute();
		$stmt->close();
		
		if (!isset($user['email']) || $user['email'] == NULL){
			$query = "SELECT email FROM users WHERE (fb_id = ?)";
			$stmt = $mysqli -> prepare($query);
			$stmt -> bind_param("s", $_SESSION['user']['fb_id']);
			$stmt -> execute();
			$stmt->store_result();
			$stmt->bind_result($email);
			$stmt->fetch();
			$_SESSION['user']['email'] = $email;
			$stmt->close();
		}
	}
	else
	{
		$stmt->close();
		$query = "INSERT INTO users (fb_id, email, lastLoggedIn, active) VALUES (?, ?, UNIX_TIMESTAMP(), ?)";
		$stmt = $mysqli -> prepare($query);
		$active = (isset($_SESSION['user']['email']) && $_SESSION['user']['email'] != NULL)? true : false;
		$email = $_SESSION['user']['email'] ?? "";
		$stmt -> bind_param("sss", $_SESSION['user']['fb_id'], $email, $active);
		$stmt -> execute();
		$stmt->close();
		
		if ($email==null) $_SESSION['msg']['active'] = "noemail";
		else if (!$active && $email!=null) $_SESSION['msg']['active'] = "sent";
		
		$query = "SELECT id FROM users WHERE (fb_id = ?)";
		$stmt = $mysqli -> prepare($query);
		$stmt -> bind_param("s", $_SESSION['user']['fb_id']);
		$stmt -> execute();
		$stmt->store_result();
		$stmt->bind_result($id);
		$stmt->fetch();
		$_SESSION['user']['id'] = $id;
		$stmt->close();
	}
	
	if (!isset($_SESSION['user']['email']) || $_SESSION['user']['email'] == NULL){
		echo "There is no verified email address associated with your Facebook account; perhaps because you have signed up using just a mobile number. <br>";
		echo "We need your email address to send you confirmation emails.<br>";
		echo "Please add an email address to your Facebook account and click <a href='".$next."'>here</a>. OR just send an email to support@tktpass.com" . PHP_EOL;
		unset($_SESSION['user']);
		die();
	}	
	
	header('Location: '.$next);
}

?>