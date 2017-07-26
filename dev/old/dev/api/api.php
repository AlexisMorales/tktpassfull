<?php

session_start();	
	
require_once 'paths.php';
require_once API.'config.php';
require_once ROOT.'/vendor/autoload.php';
require_once 'sendmail.php';
require_once 'IO.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// Create and configure Slim app
$app = new \Slim\App;

// Define app routes
$app->get('/events[/[{id}]]', function ($request, $response, $args) {	
	global $mysqli;
	$events=array();
	$query = "";
	$stmt = "";
	
	//If no ID is specified, return Upcoming Events
	if (!isset($args['id'])){
		$query = "SELECT id, title, startTime, price, description, fb_id, postcode, venue FROM events WHERE (startTime >= UNIX_TIMESTAMP()) AND (private = FALSE) ORDER by startTime LIMIT ?";
		$stmt = $mysqli -> prepare($query);
		$stmt -> bind_param("i", $tmp = DEF_NUM_EVENTS);
	}
	else
	{
		$query = "SELECT id, title, startTime, price, description, fb_id, postcode, venue FROM events WHERE id = ?";
		$stmt = $mysqli -> prepare($query);
		$stmt -> bind_param("i", $args['id']);
	}
	
	$stmt -> execute();
	$stmt->store_result();
	$stmt->bind_result($id, $title, $startTime, $price, $description, $fb_id, $postcode, $venue);
	$i=0;
	while ($stmt->fetch()) {
        $events[$i]['id']=$id;
        $events[$i]['title']=$title;
        $events[$i]['startTime']=$startTime;
		$events[$i]['description']=$description;
        $events[$i]['price']=$price;
        $events[$i]['fb_id']=$fb_id;
        $events[$i]['postcode']=$postcode;
		$events[$i++]['venue']=$venue;
	}
	$stmt->close();
	$response->write(json_encode($events));
	$newResponse = $response->withHeader(
        'Content-type',
        'application/json; charset=utf-8'
    );
    return $newResponse;
});

$app->get('/users/{id}', function ($request, $response, $args) {

});

$app->get('/users/{id}/bookings', function ($request, $response, $args) {
		/*if(!isset($_SESSION['user']) || $_SESSION['user']['id'] != $args['id']){
				$newResponse = $response->withHeader(
					 'Content-type',
					 'application/json; charset=utf-8'
				);
				$newResponse = $newResponse->withStatus(403, "Forbidden");
				$newResponse = $newResponse->write(json_encode(array("error"=>"auth")));
				return $newResponse;
		}*/
		
		global $mysqli;
		$bookings = array();
	
		$query = "SELECT * FROM bookings WHERE FROM_UNIXTIME(bookingTime) > DATE_SUB(now(), INTERVAL 6 MONTH) AND user_id = ?";
		if($stmt = $mysqli -> prepare($query)){
			$stmt -> bind_param("s", $args['id']);
			$stmt -> execute();
			$stmt->bind_result($id, $user_id, $event_id, $quantity, $transport, $mobile, $bookingTime);//,$status);
			$stmt->store_result();
			$i=0;
			while ($stmt->fetch()) {
					$bookings[$i]['id']=$id;
					$bookings[$i]['user_id']=$user_id;
					$bookings[$i]['event_id']=$event_id;
					$bookings[$i]['quantity']=$quantity;
					$bookings[$i]['transport']=$transport;
					$bookings[$i]['mobile']=$mobile;
					$bookings[$i++]['bookingTime']=$bookingTime;
					//$bookings[$i++]['status']=$status;
			}
			$stmt->close();
			$newResponse = $response->withHeader(
						'Content-type',
						'application/json; charset=utf-8'
				);
			$newResponse->write(json_encode($bookings));
			return $newResponse;
		} else {
			$newResponse = $response->withHeader(
				'Content-type',
				'application/json; charset=utf-8'
			);
		  $newResponse = $newResponse->withStatus(500, "Server Error");
		  $newResponse = $newResponse->write(json_encode(array("error"=>500)));
	    return $newResponse;
		}
});

