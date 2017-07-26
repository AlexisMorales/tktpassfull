<?php
	session_start();
	require_once 'api/paths.php';
	require_once API.'config.php';
	require_once API.'IO.php';
	require_once ROOT.'/vendor/autoload.php';
	$events = getEvents();
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8">
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1">
        <title>tktpass</title>
        <meta name="description" content="Buy. Resell. Create.">
        <meta name="keywords" content="tickets, events, web, buy, sell">
        <meta name="author" content="tktpass">
        <meta name="Copyright" content="Copyright 2016 tktpass. All Rights Reserved.">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <link rel="canonical" href="https://tktpass.com/">
        <link type="text/plain" rel="author" href="/humans.txt">
        <!--link rel="alternate" type="application/rss+xml" title="RSS" href="index.html"-->
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
        <meta property="og:url" content="https://tktpass.com/">
        <meta property="og:type" content="website">
        <meta property="og:description" content="The easiest way to book and resell your tickets.">
        <meta property="og:image" content="https://tktpass.com/img/og-image.jpg">
        <meta property="og:image:type" content="image/jpeg">
        <meta name="og:email" content="contact@tktpass.com"/>
        <meta name="fb:page_id" content="tktpassOfficial" />
        <meta property="fb:app_id" content="1616269921948808">

        <!-- Mobiles -->
        <meta name="apple-mobile-web-app-capable" content="no">
        <meta name="apple-touch-fullscreen" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="HandheldFriendly" content="True" />
        <meta name="MobileOptimized" content="320" />
        <!--[if IEMobile]>  <meta http-equiv="cleartype" content="on">  <![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />

        <!-- Styles -->
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <link rel="stylesheet" href="css/slick.css">
        <!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" integrity="sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd" crossorigin="anonymous"-->
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <!--[if lte IE 8]><link rel="stylesheet" href="css/ie8.css"><![endif]-->
        <link rel="stylesheet" href="css/slick-theme.css">
        <link rel="stylesheet" href="css/animate.css">
        <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
        <link rel="stylesheet" href="css/powerange.css">
        <link rel="stylesheet" href="css/main.css?t=<?php echo time();?>">
        
        <!-- Scripts -->
<!--        <script src="js/vendor/modernizr-2.8.3.min.js"></script> -->
        <!--[if lt IE 9]>
          <script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
          <script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <!--script src='https://www.google.com/recaptcha/api.js'></script-->
    </head>
    <body>
        <!--[if lt IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <div class="skip-link">
            <a href="#section1" class="sr-only sr-only-focusable">Skip to main content</a>
        </div>
        
        <nav class="navbar navbar-fixed-top mobile-navbar hidden-md-up<?php echo isset($_SESSION['user']) ? " logged-in" : "" ?>" id="mob-menu">
          <a class="navbar-brand"><img src="img/logo_f.svg" class="logo"></a>
          <a class="nav-toggle pull-right" id="toggle"><s class="bar"></s><s class="bar"></s><s class="bar"></s></a>
          <span class="clearfix"></span>
          <ul class="nav nav-pills nav-stacked">
<?php
$txt = NULL;
if(!isset($_SESSION['user'])) { 
	$txt = <<<TXT
	<li class="nav-item">
	<a id="mob-login" class="nav-link">Sign up / Log in</a>
	</li>
TXT;
}
else {
$txt = <<<TXT
	<li class="nav-item" id="mob-account"> <img src="{$_SESSION['user']['picture']}" class="account-pic"> Hi {$_SESSION['user']['first_name']}</li>
	<li class="nav-item">
		<a class="nav-link" href="mytickets"><img src="img/tickets.svg" class="mobile-nav-icon"> My Tickets</a>
	</li>
	<li class="nav-item" style="margin-left: -0.05rem;">
		<a class="nav-link" href="myevents"><img src="img/party.svg" class="mobile-nav-icon"> My Events</a>
	</li>
	<!--li class="nav-item" style="margin-left: -1rem;">
		<a class="nav-link disabled" href="#"><img src="img/settings.svg" class="mobile-nav-icon"> Settings</a>
	</li-->
TXT;
}
echo $txt; ?>
          </ul>
        </nav>

        <nav class="navbar navbar-fixed-top landing-navbar animated bounceInDown hidden-sm-down" id="main-menu">
            <!-- Brand -->
            <a class="navbar-brand" href="/"><img src="img/logo_w.svg" class="logo"></a>
            <!-- Links -->
            <ul class="nav navbar-nav pull-right">
<?php /*                <!--li class="nav-item square">
                    <a class="nav-link" id="host-link">Host an event</a>
                </li-->  */ ?>
<?php
if(!isset($_SESSION['user'])) { 
	$txt = <<<TXT
	<li class="nav-item">
		<a class="nav-link" id="login">Sign up / Log in</a>
	</li>
TXT;
}
else {
	$txt = <<<TXT
	<li class="nav-item">
		<a class="nav-link" id="account" title="My Account" href="/mytickets" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="{$_SESSION['user']['picture']}" class="account-pic"> Hi {$_SESSION['user']['first_name']} <i class="fa fa-angle-down"></i></a>
		<div id="account-drop" class="dropdown-menu" aria-labelledby="account">
			<a class="dropdown-item" href="mytickets"><img src="img/tickets-w.svg" class="account-drop-icon"><img src="img/tickets.svg" class="account-drop-icon b"> My Tickets</a>
			<a class="dropdown-item" href="myevents"><img src="img/party-w.svg" class="account-drop-icon"><img src="img/party.svg" class="account-drop-icon b"> My Events</a>
			<!--a class="dropdown-item" style="opacity:0.3;cursor:not-allowed;"><img src="img/settings-w.svg" class="account-drop-icon"><img src="img/settings.svg" class="account-drop-icon b"> Settings</a-->
		</div>
		</li>
TXT;
}
echo $txt; ?>
            </ul>
          </nav>
        
      <div class="section text-center display-table" id="landing" data-anchor="landing">
          <div class="display-tablecell">
            <h2 class="display-3">Dev the night.</h2>
            <p class="lead">Buy and sell tickets to your favourite events.</p>
            <a class="arrow animated"></a>
          </div>
      </div>
      
      <div class="section display-table" id="section2" data-anchor="2">
        <div class="intro display-tablecell text-center">
        <div class="overlay"></div>
          <h1>The Cool Benefits</h1>
          <div class="icons row">
              <div class="icon-col col-sm-6 col-md-3">
              	<img src="img/facebook.svg" class="icon fb" style="opacity: 0.4;">
                <p class="icon-caption">See which of your friends are going - coming soon!</p>
              </div>
              <div class="icon-col col-sm-6 col-md-3">
              	<img src="img/tickets.svg" class="icon">
                <p class="icon-caption">Quickly book your tickets.</p>
              </div>
						  <span class="clearfix hidden-md-up"></span>
              <div class="icon-col col-sm-6 col-md-3">
              	<img src="img/creditcard.svg" class="icon" style="opacity: 0.4;">
                <p class="icon-caption">Convenient online payment with the smallest fee around</p>
              </div>
              <div class="icon-col col-sm-6 col-md-3">
              	<img src="img/resell.svg" class="icon" style="margin-top:0.8rem;">
                <p class="icon-caption">If you can no longer go, one click and we'll resell your ticket!</p>
              </div>
            </div>
        </div>
      </div>
      
      <div class="section" id="section1" data-anchor="1">
        <div class="intro">
            <h1 class="text-center">Events</h1>
            <p class="lead text-center">Easiest and fastest way to manage your tickets.</p>
            <div class="event-buttons text-center">
                <!--div class="btn-group" role="group" data-toggle="buttons">
                  <label class="btn btn-secondary active">
                    <input type="radio" name="options" id="option1" autocomplete="off" checked> Upcoming
                  </label>
                  <label class="btn btn-secondary">
                    <input type="radio" name="options" id="option2" autocomplete="off"><i class="fa fa-fire"></i> Hot
                  </label>
                </div-->
            </div>            
            <div id="event-carousel" class="col-md-10 col-md-offset-1 col-lg-6 col-lg-offset-3"><?php    
	            $template = <<<EOT
<div class="event" style="background-image: url('img/events/%%ID%%');"
  data-description="%%DESCRIPTION%%"
	data-venue="%%VENUE%%"
	data-postcode="%%POSTCODE%%"
	data-price="%%PRICE%%"
	data-transport="%%TRANSPORT%%"
	data-fbid="%%FBID%%"
	data-status="%%STATUS%%"
	id="event-%%ID%%">
  <a class="event-wrapper">
    <h4 class="event-title">%%TITLE%%</h4>
    <p class="event-time">%%START%%</p>
    <p class="event-location"><i class="fa fa-map-marker"></i> %%VENUE%%</p>
  </a>
</div>
EOT;
	            foreach($events as $ev){
					$out = str_replace("%%ID%%", $ev["id"],$template);
					$out = str_replace("%%TITLE%%",$ev['title'],$out);
					$out = str_replace("%%DESCRIPTION%%",addslashes($ev['description']),$out);
					$out = str_replace("%%POSTCODE%%",$ev['postcode'],$out);
					$out = str_replace("%%PRICE%%",$ev['price'],$out);
					$out = str_replace("%%TRANSPORT%%",$ev['transport'],$out);
					$out = str_replace("%%STATUS%%",($ev['quantity']<=0?"waitlist":""),$out);
					$out = str_replace("%%FBID%%",$ev['fb_id'],$out);
					$out = str_replace("%%START%%",date(($ev['startTime']-time() < 60*60*24*7 ? "D, " : "")."j\<\s\u\p\>S\<\/\s\u\p\> F @ g:ia",$ev['startTime']),$out);
					$out = str_replace("%%VENUE%%",$ev['venue'],$out);
					echo $out;
				}?>
			</div>
          </div>
          <div class="container-fluid">
            <div class="row">
                <div class="event-info col-md-10 col-md-offset-1 col-lg-6 col-lg-offset-3">
                    <div class="event-info-arrow"></div>
                    <p class="event-info-buttons text-center">
                        <button type="button" class="btn btn-primary">Need a ticket?</button>
                        <button type="button" class="btn btn-secondary">Selling a ticket?</button>
                    </p>
                    <h4 class="event-info-title"></h4>
                    <a href="https://www.facebook.com/events/" class="event-info-fb" target="_blank"><i class="fa fa-facebook"></i></a>
                    <p class="event-info-time"></p>
                    <p class="event-info-location"><a href target="_blank"><i class="fa fa-map-marker"></i></a> <span></span></p>
                    <pre class="event-info-description"></pre>
                </div>
            </div>
          </div>
      </div>
      
      <div class="section" id="section4" data-anchor="2">
        <div class="intro text-center">
          <div class="overlay"></div>
              <h2>What your classmates are saying</h2>
              <div id="testimonial-carousel">
                    <div class="testimonial text-center">
                        <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                          <p class="quotation">"Brilliant! Reselling a ticket through tktpass is so much easier than trying to find students on Facebook, it has really saved me time."</p>
                          <img src="img/testimonials/wesley.jpg" class="testimonial-pic">
                          <p class="testimonial-caption"><span>Wesley</span> — 3rd Year, Warwick</p>
                        </div>
                    </div>
                    <div class="testimonial text-center">
                        <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                          <p class="quotation">"Booking tickets like this is purely amazing! I've been using it for a while now and have never been dissapointed."</p>
                          <img src="img/testimonials/valentina.jpg" class="testimonial-pic">
                          <p class="testimonial-caption"><span>Valentina</span> — 3rd Year, Warwick</p>
                        </div>
                    </div>
                    <div class="testimonial text-center">
                        <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                          <p class="quotation">"Professional site, brilliant solution to changing plans, charming and reliable people behind the scenes. The perfect combination for a night out."</p>
                          <img src="img/testimonials/rebecca.jpg" class="testimonial-pic">
                          <p class="testimonial-caption"><span>Rebecca</span> — 3rd Year, Warwick</p>
                        </div>
                    </div>
                  </div>
              </div>
        </div>
      </div>
      
       <footer id="footer" class="container-fluid" style="clear:both;">
          <div class="row text-center">
            <div class="col-md-4 col-md-offset-4">
              <ul class="nav navbar-nav footer-nav text-center">
                <!--li class="nav-item">
                    <a class="nav-link" id="host-link">About Us</a>
                </li-->
                <li class="nav-item">
                    <a class="nav-link" id="contact-us">Contact Us</a>
                </li>
              </ul>
              <div class="social-bubbles">
                  <a href="https://twitter.com/tktpassOfficial" target="_blank"><svg width="35px" height="35px" viewBox="0 0 35 35" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"><defs></defs><g id="Welcome" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="visible-logo" transform="translate(-1164.000000, -523.000000)" fill="#646771"><g id="social" transform="translate(1164.000000, 523.000000)"><path d="M17.5,35 C27.1649836,35 35,27.1649836 35,17.5 C35,7.8350164 27.1649836,0 17.5,0 C7.8350164,0 0,7.8350164 0,17.5 C0,27.1649836 7.8350164,35 17.5,35 Z M17.5,32.9 C26.0051856,32.9 32.9,26.0051856 32.9,17.5 C32.9,8.99481443 26.0051856,2.1 17.5,2.1 C8.99481443,2.1 2.1,8.99481443 2.1,17.5 C2.1,26.0051856 8.99481443,32.9 17.5,32.9 Z M17.2775788,14.3880082 L17.3109608,14.9632708 L16.7545933,14.8928305 C14.7294158,14.6228093 12.9601672,13.7070853 11.4579751,12.1691386 L10.72357,11.4060352 L10.5344051,11.9695577 C10.1338205,13.2257432 10.3897495,14.552369 11.2243007,15.4446128 C11.6693947,15.937695 11.5692485,16.0081353 10.8014614,15.714634 C10.5344051,15.6207136 10.3007307,15.5502733 10.278476,15.5854935 C10.2005846,15.6676738 10.467641,16.7360184 10.6790606,17.1586603 C10.9683717,17.7456629 11.5581212,18.3209254 12.2035074,18.6613869 L12.7487475,18.9314081 L12.1033613,18.9431481 C11.4802297,18.9431481 11.4579751,18.9548881 11.5247392,19.2014292 C11.7472861,19.9645325 12.6263467,20.7745961 13.6055535,21.1267976 L14.2954491,21.3733387 L13.6945722,21.7490203 C12.8043843,22.2890627 11.7584135,22.594304 10.7124426,22.6177841 C10.2117119,22.6295241 9.8,22.6764843 9.8,22.7117045 C9.8,22.829105 11.1575366,23.4865479 11.9475784,23.744829 C14.3177038,24.5079324 17.1329232,24.1792109 19.2471196,22.8760653 C20.7493118,21.9486012 22.2515039,20.1054132 22.9525269,18.3209254 C23.3308568,17.3699812 23.7091867,15.6324537 23.7091867,14.79891 C23.7091867,14.2588677 23.7425687,14.1884274 24.3657003,13.5427246 C24.7329028,13.1670429 25.0778507,12.7561411 25.1446147,12.6387406 C25.2558882,12.4156796 25.2447609,12.4156796 24.6772661,12.6152605 C23.7314414,12.9674621 23.5979132,12.9205019 24.0652619,12.3921995 C24.4102097,12.0165179 24.8219216,11.3355949 24.8219216,11.136014 C24.8219216,11.1007939 24.6550114,11.1594942 24.4658464,11.2651546 C24.2655541,11.3825551 23.8204601,11.5586559 23.4866397,11.6643164 L22.8857628,11.8638972 L22.3405227,11.4764755 C22.0400842,11.2651546 21.617245,11.0303536 21.394698,10.9599133 C20.8272032,10.7955526 19.9592699,10.8190327 19.4474119,11.0068735 C18.0564932,11.5351758 17.1774326,12.8970218 17.2775788,14.3880082 C17.2775788,14.3880082 17.1774326,12.8970218 17.2775788,14.3880082 Z" id="Oval-1"></path></g></g></g></svg></a>
                  <a href="https://www.facebook.com/tktpassOfficial" target="_blank"><svg width="35px" height="35px" viewBox="0 0 35 35" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"><defs></defs><g id="Welcome" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="visible-logo" transform="translate(-1214.000000, -523.000000)" fill="#646771"><g id="social" transform="translate(1164.000000, 523.000000)"><path d="M67.5,35 C77.1649836,35 85,27.1649836 85,17.5 C85,7.8350164 77.1649836,0 67.5,0 C57.8350164,0 50,7.8350164 50,17.5 C50,27.1649836 57.8350164,35 67.5,35 Z M67.5,32.9 C76.0051856,32.9 82.9,26.0051856 82.9,17.5 C82.9,8.99481443 76.0051856,2.1 67.5,2.1 C58.9948144,2.1 52.1,8.99481443 52.1,17.5 C52.1,26.0051856 58.9948144,32.9 67.5,32.9 Z M68.7701638,25.2 L68.7701638,17.4990984 L71.0481214,17.4990984 L71.35,14.845325 L68.7701638,14.845325 L68.774034,13.5170859 C68.774034,12.8249409 68.8445046,12.4540739 69.9097877,12.4540739 L71.333874,12.4540739 L71.333874,9.8 L69.0555939,9.8 C66.319013,9.8 65.3558074,11.0873656 65.3558074,13.2523097 L65.3558074,14.8456256 L63.65,14.8456256 L63.65,17.4993989 L65.3558074,17.4993989 L65.3558074,25.2 L68.7701638,25.2 Z" id="Oval-1"></path></g></g></g></svg></a>
<?php /*                 <!--a href="https://www.pinterest.com/tktpassOfficial" target="_blank"><svg width="35px" height="35px" viewBox="0 0 512 512" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink" x="0px" y="0px" style="enable-background:new 0 0 35 35;" xml:space="preserve"> <g> <g> <path d="M256,0C114.609,0,0,114.609,0,256c0,141.391,114.609,256,256,256c141.391,0,256-114.609,256-256 C512,114.609,397.391,0,256,0z M256,472c-119.297,0-216-96.703-216-216S136.703,40,256,40s216,96.703,216,216S375.297,472,256,472 z"/> <path d="M262.031,128c-70.188,0-105.594,50.094-105.594,91.859c0,25.297,9.609,47.797,30.25,56.172 c3.406,1.312,6.422,0.062,7.406-3.672c0.688-2.578,2.297-9.094,3.016-11.812c0.984-3.688,0.609-5-2.125-8.188 c-5.953-6.984-9.75-16.016-9.75-28.828c0-37.172,27.938-70.422,72.734-70.422c39.625,0,61.47,24.109,61.47,56.359 c0,42.375-18.845,78.125-46.812,78.125c-15.484,0-27.062-12.702-23.344-28.297c4.438-18.594,13.031-38.703,13.031-52.172 c0-12.031-6.5-22.094-19.906-22.094c-15.812,0-28.484,16.297-28.484,38.078c0,13.875,4.703,23.266,4.703,23.266 s-16.156,68.203-19,80.125c-5.656,23.797-0.844,52.938-0.438,55.875c0.234,1.75,2.5,2.172,3.5,0.844 c1.469-1.891,20.281-25.016,26.672-48.125c1.828-6.516,10.391-40.422,10.391-40.422c5.141,9.75,20.141,18.345,36.094,18.345 c47.484,0,79.72-43.078,79.72-100.782C355.562,168.625,318.438,128,262.031,128z"/> </g> </g> </svg></a-->
                  <!--a href="https://www.youtube.com/c/tktpass" target="_blank"><svg width="35px" height="35px" viewBox="0 0 35 35" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"><defs></defs><g id="Welcome" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="visible-logo" transform="translate(-1264.000000, -523.000000)" fill="#646771"><g id="social" transform="translate(1164.000000, 523.000000)"><path d="M135,17.5 C135,7.8350164 127.164984,0 117.5,0 C107.835016,0 100,7.8350164 100,17.5 C100,27.1649836 107.835016,35 117.5,35 C127.164984,35 135,27.1649836 135,17.5 Z M132.9,17.5 C132.9,8.99481443 126.005186,2.1 117.5,2.1 C108.994814,2.1 102.1,8.99481443 102.1,17.5 C102.1,26.0051856 108.994814,32.9 117.5,32.9 C126.005186,32.9 132.9,26.0051856 132.9,17.5 Z M125.737829,21.5358341 C125.52888,22.4438919 124.786101,23.1134303 123.892339,23.213303 C121.775235,23.4498641 119.632477,23.4510391 117.498923,23.4498641 C115.365565,23.4510391 113.222611,23.4498641 111.105703,23.213303 C110.211744,23.1134303 109.469358,22.4438919 109.260604,21.5358341 C108.963336,20.2425813 108.963336,18.8314395 108.963336,17.5001958 C108.963336,16.1687563 108.966861,14.7574187 109.263933,13.4641659 C109.472883,12.5561081 110.215269,11.8863738 111.109032,11.786697 C113.226136,11.5501359 115.368894,11.5489609 117.502448,11.5501359 C119.635806,11.5489609 121.77876,11.5501359 123.895668,11.786697 C124.789626,11.8863738 125.532405,12.5561081 125.741158,13.4641659 C126.038426,14.7574187 126.036664,16.1687563 126.036664,17.5001958 C126.036664,18.8314395 126.034901,20.2425813 125.737829,21.5358341 Z M115.963336,14.35 L120.688336,17.07798 L115.963336,19.80596 L115.963336,14.35 Z" id="Oval-1"></path></g></g></g></svg></a-->    */ ?>
                </div>
            </div>
            <div class="col-md-4">
                <p class="disclaimer-text">
<?php /*          <!--a class="small" href="/legal/privacy-policy" title="" target="_blank">Privacy Policy</a> |
                  <a class="small" href="/legal" title="" target="_blank">Legal</a> |
                  <a class="small" href="/sitemap" title="">Sitemap</a> | 
                  <a class="small" href="/legal/tktpass_TermsandConditions.pdf" title="" target="_blank">*Terms &amp; Conditions</a> |--> */ ?>
                  © 2016
                </p><a href="#" class="brand" title="Back to top"><img src="img/logo_g.svg"></a>
                
              </div>
              
              
            </div>
        </footer>
        
        <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" style="
                width: 450px;
                max-width: 90%;
            ">
              <div class="modal-content">
                <!--div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                  <h4 class="modal-title" id="exampleModalLabel">Sign Up / Log In</h4>
                </div-->
                <div class="modal-body">
                  <p class="text-center" id="login-required" style="
                        font-size: 1.4rem;
                        color: #d33;
                        display: none;
                  ">Please log in to buy or sell tickets</p>
                  <?php /*<p class="text-center" style="
                        font-size: 1.4rem;
                        margin: 0;
                  ">...with just one click :)</p> */ ?>
                  <div class="text-center">
                    <a href="<?php
	if (!isset($_SESSION['user'])){
		global $fb;
		$helper = $fb->getRedirectLoginHelper();
		$permissions = ['email', 'public_profile','user_friends', 'user_birthday', 'user_events', 'rsvp_event'];
		$loginUrl = $helper->getLoginUrl(URL.'/fb-callback?next='.urlencode($_SERVER['REQUEST_URI']), $permissions);
		echo $loginUrl; 
	}?>	" id="fb-login"><img src="/img/fb.png" style="width: 16rem;max-width:92%;height: auto;"></a>
                  </div>
                  <p class="divider">or</p>
                  <p class="text-center">Don't have a Facebook account?<br>Form registration coming soon.</p>
                  <p class="text-center" style="display:none">Don't have a Facebook account?<br>Use our <a id="reg-btn">form registration</a> instead.<br>Already have an account? <a id="email-btn">Login with email</a>.</p>
                  <p class="alert alert-danger text-center">Error: you need to allow all permissions. Try again.</p>
<?php /*                  <!--form class="signup-form">
                    <fieldset class="form-group">
                      <input type="email" class="form-control" id="regEmail" name="regEmail" placeholder="Email">
                      <small class="text-muted">We'll never share your email with anyone else.</small>
                    </fieldset>
                    <fieldset class="form-group">
                      <label for="regPassword1">Create a Password</label>
                      <input type="password" class="form-control" id="regPassword1" name="regPassword1">
                      <small class="text-muted">Password must be at least 6 characters long.</small>
                    </fieldset>
                    <fieldset class="form-group">
                      <label for="regPassword2">Confirm Password</label>
                      <input type="password" class="form-control" id="regPassword2" name="regPassword2">
                    </fieldset>
                    <fieldset class="form-group">
                      <label for="regFname">First Name</label>
                      <input type="password" class="form-control" id="regFname" name="regFname">
                    </fieldset>
                    <fieldset class="form-group">
                      <label for="regLname">Last Name</label>
                      <input type="password" class="form-control" id="regLname" name="regLname">
                    </fieldset>
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="terms"> I accept the <a>terms and conditions</a>
                      </label>
                    </div>
                    <button type="submit" class="btn btn-primary pull-right">Submit</button>
                  </form>
                  <form class="login-form">
                    <fieldset class="form-group">
                      <label for="loginEmail">Email</label>
                      <input type="email" class="form-control" id="loginEmail" name="loginEmail">
                    </fieldset>
                    <fieldset class="form-group">
                      <label for="loginPassword">Password</label>
                      <input type="password" class="form-control" id="loginPassword" name="loginPassword">
                    </fieldset>
                    <div class="checkbox">
                      <label>
                        <input type="checkbox"> Remember me
                      </label>
                    </div>
                    <button type="submit" class="btn btn-primary pull-right">Log in</button>
                  </form-->  */ ?>
                </div>
              </div>
            </div>
          </div>
        <div class="modal fade" id="hostModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                  <h4 class="modal-title" id="exampleModalLabel">Create an event</h4>
                </div>
                <div class="modal-body">
                  <form>
                    <div class="form-group">
                      <label for="recipient-name" class="form-control-label">Name:</label>
                      <input type="text" class="form-control" id="recipient-name">
                    </div>
                    <div class="form-group">
                      <label for="message-text" class="form-control-label">Description:</label>
                      <textarea class="form-control" id="message-text"></textarea>
                    </div>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" class="btn btn-primary">Create</button>
                </div>
              </div>
            </div>
        </div>
        
        <div class="modal fade" id="buyModal">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
								</button>
								<h4 class="modal-title">Buy tickets</h4>
							</div>
							<div class="modal-body" style="overflow: visible;">
								<h5 class="buy-section text-center" disabled="">How many tickets do you need?</h5>
								<div class="quantity-row">
									<a id="decrease-buy-quantity" class="buy-icon-button minus" style="-webkit-user-select: none;"><img class="buy-icon minus" src="img/minus.svg" pagespeed_url_hash="1831113049" onload="(function(a){window.CloudFlare &amp;&amp; window.CloudFlare.push(function(b){b([&quot;cloudflare/rocket&quot;],function(c){c.push(function(){(function(){pagespeed.CriticalImages.checkImageForCriticality(this);}).call(a)})})})})(this);"></a>
									<h1 id="buy-quantity" class="text-center">1</h1>
									<a id="increase-buy-quantity" class="buy-icon-button plus" style="-webkit-user-select: none;"><img class="buy-icon plus" src="img/plus.svg" pagespeed_url_hash="2013066229" onload="(function(a){window.CloudFlare &amp;&amp; window.CloudFlare.push(function(b){b([&quot;cloudflare/rocket&quot;],function(c){c.push(function(){(function(){pagespeed.CriticalImages.checkImageForCriticality(this);}).call(a)})})})})(this);"></a>
								</div>
								<div class="buy-options">
									<div class="checkbox checkbox-success text-center">
										<input type="checkbox" value="" class="styled" id="buy-option-1">
										<label id="buy-option-1-label">
										Add transport for £<span></span> <i class="fa fa-question-circle" title="More info?" style="cursor: help;"></i>
										</label>
										<small id="buy-option-1-info" style="display:none">A transport ticket includes 11PM transport from campus to the venue by bus and return transport at 3AM.</small>
									</div>
								</div>
								<form role="form" id="payment-form" autocomplete="on">
									<fieldset style="margin-bottom: 1rem;">
										<div class="row">
											<div class="col-xs-12">
												<div class="input-group top animated">
													<span class="input-group-addon">
														<i class="fa fa-phone"></i>
													</span>
													<input type="tel" class="form-control " name="mobNumber" placeholder="Mobile number" required="" autofocus="" autocomplete="phone-number" id="buy-phone-number" >
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-xs-12">
												<div class="input-group middle animated">
													<span class="input-group-addon">
														<i class="fa fa-credit-card"></i>
													</span>
													<input type="tel" class="form-control" placeholder="Card number" id="cc-number" required="" autofocus="" data-stripe="number" autocomplete="cc-number" name="cardnumber">
												</div>
											</div>
										</div>
										<div class="row" style="margin:0">
											<div class="col-xs-3 col-md-3 animated" style="padding: 0;">
												<input type="tel" class="form-control bottom-left" placeholder="MM" id="cc-exp-mm" maxlength="2" required="" data-stripe="exp-month" autocomplete="cc-exp-month" name="ccmonth">
											</div>
											<div class="col-xs-3 col-md-3 animated" style="padding: 0;">
												<label style="position: absolute;top: .4rem;left: 0.5rem;">20</label>
												<input type="tel" class="form-control bottom-middle" placeholder="YY" id="cc-exp-yy" maxlength="2" required="" data-stripe="exp-year" autocomplete="cc-exp-year" name="ccyear" style="padding-left: 1.7rem;">
											</div>
											<div class="col-xs-6 col-md-6 pull-right animated" style="padding: 0;">
												<input type="password" pattern="[0-9]*" class="form-control bottom-right" placeholder="CVC" id="cc-cvc" maxlength="4" required="" data-stripe="cvc" autocomplete="cc-csc" name="cvc">
											</div>
										</div>
									</fieldset>
									<div class="row">
										<div class="col-xs-12 text-center">
											<button class="btn btn-primary btn-lg btn-block" id="buy-book-button" type="submit">£<span>6.00</span> - Buy now</button>
											<small id="card-fee" style="display: none;color: #999999;">+£<span class="total"></span><span class="brackets" style="display:none"> (<span class="per-ticket"></span>p per ticket)</span> processing fee</small>
										</div>
									</div>
								</form>
								<div class="tktpass-spinner" style="display:none"></div>
							</div>
							<div class="modal-footer text-center" style="display:none">
								<div id="buy-success" class="alert alert-success">Thank you for booking with tktpass. We have sent an email to '<span class="email" id="email"><?php echo $_SESSION['user']['email'] ?? '' ?></span>'.<br><small>Please check your junk folder and add us as a safe sender.</small></div>
								<a href="/mytickets" class="btn btn-mytickets">My Tickets</a>
								<!--small>29 remaining</small-->
							</div>
						</div>
						<!-- /.modal-content -->
					</div>
					<!-- /.modal-dialog -->
				</div>
				<!-- /.modal -->
        
        
        <div class="modal fade" id="sellModal">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Sell tickets</h4>
              </div>
              <div class="modal-body">
                <h5 class="buy-section text-center">How many do you have to sell?</h5>
                <div class="quantity-row">
                  <a id="decrease-sell-quantity" class="sell-icon-button minus"><img class="sell-icon minus" src="img/minus.svg"></a>
                  <h1 id="sell-quantity" class="text-center">1</h1>
                  <a id="increase-sell-quantity" class="sell-icon-button plus"><img class="sell-icon plus" src="img/plus.svg"></a>
                </div>
                <hr>
                <h5 class="buy-section text-center">Price?</h5>
                <div class="sell-options">
                  <div class="sell-slider-wrap">
                    <div id="sell-slider-value"></div>
                    <input type="text" id="sell-slider" value="0.5" />
                  </div>
                  <small id="sell-slider-chance"><span>92</span>% chance of selling before the event</small>
                  <div class="checkbox checkbox-success text-center">
                    <input type="checkbox" value="" class="styled" id="sell-option-1">
                    <label id="sell-option-1-label">
                      Transport ticket? <i class="fa fa-question-circle" title="More info?" style="cursor: help;"></i>
                    </label>
                    <small id="sell-option-1-info" style="display:none">When purchased, did the ticket include the £<span></span> extra transport option?</small>
                  </div>
                </div>
                <div class="sell-phone">
                  <form>
                    <div class="input-group animated">
											<span class="input-group-addon">
												<i class="fa fa-phone"></i>
											</span>
											<input type="tel" class="form-control" name="mobNumber" placeholder="Mobile number" required="" autofocus="" id="sell-phone-number">
										</div>
                  <form>
                </div>

              </div>
              <div class="modal-footer text-center">
                <button type="button" class="btn btn-primary" id="sell-book-button">Sell now (£<span></span>)</button>
                <div id="sell-success" class="alert alert-success" style="display: none;">Thank you for selling with tktpass. We'll be in touch when we find a buyer for your ticket(s).</div>
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        
        <div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                  <h4 class="modal-title" id="exampleModalLabel">Contact Us</h4>
                </div>
                <div class="modal-body">
                  <form>
                    <div class="form-group">
                      <label for="contact-us-name" class="form-control-label">Your name:</label>
                      <input type="text" class="form-control" id="contact-us-name">
                    </div>
                    <div class="form-group">
                      <label for="contact-us-email" class="form-control-label">Your email address:</label>
                      <input type="email" class="form-control" id="contact-us-email">
                    </div>
                    <div class="form-group">
                      <label for="contact-us-message" class="form-control-label">Message:</label>
                      <textarea class="form-control" id="contact-us-message" style="height:160px"></textarea>
                    </div>
                    <!--div class="form-group">
                      <div id="contact-us-captcha" class="g-recaptcha" data-sitekey="6Ld4Og0TAAAAAOACNYMv6FPb7CwCKHUpEZTfqnuE"></div>
                    </div-->
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" class="btn btn-primary" id="contact-us-send">Send</button>
                  <div id="contact-us-success" class="alert alert-success" style="display: none;">Message sent. A member of the team will get back to you as soon as possible.</div>
                </div>
              </div>
            </div>
        </div>
	
        <!--link href='https://fonts.googleapis.com/css?family=Open+Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic' rel='stylesheet' type='text/css'->
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <link rel="stylesheet" href="css/slick.css">
        <!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" integrity="sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd" crossorigin="anonymous"->
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <!--[if lte IE 8]><link rel="stylesheet" href="css/ie8.css"><![endif]->
        <link rel="stylesheet" href="css/slick-theme.css">
        <link rel="stylesheet" href="css/animate.css">
        <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
        <link rel="stylesheet" href="css/powerange.css">
        <link rel="stylesheet" href="css/main.css?r=<?php echo time();?>"-->


        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.3.min.js"><\/script>')</script>
        <script src="js/vendor/slick.js"></script>
        <!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="anonymous"></script>
        <script>(typeof $().modal == 'function') || document.write('<script src="js/vendor/bootstrap-4.0.0-alpha.2.min.js"><\/script>')</script-->
        <script src="js/vendor/bootstrap-4.0.0-alpha.2.min.js"></script>
        <script src="js/plugins.js?"></script>
<!--        <script src="js/vendor/underscore.min.js"></script> -->
        <script src="js/vendor/powerange.js"></script>
        <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
				<script type="text/javascript">
						Stripe.setPublishableKey('<?php echo STRIPE_PUBLIC;?>');
						if (window.location.hash && window.location.hash == '#_=_') {
								if (window.history && history.pushState) {
										window.history.pushState("/", document.title, window.location.pathname);
								} else {
										// Prevent scrolling by storing the page's current scroll offset
										var scroll = {
												top: document.body.scrollTop,
												left: document.body.scrollLeft
										};
										window.location.hash = '';
										// Restore the scroll offset, should be flicker free
										document.body.scrollTop = scroll.top;
										document.body.scrollLeft = scroll.left;
								}
						}
				</script>
        <script src="js/main.js?r=<?php echo time();?>"></script>
        <!--script>
			    window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
			    ga('create', 'UA-64778762-1', 'auto');
			    ga('send', 'pageview');
		    </script>
		    <script async src='//www.google-analytics.com/analytics.js'></script-->
    </body>
</html>