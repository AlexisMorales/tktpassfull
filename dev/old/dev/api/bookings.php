<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if(!isset($_SESSION['user'])){
//	header('Location: /');
//	die();
}
require_once 'paths.php';
require_once API.'config.php';
require_once API.'IO.php';
require_once ROOT.'/vendor/autoload.php';

global $mysqli, $fb;

$query = "SELECT * FROM events WHERE startTime >= UNIX_TIMESTAMP() ORDER BY startTime";
if (isset($_GET['all'])) $query = "SELECT * FROM events WHERE id >= 1";
$stmt = $mysqli->query($query);
$events = array();

while ($row = $stmt->fetch_assoc()) {
	$events[] = $row;
}
$stmt->close();

$query = "SELECT * FROM bookings WHERE event_id IN (SELECT id FROM events WHERE startTime >= UNIX_TIMESTAMP())";
if (isset($_GET['all'])) $query = "SELECT * FROM bookings";
$stmt = $mysqli->query($query);
$bookings = array();
while ($row = $stmt->fetch_assoc()) {
	$bookings[] = $row;
}
$stmt->close();

$total = array();
foreach ($events as $ev) {
	$total[$ev['id']] = array("b"=>0,"q"=>0, "cb"=>0, "ct"=>0);
}

foreach ($bookings as &$item) {
	$item['user'] = null;
	if (!isset($item['user_id']) || $item['user_id'] == 0){
		$item['user'] = array();
		$item['user']['picture'] = "";
		$item['user']['id'] = "";
		$item['user']['name'] = "";
		$item['user']['email'] = "";
		$item['user']['gender'] = "";	
	}
	else{
		$query = "SELECT fb_id FROM users WHERE (id = ?)";
		$stmt = $mysqli -> prepare($query);
		$stmt -> bind_param("s", $item['user_id']);
		$stmt -> execute();
		$stmt->store_result();
		$stmt->bind_result($fb_id);
		$stmt->fetch();
		$stmt->close();
		$item['user'] = getUser($fb_id);
		if (!isset($item['user']['email']) || $item['user']['email'] == NULL){
			$query = "SELECT email FROM users WHERE (fb_id = ?)";
			$stmt = $mysqli -> prepare($query);
			$stmt -> bind_param("s", $item['user']['fb_id']);
			$stmt -> execute();
			$stmt->store_result();
			$stmt->bind_result($email);
			$stmt->fetch();
			$item['user']['email'] = $email;
			$stmt->close();
		}
	}
	
	$item['row'] = "<tr id=bookingRow".(($item['status'])?(intval($item['id'])." style='color:#6DC066;'"):intval($item['id'])).">";
	$item['row'] .= "<td> <a target='_blank' href='https://facebook.com/app_scoped_user_id/".$item['user']['id']."'> <img src='".$item['user']['picture']."'> </a></td>";
	$item['row'] .= "<td>".$item['user']['name']."</td>";
	$item['row'] .= "<td>".$item['quantity']."</td>";
	$item['row'] .= "<td>".(($item['transport'])?'<font color=red>Yes</font>':'No')."</td>";
	$item['row'] .= "<td>".date('m/d/Y H:i:s', $item['bookingTime'])."</td>";
	$item['row'] .= "<td><a href='mailto:".($item['user']['email'] ?? "")."'>".($item['user']['email'] ?? "")."</a></td>";		
	$item['row'] .= "<td>".($item['mobile'] ?? "")."</td>";	
	$item['row'] .= "<td>".($item['user']['gender'] ?? "")."</td>";					
	$item['row'] .= "<td><button id=colBut".intval($item['id'])." class='btn btn-success colBut ".(($item['status'])? "collected":"")." '>Collected</button></td>";		
	$item['row'] .= "<td><textarea id=comBox".intval($item['id'])." class='form-control comBox' rows='2'>".$item['comment']."</textarea></td>";	
	$item['row'] .= "</tr>";
	
	$total[$item['event_id']]["q"] += $item['quantity'];
	$total[$item['event_id']]["b"]++;
	if ($item['status'] == 1) $total[$item['event_id']]["cb"]++;
	if ($item['status'] == 1) $total[$item['event_id']]["ct"] += $item['quantity'];
}

