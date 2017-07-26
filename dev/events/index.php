<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

require_once '/var/www/includes/db-io.php';
require_once '/var/www/includes/utils-login.php';
require_once '/var/www/includes/fb-setup.php';

if(isLoggedIn() && !user_has_email()){
    header('Location: /fb-email.php?next='.urlencode($_SERVER['REQUEST_URI']));
    die;
}

$helper = $fb->getRedirectLoginHelper();

if(!isLoggedIn()){
  $next = urlencode($_SERVER['REQUEST_URI']);
  $fbLoginUrl = $helper->getLoginUrl('https://'.$_SERVER['HTTP_HOST'].'/fb-callback.php?next='.$next, $fb_permissions);
}

$id = $_GET['id'];
if(!$id){
  die('No event ID provided.');
}
$event = get_event($id);
if(!$event || $event["err"]){
  die('Provided event ID not recognised.');
}
$tickets = get_event_ticket_types($id);
if(!$tickets || $tickets["err"]){
  die('Could not get ticket Information for event with ID '.$id.'.');
}

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title><?php echo substr($event['name'],0,12).(strlen($event['name']) > 12 ? '...' : ''); ?> | tktpass</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!--link href="/css/fonts.css" rel="stylesheet"-->
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
    <link rel="stylesheet" href="/css/event.css">

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
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand navbar-brand-white" href="#">
                  <img src="/img/logo_w.svg">
                </a>
                <a class="navbar-brand navbar-brand-black" href="#">
                  <img src="/img/brand-black.png">
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <!-- <li class="">
                        <a class="btn btn-host" href="/organisers/">Host an event</a>
                    </li> -->
                    <li>
                        <?php
if(!isset($_SESSION['user']))
echo '<a class="btn" href="/login.php?next='.urlencode($_SERVER['REQUEST_URI']).'" style="padding-top: 15px;padding-bottom: 14px;">Login</a>';
else {
  echo '<a class="btn btn-account'.($_SESSION['user']['picture'] ? '' : ' default-pic').'" id="account-btn" href="/mytickets.php" data-obj="'.base64_encode(array("id"=>$_SESSION['user']['id'])).'">'.$_SESSION['user']['first_name'].'<img src="';
  echo $_SESSION['user']['picture'] ? $_SESSION['user']['picture'].'" class="img-circle account-pic" /></a>'."\n" :
    '/img/icon/user.svg" class="img-circle account-pic default-pic" /></a>'."\n";
}
?>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <header style="background-image:url(<?php echo $event['image']; ?>);">
        <div class="dim"></div>
        <div class="header-content">
            <div class="header-content-inner">
                <h1 id="homeHeading"><?php echo substr($event['name'],0,31).(strlen($event['name']) > 31 ? '&hellip;' : ''); ?></h1>
                <p style="font-weight:600">by <?php echo $event['host']; ?></p>
            </div>
        </div>
    </header>
    
    <section>
      <div class="container">
        <div class="row">
          <div class="col-sm-12">
            <p>Content for event with ID <?php echo $id; ?>.</p>
          </div>
        </div>
      </div>
    </section>

    <footer id="footer">
      <div class="container">
        <div class="row footer-callout">
          <h1>Organising an event?</h1>
          <p class="tagline">Sell tickets to your event completely FREE!</p>
          <p>
            <a href="/organisers/#magic" class="btn btn-success">+ CREATE EVENT</a>
          </p>
          <p class="more">
            <a href="/organisers/">Tell me more</a>
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
            <p><a href="/forgot.html" class="forgot-password">Forgot your password?</a></p>
            <p><a href="/register.html" class="register">Don't have an account?</a></p>
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
    <!-- <script src="/js/event.js"></script> -->

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--[if gt IE 9]>
      <script src="/js/vendor/ie10-viewport-bug-workaround.js"></script>
    <![endif]-->
  </body>
</html>
