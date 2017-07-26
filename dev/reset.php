<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db-io.php';
require_once '../includes/utils-login.php';

/*if(!isset($_GET['selector']) || !$_GET['selector'] || !isset($_GET['validator']) || !$_GET['validator']){
  header("Location: /");
  exit;
}*/

$row = validate_user_recovery($_GET['selector'], $_GET['validator']);
if(!$row || $row['err']){
  die($row['err']);
}

if(isLoggedIn()){
  logout();
  header("Refresh:0");
  exit;
}

$token = set_used_user_recovery($row['id']);
if(!$token || $token['err']){
  die($token['err']);
} else
  $token = $token['token'];

$user = get_user($row['user_id']);
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>Reset password | tktpass</title>
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

    <link rel="stylesheet" href="/css/vendor/animate.css">

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
            <img id="profile-img" class="img-responsive profile-img-card" src="img/brand-black.png" />
            <h4>Reset password</h4>
            <p>To complete resetting your tktpass password, please enter your new password below.</p>
            <form class="form-signin" id="reset-form">
                <input type="password" id="inputPassword" class="form-control" placeholder="New password" autocomplete="new-password" required autofocus>
                <input type="password" id="inputPassword2" class="form-control" placeholder="Confirm password" autocomplete="new-password" required>
                <button class="btn btn-lg btn-block btn-signin-green" type="submit">Submit</button>
            </form><!-- /form -->
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
          $("#reset-form").on('submit',function(e){
            e.preventDefault();
            $('#inputPassword,#inputPassword2').removeClass('animated tada has-error');
            var password = $('#inputPassword').val();
            var password2 = $('#inputPassword2').val();
            if(!password || password.length<6){
              setTimeout(function(){$('#inputPassword').addClass('animated tada has-error');},10);
              return false;
            }
            if(!password2 || password2!==password){
              setTimeout(function(){$('#inputPassword2').addClass('animated tada has-error');},10);
              return false;
            }
            $.ajax({
              url: 'https://api.tktpass.com/reset',
              method: 'POST',
              data: {
                password: password,
                selector: '<?php echo htmlspecialchars($_GET['selector']); ?>',
                validator: '<?php echo htmlspecialchars($_GET['validator']); ?>',
                token: '<?php echo htmlspecialchars($token); ?>'
              },
              xhrFields: {
                withCredentials: true
              },
              success: function(data, textStatus, jqXHR){
                window.location.href = '/login.php';
              },
              error: function(jqXHR, textStatus, errorThrown){
                alert(errorThrown+' occurred, please try again later.');
              }
            });
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