<?php session_start();
if(!isset($_SESSION['user'])){
	header('Location: /');
	die();
}

header('Content-Type: text/html; charset=utf-8');
require_once 'api/paths.php';
require_once API.'config.php';
require_once API.'IO.php';
require_once ROOT.'/vendor/autoload.php';

global $mysqli, $fb;


$query = "SELECT * FROM events WHERE id IN (SELECT event_id FROM hosts WHERE user_id = ".$_SESSION['user']['id'].") AND startTime >= UNIX_TIMESTAMP() ORDER BY startTime";

$stmt = $mysqli->query($query);
$events = array();

while ($row = $stmt->fetch_assoc()) {
	$events[] = $row;
}
$stmt->close();

if (count($events)==0){
	header('Location: /myevents-new');
	die();
}  


$query = "SELECT * FROM bookings WHERE event_id IN (SELECT event_id FROM hosts WHERE user_id = ".$_SESSION['user']['id'].")";
$stmt = $mysqli->query($query);
$bookings = array();
while ($row = $stmt->fetch_assoc()) {
	$bookings[] = $row;
}
$stmt->close();

$total = array();
foreach ($events as $ev) {
	$total[$ev['id']] = array("b"=>0,"q"=>0);
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
		$item['user'] = getUserPartial($fb_id);
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
	
	$item['row'] = "<tr id='bookingRow".intval($item['id'])."'>";
	$item['row'] .= "<td>".$item['user']['name']."</td>";
	$item['row'] .= "<td>".$item['quantity']."</td>";
	$item['row'] .= "<td>".date('m/d/Y H:i:s', $item['bookingTime'])."</td>";
	$item['row'] .= "<td><a href='mailto:".($item['user']['email'] ?? "")."'>".($item['user']['email'] ?? "")."</a></td>";		
	$item['row'] .= "<td>".($item['mobile'] ?? "")."</td>";	
	$item['row'] .= "<td><button id=colBut".intval($item['id'])." class='btn btn-success colBut ".(($item['status'])? "collected":"")." '>Checked-in</button></td>";		
//	$item['row'] .= "<td><textarea id=comBox".intval($item['id'])." class='form-control comBox' rows='2'>".$item['comment']."</textarea></td>";	
	$item['row'] .= "</tr>";
	
	$total[$item['event_id']]["q"] += $item['quantity'];
	$total[$item['event_id']]["b"]++;
}

unset($item);

