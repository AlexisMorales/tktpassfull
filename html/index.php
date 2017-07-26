<?php
  header('Location: /organisers/');
  die;
  require_once '/var/www/includes/isValidEmail.php';
  $isValid = false;
  $MC_saved = false;
  if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email']) && ($isValid = isValidEmail($_POST['email']))){
    require_once('/var/www/includes/MCAPI.class.php');
    $api = new MCAPI('8bfde0ff9b1c287eba302bab30975068-us14');
    $organisers = "6da4a854c9";
    $users = "bb41b0c778";
    function storeAddress($api, $list, $email){
      if($api->listSubscribe($list, $email, null, 'html', false) === true)
        return true;
      else // An error ocurred
        return false;
    }
    $MC_saved = storeAddress($api, $users, $_POST['email']);
    /*$to      = 'contact@tktpass.com';
    $subject = 'Subscriber!';
    $body = "A user with email ".htmlspecialchars($_POST['email'])." subscribed on the website at ".date('H:i \o\n l jS F Y',$_SERVER['REQUEST_TIME']);
    $headers = 'From: no-reply@tktpass.com' . "\r\n" .
        'Reply-To: ' . $_POST['email'] . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    mail($to, $subject, $body, $headers)*/
  }
?><!DOCTYPE html>
<html class="full" lang="en"  xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
  <meta charset="utf-8">
  <title>tktpass</title>
  <meta name="robots" content="noindex, follow">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="../../favicon.ico">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">
  <!--link href='//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'-->
  <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i|Just+Me+Again+Down+Here" rel="stylesheet">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet" >
  <link href="assets/css/font-awesome.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
      <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->

  <meta property="og:title" content="tktpass | Buy, Sell &amp; Transfer Tickets Last Minute">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://tktpass.com">
  <meta property="og:image" content="http://i.imgur.com/D5bM1Ph.jpg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:site_name" content="tktpass">
  <meta property="fb:admins" content="100000215228624">
  <meta property="fb:app_id" content="1616269921948808">
  <meta property="fb:profile_id" content="915273691851806">
  <meta property="og:description" content="">
  <!--meta property="og:email" content="contact@tktpass.com"-->
  <!-- Facebook Pixel Code -->
  <script> !function(f,b,e,v,n,t,s){
    if(f.fbq) return;
    n = f.fbq = function () {
      n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
    };
    if(!f._fbq) f._fbq = n;
    n.push = n;
    n.loaded = !0;
    n.version = '2.0';
    n.queue = [];
    t = b.createElement(e);
    t.async = !0;
    t.src = v;
    s = b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t, s)
  }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '1270487279662971');
  fbq('track', "PageView");
  </script>
  <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=1270487279662971&ev=PageView&noscript=1" /></noscript>
  <!-- End Facebook Pixel Code -->
</head>

<body>
<?php if($_SERVER['REQUEST_METHOD'] === 'POST'){ ?>
  <div id="fb-root"></div>
  <script>(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.7&appId=220363231474709";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));</script>
<?php } ?>
<!--div id="preloader">
  <div id="status"> <img src="assets/img/preloader.gif" height="64" width="64" alt=""> </div>
</div-->

<div class="outer">
  <div class="inner">
    <div class="navbar">
      <div class="navbar-inner">
        <a class="brand" href="#"><img src="assets/img/logo-white-all.png" /></a>
      </div>
    </div>
    <h1><span id="typed"></span><span class="blinking-cursor">|</span></h1>
    <div class="span6 push6 subscribe">
      <?php if($MC_saved) { ?>
      <p>Like us or tell your friends <br class="hidden-xs">because flexibility is sexy ;)</p>
      <div class="social">
        <div class="fb-like" style="margin-bottom:10px" data-href="//facebook.com/tktpassOfficial" data-layout="button" data-action="like" data-size="small" data-show-faces="true" data-share="false"></div>
        <br>
        <a href="https://www.facebook.com/sharer/sharer.php?u=https%3A//tktpass.com/" target="_blank"><i class="fa fa-facebook-square fa-2x"></i></a>
        <a href="https://twitter.com/home?status=The%20renewed%20tktpass%20is%20coming,%take%20a%20look%20https%3A//tktpass.com/" target="_blank"><i class="fa fa-twitter-square fa-2x"></i></a>
        <!--a href="//pinterest.com/tktpassOfficial" target="_blank"><i class="fa fa-pinterest-square fa-2x"></i></a-->
        <!--a href="#" target="_blank"><i class="fa fa-google-plus-square fa-2x"></i></a>
        <a href="#" target="_blank"><i class="fa fa-linkedin-square fa-2x"></i></a-->
      </div>
      <div class="copyright">Copyright &copy; 2016 tktpass.</div>
      <?php  } else { ?>
      <p class="massaged">Sign up to get a <span>massage</span> when we launch!</p>
      <form method="post" class="form-inline">
        <input type="email" name="email" placeholder="Enter your email">
        <button type="submit">Sign up</button>
      </form>
      <?php
          if(isset($_POST['email']) && !$isValid)
            echo '<p class="error"><span>Please enter a valid email address.</span></p>';
          else
            echo '<p class="error"><span>'.($api->errorMessage).'</span></p>';
        }
      ?>
    </div>
    <div class="span6 pull6 counter-wrapper">
      <p>Time left until launch</p>
      <hr>
      <div class="counter">
        <div class="days-wrapper"> <span class="days"></span> <br>
          days </div>
        <div class="hours-wrapper"> <span class="hours"></span> <br>
          hours </div>
        <div class="minutes-wrapper"> <span class="minutes"></span> <br>
          minutes </div>
        <div class="seconds-wrapper"> <span class="seconds"></span> <br>
          seconds </div>
      </div>
    </div>
  </div>
</div>

<!-- Javascript --> 
<script src="assets/js/jquery-1.10.2.min.js"></script> 
<script src="assets/js/bootstrap.min.js"></script> 
<script src="assets/js/jquery.countdown.js"></script> 
<script src="assets/js/typed.js"></script> 
<script src="assets/js/custom.js"></script>
</body>
</html>