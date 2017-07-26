<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>Forgot password | tktpass</title>
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
            <a href="index.html"><img id="profile-img" class="img-responsive profile-img-card" src="img/brand-black.png" /></a>
            <p>If you used Facebook to sign in, please click the button on the <a href="login.php">log in page</a>.</p>
            <hr>
            <p style="margin-bottom:2em;">Otherwise, please enter the email address that you used to sign up so we can send you a password reset link.</p>
            <form class="form-signin" id="forgot-form">
                <input type="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
                <button class="btn btn-lg btn-block btn-signin-green" type="submit">Send</button>
            </form><!-- /form -->
            <p class="alert alert-success" style="display:none">If an account exists with that email address, you will recieve a link to reset your password</p>
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
          $("#forgot-form").on('submit',function(e){
            e.preventDefault();
            $('.alert').hide();
            $('#inputEmail').removeClass('animated tada has-error');
            var email = $('#inputEmail').val();
            if(!email || email.length<6){
              setTimeout(function(){$('#inputEmail').addClass('animated tada has-error');},10);
              return false;
            }
            $('#inputEmail').val('');
            $.ajax({
              url: 'https://api.tktpass.com/forgot',
              method: 'POST',
              data: {
                email: email
              },
              xhrFields: {
                withCredentials: true
              },
              success: function(data, textStatus, jqXHR){
                $('.alert').slideDown();
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