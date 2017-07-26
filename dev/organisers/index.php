<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

require_once '/var/www/includes/db-io.php';
require_once '/var/www/includes/utils-login.php';
require_once '/var/www/includes/fb-setup.php';

if(isLoggedIn() && !user_has_email()){
    header('Location: /fb-email.php?next=/organisers/');
    die;
}

$helper = $fb->getRedirectLoginHelper();
$prefill = false;

$fbLoginUrl = $helper->getLoginUrl('https://'.$_SERVER['HTTP_HOST'].'/fb-callback.php?next='.urlencode($next), $fb_permissions);
if(!isLoggedIn()){
  $next = '/organisers/';
} else if(isset($_COOKIE['prefill'])) {
  $prefill = $_COOKIE['prefill'].'';
  setcookie("prefill", "", time() - 3600, "/");
}

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>tktpass For Organisers</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <!--link href="css/fonts.css" rel="stylesheet"-->
    <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="/css/vendor/bootstrap.css" rel="stylesheet">
    <link href="/css/vendor/bootstrap-v4-buttons.css" rel="stylesheet">
    <link href="/css/vendor/bootstrap-v4-tags.css" rel="stylesheet">
    <link href="/css/vendor/bootstrap-v4-custom-controls.css" rel="stylesheet">

    <!--link rel="stylesheet" href="//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css"/-->
    <link rel="stylesheet" href="/css/vendor/slick.css"/>
    <link rel="stylesheet" href="/css/vendor/slick-theme.css"/>

    <link href="/css/vendor/responsive-calendar.css" rel="stylesheet" media="screen">

    <link href="/css/vendor/font-awesome.min.css" rel="stylesheet">

    <!-- Bootstrap theme -->
    <!--link href="/css/vendor/bootstrap-theme.min.css" rel="stylesheet"-->
    <link href="/css/theme/buttons.css" rel="stylesheet">
    <link href="/css/theme/navbar.css" rel="stylesheet">
    <link href="/css/theme/tables.css" rel="stylesheet">
    <link href="/css/theme/tabs.css" rel="stylesheet">
    <link href="/css/theme/modal.css" rel="stylesheet">
    <link href="/css/theme/misc.css" rel="stylesheet">
    <!--link href="/css/theme.css" rel="stylesheet"-->

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="/css/vendor/ie10-viewport-bug-workaround.css" rel="stylesheet">
    
    <link href="/css/vendor/animate.css" rel="stylesheet">

    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/organisers.css" content="text/css;charset=UTF-8">

    <!-- HTML5 shim for IE8 support of HTML5 elements (for media queries Respond.js is included below regardless) -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <![endif]-->
    <!--script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script-->

    <script src="/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
  </head>

  <body role="document" class="">

    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top affix-top">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                    <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand navbar-brand-white" title="Back to homepage" href="/">
                  <img src="/img/logo_w.svg">
                </a>
                <a class="navbar-brand navbar-brand-black" title="Back to homepage" href="/">
                  <img src="/img/brand-black.png">
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <?php
if(!isLoggedIn())
  echo '<a class="btn btn-account default-pic" href="/login.php?next=/organisers/" style="padding-top: 15px;padding-bottom: 14px;">Login</a>';
