<?php session_start();?><!DOCTYPE html>
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
        <link rel="stylesheet" href="/old/live/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" integrity="sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd" crossorigin="anonymous">
        <!--link rel="stylesheet" href="css/bootstrap.min.css"-->
        <!--[if lte IE 8]><link rel="stylesheet" href="css/ie8.css"><![endif]-->
        <link rel="stylesheet" href="/old/live/css/bootstrap-social.css">
        <link rel="stylesheet" href="/old/live/css/slick.css">
        <link rel="stylesheet" href="/old/live/css/slick-theme.css">
        <link rel="stylesheet" href="/old/live/css/animate.css">
        <link rel="stylesheet" href="/old/live/css/jquery-ui.css<?php echo "?t=".time() ?>">
        <link rel="stylesheet" href="/old/live/css/jquery.timeentry.css">
        <link rel="stylesheet" href="/old/live/css/main.css<?php echo "?t=".time() ?>">
        <link rel="stylesheet" href="/old/live/css/host.css<?php echo "?t=".time() ?>">
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
          <a class="navbar-brand" href="/"><img src="/old/img/logo_f.svg" class="logo"></a>
          <a class="nav-toggle pull-right" id="toggle"><s class="bar"></s><s class="bar"></s><s class="bar"></s></a>
          <span class="clearfix"></span>
          <ul class="nav nav-pills nav-stacked">
            <?php if(isset($_SESSION['user'])) { ?>
            <li class="nav-item" id="mob-account"><img src="<?php echo $_SESSION['user']['picture'] ?>" class="account-pic"> Hi <?php echo $_SESSION['user']['first_name'] ?></li>
            <li class="nav-item">
              <a class="nav-link" href="/mytickets"><img src="/old/img/tickets.svg" class="mobile-nav-icon"> My Tickets</a>
            </li>
            <li class="nav-item active" style="margin-left: -0.05rem;">
              <a class="nav-link"><img src="/old/img/party.svg?r=001" class="mobile-nav-icon"> My Events <span class="sr-only">(current)</span></a>
            </li>
            <!--li class="nav-item" style="margin-left: -1rem;">
              <a class="nav-link disabled" style="opacity:0.3;cursor:not-allowed"><img src="/old/img/settings.svg" class="mobile-nav-icon"> Settings</a>
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
                    <a class="nav-link" id="account" title="My Account" href="account" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo '<img src="'.(isset($_SESSION['user']['picture']) ? $_SESSION['user']['picture'] : '').'" class="account-pic"> Hi ' . $_SESSION['user']['first_name'].' <i class="fa fa-angle-down"></i>';?></a>
                    <div id="account-drop" class="dropdown-menu" aria-labelledby="account">
                      <a class="dropdown-item" href="/mytickets"><img src="/old/img/tickets.svg?r=001" class="account-drop-icon"> My Tickets</a>
                      <a class="dropdown-item"><img src="/old/img/party.svg?r=001" class="account-drop-icon"> My Events <span class="sr-only">(current)</span></a>
                      <!--a class="dropdown-item" style="opacity:0.3;cursor:not-allowed"><img src="/old/img/settings.svg" class="account-drop-icon"> Settings</a-->
                    </div>
                </li>
            </ul>
          </nav>
        
        <div class="container-fluid" id="main-content">
          
          <div class="row">
            <div class="host-header text-center">
              <h2 class="host-header-text">Host an event</h2>
              <p class="lead">tktpass is the <strong>#1</strong> choice <strong>for societies and students</strong> organising events to raise or make money.</p>
            </div>
          </div>
            
        <div class="form">
          <div class="display-table fb-input" style="position:relative">
            <div class="display-tablecell text-center">
              <div class="form-titles">
                <h1>Do you have a Facebook event?</h1>
                <h5>Start selling your tickets in 2 minutes!</h5>
              </div>
              <div class="fb-input-wrap">
                <span>
                  <input class="basic-slide" id="fb-input" type="text" placeholder="Paste and see the magic happen!">
                  <label for="fb-input"><i class="fa fa-facebook" style="font-size: 2.1rem;"></i></label>
                </span>
                <p>Or <a id="create-link">create a new event</a>.</p>
              </div>
              <div class="alert alert-warning col-md-6 col-md-offset-3" role="alert" style="display:none;">
                <strong>Oops!</strong> This doesn't look like a Facebook event link.
              </div>
              <svg class="progress-icon" width="60" height="60" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:1rem">
                  <defs>
                      <path id="tick-outline-path" d="M14 28c7.732 0 14-6.268 14-14S21.732 0 14 0 0 6.268 0 14s6.268 14 14 14z" opacity="0" />
                      <path id="tick-path" d="M6.173 16.252l5.722 4.228 9.22-12.69" opacity="0"/>
                  </defs>
                  <g class="tick-icon" stroke-width="2" stroke="none" fill="none" transform="translate(1, 1)">
                      <use class="tick-outline" xlink:href="#tick-outline-path" />
                      <use class="tick" xlink:href="#tick-path" />
                  </g>
                  <g class="tick-icon" stroke-width="2" stroke="#aaa" fill="none" transform="translate(1, 1.2)">
                      <use class="tick-outline" xlink:href="#tick-outline-path" />
                      <use class="tick" xlink:href="#tick-path" />
                  </g>
              </svg>
            </div>
          </div>
          <div class="display-table steps-wrap" style="postition:absolute;opacity:0;display:none;">
            <div class="display-tablecell text-center">
              <ul id="progressbar">
                <li class="active">Event Details</li>
                <li class="">Ticket Details</li>
                <li class="">Publish</li>
              </ul>
              <div class="steps">
                <div class="step animated current">
                  <div class="sexy-field">
                    <input type="text" required="required" id="event-name" value="" pattern=".*[A-Za-z]{2,}.*">
                    <label for="event-name">
                      Event name
                    </label>
                    <div class="tick"></div>
                  </div>
                  <div class="sexy-field">
                    <input type="text" required="required" id="event-loc" value="" pattern=".*[A-Za-z]{3,}.*">
                    <label for="event-loc">
                      Location
                    </label>
                    <div class="tick"></div>
                  </div>
                  <div class="sexy-field event-date">
                    <input type="text" required="required" id="event-date" value="" pattern="^ *\d{1,2}\/\d{1,2}\/(?:\d{2}|\d{4}) *$" readonly="true">
                    <label for="event-date">
                      Date
                    </label>
                    <div class="tick"></div>
                  </div>
                  <div class="sexy-field event-time">
                    <input type="time" required="required" id="event-time" value="07:00 PM" pattern="^(?:[01]?\d|2[0123])\:[012345]\d *(?:AM|PM|Am|Pm|aM|pM|am|pm)?$">
                    <label for="event-time">
                    Time (24h)
                    </label>
                    <div class="tick"></div>
                  </div>
                  <span class="clearfix"></span>
                  <div class="sexy-field sexy-textarea">
                    <div id="event-desc" class="textarea" contenteditable pattern=".*(?:[^\s]+(?:\s|$)){3,}.*"></div>
                    <label for="event-desc">
                      Description
                    </label>
                    <div class="tick"></div>
                  </div>
                  <div class="sexy-picture">
                    <label class="sexy-label">Picture</label>
                    <div class="click-div">
                      <img src="/old/img/up-arrow-square.svg?r" class="upload-icon">
                      <img src="" class="uploaded">
                      <input type="file" id="event-pic">
                    </div>
                    <div class="tick"></div>
                  </div>
                  <div class="buttons-row text-center">
                    <a class="btn btn-success animateNextStep">Next</a>
                  </div>
                </div>
                <div class="step animated">
                  <div class="sexy-field">
                    <input type="text" required="required" id="event-name" value="" pattern=".*[A-Za-z]{2,}.*">
                    <label for="ticket-name">
                      Ticket name
                    </label>
                    <div class="tick"></div>
                  </div>
                  <div class="buttons-row text-center">
                    <a class="btn btn-success animatePrevStep">Back</a>
                    <a class="btn btn-success animateNextStep">Next</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
          
          <div class="row">
            <div class="panel-body container-fluid">
              <div class="panel-dim"></div>
              <div class="container">
                <div class="row">
                  <div class="col-sm-6 col-md-3 text-center">
                    <img class="host-icon" src="/old/img/fb-import.svg?" />
                    <h4 class="host-icon-subtitle">Quick and easy</h4>
                    <p class="host-icon-caption">With our import feature, it has never been so simple for you to monetise your event!</p>
                  </div>
                  <div class="col-sm-6 col-md-3 text-center">
                    <img class="host-icon" src="/old/img/shout.svg" />
                    <h4 class="host-icon-subtitle">Just tell 'em</h4>
                    <p class="host-icon-caption">You’ll only need to tell them: from tktpass. Because we’ll do the rest. Generate your tickets, sell them, and process all payments!</p>
                  </div>
                  <div class="col-sm-6 col-md-3 text-center">
                    <img class="host-icon" src="/old/img/mobile-stats.svg" />
                    <h4 class="host-icon-subtitle">Track progress</h4>
                    <p class="host-icon-caption">Once created, you will be able to manage your event and keep and eye on your ticket sales 24/7.</p>
                  </div>
                  <div class="col-sm-6 col-md-3 text-center">
                    <img class="host-icon" src="/old/img/hand-money.svg?" />
                    <h4 class="host-icon-subtitle">Get paid. Fast.</h4>
                    <p class="host-icon-caption">Choose from getting the money in your bank account or collecting it straight after the event.</p>
                  </div>
                </div>
                <div class="row button-row">
                  <div class="col-sm-3 col-sm-offset-3">
                    <button type="button" class="btn btn-white-outline" id="tmm-btn">Tell me more</button>
                  </div>
                  <div class="col-sm-3">
                    <button type="button" class="btn btn-white-outline" id="price-btn">Price</button>
                  </div>
                </div>
                <div id="price-carousel">
                  <div class="text-center">
                    <div class="benefits-list">
                      <ul class="tmm-list">
                        <li> <img src="/old/img/stopwatch.svg"/> Save Time</li>
                        <li><img src="/old/img/increase.svg" style="margin-left: 5px;margin-right: 12px;"/> Sell more tickets</li>
                        <li><img src="/old/img/mobile-qr.svg"/> Scan tickets on the door</li>
                        <li><img src="/old/img/24-hours.svg"/> 24/7 customer support</li>
                      </ul>
                    </div>
                  </div>
                  <div>
                    <div class="col-sm-4 col-sm-offset-4 text-center pricing-div">
                      <h4>Simple Pricing</h4>
                      <h1>5%</h1>
                      <h4>per ticket</h4> 
                      <p>Or free for free events!</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--div class="row steps">
            <div class="col-md-4 text-center step1">
              <h2 class="calc-header">
                <span class="fa-stack">
                  <i class="fa fa-circle fa-stack-2x"></i>
                  <strong class="fa-stack-1x" style="color:#FFF">1</strong>
                </span>
                Enter event details
              </h2>
              <button class="btn btn-default">Import from Facebook</button>
              <button class="btn btn-default">Create</button>
            </div>
            <div class="col-md-4">
              <h2 class="text-center calc-header">
                <span class="fa-stack">
                  <i class="fa fa-circle fa-stack-2x"></i>
                  <strong class="fa-stack-1x" style="color:#FFF">2</strong>
                </span>
                Enter ticket details
              </h2>
              <button type="button" class="btn btn-black-outline col-md-8 col-md-offset-2 btn-input" onclick="document.getElementById('sales-input').focus();document.execCommand('selectAll',false,null);"><span id="sales-input" contenteditable>0</span> tickets</button>
            </div>
            <div class="col-md-4">
              <h2 class="text-center calc-header">
                <span class="fa-stack">
                  <i class="fa fa-circle fa-stack-2x"></i>
                  <strong class="fa-stack-1x" style="color:#FFF">3</strong>
                </span>
                Publish!
              </h2>
              <button type="button" class="btn btn-black-outline col-md-8 col-md-offset-2 btn-price-output" >£5.00</button>
            </div>
          </div>
        </div-->
      </div>
      
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/old/live/js/vendor/jquery-1.11.3.min.js"><\/script>')</script>
        <script src="https://popmotion.io/assets/js/popmotion.global.min.3.5.0.js"></script><!--script src="http://thecodeplayer.com/uploads/js/jquery.easing.min.js"></script-->
        <script src="/old/live/js/vendor/slick.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <!--script src="/old/live/js/vendor/jquery.slimscroll.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/2.7.6/jquery.fullPage.js"></script>
        <script>(typeof window.jQuery('body').fullpage != "undefined") || document.write('<script src="/old/live/js/vendor/jquery.fullPage.min.js"><\/script>')</script-->
        <!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="anonymous"></script-->
        <script>(typeof $().modal == 'function') || document.write('<script src="/old/live/js/vendor/bootstrap-4.0.0-alpha.2.min.js"><\/script>')</script>
        <script src="/old/live/js/plugins.js"></script>
        <script src="js/vendor/jquery.plugin.js"></script>
        <script src="js/vendor/jquery.timeentry.js?r=1"></script>
        <script src="/old/live/js/host.js<?php if(DEV) echo "?t=".time() ?>"></script>
</html>