$app->post('/booking', function ($request, $response, $args) {
		
	if (!isset($_SESSION['user']['id']))
	{
		  $newResponse = $response->withHeader(
	       'Content-type',
	       'application/json; charset=utf-8'
	    );
		  $newResponse = $newResponse->withStatus(403, "Forbidden");
		  $newResponse = $newResponse->write(json_encode(array("error"=>"login")));
	    return $newResponse;
	}
	
	if (!isset($_POST['event_id']) || !isset($_POST['quantity']) || !isset($_POST['transport']) || !isset($_POST['mobile']) || $_POST['quantity'] < 1)
	{
		$newResponse = $response->withHeader('Content-type','application/json; charset=utf-8');
		$newResponse = $newResponse->write(json_encode(array("tktpass"=>false)));
		return $newResponse;
	}
	
				
	if((!isset($_POST['stripeToken']) || isset($_POST['stripeToken']) == NULL))
	{
		//return json_encode("No token");
		return json_encode(array("tktpass" => false));
	}
	
	\Stripe\Stripe::setApiKey(STRIPE_PRIVATE);
	$token = $_POST['stripeToken'];
	$customer = \Stripe\Customer::create(array(
		"source" => $token,
		"description" => $_SESSION['user']['name'],
		"metadata" => array("user_id" => $_SESSION['user']['id'])
	));

	$evn = getEvent($_POST['event_id']);
	$amount = ($evn['price']+($_POST['transport'] ? $evn['transport'] : 0)) * max(intval($_POST['quantity']),0);
	if ($_POST['event_id']==26) $amount += round((1/493)*(7*$amount+10000));
	
	try {
		\Stripe\Charge::create(array(
		  "amount" => $amount, // amount in cents, again
		  "currency" => "gbp",
		  "customer" => $customer->id,
		  "description" => $evn['title']." x".$_POST['quantity'],
		  "receipt_email" => $_SESSION['user']['email'],
		  "metadata" => array("user_id" => $_SESSION['user']['id'], "event_id" => $_POST['event_id'], "quantity" => $_POST['quantity'])
    ));
	} catch(\Stripe\Error\Card $e){
//		return $e;
		//return json_encode($e);
		return json_encode(array("tktpass" => false));
	}
	
	global $mysqli;
	$query = "INSERT INTO bookings (user_id, event_id, quantity, transport, mobile, bookingTime) VALUES (?, ?, ?, ?, ?, UNIX_TIMESTAMP());";
	$stmt = $mysqli -> prepare($query);
	$tmp=filter_var($_POST['transport'],FILTER_VALIDATE_BOOLEAN);
	$stmt -> bind_param("iiiis", $_SESSION['user']['id'], $_POST['event_id'], $_POST['quantity'], $tmp, $_POST['mobile']);
	$stmt -> execute();
	$stmt->close();
	
	$query = "UPDATE events SET quantity = quantity - ? WHERE id = ?;";
	$stmt = $mysqli -> prepare($query);
	$stmt -> bind_param("ss", $_POST['quantity'], $_POST['event_id']);
	$stmt -> execute();
	$stmt->close();

	confirmationMail("booking");
});

$app->post('/booking/{id}/collected', function ($request, $response, $args) {
	if (isset($_POST['secret']) && $_POST['secret'] == "Terrace Bar")
	{
		global $mysqli;
		$query = "UPDATE bookings SET status = NOT status WHERE id = ?";
		$stmt = $mysqli -> prepare($query);
		$tmp = $args['id'];
		$stmt -> bind_param("s", $tmp);
		$success = $stmt -> execute();
		$stmt->close();
	}
	$newResponse = $response->withHeader(
		 'Content-type',
		 'application/json; charset=utf-8'
	);
	if(!$success){
		  $newResponse = $newResponse->withStatus(400, "Bad Request");
		  $newResponse = $newResponse->write(json_encode(array("error"=>$args['id'])));
	    return $newResponse;
	} else {
		  $newResponse = $newResponse->write(json_encode(array("success"=>1)));
	    return $newResponse;
	}
});


$app->post('/booking/{id}/comment', function ($request, $response, $args) {
	if (isset($_POST['secret']) && $_POST['secret'] == "Terrace Bar" && isset($_POST['comment']))
	{
		global $mysqli;
		
		
		/*$query = "SELECT event_id FROM hosts WHERE event_id = ? AND user_id = ?";
		$stmt = $mysqli -> prepare($query);
		$stmt -> bind_param("ss", $args['id'], $_SESSION['user']['id']);
		$stmt -> execute();
		$stmt -> store_result();
		$rows = $stmt -> num_rows;
		$stmt->close();

		if ($rows == 0 && ($_SESSION['user']['id']!=2 && $_SESSION['user']['id']!=2 && $_SESSION['user']['id']!=3) )
		{
			echo $rows;
			echo 403;
			return;
		}*/
		
		$query = "UPDATE bookings SET comment = ? WHERE id = ?";
		$stmt = $mysqli -> prepare($query);
		$tmp2 = $args['id'];
		$stmt -> bind_param("ss", $_POST['comment'], $tmp2);
		$success = $stmt -> execute();
		$stmt->close();
	}
	$newResponse = $response->withHeader(
		 'Content-type',
		 'application/json; charset=utf-8'
	);
	if(!$success){
		  $newResponse = $newResponse->withStatus(400, "Bad Request");
		  $newResponse = $newResponse->write(json_encode(array("error"=>$args['id'])));
	    return $newResponse;
	} else {
		  $newResponse = $newResponse->write(json_encode(array("success"=>1)));
	    return $newResponse;
	}
});

$app->post('/resell', function ($request, $response, $args) {

	if (!isset($_SESSION['user']['id']))
	{
		  $newResponse = $response->withHeader(
	       'Content-type',
	       'application/json; charset=utf-8'
	    );
		  $newResponse = $newResponse->withStatus(403, "Forbidden");
		  $newResponse = $newResponse->write(json_encode(array("error"=>"login")));
	    return $newResponse;
	}
	
	
	if (!isset($_POST['event_id']) || !isset($_POST['quantity']) || !isset($_POST['transport']) || !isset($_POST['mobile']))
	{
		$newResponse = $response->withHeader('Content-type','application/json; charset=utf-8');
		$newResponse = $newResponse->write(json_encode(array("tktpass"=>false)));
		return $newResponse;
	}

	
	
	global $mysqli;
	$query = "INSERT INTO resell (user_id, event_id, quantity, transport, mobile, bookingTime) VALUES (?, ?, ?, ?, ?, UNIX_TIMESTAMP());";
	$stmt = $mysqli -> prepare($query);
	$stmt -> bind_param("iiiis", $_SESSION['user']['id'], $_POST['event_id'], $_POST['quantity'], $_POST['transport'], $_POST['mobile']);
	$stmt -> execute();
	$stmt->close();
	
	confirmationMail("resell");	
});

$app->post('/contact', function ($request, $response, $args) {
	confirmationMail("contact");	
});


// Run app
$app->run();



?>