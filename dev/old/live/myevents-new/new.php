<?php session_start();
if(!isset($_SESSION['user'])){
	header('Location: /');
	die();
}
	require_once '../api/IO.php'; ?><!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8">
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1">
        <title>New event | tktpass</title>
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
        <link rel="stylesheet" href="../css/font-awesome.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" integrity="sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd" crossorigin="anonymous">
        <!--link rel="stylesheet" href="css/bootstrap.min.css"-->
        <!--[if lte IE 8]><link rel="stylesheet" href="css/ie8.css"><![endif]-->
        <link rel="stylesheet" href="../css/bootstrap-social.css">
        <link rel="stylesheet" href="../css/slick.css">
        <link rel="stylesheet" href="../css/slick-theme.css">
        <link rel="stylesheet" href="../css/animate.css">
        <link rel="stylesheet" href="../css/main.css<?php echo "?t=".time() ?>">
        <link rel="stylesheet" href="../css/host.css<?php echo "?t=".time() ?>">
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
          <a class="navbar-brand" href="/"><img src="../../img/logo_f.svg" class="logo"></a>
          <a class="nav-toggle pull-right" id="toggle"><s class="bar"></s><s class="bar"></s><s class="bar"></s></a>
          <span class="clearfix"></span>
          <ul class="nav nav-pills nav-stacked">
						<?php if(isset($_SESSION['user'])) { ?>
            <li class="nav-item" id="mob-account"><img src="<?php echo $_SESSION['user']['picture'] ?>" class="account-pic"> Hi <?php echo $_SESSION['user']['first_name'] ?></li>
            <li class="nav-item">
              <a class="nav-link" href="/mytickets"><img src="../../img/tickets.svg" class="mobile-nav-icon"> My Tickets</a>
            </li>
            <li class="nav-item active" style="margin-left: -0.05rem;">
              <a class="nav-link"><img src="../../img/party.svg?r=001" class="mobile-nav-icon"> My Events <span class="sr-only">(current)</span></a>
            </li>
            <!--li class="nav-item" style="margin-left: -1rem;">
              <a class="nav-link disabled" style="opacity:0.3;cursor:not-allowed"><img src="../../img/settings.svg" class="mobile-nav-icon"> Settings</a>
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
            <a href="/" class="navbar-brand"><img src="../../img/logo_f.svg" class="logo"></a>
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
                      <a class="dropdown-item" href="/mytickets"><img src="../../img/tickets.svg?r=001" class="account-drop-icon"> My Tickets</a>
                      <a class="dropdown-item"><img src="../../img/party.svg?r=001" class="account-drop-icon"> My Events <span class="sr-only">(current)</span></a>
                      <!--a class="dropdown-item" style="opacity:0.3;cursor:not-allowed"><img src="../../img/settings.svg" class="account-drop-icon"> Settings</a-->
                    </div>
                </li>
            </ul>
          </nav>
        
        <div class="container-fluid" id="main-content">
          <div class="row">
            <div class="host-header text-center">
            	<h2 class="host-header-text">Host an event</h2>
            	<p class="lead">tktpass is the <strong>#1</strong> choice for <strong>societies and students</strong> organising events to raise or make money.</p>
         	 	</div>
						<div class="panel-body container-fluid">
							<div class="panel-dim"></div>
							<div class="container">
								<div class="row">
									<div class="col-sm-6 col-md-3 text-center">
										<img class="host-icon" src="../../img/fb-import.svg?" />
										<h4 class="host-icon-subtitle">Quick and easy</h4>
										<p class="host-icon-caption">With our import feature, it has never been so simple for you to add an event!</p>
									</div>
									<div class="col-sm-6 col-md-3 text-center">
										<img class="host-icon" src="../../img/shout.svg" />
										<h4 class="host-icon-subtitle">Just tell 'em</h4>
										<p class="host-icon-caption">You’ll only need to tell them: from tktpass. Because we’ll do the rest. Generate your tickets, sell them, and process all payments!</p>
									</div>
									<div class="col-sm-6 col-md-3 text-center">
										<img class="host-icon" src="../../img/mobile-stats.svg" />
										<h4 class="host-icon-subtitle">Track progress</h4>
										<p class="host-icon-caption">Once created, you will be able to manage your event and keep and eye on your ticket sales 24/7.</p>
									</div>
									<div class="col-sm-6 col-md-3 text-center">
										<img class="host-icon" src="../../img/hand-money.svg?" />
										<h4 class="host-icon-subtitle">Get paid. Fast.</h4>
										<p class="host-icon-caption">Choose from getting the money in your bank account or collecting it straight after the event.</p>
									</div>
								</div>
								<div class="row button-row">
									<div class="col-sm-4 col-sm-offset-4">
										<button type="button" class="btn btn-white-outline" onclick="$('.host-icon-caption').slideToggle();">Tell me more</button>
									</div>
								</div>
								<div class="row">
									<div class="col-md-4">
										<h2 class="text-center calc-header">
											<span class="fa-stack">
												<i class="fa fa-circle fa-stack-2x"></i>
												<strong class="fa-stack-1x" style="color:#333">1</strong>
											</span>
											How much do you want to make?
										</h2>
										<button type="button" class="btn btn-white-outline col-md-8 col-md-offset-2 btn-input" onclick="document.getElementById('currency-input').focus();document.execCommand('selectAll',false,null);">£<span id="currency-input" contenteditable>0.00</span></button>
									</div>
									<div class="col-md-4">
										<h2 class="text-center calc-header">
											<span class="fa-stack">
												<i class="fa fa-circle fa-stack-2x"></i>
												<strong class="fa-stack-1x" style="color:#333">2</strong>
											</span>
											How many tickets are you likely to sell?
										</h2>
										<button type="button" class="btn btn-white-outline col-md-8 col-md-offset-2 btn-input" onclick="document.getElementById('sales-input').focus();document.execCommand('selectAll',false,null);"><span id="sales-input" contenteditable>0</span> tickets</button>
									</div>
									<div class="col-md-4">
										<h2 class="text-center calc-header">
											<span class="fa-stack">
												<i class="fa fa-circle fa-stack-2x"></i>
												<strong class="fa-stack-1x" style="color:#333">3</strong>
											</span>
											Your ticket price
										</h2>
										<button type="button" class="btn btn-white-outline col-md-8 col-md-offset-2 btn-price-output" >£5.00</button>
									</div>
								</div>
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

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="../js/vendor/jquery-1.11.3.min.js"><\/script>')</script>
        <script src="../js/vendor/slick.js"></script>
        <!--script src="../js/vendor/jquery.slimscroll.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/2.7.6/jquery.fullPage.js"></script>
        <script>(typeof window.jQuery('body').fullpage != "undefined") || document.write('<script src="../js/vendor/jquery.fullPage.min.js"><\/script>')</script-->
        <!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="anonymous"></script-->
        <script>(typeof $().modal == 'function') || document.write('<script src="../js/vendor/bootstrap-4.0.0-alpha.2.min.js"><\/script>')</script>
        <script src="../js/plugins.js"></script>
        <script src="../js/mytickets.js<?php if(DEV) echo "?t=".time() ?>"></script>
</html>