?>


	
	
	<head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8">
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1">
        <title>My events | tktpass</title>
        <meta name="description" content="Buy. Resell. Create.">
        <meta name="keywords" content="tickets, events, web, buy, sell">
        <meta name="author" content="Alex Taylor">
        <meta name="Copyright" content="Copyright 2015. All Rights Reserved.">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <link rel="canonical" href="http://tktpass.com/">
        <link type="text/plain" rel="author" href="humans.txt">
        <link rel="alternate" type="application/rss+xml" title="RSS" href="">
        <meta http-equiv="window-target" content="_top">

        <link rel="icon" type="image/vnd.microsoft.icon" href="/favicon.ico">
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <!-- IE11 tiles -->
        <meta name="msapplication-square70x70logo" content="">
        <meta name="msapplication-square150x150logo" content="">
        <!-- Windows 8 -->
        <meta name="application-name" content="tktpass">
        <meta name="msapplication-TileColor" content="">
        <meta name="msapplication-TileImage" content="">
        <meta name="msapplication-tooltip" content="Launch tktpass site"/>
        <meta name="msapplication-task" content="name=Buy;action-uri=/buy/;icon-uri=/images/buy.ico" />
        <meta name="msapplication-task" content="name=Sell;action-uri=/sell/;icon-uri=/images/sell.ico" />
        <meta name="msapplication-task" content="name=Create;action-uri=/create/;icon-uri=/images/create.ico" />
        <!-- Twitter -->
        <meta name="twitter:url" content="">
        <meta name="twitter:site" content="">
        <meta name="twitter:card" content="">
        <meta name="twitter:title" content="">
        <meta name="twitter:description" content="">
        <meta name="twitter:image" content="">
        <!-- Facebook -->
        <meta property="og:site_name" content="tktpass">
        <meta property="og:locale" content="en_GB">
        <meta name="og:country-name" content="UK"/>
        <meta property="og:title" content="tktpass">
        <meta property="og:url" content="">
        <meta property="og:type" content="website">
        <meta property="og:description" content="Buy. Resell. Create.">
        <meta property="og:image" content="/og-image.jpg">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="">
        <meta property="og:image:height" content="">
        <meta name="og:email" content="contact@tktpass.com"/>
        <meta name="fb:page_id" content="" />
        <meta property="fb:app_id" content="">

        <!-- Mobiles -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-touch-fullscreen" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="HandheldFriendly" content="True" />
        <meta name="MobileOptimized" content="320" />
        <!--[if IEMobile]>  <meta http-equiv="cleartype" content="on">  <![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />

        <!-- Styles -->
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" integrity="sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd" crossorigin="anonymous">
        <!--link rel="stylesheet" href="css/bootstrap.min.css"-->
        <!--[if lte IE 8]><link rel="stylesheet" href="css/ie8.css"><![endif]-->
        <link rel="stylesheet" href="/css/bootstrap-social.css">
        <link rel="stylesheet" href="/css/slick.css">
        <link rel="stylesheet" href="/css/slick-theme.css">
        <link rel="stylesheet" href="/css/animate.css">
        <link rel="stylesheet" href="/css/jquery-ui.css<?php echo "?t=".time() ?>">
        <link rel="stylesheet" href="/css/main.css<?php echo "?t=".time() ?>">
        <link rel="stylesheet" href="/css/host.css<?php echo "?t=".time() ?>">
        
        <!-- Scripts -->
        <!--[if lt IE 9]>
          <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
          <script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <!--[if lt IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <div class="skip-link">
            <a href="#main-content" class="sr-only sr-only-focusable">Skip to main content</a>
        </div>
        
        
        
        <nav class="navbar navbar-fixed-top mobile-navbar hidden-md-up<?php echo isset($_SESSION['user']) ? " logged-in" : "" ?>" id="mob-menu">
          <a class="navbar-brand" href="/"><img src="/img/logo_f.svg" class="logo"></a>
          <a class="nav-toggle pull-right" id="toggle"><s class="bar"></s><s class="bar"></s><s class="bar"></s></a>
          <span class="clearfix"></span>
          <ul class="nav nav-pills nav-stacked">
						<?php if(isset($_SESSION['user'])) { ?>
            <li class="nav-item" id="mob-account"><img src="<?php echo $_SESSION['user']['picture'] ?>" class="account-pic"> Hi <?php echo $_SESSION['user']['first_name'] ?></li>
            <li class="nav-item">
              <a class="nav-link" href="/mytickets"><img src="/img/tickets.svg" class="mobile-nav-icon"> My Tickets</a>
            </li>
            <li class="nav-item active" style="margin-left: -0.05rem;">
              <a class="nav-link"><img src="/img/party.svg?r=001" class="mobile-nav-icon"> My Events <span class="sr-only">(current)</span></a>
            </li>
            <!--li class="nav-item" style="margin-left: -1rem;">
              <a class="nav-link disabled" style="opacity:0.3;cursor:not-allowed"><img src="/img/settings.svg" class="mobile-nav-icon"> Settings</a>
            </li-->
						<?php } else { ?>
            <li class="nav-item">
              <a id="mob-login" class="nav-link">Sign up / Log in</a>
            </li>
						<?php } ?>
          </ul>
        </nav>

        <nav class="navbar main-navbar hidden-sm-down" id="main-menu">
            <!-- Brand -->
            <a href="/" class="navbar-brand"><img src="/img/logo_f.svg" class="logo"></a>
            <!-- Links -->
            <ul class="nav navbar-nav pull-right">
                <!--li class="nav-item square">
                    <a class="nav-link" id="host-link">Host an event</a>
                </li-->
                <li class="nav-item"<?php echo isset($_SESSION['user']) ? ' style="display:none;"' : '' ?>>
                    <a class="nav-link" id="login">Sign up / Log in</a>
                </li>
                <li class="nav-item"<?php echo isset($_SESSION['user']) ? '' : ' style="display:none;"' ?>>
                    <a class="nav-link" id="account" title="My Account" href="account" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo '<img src="'.(isset($_SESSION['user']['picture']) ? $_SESSION['user']['picture'] : '').'" class="account-pic"> Hi ' . $_SESSION['user']['first_name'].' <i class="fa fa-angle-down"></i>';?></a>
                    <div id="account-drop" class="dropdown-menu" aria-labelledby="account">
                      <a class="dropdown-item" href="/mytickets"><img src="/img/tickets.svg?r=001" class="account-drop-icon"> My Tickets</a>
                      <a class="dropdown-item"><img src="/img/party.svg?r=001" class="account-drop-icon"> My Events <span class="sr-only">(current)</span></a>
                      <!--a class="dropdown-item" style="opacity:0.3;cursor:not-allowed"><img src="/img/settings.svg" class="account-drop-icon"> Settings</a-->
                    </div>
                </li>
            </ul>
          </nav>
          
          
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>	
<style>.colBut.collected{background:#93CD94}</style>
</head>
<body>
<center><h3>This page is just for test purposes. We're gonna move to our new "My events" soon. Stay tuned! </h3></center><br>
						<div class="container">	
							
							<?php
	foreach ($events as $ev) {
		$ev['tbl'] = "<center><h1>".$ev['title']."</h1><br>";
		$ev['tbl'] .= '<h2>Sold : '.  $total[$ev['id']]['q'] .'</h2><br>';
		$ev['tbl'] .= '<h2>Available : '.  $ev['quantity'] .'</h2><br>';
		$ev['tbl'] .= '</table>';
		$ev['tbl'] .= '<table border="1" align=center class="table table-hover">';
		$ev['tbl'] .= '<thead><tr><th>Name</th><th>Quantity</th><th>Time</th><th>Email</th><th>mobile</th><th style="min-width: 250px">Check In</th></tr></thead>';
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
		
		</script>
	
												
</body></html>