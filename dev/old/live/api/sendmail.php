<?php	
require_once 'paths.php';
require_once ROOT.'/vendor/autoload.php';
require_once API.'config.php';
require_once 'IO.php';

function generateMessageID(){
  return sprintf(
    "<%s-%s@%s>",
    base_convert(microtime(), 10, 36),
    base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
    $_SERVER['SERVER_NAME']
  );
}

function confirmationMail($type) {
	$stamp = time();
	$event = getEvent($_POST['event_id']);
	$temDir = "../email-templates/";
	if ($_POST['event_id'] == 37) $temDir = "../email-templates-26/";
	if($type == "booking"){
		$userMessage="";
		if ($event['quantity'] <= 0){
			$userMessage =  file_get_contents($temDir.'user-waitlist-email.html');
			$userMessage = str_replace('%%FIRST_NAME%%',explode(' ', htmlspecialchars($_SESSION['user']['first_name']))[0],$userMessage);
			$userMessage = str_replace('%%EVENT%%', $event['title'],$userMessage);
			$userMessage = str_replace('%%QUANTITY%%',$_POST['quantity'] > 1 ? htmlspecialchars($_POST['quantity']).' tickets' : '1 ticket',$userMessage);
		}
		else{
				$userMessage =  file_get_contents($temDir.'user-order-email.html');
				$userMessage = str_replace('%%FIRST_NAME%%',explode(' ', htmlspecialchars($_SESSION['user']['first_name']))[0],$userMessage);
				$userMessage = str_replace('%%EVENT%%', $event['title'],$userMessage);
				$userMessage = str_replace('%%QUANTITY%%',$_POST['quantity'] > 1 ? htmlspecialchars($_POST['quantity']).' tickets' : '1 ticket',$userMessage);
				
				
				
				if ($_POST['event_id'] != 37)
				{
				$userMessage = str_replace('%%LOCATION%%','Terrace Bar',$userMessage);
				$pickUp = null;
				if ($_POST['event_id']==27)
					$pickUp = 'Monday 5:00-6:00pm';
				else
				{
					if(
						intval(date("w",$stamp)) < 4 ||
						(intval(date("w",$stamp)) == 4 && intval(date("G",$stamp)) < 13) ||
						(intval(date("w",$stamp)) == 4 && intval(date("G",$stamp)) == 13 && intval(date("i",$stamp)<30)) ||
						intval(date("w",$stamp)) > 5 ||
						(intval(date("w",$stamp)) == 5 && intval(date("G",$stamp)) > 19) ||
						(intval(date("w",$stamp)) == 5 && intval(date("G",$stamp)) == 19 && intval(date("i",$stamp)>=30))
					) $pickUp = 'Thursday 12:00-1:30pm';
					else
						$pickUp = 'Friday 4:00-5:30pm';
				}
				$userMessage = str_replace('%%TIME%%',$pickUp,$userMessage);
			
			}
	   }

	   $headers = 'MIME-Version: 1.0' . PHP_EOL .
			'Content-Type: text/html; charset=UTF-8' . PHP_EOL .	  
			'From: tktpass <orders@tktpass.com>' . PHP_EOL .
			'Reply-To: tktpass Team <orders@tktpass.com>' . PHP_EOL .
			'To: '. $_SESSION['user']['email'] . PHP_EOL . 
			'Subject: =?utf-8?B?'. base64_encode('tktpass | '. $event['title']) ."?=" . PHP_EOL .
			'Date: ' . date(DATE_RFC2822, $stamp) . PHP_EOL .
			'Envelope-To: '. $_SESSION['user']['email'] .PHP_EOL .
			'Message-ID: ' . generateMessageID() . PHP_EOL .
			'Content-Transfer-Encoding: 8bit' . PHP_EOL .
			'X-Priority: 3' . PHP_EOL;
	  	
	  	
	  	if (isset($_SESSION['user']['email']) && $_SESSION['user']['email'] != NULL){
	   		$client_email = 'tktpass-mail@tktpass-1199.iam.gserviceaccount.com';
	   		$private_key = file_get_contents('../../.certificates/tktpass-8c7e1b80d609.p12');
	   		$scopes = array('https://www.googleapis.com/auth/gmail.send');
		
	   		$user_to_impersonate = 'contact@tktpass.com';
	   		$credentials = new Google_Auth_AssertionCredentials(
		    	$client_email,
				$scopes,
				$private_key,
				'notasecret',                                 // Default P12 password
				'http://oauth.net/grant_type/jwt/1.0/bearer', // Default grant type
				$user_to_impersonate
			);
		
			$client = new Google_Client();
			$client->setAssertionCredentials($credentials);
			if ($client->getAuth()->isAccessTokenExpired()) $client->getAuth()->refreshTokenWithAssertion();
		
			$service = new Google_Service_Gmail($client);
			
			$message = $headers.PHP_EOL.$userMessage;
			
			//create the Gmail Message
			$mime = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');
			$msg = new Google_Service_Gmail_Message();
			$msg->setRaw($mime);
			
			try {	 
			    $service->users_messages->send("me", $msg);
			    $userSent = true;
			}
			catch (Exception $e){
			//	print($e->getMessage());
			//	unset($_SESSION['access_token']);
				$userSent = false;
			}
		}
		else{
			$userSent = false;
		}

		
		  //Send tktpass email
		$to      = 'orders@tktpass.com';
		$subject = 'Booking: '.htmlspecialchars($_POST['quantity']).' '.$event['title'].' @ '.date(DATE_RFC2822, $stamp);
		
		$tktpassMessage = file_get_contents($temDir.'tktpass-booking-email.html');
		$tktpassMessage = str_replace('%%EVENT%%',htmlspecialchars($event['title']),$tktpassMessage);
		$tktpassMessage = str_replace('%%STARTTIME%%',htmlspecialchars($event['startTime']),$tktpassMessage);
		$tktpassMessage = str_replace('%%TRANSPORT%%',$_POST['transport'],$tktpassMessage);
		$tktpassMessage = str_replace('%%NAME%%',htmlspecialchars($_SESSION['user']['name']),$tktpassMessage);
		$tktpassMessage = str_replace('%%MOBILE%%',htmlspecialchars($_POST['mobile']),$tktpassMessage);
		$tktpassMessage = str_replace('%%EMAIL%%',htmlspecialchars($_SESSION['user']['email']),$tktpassMessage);
		$tktpassMessage = str_replace('%%QUANTITY%%',$_POST['quantity'] > 1 ? htmlspecialchars($_POST['quantity']).' tickets' : '1 ticket',$tktpassMessage);
		$tktpassMessage = str_replace('%%USEREMAILSENT%%',($userSent ? 'yes' : '<span style="color:red;font-weight:600">failed!</span>'),$tktpassMessage);
		$tktpassMessage = str_replace('%%IP%%',$_SERVER["HTTP_CF_CONNECTING_IP"],$tktpassMessage);
		$tktpassMessage = $tktpassMessage.$userMessage;
	}
	else if($type == "resell"){
		$to      = 'resell@tktpass.com';
		$subject = 'Resell: '.htmlspecialchars($_POST['quantity']).' '.htmlspecialchars($event['title']).' @ '.date(DATE_RFC2822, $stamp);
		$tktpassMessage = file_get_contents($temDir.'tktpass-sell-email.html');
		$tktpassMessage = str_replace('%%EVENT%%',htmlspecialchars($event['title']),$tktpassMessage);
		$tktpassMessage = str_replace('%%STARTTIME%%',htmlspecialchars($event['title']),$tktpassMessage);
		$tktpassMessage = str_replace('%%TRANSPORT%%',$_POST['transport'],$tktpassMessage);
		$tktpassMessage = str_replace('%%NAME%%',htmlspecialchars($_SESSION['user']['name']),$tktpassMessage);
		$tktpassMessage = str_replace('%%MOBILE%%',htmlspecialchars($_POST['mobile']),$tktpassMessage);
		$tktpassMessage = str_replace('%%EMAIL%%',htmlspecialchars($_SESSION['user']['email']),$tktpassMessage);
		$tktpassMessage = str_replace('%%QUANTITY%%',$_POST['quantity'] > 1 ? htmlspecialchars($_POST['quantity']).' tickets' : 'ticket',$tktpassMessage);
		$tktpassMessage = str_replace('%%PRICE%%',htmlspecialchars($_POST['price']),$tktpassMessage);
		$tktpassMessage = str_replace('%%IP%%',$_SERVER["HTTP_CF_CONNECTING_IP"],$tktpassMessage);
		$userSent = true;
	}
	else if($type == "contact"){
		$to      = 'contact@tktpass.com';
		$subject = 'Contact Form @ '.date(DATE_RFC2822, $stamp);
		$tktpassMessage = file_get_contents($temDir.'tktpass-contact-email.html');
		$tktpassMessage = str_replace('%%NAME%%',htmlspecialchars($_POST['name']),$tktpassMessage);
		$tktpassMessage = str_replace('%%EMAIL%%',htmlspecialchars($_POST['email']),$tktpassMessage);
		$tktpassMessage = str_replace('%%MESSAGE%%',htmlspecialchars($_POST['message']),$tktpassMessage);
		$tktpassMessage = str_replace('%%IP%%',$_SERVER["HTTP_CF_CONNECTING_IP"],$tktpassMessage);
	}
	
	
	$repAddr = ($type=="booking" || $type=="resell")? $_SESSION['user']['email']: $_POST['email'];
	$repName = ($type=="booking" || $type=="resell")? $_SESSION['user']['name']: $_POST['name'];
	$headers = 'From: tktpass <contact@tktpass.com>' . PHP_EOL .
		'Reply-To: ' . htmlspecialchars($repName).' <'.htmlspecialchars($repAddr).'>' . "\r\n" .
		'Date: ' . date(DATE_RFC2822, $stamp) . "\r\n" .
		'Envelope-To: '. $to . "\r\n" .
		'Message-ID: ' . generateMessageID() . "\r\n" .
		'MIME-Version: 1.0' . PHP_EOL .
		'Content-Transfer-Encoding: 8bit' . "\r\n" .
		'Content-Type: text/html; charset=UTF-8' . PHP_EOL .
		'X-Priority: 3' . "\r\n" .
		'X-Mailer: PHP/' . phpversion() . "\r\n";
	
	$systemSent = mail($to, $subject, $tktpassMessage, $headers);
	
	$systemSent = true;
	
	$ret = array('tktpass' => $systemSent);
	
	header('Content-Type: application/json');
	echo json_encode($ret);	
}

?>