else {
  echo '<a class="btn" id="account-btn" href="/myevents.php" data-obj="'.base64_encode(array("id"=>$_SESSION['user']['id'])).'">'.$_SESSION['user']['first_name'].'<img src="';
  echo $_SESSION['user']['picture'] ? $_SESSION['user']['picture'].'" class="img-circle account-pic" /></a>'."\n" :
    'data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjMycHgiIGhlaWdodD0iMzJweCIgdmlld0JveD0iMCAwIDMxMS41NDEgMzExLjU0MSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMzExLjU0MSAzMTEuNTQxOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPGc+CgkJPHBhdGggZD0iTTE1NS43NzEsMjYuMzMxQzY5Ljc0LDI2LjMzMSwwLDk2LjA3MSwwLDE4Mi4xMDJjMCwzNy40ODgsMTMuMjUsNzEuODgzLDM1LjMxNCw5OC43NjEgICAgYzMuNDA0LTI3LjI1NiwzMC42MjctNTAuMzA4LDY4LjgtNjEuMjI1YzEzLjk0NiwxMi45OTQsMzEuOTYsMjAuODc4LDUxLjY1NiwyMC44NzhjMTkuMjMzLDAsMzYuODk0LTcuNDg3LDUwLjY5OC0xOS45MzYgICAgYzM4LjUwMywxMS44NzEsNjUuMTQxLDM2LjI3LDY2LjAxNyw2NC42M2MyNC4yODQtMjcuNDcyLDM5LjA1Ni02My41NTUsMzkuMDU2LTEwMy4xMDggICAgQzMxMS41NDEsOTYuMDcxLDI0MS44MDEsMjYuMzMxLDE1NS43NzEsMjYuMzMxeiBNMTU1Ljc3MSwyMjIuMDY5Yy05Ljk0NCwwLTE5LjMxNC0yLjczMi0yNy42MzQtNy40NjQgICAgYy0yMC4wNS0xMS40MDktMzMuODU1LTM0Ljc1Ni0zMy44NTUtNjEuNzExYzAtMzguMTQzLDI3LjU4My02OS4xNzYsNjEuNDg5LTY5LjE3NmMzMy45MDksMCw2MS40ODksMzEuMDMzLDYxLjQ4OSw2OS4xNzYgICAgYzAsMjcuMzY5LTE0LjIzNyw1MS4wMDQtMzQuNzg2LDYyLjIxNUMxNzQuMzc5LDIxOS41MjMsMTY1LjM0NiwyMjIuMDY5LDE1NS43NzEsMjIyLjA2OXoiIGZpbGw9IiNGRkZGRkYiLz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K" class="img-circle account-pic default-pic" /></a>'."\n";
}
?>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <header>
        <div class="dim dim-5"></div>
        <div class="header-content">
            <div class="header-content-inner">
                <h1 id="homeHeading">Out with ticket fees</h1>
                <hr>
                <p>No fees. No hassle. Sell tickets.</p>
                <a href="#tabs" class="btn btn-outline-white">Learn More</a>
                <a href="#magic" class="btn btn-outline-white filled">Sell tickets &gt;&gt;</a>
            </div>
        </div>
        <img src="/img/reath.png" class="reath" />
    </header>
    
    <section id="tabs" class="container">
      <ul class="nav nav-tabs row" role="tablist">
        <li class="nav-item active item-1 col-xs-4">
          <a class="nav-link" href="#perfect-fit" role="tab" data-toggle="tab">The perfect fit</a>
        </li>
        <li class="nav-item item-2 col-xs-4">
          <a class="nav-link" href="#features" role="tab" data-toggle="tab">What can you do?</a>
        </li>
        <li class="nav-item item-3 col-xs-4">
          <a class="nav-link" href="#pricing" role="tab" data-toggle="tab">Simple pricing</a>
        </li>
        <hr />
      </ul>
      <div class="tab-content">
        <!-- <div role="tabpanel" class="tab-pane fade" id="how-it-works">
          <div>
            <div class="container">
              <div class="row">
                <div class="col-sm-3">
                  <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
                    <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/fb-import.svg" />
                  </svg>
                  <h4>Create</h4>
                  <p>With our import feature, it has never been so quick and easy  for you to add an event!</p>
                </div>
                <div class="col-sm-3">
                  <svg id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
                    <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/speaker.svg" />
                  <h4>Tell 'em</h4>
                  <p>Just spread the word because we’ll do the rest. Generate your tickets, sell them, and process all payments!</p>
                </div>
                <div class="col-sm-3">
                  <svg id="analytics-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
                    <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/analytics.svg" />
                  </svg>
                  <h4>Track progress</h4>
                  <p>Stay in control of your event and keep and eye on your sales 24/7.</p>
                </div>
                <div class="col-sm-3">
                  <svg id="get-paid-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
                    <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/get-paid.svg" />
                  </svg>
                  <h4>Get your money</h4>
                  <p>Forget that old school way to collect money. Get it directly transferred to your bank account!</p>
                </div>
              </div>
            </div>
          </div>
        </div> -->
        <!-- <div role="tabpanel" class="tab-pane fade in active" id="why-us">
          <p>Because we are passionate about making life simple. Plus, it’s FREE!</p>
          <p>We understand how stressful and time-consuming event planning can be, and we think it shouldn't be this way. That is why every day we work very hard on tktpass.</p>
          <p>Our goal is to help event organisers save time and money.</p>
        </div> -->
        <div role="tabpanel" class="tab-pane fade in active" id="perfect-fit">
          <div class="col-sm-3 col-xs-6">
            <img src="/img/icon/business.png">
            <p>Business</p>
          </div>
          <div class="col-sm-3 col-xs-6">
            <img src="/img/icon/society.png">
            <p>Society</p>
          </div>
          <div class="col-sm-3 col-xs-6">
            <img src="/img/icon/sport.png">
            <p>Sport club</p>
          </div>
          <div class="col-sm-3 col-xs-6">
            <img src="/img/icon/charity.png">
            <p>Charity</p>
          </div>
        </div>
        <div role="tabpanel" class="tab-pane container fade" id="features">
          <div class="row">
            <div class="col-xs-6 col-sm-4 feature">
              <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/party.svg" />
              </svg>
              <p><i class="fa fa-check" style="visibility:hidden"></i> Create UNLIMITED events</p>
            </div>
            <div class="col-xs-6 col-sm-4 feature">
              <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="60" width="60" xlink:href="/img/icon/stack.svg" />
              </svg>
              <p><i class="fa fa-check" style="visibility:hidden"></i> Sell UNLIMITED tickets</p>
            </div>
            <span class="visible-xs-inline clearfix"></span>
            <div class="col-xs-6 col-sm-4 feature">
              <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="5" y="5" height="50" width="50"  xlink:href="/img/icon/percent.svg" />
              </svg>
              <p><i class="fa fa-check" style="visibility:hidden"></i> ZERO fees</p>
            </div>
            <span class="hidden-xs clearfix"></span>
            <div class="col-xs-6 col-sm-4 feature">
              <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/tickets.svg" />
              </svg>
              <p><i class="fa fa-check" style="visibility:hidden"></i> Create different types of tickets</p>
            </div>
            <span class="visible-xs-inline clearfix"></span>
            <div class="col-xs-6 col-sm-4 feature">
              <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="60" width="60" xlink:href="/img/icon/payment.svg" />
              </svg>
              <p><i class="fa fa-check" style="visibility:hidden"></i> Online payment</p>
            </div>
            <div class="col-xs-6 col-sm-4 feature">
              <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/qr-scan.svg" />
              </svg>
              <p><i class="fa fa-check" style="visibility:hidden"></i> Easy-scan check-in</p>
            </div>
            <span class="clearfix"></span>
            <div class="col-xs-6 col-sm-4 feature">
              <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/analytics.svg" />
              </svg>
              <p><i class="fa fa-check" style="visibility:hidden"></i> Track real-time progress</p>
            </div>
            <div class="col-xs-6 col-sm-4 feature">
              <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="60" width="60" xlink:href="/img/icon/speaker.svg" />
              </svg>
              <p><i class="fa fa-check" style="visibility:hidden"></i> Promote your event</p>
            </div>
            <span class="visible-xs-inline clearfix"></span>
            <div class="col-xs-6 col-sm-4 feature">
              <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/phone-clock.svg" />
              </svg>
              <p><i class="fa fa-check" style="visibility:hidden"></i> Customer support</p>
            </div>
          </div>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="pricing">
          <h1>100% FREE.</h1>
          <p>With tktpass sell your tickets, pay ZERO fees.</p>
        </div>
      </div>
    </section>

    <!-- <section id="features">
      <h2>What can I do?</h2>
      <div class="container">
        <div class="row">
          <div class="col-xs-6 col-sm-4 feature">
            <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/party.svg" />
            </svg>
            <p><i class="fa fa-check" style="visibility:hidden"></i> Create UNLIMITED events</p>
          </div>
          <div class="col-xs-6 col-sm-4 feature">
            <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <image x="0" y="0" height="60" width="60" xlink:href="/img/icon/stack.svg" />
            </svg>
            <p><i class="fa fa-check" style="visibility:hidden"></i> Sell UNLIMITED tickets</p>
          </div>
          <span class="visible-xs-inline clearfix"></span>
          <div class="col-xs-6 col-sm-4 feature">
            <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <image x="5" y="5" height="50" width="50"  xlink:href="/img/icon/percent.svg" />
            </svg>
            <p><i class="fa fa-check" style="visibility:hidden"></i> ZERO fees</p>
          </div>
          <span class="hidden-xs clearfix"></span>
          <div class="col-xs-6 col-sm-4 feature">
            <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/tickets.svg" />
            </svg>
            <p><i class="fa fa-check" style="visibility:hidden"></i> Create different types of tickets</p>
          </div>
          <span class="visible-xs-inline clearfix"></span>
          <div class="col-xs-6 col-sm-4 feature">
            <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <image x="0" y="0" height="60" width="60" xlink:href="/img/icon/payment.svg" />
            </svg>
            <p><i class="fa fa-check" style="visibility:hidden"></i> Online payment</p>
          </div>
          <div class="col-xs-6 col-sm-4 feature">
            <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/qr-scan.svg" />
            </svg>
            <p><i class="fa fa-check" style="visibility:hidden"></i> Easy-scan check-in</p>
          </div>
          <span class="clearfix"></span>
          <div class="col-xs-6 col-sm-4 feature">
            <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/analytics.svg" />
            </svg>
            <p><i class="fa fa-check" style="visibility:hidden"></i> Track real-time progress</p>
          </div>
          <div class="col-xs-6 col-sm-4 feature">
            <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <image x="0" y="0" height="60" width="60" xlink:href="/img/icon/speaker.svg" />
            </svg>
            <p><i class="fa fa-check" style="visibility:hidden"></i> Promote your event</p>
          </div>
          <span class="visible-xs-inline clearfix"></span>
          <div class="col-xs-6 col-sm-4 feature">
            <svg class="animated" style="opacity:0" id="speaker-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/phone-clock.svg" />
            </svg>
            <p><i class="fa fa-check" style="visibility:hidden"></i> Customer support</p>
          </div>
        </div>
      </div>
    </section> -->

    <section id="reviews">
      <h2>What others are saying</h2>
      <div id="testimonial-carousel">
        <div class="testimonial-wrap">
        <div class="testimonial">
            <img src="../img/testimonials/wesley.jpg" class="testimonial-pic">
            <div class="testimonial-content">
              <p class="quotation">"Brilliant! Reselling a ticket through tktpass is so much easier than trying to find students on Facebook, it has really saved me time."</p>
              <p class="testimonial-caption">Wesley — <small>3rd Year, Warwick</small> <br class="visible-xs-inline"><span class="stars"><img src="../img/icon/star.png"><img src="../img/icon/star.png"><img src="../img/icon/star.png"><img src="../img/icon/star.png"><img src="../img/icon/star.png"></span></p>
            </div>
        </div>
        </div>
        <div class="testimonial-wrap">
        <div class="testimonial">
            <img src="../img/testimonials/valentina.jpg" class="testimonial-pic">
            <div class="testimonial-content">
              <p class="quotation">"Booking tickets like this is purely amazing! I've been using it for a while now and have never been dissapointed."</p>
              <p class="testimonial-caption">Valentina — <small>3rd Year, Warwick</small> <br class="visible-xs-inline"><span class="stars"><img src="../img/icon/star.png"><img src="../img/icon/star.png"><img src="../img/icon/star.png"><img src="../img/icon/star.png"><img src="../img/icon/star.png"></span></p>
            </div>
        </div>
        </div>
        <div class="testimonial-wrap">
        <div class="testimonial">
            <img src="../img/testimonials/rebecca.jpg" class="testimonial-pic">
            <div class="testimonial-content">
              <p class="quotation">"Professional site, brilliant solution to changing plans, charming and reliable people behind the scenes. The perfect combination for a night out."</p>
              <p class="testimonial-caption">Rebecca — <small>3rd Year, Warwick</small> <br class="visible-xs-inline"><span class="stars"><img src="../img/icon/star.png"><img src="../img/icon/star.png"><img src="../img/icon/star.png"><img src="../img/icon/star.png"><img src="../img/icon/star.png"></span></p>
            </div>
        </div>
        </div>
      </div>
    </section>

    <section id="magic">
      <h2>Let us show you how simple it is to sell tickets</h2>
      <div class="input-group input-group-lg fb-input-group">
        <span class="input-group-addon"><i class="fa fa-facebook"></i></span>
        <input type="text" class="form-control fb-input" placeholder="Paste your Facebook event link">
        <input type="hidden" id="event-fb-id" value="">
      </div>
      <p>Or <a id="create-link"  data-toggle="modal" <?php echo isLoggedIn() ? 'data-target="#publish-steps-modal"' : 'data-target="#login-modal"'; ?>
      >create a new event</a>.</p>
      <div class="alert alert-warning" role="alert" style="display:none;">
        <strong>Oops!</strong> This doesn't look like a Facebook event link.
      </div>
      <div class="alert alert-danger" role="alert" style="display:none;">
        <strong>Error:</strong> An error occured, check and try again in a few moments.
      </div>
      <svg class="progress-icon" width="60" height="60" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
          <defs>
              <path id="tick-outline-path" d="M14 28c7.732 0 14-6.268 14-14S21.732 0 14 0 0 6.268 0 14s6.268 14 14 14z" opacity="0" />
              <path id="tick-path" d="M6.173 16.252l5.722 4.228 9.22-12.69" opacity="0"/>
          </defs>
          <g class="tick-icon" stroke-width="2" stroke="none" fill="none" transform="translate(1, 1)">
              <use class="tick-outline" xlink:href="#tick-outline-path" />
              <use class="tick" xlink:href="#tick-path" />
          </g>
          <g class="tick-icon" stroke-width="2" stroke="#5ac336" fill="none" transform="translate(1, 1.2)">
              <use class="tick-outline" xlink:href="#tick-outline-path" />
              <use class="tick" xlink:href="#tick-path" />
          </g>
      </svg>
    </section>

    <section id="calculator">
      <h2>Compare your revenue&hellip;</h2>
      <table>
        <tr>
          <th class="num-col">How many tickets will you sell?</th>
          <th class="price-col">What is the price of a ticket?</th>
          <th class="fee-col">Fees<br><small>We don't charge you any!</small></th>
          <th class="rev-col">Your estimated revenue</th>
        </tr>
        <tr class="tktpass">
          <td class="num-col"><input id="num-tickets" class="form-control" type="number" value="300"></td>
          <td class="price-col"><input type="number" step="0.01" min="0" max="1000" id="ticket-price" class="form-control" value="12.00"></td>
          <td class="fee-col">£ <span style="font-size:2em">0</span>.00</td>
          <td class="rev-col">£ <span style="font-size:2em">3600</span>.00</td>
        </tr>
        <tr class="diff">
          <td class="num-col"></td>
          <td class="price-col"></td>
          <td class="fee-col"></td>
          <td class="rev-col"><img src="/img/red-triangle-down.png" style="vertical-align: text-top;"> <span id="diff">9.6</span>%</td>
        </tr>
        <tr class="other">
          <td class="num-col">
            <select id="other" class="form-control">
              <option value="eventbrite" selected>Eventbrite</option>
              <option value="billetto">Billetto</option>
              <option value="ticketleap">Ticketleap</option>
            </select>
          </td>
          <td class="price-col"></td>
          <td class="fee-col">£ <span style="font-size:2em">345</span>.00</td>
          <td class="rev-col">
            £ <span style="font-size:2em">3255</span>.00
          </td>
        </tr>
      </table>
    </section>

    <div class="green-highlight">
      <div class="container">
        <div class="row">
          <div class="col-sm-9">
            <h1>Sell your tickets for free.</h1>
          </div>
          <div class="col-sm-3">
            <a href="#magic" class="btn btn-outline-white">+ CREATE EVENT</a>
          </div>
        </div>
      </div>
    </div>

    <section id="faq">
      	<h2>Frequently asked questions, answered.</h2>
	    <div class="container">
	      <div class="row">
            <div class="col-md-6">
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question0">
		                 <h4 class="panel-title">
		                    <a href="javascript:">Seriously, free?!</a>
		              	</h4>
		            </div>
		            <div id="question0" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p>Yes. You don't pay initial-setup, monthly, annual or per-ticket fees. We organised events and know how much time and effort you put in when planning events. We at tktpass feel you shouldn't pay fees on top of that. It's not fair. That's why we promise to help you sell your tickets with no hassle and without fees.</p>
		                </div>
		            </div>
		        </div>
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question1">
		                 <h4 class="panel-title">
		                    <a href="javascript:">How do tickets work?</a>
		              	</h4>
		            </div>
		            <div id="question1" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p>When someone purchases your ticket, we'll send them an email with your event details, a booking reference, and a unique barcode. You can use any free barcode app (eg. <a href="//itunes.apple.com/gb/app/qr-reader-for-iphone/id368494609?mt=8" target="_blank">iOS</a>, <a href="//play.google.com/store/apps/details?id=com.google.zxing.client.android&hl=en_GB" target="_blank">Android</a>) to scan the tickets at the entrance, or if you are old school, <strong>Coming soon</strong>: you can also download and print a beautiful sheet with attendee names and the tickets they bought. Simple!</p>
		                </div>
		            </div>
		        </div>
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question2">
		                 <h4 class="panel-title">
		                    <a href="javascript:">How do I collect my money?</a>
		              	</h4>
		            </div>
		            <div id="question2" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p><strong>Coming soon</strong>: As soon as your event ends, we'll immediately send the payment to your bank account. Note: depending on your bank it can then take a couple of days to appear in your account. However, we also know how important is to access your sales revenue before the event to cover expenses, so if you need to be paid before the event, you can apply for early payments by verifying your identity with us &mdash; <a href="contact@tktpass.com">contact us</a> to enquire about this.</p>
		                </div>
		            </div>
		        </div>
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question3">
		                 <h4 class="panel-title">
		                    <a href="javascript:">If it's free, are you homeless?</a>
		              	</h4>
		            </div>
		            <div id="question3" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p>Haha, not yet! Our costs are covered by a proportional and capped charge for handling/processing card payments when people purchase tickets.</p>
		                </div>
		            </div>
		        </div>
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question4">
		                 <h4 class="panel-title">
		                    <a href="javascript:">Do I need to pay the payment processing fees?</a>
		              	</h4>
		            </div>
		            <div id="question4" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p>No amigo. We'll absorb them for you :)</p>
		                </div>
		            </div>
		        </div>
            </div>
            <div class="col-md-6">
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question5">
		                 <h4 class="panel-title">
		                    <a href="javascript:">Can I see the attendees list for my event?</a>
		              	</h4>
		            </div>
		            <div id="question5" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p><strong>Coming soon</strong>: Yes, and you can also download it. You have a user-friendly dashboard that helps to monitor the attendees for all your events and keep an eye on your ticket sales 24/7.</p>
		                </div>
		            </div>
		        </div>
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question6">
		                 <h4 class="panel-title">
		                    <a href="javascript:">Can I run a public event and give a special price to certain members?</a>
		              	</h4>
		            </div>
		            <div id="question6" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p><strong>Coming soon</strong>: Yes, you can! And it’s very simple.</p>
		                     <p>When creating your event tickets, under the "members only" option just upload a (comma- or line-separated) list of the relevant email addresses. We'll do the rest and only allow those members to purchase those tickets.</p>
		                </div>
		            </div>
		        </div>
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question7">
		                 <h4 class="panel-title">
		                    <a href="javascript:">Will I be able to message my attendees?</a>
		              	</h4>
		            </div>
		            <div id="question7" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p><strong>Coming soon</strong>: Yes. If you need to communicate a major update or information, tktpass allows you to send a message to everyone who has purchased a ticket.</p>
		                </div>
		            </div>
		        </div>
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question8">
		                 <h4 class="panel-title">
		                    <a href="javascript:">Can I run private events?</a>
		              	</h4>
		            </div>
		            <div id="question8" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p>Absolutely! When you create your event just select “private” and your event will be hidden from the site. You will still have your event URL so you can share it with only the people you want.</p>
		                </div>
		            </div>
		        </div>
				<div class="panel panel-default">
		            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#faq" data-target="#question9">
		                 <h4 class="panel-title">
		                    <a href="javascript:">What happens if I have a periodic event?</a>
		              	</h4>
		            </div>
		            <div id="question9" class="panel-collapse collapse" style="height: 0px;">
		                <div class="panel-body">
		                     <p>Cry! <strong>Coming soon</strong>: &hellip;tears of happiness! Because with 1-click you can clone it or enable automatic recurrence. ;)</p>
		                </div>
		            </div>
		        </div>
            </div>
	      </div>
	      <p style="font-size:1em;color:#999;text-align:center;">Got more questions? Ask us <a href="mailto:contact@tktpass.com">here</a> :)</p>
	    </div>
    </section>

    <footer id="footer">
      <div class="container">
        <div class="row footer-callout">
          <h1 style="text-transform:uppercase;">Screw ticket fees.</h1>
          <p class="tagline">It's your choice to sell your tickets completely free.</p>
          <p>
            <a href="#magic" class="btn btn-success">Sell tickets</a>
          </p>
        </div>
        <div class="row">
            <p class="lead">Follow tktpass on Facebook</p>
            <div class="lead-sub">
              Get free event planning advice
              <div class="fb-like" style="display:inline-block;margin-left:10px;" data-href="http://facebook.com/tktpassOfficial" data-layout="button_count" data-action="like" data-size="small" data-show-faces="true" data-share="false"></div>
            </div>
          <!-- <div class="col-sm-5">
            <a href="/organisers/#magic" class="btn btn-outline-white">+ CREATE EVENT</a>
            <a href="/membership.php" class="btn btn-outline-white green">GET FREEDOM</a>
          </div> -->
          <p><a href="mailto:contact@tktpass.com">Contact Us</a></p>
          <p class="social">
            <a href="http://facebook.com/tktpassOfficial" target="_blank"><i class="fa fa-facebook fa-lg"></i></a>
            <a href="http://twitter.com/tktpassOfficial" target="_blank"><i class="fa fa-twitter fa-lg"></i></a>
          </p>
          <p><a>Privacy Policy</a> | <a>Terms and Conditions</a> | tktpass &copy; All Rights Reserved.</p>
        </div>
      </div>
    </footer>

    <!-- Create Modal -->
    <div class="modal modal-fullscreen fade" id="publish-steps-modal" tabindex="-1" role="dialog" aria-labelledby="Create an event modal" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              <svg id="speaker-icon" height="40" width="40" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="40" width="40" xlink:href="/img/icon/close-circular.svg"></image>
              </svg>
              <span class="sr-only">Close</span>
            </button>
          </div>
          <div class="modal-body">
            
            
            <div class="display-table steps-wrap">
              <div class="container display-tablecell text-center">
                <ul class="progressbar">
                  <li class="active">Event Details</li>
                  <li class="">Ticket Details</li>
                  <li class="">Publish</li>
                </ul>
                <div class="steps">
                  <div class="step animated active">
                    <div class="sexy-field col-sm-6">
                      <img src="/img/icon/pencil.svg" class="icon" />
                      <label for="event-name">Event name</label>
                      <input type="text" required="required" id="event-name" value="" pattern="\s*[A-Za-z0-9]{2,}\s*">
                      <div class="tick"></div>
                    </div>
                    <div class="sexy-field col-sm-6">
                      <img src="/img/icon/user-circle.svg" class="icon" />
                      <label for="event-host">Hosted by</label>
                      <input type="text" required="required" id="event-host" value="" pattern="\s*[A-Za-z0-9]{2,}\s*">
                      <div class="tick"></div>
                    </div>
                    <span class="clearfix"></span>
                    <div class="sexy-field col-sm-6">
                      <img src="/img/icon/map.svg" class="icon" />
                      <label for="event-address-1">Address</label>
                      <input type="text" id="event-predict" placeholder="Start typing..">
                      <img class="gm-logo" src="//developers.google.com/places/documentation/images/powered-by-google-on-white.png" />
                      <input type="text" required="required" id="event-venue" placeholder="Venue name" style="display:none;" value="" pattern="\s*[A-Za-z0-9@!#$%\&'*+\-\/=?^_{\|}~ ]{3,}\s*">
                      <input type="text" required="required" id="event-address-1" placeholder="Street" style="display:none;" value="" pattern="\s*[A-Za-z0-9\- ]{3,}\s*">
                      <input type="text" id="event-address-2" class="visible-xs-inline" placeholder="" value="" pattern="\s*[A-Za-z0-9\- ]{3,}\s*">
                      <input type="text" required="required" id="event-city" placeholder="City" style="display:none;" value="" pattern="\s*[A-Za-z\- ]{3,}\s*">
                      <input type="text" required="required" id="event-postcode" placeholder="Post code" style="width:50%;display:none;" value="" pattern="\s*[A-Za-z0-9\- ]{3,}\s*">
                      <div class="tick"></div>
                      <span class="clearfix"></span>
                      <a id="event-add-address-line" style="display:none;" href="javascript:$.noop()">Need another line?</a>
                    </div>
                    <div class="col-sm-6 gm">
                      <div id="gm-map" style="display:none"></div>
                      <ul id="gm-results" class="list-group"></ul>
                    </div>
                    <span class="clearfix"></span>
                    <div class="sexy-field col-sm-6">
                      <img src="/img/icon/clock.svg" class="icon" />
                      <label for="event-start-date">Start</label>
                      <input type="text" required="required" id="event-start-date" placeholder="dd/mm/yy" value="" pattern="^ *(?:0?[1-9]|[12][0-9]|30|31)\/(?:0?[1-9]|[1][012])\/(?:\d{2}|\d{4}) *$">
                      <input type="text" required="required" id="event-start-time" placeholder="hh:mm" value="" pattern="^(?:[01]?\d|2[0123]):[012345]\d *(?:AM|PM|Am|Pm|aM|pM|am|pm)?$">
                      <div class="tick"></div>
                    </div>
                    <div class="sexy-field col-sm-6">
                      <img src="/img/icon/clock.svg" class="icon" />
                      <label for="event-end-date">End</label>
                      <input type="text" required="required" id="event-end-date" placeholder="dd/mm/yy" value="" pattern="^ *(?:0?[1-9]|[12][0-9]|30|31)\/(?:0?[1-9]|[1][012])\/(?:\d{2}|\d{4}) *$">
                      <input type="text" required="required" id="event-end-time" placeholder="hh:mm" value="" pattern="^(?:[01]?\d|2[0123])\:[012345]\d *(?:AM|PM|Am|Pm|aM|pM|am|pm)?$">
                      <div class="tick"></div>
                    </div>
                    <span class="clearfix"></span>
                    <div class="sexy-field sexy-textarea">
                      <img src="/img/icon/details.svg" class="icon" />
                      <label for="event-desc">Description</label>
                      <div id="event-desc" class="textarea" contenteditable pattern="\s*[A-Za-z0-9@!#$%&'*+-/=?^_{|}~ ]{32,}\s*"></div>
                      <div class="tick"></div>
                    </div>
                    <div class="sexy-picture">
                      <label class="sexy-label">Picture</label>
                      <div class="click-div">
                        <img src="/img/icon/up-arrow-square.svg" class="upload-icon">
                        <img src="" class="uploaded">
                        <form id="upload-form" action="" method="post" enctype="multipart/form-data">
                          <input type="file" name="image" id="event-pic" accept="image/jpeg,image/png">
                        </form>
                        <input type="hidden" id="event-pic-url">
                      </div>
                      <div class="tick"></div>
                    </div>
                    <div style="text-align:left">
                      <label class="sexy-label">Privacy</label>
                      <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-success active" onclick="if(!$(this).hasClass('btn-success'))$(this).toggleClass('btn-success btn-default').next().toggleClass('btn-success btn-default')">
                          <input type="radio" name="private" value="false" id="privacy-button-public" checked> Public
                        </label>
                        <label class="btn btn-default" onclick="if(!$(this).hasClass('btn-success'))$(this).toggleClass('btn-success btn-default').prev().toggleClass('btn-success btn-default')">
                          <input type="radio" name="private" value="true" id="privacy-button-private"> Private
                        </label>
                      </div>
                    </div>
                  </div>
                  
                  <div class="step animated">
                    <button type="button" class="btn btn-success" id="ticket-table-add-row">+ Add Ticket Type</button>
                    <table id="ticket-table" class="borderless">
                      <tbody>
                        <tr>
                          <th></th>
                          <th>Name</th>
                          <th>Price</th>
                          <th>Quantity</th>
                        </tr>
                        <tr>
                          <td>
                            <select required class="custom-select">
                              <!--option selected disabled>Ticket Type</option-->
                              <option selected value="1">Paid</option>
                              <option value="0">Free</option>
                              <option value="2">Donation</option>
                            </select>
                          </td>
                          <td>
                            <div class="sexy-field">
                              <input type="text" class="ticket-name" name="ticket-1-name" required="required" placeholder="eg. General" pattern="\s*[A-Za-z]{2,}\s*">
                            </div>
                          </td>
                          <td class="price">
                            <div class="sexy-field">
                              <input type="text" class="ticket-price" name="ticket-1-price" required="required" placeholder="eg. 6.00" value="" pattern="\s*\d+(?:\.\d\d)?\s*">
                            </div>
                          </td>
                          <td class="quantity">
                            <div class="sexy-field">
                              <input type="tel" class="ticket-quantity" name="ticket-1-quantity" required="required" value="" pattern="\s*\d{1,3}\s*">
                            </div>
                          </td>
                          <td><i class="fa fa-trash-o fa-2x"></i></td>
                        </tr>
                      </tbody>
                    </table>
                    <script type="text/template" id="ticket-table-row-template">
                      <tr>
                        <td>
                          <select required class="custom-select">
                              <!--option selected disabled>Ticket Type</option-->
                              <option selected value="1">Paid</option>
                              <option value="0">Free</option>
                              <option value="2">Donation</option>
                          </select>
                        </td>
                        <td>
                          <div class="sexy-field">
                            <input type="text" class="ticket-name" name="ticket-<%= index %>-name" required="required" placeholder="" value="" pattern="\s*[A-Za-z]{2,}\s*">
                          </div>
                        </td>
                        <td class="price">
                          <div class="sexy-field">
                            <input type="text" class="ticket-price" name="ticket-<%= index %>-price" required="required" value="" pattern="\s*\d+(?:\.\d\d)?\s*">
                          </div>
                        </td>
                        <td class="quantity">
                          <div class="sexy-field">
                            <input type="tel" class="ticket-quantity" name="ticket-<%= index %>-quantity" required="required" value="" pattern="\s*\d{1,3}\s*">
                          </div>
                        </td>
                        <td><i class="fa fa-trash-o fa-2x"></i></td>
                      </tr>
                    </script>
                  </div>
                  
                  <div class="step animated final">
                    <h2>Congratulations!</h2>
                    <p class="lead">Your event is published and you're selling your tickets with ZERO fees!</p>
                    <div class="text-separator">
                      <span>What's next?</span>
                      <hr>
                    </div>
                    
                    <ul class="nav nav-tabs row" role="tablist">
                      <li class="nav-item active item-1 col-xs-6">
                        <a class="nav-link" href="#whats-next-payment" role="tab" data-toggle="tab">
                          <img type="image/svg+xml" src="/img/icon/bank.svg" width="64" height="64" />
                          <br>Set up payment
                        </a>
                      </li>
                      <li class="nav-item item-2 col-xs-6">
                        <a class="nav-link" href="#whats-next-share" role="tab" data-toggle="tab">
                          <img type="image/svg+xml" src="/img/icon/promote.svg" width="64" height="64" />
                          <br>Share your event
                        </a>
                      </li>
                    </ul>
                    <div class="tab-content">
                      <div role="tabpanel" class="tab-pane fade in active" id="whats-next-payment">
                        <form class="form-update-payment">
                          <input type="tel" id="payment-sc" name="payment-sc" class="form-control" placeholder="Sort code" required>
                          <input type="tel" id="payment-acc" name="payment-acc" class="form-control" placeholder="Account number" required>
                          <input type="hidden" id="payment-swift" name="payment-swift" class="form-control" placeholder="SWIFT">
                          <input type="hidden" id="payment-iban" name="payment-iban" class="form-control" placeholder="IBAN">
                          <input type="hidden" id="payment-international" value="false" required>
                          <button class="btn btn-lg btn-block btn-success" type="submit">Update Information</button>
                        </form>
                        <a id="payment-swap-international" href="javascript:;"><small>Change to IBAN and SWIFT</small></a>
                      </div>
                      <div role="tabpanel" class="tab-pane fade" id="whats-next-share">
                        <div class = "btn-group-vertical">
                           <a target="_blank" type="button" class="btn btn-facebook" href>Share your event on Facebook<i class="fa fa-facebook"></i></a>
                           <a target="_blank" type="button" class="btn btn-twitter" href>Tweet out your event<i class="fa fa-twitter"></i></a>
                           <a target="_blank" type="button" class="btn btn-default" href>Send emails<i class="fa fa-envelope"></i></a>
                        </div>
                        <p id="event-link-p">Your event link:</p>
                        <input id="event-link" type="text" readonly class="form-control" value="https://tktpass.com/events/uniexpress-smack-week-3">
                      </div>
                    </div>
                  </div>
                  
                </div>
              </div>
            </div>
            
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-animate="prev" style="display:none">Back</button>
            <button type="button" class="btn btn-success" data-animate="next">Next</button>
          </div>
        </div>
        <div class="spinner-wrap" id="upload-spinner" style="display:none"><div class="spinner"></div></div>
      </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="Login modal" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              <svg id="speaker-icon" height="45" width="45" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <image x="0" y="0" height="45" width="45" xlink:href="/img/icon/close-circular.svg" />
              </svg>
              <span class="sr-only">Close</span>
            </button>
          </div>
          <div class="modal-body">
            <a href="<?php echo $fbLoginUrl; ?>" class="btn btn-facebook"><i class="fa fa-facebook"></i>Continue with Facebook</a>
            <div class="text-separator">
              <span>or</span>
              <hr>
            </div>
            <form class="form-signin">
                <input type="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
                <input type="password" id="inputPassword" class="form-control" placeholder="Password" required>
                <div id="remember" class="checkbox">
                    <label class="input-control checkbox">
                        <input type="checkbox" checked="" data-show="indeterminate">
                        <span class="check"></span>
                        <span class="caption">Keep me signed in</span>
                    </label>
                </div>
                <button class="btn btn-lg btn-block btn-signin-green" type="submit">Sign in</button>
            </form><!-- /form -->
            <p><a href="forgot.html" class="forgot-password">Forgot your password?</a></p>
            <p><a href="register.html" class="register">Don't have an account?</a></p>
            <hr>
            <p>By proceeding you are agreeing to our <a href="#" target="_blank">Terms &amp; Conditions</a> and <a href="#" target="_blank">Privacy Policy</a>.</p>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="/js\/vendor\/jquery-1.11.2.min.js"><\/script>')</script>

    <script src="/js/vendor/bootstrap.min.js"></script>
    <script src="/js/vendor/bootstrap-tab.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    
    <script src="/js/vendor/responsive-calendar.min.js"></script>

    <!--script src="//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js"></script>
    <script>window.jQuery.slick || document.write('<script src="/js\/vendor\/slick.min.js"><\/script>')</script-->
    <script src="/js/vendor/slick.min.js"></script>
    
    <script src="https://popmotion.io/assets/js/popmotion.global.min.3.5.0.js"></script>
    
    <script src="/js/main.js"></script>
    <script src="/js/organisers.js"></script>
    <?php if($prefill !== false) {
      if($prefill === "0") {
    ?>
    <script>
      $(function(){
        $("html, body").scrollTop($("#tabs").offset().top - $('#mainNav .navbar-brand-black').height());
        $("#publish-steps-modal").modal('show');
      });
    </script>
    <?php } else { ?>
    <script>
      $(function(){
        $("html, body").scrollTop($("#tabs").offset().top - $('#mainNav .navbar-brand-black').height());
        $('#magic .fb-input').val('/events/<?php echo $prefill ?>').trigger('input');
      });
    </script>
    <?php } } ?>
    <script>
      function initService() {
        $(function(){setTimeout(function(){
          window.autocompleteService = new google.maps.places.AutocompleteService();
          window.map = new google.maps.Map(document.getElementById('gm-map'));
          window.placesService = new google.maps.places.PlacesService(window.map);

          var displaySuggestions = function(predictions, status) {
            $(document.getElementById('gm-results')).empty();
            if (status === google.maps.places.PlacesServiceStatus.ZERO_RESULTS)
              return;
            if (status != google.maps.places.PlacesServiceStatus.OK) {
              alert('Google Maps error: '+status);
              return;
            }
            predictions.forEach(function(prediction) {
              var terms = $.map(prediction.terms,function(t){return t.value});
              var $li = $(document.createElement('li')).addClass('list-group-item').append('<i class="fa fa-map-marker" aria-hidden="true"></i> '+terms.slice(0,3).join(', ')).data(prediction);
              $(document.getElementById('gm-results')).append($li);
            });
          };

          $("#event-predict").on("input",function(){
            if($(this).val().trim().length > 2)
              autocompleteService.getPlacePredictions({
                input: $(this).val(),
                componentRestrictions: {country: 'gb'}
              }, displaySuggestions);
            else
              $(document.getElementById('gm-results')).empty();
          });
          $("#gm-results").on("click","li",function(){
            if($(this).data('types').indexOf('establishment') !== -1)
              $('#event-venue').val($(this).data('terms')[0].value).addClass('has-value valid');
            var id = $(this).data('place_id');
            placesService.getDetails({'placeId':id},function(placeResult, placesServiceStatus){
              $('#event-predict').hide().siblings('.gm-logo').hide();
              var components = placeResult.address_components;
              function findInComponents(components, type, name){
                var found = '';
                $.each(components,function(i,o){
                  if(o.types[0] === type) found = o[name];
                });
                return found;
              }
              var sn = findInComponents(components, 'street_number', 'long_name');
              var st = findInComponents(components, 'route', 'short_name');
              $('#event-venue').show();
              $('#event-address-1').val((sn?sn+' ':'')+st).show().addClass('has-value valid');
              $('#event-city').val(findInComponents(components, 'postal_town', 'long_name')).show().addClass('has-value valid');
              $('#event-postcode').val(findInComponents(components, 'postal_code', 'long_name')).show().addClass('has-value valid');
              $('#event-postcode').siblings('a').show();
              $('#gm-results').empty().siblings('img').hide();
            });
          });
        },2000)});
      }
    </script>

    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.8&appId=1616269921948808";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3M1zCdef4Qm9zIpT-pIkHe2aKgfjw0PU&libraries=places&callback=initService"
        async defer></script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--[if gt IE 9]>
      <script src="/js/vendor/ie10-viewport-bug-workaround.js"></script>
    <![endif]-->
  </body>
</html>
