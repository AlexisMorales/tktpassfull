<?php

require_once 'config.php';

function getEvent($id)
{
	global $mysqli;
	$event=array();
	$query = "SELECT id, title, startTime, price, transport, description, fb_id, postcode, venue, quantity FROM events WHERE id = ?";
	$stmt = $mysqli -> prepare($query);
	$stmt -> bind_param("i", $id);
	$stmt -> execute();
	$stmt->store_result();
	$stmt->bind_result($event['id'], $event['title'], $event['startTime'], $event['price'], $event['transport'], $event['description'], $event['fb_id'], $event['postcode'], $event['venue'], $event['quantity']);
	$stmt->fetch();
	return $event;
}

function getUser($id)
{
	global $fb;
	try {
	  $response = $fb->get('/'.$id.'?fields=name,first_name,email,gender,picture', FB_APP_TOKEN);
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  echo 'Graph returned an error: ' . $e->getMessage();
	  exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  exit;
	}
	
	$user = array();
	$fbUser = $response->getGraphUser();
	$propsNames = $fbUser->getPropertyNames();
	
	foreach ($propsNames as $property) {
		$user[$property] = $fbUser->getProperty($property);
	}
	
	$user['fb_id'] = $user['id'];
	$user['picture'] = $user['picture']['url'];
	return $user;
}


function getUserPartial($id)
{
	global $fb;
	try {
	  $response = $fb->get('/'.$id.'?fields=name,first_name,email', FB_APP_TOKEN);
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  echo 'Graph returned an error: ' . $e->getMessage();
	  exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  exit;
	}
	
	$user = array();
	$fbUser = $response->getGraphUser();
	$propsNames = $fbUser->getPropertyNames();
	
	foreach ($propsNames as $property) {
		$user[$property] = $fbUser->getProperty($property);
	}
	
	$user['fb_id'] = $user['id'];
	return $user;
}



function getEvents()
{
	global $mysqli;
	$events=array();
	$query = "SELECT id, title, startTime, price, transport, description, fb_id, postcode, venue, quantity FROM events WHERE (startTime >= UNIX_TIMESTAMP()) AND (private = FALSE) ORDER by startTime LIMIT ?";
	$stmt = $mysqli -> prepare($query);
	$tmp = DEF_NUM_EVENTS;
	$stmt -> bind_param("i", $tmp);
	$stmt -> execute();
	$stmt->store_result();
	$stmt->bind_result($id, $title, $startTime, $price, $transport, $description, $fb_id, $postcode, $venue, $quantity);
	$i=0;
	while ($stmt->fetch()) {
		$events[$i]['id']=$id;
		$events[$i]['title']=$title;
		$events[$i]['startTime']=$startTime;
		$events[$i]['description']=$description;
		$events[$i]['price']=$price;
		$events[$i]['transport']=$transport;
		$events[$i]['fb_id']=$fb_id;
		$events[$i]['postcode']=$postcode;
		$events[$i]['quantity']=$quantity;
		$events[$i++]['venue']=$venue;
	}
	$stmt->close();
	return $events;
}

function amIHost($id,$table="bookings"){
		global $mysqli;
		if($table == "bookings"){
			$query = "SELECT event_id FROM bookings WHERE id = ?";
			$stmt = $mysqli -> prepare($query);
			$stmt -> bind_param("s", $id);
			$stmt -> execute();
			$stmt->store_result();
			$stmt->bind_result($eventId);
			$stmt->fetch();
			$stmt->close();
		}
		
		if(!$eventId) return false;
		
		$query = "SELECT event_id FROM hosts WHERE event_id = ? AND user_id = ?";
		$stmt = $mysqli -> prepare($query);
		$stmt -> bind_param("ss", $eventId, $_SESSION['user']['id']);
		$stmt -> execute();
		$stmt -> store_result();
		$rows = $stmt -> num_rows;
		$stmt->close();

		if($rows == 0 && !in_array($_SESSION['user']['id'], array(1,2,3))) return false;
	  
	  return true;
}


?>