<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/utils-login.php';
require_once '../includes/fb-setup.php';

$next = '/';
if(isset($_GET['next']) &&
   (is_null(parse_url($_GET['next'],PHP_URL_HOST)) || substr(parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST),-11)=='tktpass.com')
  )
  $next = $_GET['next'];
/* else if(isset($_SERVER['HTTP_REFERER']) && substr(parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST),-11)=='tktpass.com')
  $next = $_SERVER['HTTP_REFERER'];*/

if(isLoggedIn()){
  if(isFbConnected()){
    try {
      $accessToken = new Facebook\Authentication\AccessToken($_SESSION['user']['fb_access_token'], $_SESSION['user']['fb_expires']);
      $fb->setDefaultAccessToken($accessToken);
      $response = $fb->get('/me?fields=id,name,first_name,last_name,email,birthday,gender,picture');
      $user = $response->getGraphUser();
      $propsNames = $user->getPropertyNames();
      foreach ($propsNames as $property) {
        if($property == 'id') $_SESSION['user']['fb_id'] = $user->getProperty($property);
        else $_SESSION['user'][$property] = $user->getProperty($property);
      }
      $_SESSION['user']['picture'] = is_array($_SESSION['user']['picture']) && $_SESSION['user']['picture']['url'] ? $_SESSION['user']['picture']['url'] : $_SESSION['user']['picture'];
      header('Location: '.$next);
      exit;
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error (invalid token?)
      // Try update token? But for now
      logout();
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    } catch(Exception $e) {
      // When validation fails or other local issues
      echo 'An error occurred: ' . $e->getMessage();
      exit;
    }
  } else {
    header('Location: '.$next);
    exit;
  }
}
//$accessToken = $fb->getApp()->getAccessToken();
//$fb->setDefaultAccessToken($accessToken);
$helper = $fb->getRedirectLoginHelper();
$fbLoginUrl = $helper->getLoginUrl('https://'.$_SERVER['HTTP_HOST'].'/fb-callback.php?next='.$next, $fb_permissions);
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>Login | tktpass</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <link href="css/fonts.css" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="css/vendor/bootstrap.css" rel="stylesheet">
    <link href="css/vendor/bootstrap-v4-buttons.css" rel="stylesheet">
    <link href="css/vendor/bootstrap-v4-tags.css" rel="stylesheet">

    <link href="css/vendor/font-awesome.min.css" rel="stylesheet">

    <!-- Bootstrap theme -->
    <!--link href="css/vendor/bootstrap-theme.min.css" rel="stylesheet"-->
    <link href="css/theme/buttons.css" rel="stylesheet">
    <link href="css/theme/navbar.css" rel="stylesheet">
    <link href="css/theme/tables.css" rel="stylesheet">
    <link href="css/theme/tabs.css" rel="stylesheet">
    <link href="css/theme/misc.css" rel="stylesheet">
    <!--link href="css/theme.css" rel="stylesheet"-->

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="css/vendor/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <link rel="stylesheet" href="css/login.css">

    <!-- HTML5 shim for IE8 support of HTML5 elements (for media queries Respond.js is included below regardless) -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <![endif]-->
    <!--script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script-->

    <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
  </head>
  <body role="document" class="">
    <div class="container">
        <div class="card card-container">
            <a href="index.html"><img id="profile-img" class="img-responsive profile-img-card" src="img/brand-black.png" /></a>
            <a href="<?php echo $fbLoginUrl; ?>" class="btn btn-facebook">Continue with Facebook<i class="fa fa-facebook"></i></a>
            <div class="text-separator">
              <span>or</span>
              <hr>
            </div>
            <form class="form-signin" id="signin-form" method="POST" action="//api.tktpass.com/login?next=<?php echo urlencode($next); ?>">
                <input type="email" id="inputEmail" name="email" class="form-control" placeholder="Email address" required autofocus>
                <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
                <div id="remember" class="checkbox">
                    <label class="input-control checkbox">
                        <input type="checkbox" name="persist" checked="" data-show="indeterminate">
                        <span class="check"></span>
                        <span class="caption">Keep me signed in</span>
                    </label>
                </div>
                <button class="btn btn-lg btn-block btn-signin-green" type="submit">Sign in</button>
            </form><!-- /form -->
            <p><a href="forgot.php" class="forgot-password">Forgot your password?</a></p>
            <p><a href="register.php" class="register">Don't have an account?</a></p>
            <hr>
            <p>By proceeding you are agreeing to our <a href="#" target="_blank">Terms &amp; Conditions</a> and <a href="#" target="_blank">Privacy Policy</a>.</p>
        </div><!-- /card-container -->
    </div><!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js\/vendor\/jquery-1.11.2.min.js"><\/script>')</script>

    <script src="js/vendor/bootstrap.min.js"></script>

    <script>
      (function ($, window, document, undefined) {
        $(function(){
          $("#signin-form").on('submit',function(e){
            $(this).find('.has-error').removeClass('animated tada has-error');
            var email = $('#inputEmail').val(),
                password = $('#inputPassword').val();
            var errors = false;
            if(!email || email.length<6)
              errors = '#inputEmail';
            if(!password || password.length<6)
              errors = (errors?errors+',':'')+'#inputPassword';
            if(errors){
              e.preventDefault();
              setTimeout(function(){$(errors).addClass('animated tada has-error');},10);
              return false;
            }
            $.ajax({
              url: 'https://api.tktpass.com/login',
              method: 'POST',
              data: {
                email: email,
                password: password
              },
              xhrFields: {
                withCredentials: true
              },
              success: function(data, textStatus, jqXHR){
                window.location.href = '<?php echo $next ?>';
                return;
              },
              error: function(jqXHR, textStatus, errorThrown){
                if(textStatus === "400"){
                  $('#inputEmail,#inputPassword').addClass('animated tada has-error');
                  return;
                } else {
                  alert('Unexpected '+errorThrown+' occurred, please try again later.');
                  return;
                }
              }
            });
            e.preventDefault();
            return false;
          });
        });
      })(this.jQuery, this, this.document);
    </script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--[if gt IE 9]>
      <script src="js/vendor/ie10-viewport-bug-workaround.js"></script>
    <![endif]-->
  </body>
</html>