unset($item);

?>
<html>
	
<head>
<title>Bookings</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>	
<style>.colBut.collected{background:#93CD94}</style>
</head>
<body>
	<div class="container">	
		
	<?php
	foreach ($events as $ev) {
		$ev['tbl'] = "<center><h1>".$ev['title']."</h1><br>";
		$ev['tbl'] .= '<h3>Bookings : '. $total[$ev['id']]['b'] .'</h3><br>';
		$ev['tbl'] .= '<h3>Sold : '.  $total[$ev['id']]['q'] .'</h3><br>';
		$ev['tbl'] .= '<h3>Available : '.  $ev['quantity'] .'</h3><br>';
		$ev['tbl'] .= '<h3>Collected Bookings : '. $total[$ev['id']]['cb'] .'</h3><br>';
		$ev['tbl'] .= '<h3>Collected Tickets : '. $total[$ev['id']]['ct'] .'</h3><br>';
		$ev['tbl'] .= '</table>';
		$ev['tbl'] .= '<table border="1" align=center class="table table-hover">';
		$ev['tbl'] .= '<thead><tr><th>Picture</th><th>Name</th><th>Quantity</th><th>Transport</th><th>Time</th><th>Email</th><th>mobile</th><th>gender</th><th>Collection</th><th style="min-width: 250px">Comment</th></tr></thead>';
		$ev['tbl'] .= '<tbody align=center>';
		
		$bks = array_keys(array_column($bookings, 'event_id'), $ev['id']);

		foreach ($bks as $id) {
			$ev['tbl'] .= $bookings[$id]['row'];
		}
		$ev['tbl'] .= '</tbody></table><br>';
		echo $ev['tbl'];
	}	
	?>	
	</div>

<script>
    function uncollect(bookingId) {
        $.post(
            "/api/booking/" + bookingId + "/collected", {
                secret: "Terrace Bar"
            },
					  function(data, status){
							if (status == "success") {
									$("#colBut" + bookingId.toString()).removeClass('collected').addClass('dontClick');
									document.getElementById("bookingRow" + bookingId.toString()).style.color = "#000";
							}
						}
        );
    }
    function collect(bookingId) {
        $.post(
            "/api/booking/" + bookingId + "/collected", {
                secret: "Terrace Bar"
            },
					  function(data, status){
							if (status == "success") {
									$("#colBut" + bookingId.toString()).addClass('collected');
									document.getElementById("bookingRow" + bookingId.toString()).style.color = "#6DC066";
							}
						}
        );
    }


    $('.colBut').on('click', function() {
        if ($(this).hasClass('dontClick')){
					$(this).removeClass('dontClick');
					return false;
				}
        if (!$(this).hasClass('collected')) collect(this.id.substr(6))
    });
    var timeoutId = null;
    $('body').on('mousedown touchstart', '.colBut.collected', function() {
			  console.log('mousedown');
			  var that = this;
        timeoutId = setTimeout(function() {
			      console.log('timeout');
            if ($(that).hasClass('collected')) {
                uncollect(that.id.substr(6));
                $('body').off('mouseup mouseleave touchleave touchend', '.colBut.collected')
            }
        }, 1200);
    }).on('mouseup mouseleave touchleave touchend', '.colBut.collected', function() {
        clearTimeout(timeoutId);
    });


    $(".comBox").change(function(e) {
        $.post("/api/booking/" + this.id.substr(6) + "/comment", {
                secret: "Terrace Bar",
                comment: this.value
            },
            function(data, status) {
                if (status != "success") {
                    alert("An error occured! Please try again.");
                }
            }
        );
    });
</script>
</body></html>