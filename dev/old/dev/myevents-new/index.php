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
        <title>tktpass</title>
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
        <link rel="stylesheet" href="/css/main.css<?php echo "?t=".time() ?>">
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
        
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-3 col-lg-2 hidden-sm-down sidebar">
              <ul class="nav nav-sidebar">
                <li><a href="/mytickets"><img src="/img/tickets.svg?r=001" class="nav-sidebar-icon"><br>My Tickets</a></li>
                <li class="active"><a><img src="/img/party-w.svg" class="nav-sidebar-icon"><br>My Events <span class="sr-only">(current)</span></a></li>
              </ul>
              <!--ul class="nav nav-sidebar">
                <li><a style="opacity: 0.3;cursor: not-allowed;"><img src="/img/settings.svg" class="nav-sidebar-icon"><br>Settings</a></li>
              </ul-->
            </div>
            <div class="col-md-9 col-md-offset-3 col-lg-10 col-lg-offset-2 account-main">
              <h1 class="page-header">My Events</h1>
        		</div>
						
						<div class="display-table events-placeholder"><p class="display-tablecell lead text-center" style="padding-left: 1rem;padding-right: 1rem;"><span style="font-weight:600;font-size:1.6rem;">This will blow your mind.</span><br><span style="line-height: 0;display: inline-block;width: 80px;border-top: 1px solid #dadada;position: relative;top: -0.45rem;"></span><br>We are finishing building the easiest way to make money.<br>It will be the perfect solution for all event organisers.<br><br>Come back soon ツ</p></div>
        
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
        <script>window.jQuery || document.write('<script src="/js/vendor/jquery-1.11.3.min.js"><\/script>')</script>
        <script src="/js/vendor/slick.js"></script>
        <!--script src="/js/vendor/jquery.slimscroll.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/2.7.6/jquery.fullPage.js"></script>
        <script>(typeof window.jQuery('body').fullpage != "undefined") || document.write('<script src="/js/vendor/jquery.fullPage.min.js"><\/script>')</script-->
        <!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="anonymous"></script-->
        <script>(typeof $().modal == 'function') || document.write('<script src="/js/vendor/bootstrap-4.0.0-alpha.2.min.js"><\/script>')</script>
        <script src="/js/plugins.js"></script>
        <script src="/js/mytickets.js<?php if(DEV) echo "?t=".time() ?>"></script>
</html>