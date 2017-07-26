<?php session_start();
/*if(!isset($_SESSION['user']['id']) or $_SESSION['user']['id']==0 ){
	header('Location: /');
	die();
}
	require_once 'api/IO.php';*/ ?><!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8">
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1">
        <title>My Tickets | tktpass</title>
        <meta name="description" content="Buy. Resell. Create.">
        <meta name="keywords" content="tickets, events, web, buy, sell">
        <meta name="author" content="Alex Taylor">
        <meta name="Copyright" content="Copyright 2015. All Rights Reserved.">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <link rel="canonical" href="https://tktpass.com/">
        <link type="text/plain" rel="author" href="/humans.txt">
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
        <meta property="og:image" content="og-image.jpg">
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
        <link rel="stylesheet" href="/old/live/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" integrity="sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd" crossorigin="anonymous">
        <!--link rel="stylesheet" href="/old/live/css/bootstrap.min.css"-->
        <!--[if lte IE 8]><link rel="stylesheet" href="/old/live/css/ie8.css"><![endif]-->
        <link rel="stylesheet" href="/old/live/css/bootstrap-social.css">
        <link rel="stylesheet" href="/old/live/css/slick.css">
        <link rel="stylesheet" href="/old/live/css/slick-theme.css">
        <link rel="stylesheet" href="/old/live/css/animate.css">
        <link rel="stylesheet" href="/old/live/css/main.css<?php echo "?r=a".time() ?>">
        <link rel="stylesheet" href="/old/live/css/ticket.css<?php echo "?r=a".time() ?>">
        
        <!-- Scripts -->
        <script src="/old/live/js/vendor/modernizr-2.8.3.min.js"></script>
        <!--[if lt IE 9]>
          <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
          <script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body style="overflow-x:hidden;">
        <!--[if lt IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <div class="skip-link">
            <a href="#main-content" class="sr-only sr-only-focusable">Skip to main content</a>
        </div>
        
        
        
        <nav class="navbar navbar-fixed-top mobile-navbar hidden-md-up<?php echo isset($_SESSION['user']) ? " logged-in" : "" ?>" id="mob-menu">
          <a class="navbar-brand" href="/"><img src="/old/img/logo_f.svg" class="logo"></a>
          <a class="nav-toggle pull-right" id="toggle"><s class="bar"></s><s class="bar"></s><s class="bar"></s></a>
          <span class="clearfix"></span>
          <ul class="nav nav-pills nav-stacked">
						<?php if(isset($_SESSION['user'])) { ?>
            <li class="nav-item" id="mob-account"><img src="<?php echo $_SESSION['user']['picture'] ?>" class="account-pic"> Hi <?php echo $_SESSION['user']['first_name'] ?></li>
            <li class="nav-item">
              <a class="nav-link"><img src="/old/img/tickets.svg" class="mobile-nav-icon"> My Tickets <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item" style="margin-left: -0.05rem;">
              <a class="nav-link" href="myevents"><img src="/old/img/party.svg" class="mobile-nav-icon"> My Events</a>
            </li>
            <!--li class="nav-item" style="margin-left: -1rem;">
              <a class="nav-link disabled"><img src="/old/img/settings.svg" class="mobile-nav-icon"> Settings</a>
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
            <a href="/" class="navbar-brand"><img src="/old/img/logo_f.svg" class="logo"></a>
            <!-- Links -->
            <ul class="nav navbar-nav pull-right">
                <!--li class="nav-item square">
                    <a class="nav-link" id="host-link">Host an event</a>
                </li-->
                <li class="nav-item"<?php echo isset($_SESSION['user']) ? ' style="display:none;"' : '' ?>>
                    <a class="nav-link" id="login">Sign up / Log in</a>
                </li>
                <li class="nav-item"<?php echo isset($_SESSION['user']) ? '' : ' style="display:none;"' ?>>
                    <a class="nav-link" id="account" title="My Account" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo '<img src="'.(isset($_SESSION['user']['picture']) ? $_SESSION['user']['picture'] : '').'" class="account-pic"> Hi ' . $_SESSION['user']['first_name'].' <i class="fa fa-angle-down"></i>';?></a>
                    <div id="account-drop" class="dropdown-menu" aria-labelledby="account">
                      <a class="dropdown-item"><img src="/old/img/tickets.svg" class="account-drop-icon"> My Tickets <span class="sr-only">(current)</span></a>
                      <a class="dropdown-item" href="myevents.php"><img src="/old/img/party.svg?r=001" class="account-drop-icon"> My Events</a>
                      <!--a class="dropdown-item" style="opacity:0.3;cursor:not-allowed"><img src="/old/img/settings.svg" class="account-drop-icon"> Settings</a-->
                    </div>
                </li>
            </ul>
          </nav>
        
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-3 col-lg-2 hidden-sm-down sidebar">
              <ul class="nav nav-sidebar">
                <li class="active"><a><img src="/old/img/tickets-w.svg" class="nav-sidebar-icon"><br>My Tickets <span class="sr-only">(current)</span></a></li>
                <li><a href="myevents.php"><img src="/old/img/party.svg?r=001" class="nav-sidebar-icon"><br>My Events</a></li>
              </ul>
              <!--ul class="nav nav-sidebar">
                <li><a style="opacity: 0.3;cursor: not-allowed;"><img src="/old/img/settings.svg" class="nav-sidebar-icon"><br>Settings</a></li>
              </ul-->
            </div>
            <div class="col-md-9 col-md-offset-3 col-lg-10 col-lg-offset-2 account-main">
              <h1 class="page-header">My Tickets</h1>
    
              <div class="row">
                <?php /*
									$bookings = array();
									$query = "SELECT id, user_id, event_id, quantity, transport, mobile, bookingTime, status FROM bookings WHERE (FROM_UNIXTIME(bookingTime) > DATE_SUB(now(), INTERVAL 6 MONTH)) AND user_id = ? ORDER BY event_id ASC";
									if($stmt = $mysqli -> prepare($query)){
										$tmp = $_SESSION['user']['id'];
										$stmt -> bind_param("s", $tmp);
										$stmt -> execute();
										$stmt->bind_result($id, $user_id, $event_id, $quantity, $transport, $mobile, $bookingTime, $status);
										$stmt->store_result();
										$i=0;
										while ($stmt->fetch()) {
												$bookings[$i]['id']=$id;
												$bookings[$i]['user_id']=$user_id;
												$bookings[$i]['event_id']=$event_id;
												$bookings[$i]['quantity']=$quantity;
												$bookings[$i]['transport']=$transport;
												$bookings[$i]['mobile']=$mobile;
												$bookings[$i]['bookingTime']=$bookingTime;
												$bookings[$i++]['status']=$status;
										}
										$stmt->close();
									} else {
										echo "Error";
									}
								  $prevId = -1;
								  $event = [];
								  unset($i);
									foreach($bookings as $i=>$booking){
										if($booking['event_id'] != $prevId)
											$event = getEvent($booking['event_id']);
										$bookings[$i]['event_title']=$event['title'];
									  $bookings[$i]['startTime']=$event['startTime'];
									  $bookings[$i]['venue']=$event['venue'];
									}
								  function cmp($a, $b){
											if ($a['startTime'] == $b['startTime']) {
													return 0;
											}
											return ($a['startTime'] > $b['startTime']) ? -1 : 1;
									}
									usort($bookings, "cmp");*/
								  $template = <<<EOF
<div class="tktWrap col-lg-6 %%CLASSES%%">
  <div class="tkt tktLeft">
    <h1>%%TITLE%% <span></span></h1>
    <div class="title">
      <h2>%%VENUE%%</h2>
      <span>venue</span>
    </div>
    <div class="name">
      <h2>%%NAME%%</h2>
      <span>name</span>
    </div>
    <div class="seat">
      <h2>%%DATE%%</h2>
      <span>date</span>
    </div>
    <div class="time">
      <h2>%%TIME%%</h2>
      <span>time</span>
    </div>
  </div>
  <div class="tkt tktRight">
    <div class="eye"><img src="/old/img/icon.png"></div>
    <div class="number">
      <h3>%%QUANTITY%%</h3>
      <span>Quantity</span>
    </div>
    <div class="barcode"></div>
  </div>
	<div class="stamp" title="See email for collection details"></div>
</div>
EOF;
								  /*$out = "";
								  unset($booking);
									if(count($bookings)){
										foreach($bookings as $booking){
											$classes = ($booking['status']==0 ? 'reserved ' : '').($booking['startTime']<time()+60*60*4 ? 'past ' : '').($booking['startTime']<time() && $booking['status']==1 ? 'ripped' : '');
											$out = str_replace("%%CLASSES%%",$classes,$template);
											$out = str_replace("%%TITLE%%",$booking['event_title'],$out);
											$out = str_replace("%%VENUE%%",$booking['venue'],$out);
											$out = str_replace("%%NAME%%",$_SESSION['user']['name'],$out);
											$out = str_replace("%%DATE%%",date("j M",$booking['startTime']),$out);
											$out = str_replace("%%TIME%%",date("g:i a",$booking['startTime']),$out);
											echo str_replace("%%QUANTITY%%",$booking['quantity'],$out);
										}
									} else {*/ ?>
								<div class="display-table events-placeholder"><p class="display-tablecell lead text-center" style="padding-left: 1rem;padding-right: 1rem;"><span style="font-weight:600;font-size:1.6rem;">Oops. It's empty.</span><br><span style="line-height: 0;display: inline-block;width: 80px;border-top: 1px solid #dadada;position: relative;top: -0.45rem;"></span><br><br>But don't worry, when you purchase a ticket you will see it here ツ</p></div>
								<?php // } ?>
              </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/old/live/js/vendor/jquery-1.11.3.min.js"><\/script>')</script>
        <script src="/old/live/js/vendor/slick.js"></script>
        <script src="/old/live/js/vendor/underscore.min.js"></script>
        <!--script src="/old/live/js/vendor/jquery.slimscroll.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/2.7.6/jquery.fullPage.js"></script>
        <script>(typeof window.jQuery('body').fullpage != "undefined") || document.write('<script src="/old/live/js/vendor/jquery.fullPage.min.js"><\/script>')</script-->
        <!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="anonymous"></script-->
        <script>(typeof $().modal == 'function') || document.write('<script src="/old/live/js/vendor/bootstrap-4.0.0-alpha.2.min.js"><\/script>')</script>
        <script src="/old/live/js/plugins.js"></script>
        <script src="/old/live/js/mytickets.js<?php if(DEV) echo "?r=a".time() ?>"></script>
        <!--script>
			    window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
			    ga('create', 'UA-64778762-1', 'auto');
			    ga('send', 'pageview');
		   </script>
		   <script async src='//www.google-analytics.com/analytics.js'></script-->
    </body>
</html><?php //$mysqli->close() ?>