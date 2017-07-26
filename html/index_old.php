<!DOCTYPE html>
<html class="full" lang="en">
<head>
<meta charset="utf-8">
<title>tktpass | Coming Soon</title>
<meta name="robots" content="noindex, follow">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" href="../../favicon.ico">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<link href='//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
<link href="assets/css/bootstrap.min.css" rel="stylesheet" >
<link href="assets/css/font-awesome.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
            <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
</head>

<body>
<div id="preloader">
  <div id="status"> <img src="assets/img/preloader.gif" height="64" width="64" alt=""> </div>
</div>
<div class="coming-soon">
  <div class="container">
    <div class="row">
      <div class="span12">
        <div class="logo">
          <img src="assets/img/logo.svg" />
        </div>
        <h2>Coming soon</h2>
        <p>We are currently working on a brand new website. Stay tuned!</p>
        <h3>Time left until launching</h3>
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
</div>
<div class="container">
  <div class="row">
    <div class="span12 subscribe"><?php
        include 'isValidEmail.php';
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email']) && isValidEmail($_POST['email'])){
          $to      = 'contact@tktpass.com';
          $subject = 'Subscriber!';
          $body = "A user with email ".htmlspecialchars($_POST['email'])." subscribed on the website at ".date('H:i \o\n l jS F Y',$_SERVER['REQUEST_TIME']);
          $headers = 'From: no-reply@tktpass.com' . "\r\n" .
              'Reply-To: ' . $_POST['email'] . "\r\n" .
              'X-Mailer: PHP/' . phpversion();
          if(mail($to, $subject, $body, $headers))
            echo '<p>Thank you, we\'ll be in touch!</p>';
          else
            echo '<p class="error">Oops, something went wrong. Try again later.</p>';
        } else {
      ?>
      <h3>Subscribe to get notified!</h3>
      <form method="post" class="form-inline">
        <input type="email" name="email" placeholder="Enter your email...">
        <button type="submit">Subscribe</button>
      </form><?php
          if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email']))
            echo '<p class="error"><span>Please enter a valid email address.</span></p>';
        }
      ?>
    </div>
  </div>
  <div class="row">
    <div class="span12 social">
      <a href="//facebook.com/tktpassOfficial" target="_blank"><i class="fa fa-facebook-square fa-2x"></i></a>
      <a href="//twitter.com/tktpassOfficial" target="_blank"><i class="fa fa-twitter-square fa-2x"></i></a>
      <a href="//pinterest.com/tktpassOfficial" target="_blank"><i class="fa fa-pinterest-square fa-2x"></i></a>
      <!--a href="#" target="_blank"><i class="fa fa-google-plus-square fa-2x"></i></a>
      <a href="#" target="_blank"><i class="fa fa-linkedin-square fa-2x"></i></a-->
    </div>
  </div>
  <div class="span12 row" style="margin-left:0;">
    <div class="copyright">Copyright &copy; 2016 tktpass.</div>
  </div>
</div>

<!-- Javascript --> 
<script src="assets/js/jquery-1.10.2.min.js"></script> 
<script src="assets/js/bootstrap.min.js"></script> 
<script src="assets/js/jquery.countdown.js"></script> 
<script src="assets/js/custom.js"></script>
</body>
</html>