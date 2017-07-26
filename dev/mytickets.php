<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

require 'vendor/autoload.php';
require_once '/var/www/includes/db-io.php';
require_once '/var/www/includes/stripe-setup.php';

$user = get_user();
if(!$user || $user["err"]){
  header("Location: /");
  exit;
}

$tickets = get_user_upcoming_tickets();

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>My Tickets | tktpass</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <!--link href="css/fonts.css" rel="stylesheet"-->
    <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="css/vendor/bootstrap.css" rel="stylesheet">
    <link href="css/vendor/bootstrap-v4-buttons.css" rel="stylesheet">
    <link href="css/vendor/bootstrap-v4-tags.css" rel="stylesheet">
    <link href="css/vendor/bootstrap-v4-cards.css" rel="stylesheet">

    <!--link rel="stylesheet" href="//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css"/>
    <link rel="stylesheet" href="css/vendor/slick.css"/>
    <link rel="stylesheet" href="css/vendor/slick-theme.css"/-->

    <link href="css/vendor/nouislider.min.css" rel="stylesheet" media="screen">

    <link href="css/vendor/font-awesome.min.css" rel="stylesheet">

    <!-- Bootstrap theme -->
    <!--link href="css/vendor/bootstrap-theme.min.css" rel="stylesheet"-->
    <link href="css/theme/buttons.css" rel="stylesheet">
    <link href="css/theme/navbar.css" rel="stylesheet">
    <link href="css/theme/tables.css" rel="stylesheet">
    <link href="css/theme/tabs.css" rel="stylesheet">
    <link href="css/theme/modal.css" rel="stylesheet">
    <link href="css/theme/misc.css" rel="stylesheet">
    <!--link href="css/theme.css" rel="stylesheet"-->

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="css/vendor/ie10-viewport-bug-workaround.css" rel="stylesheet">
    
    <link href="/css/vendor/animate.css" rel="stylesheet">

    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/my.css">
    <link rel="stylesheet" href="css/mytickets.css">

    <!-- HTML5 shim for IE8 support of HTML5 elements (for media queries Respond.js is included below regardless) -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <![endif]-->
    <!--script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script-->

    <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
  </head>

  <body role="document" class="">

    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top affix">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand navbar-brand-white" href="/">
                  <img src="img/logo_w.svg">
                </a>
                <a class="navbar-brand navbar-brand-black" href="/">
                  <img src="img/brand-black.png">
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <?php
if(!isset($_SESSION['user']))
echo '<a class="btn" href="login.php" style="padding-top: 15px;padding-bottom: 14px;">Login</a>';
else {
  echo '<a class="btn btn-account'.($_SESSION['user']['picture'] ? '' : ' default-pic').'" id="account-btn" href="/" data-obj="'.base64_encode(array("id"=>$_SESSION['user']['id'])).'">'.$_SESSION['user']['first_name'].'<img src="';
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

    <div class="main-content-wrap">
      <div class="container-fluid main-content">
        <?php if($tickets) {
	      foreach ($tickets as $i=>$ticket) {
          $type = get_ticket_type($ticket['event_ticket_type_id']);
          $event = get_event($type['event_id']);
	      	// $ticket_type = get_ticket_type($ticket['event_ticket_type_id']);
	      	// $event = get_event($ticket_type['event_id']);
	        echo '<div class="row ticket-row">'.
	        	     '<div class="ticket col-xl-5 col-md-6 col-sm-8" data-ids="'.$ticket['ids'].'">'.
					         '<div class="col-xs-9">'.
	        		       '<div class="green">'.$ticket["name"].'<br><em>Hosted by: '.($event['host'] ? $event['host'] : 'User '.$event["user_id"]).'</em></div>'.
	        		       '<div>'.
	        		         '<p><em>Location</em><br>'.$ticket["venue"].', '.$ticket["city"].'</p>'.
	        		         '<p><em>Date</em><br>'.(new DateTime($ticket["start"]))->format('D j M Y').'</p>'.
	        		       '</div>'.
	        		     '</div>'.
	        		     '<div class="col-xs-3">'.
					           '<div class="green"><img src="/img/logo-mini-white.png" /></div>'.
	        		       '<div>'.
	        		         '<p class="ticket-type">'.$ticket['event_ticket_type_name'].'</p>'.
	        		         '<p class="ticket-quantity">'.$ticket["quantity"].'</p>'.
	        		       '</div>'.
	        		     '</div>'.
	        	     '</div>'.
	        	     '<div class="ticket-buttons col-xl-3 col-xl-offset-4 col-md-4 col-md-offset-2 col-sm-4">'.
	        	       '<div class="text-center" style="height:33%"><a class="btn btn-outline-black email-ticket" href="#"><i class="fa fa-envelope"></i> Email ticket</a></div>';
          if($ticket['event_ticket_type_price'] > 0){
	        	  echo '<div class="text-center" style="height:33%"><a class="btn btn-outline-green sell-ticket" href="#"><i class="fa fa-money"></i>  Sell ticket</a></div>'.
	        	       '<div class="text-center" style="height:33%"><a class="btn btn-outline-green transfer-ticket" href="#">'.
                     '<svg height="22" width="22" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 90 480 305">'.
                       '<use x="0" y="0" height="22" width="22" xlink:href="/img/icon/pass.svg#icon"></use>'.
                     '</svg> Transfer ticket'.
                   '</a></div>';
          } else {
              echo '<div class="text-center" style="height:33%"><a class="btn btn-outline-green delete-ticket" href="#"><i class="fa fa-trash"></i>  Remove ticket</a></div>';
          }
	        	echo '</div>'.
               '</div>';
	      }
	    } else { ?>
        <div class="container-fluid main-content display-table">
          <div class="display-tablecell">
            <p class="lead text-center" style="padding-left: 1rem;padding-right: 1rem;font-weight:600;">Oops! You haven't got any tickets at the moment.</p>
            <a class="btn btn-block btn-success" style="max-width: 200px; margin: 0 auto 3em;" href="/#events">Buy tickets >></a>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>

    <div class="hidden-xs sidebar">
      <ul>
        <li class="active"><a>
          <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
            <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/ticket.svg" />
          </svg>
          My Tickets
        </a></li>
        <li><a href="javascript:alert('Coming soon');" x-href="/mypasses.php">
          <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
            <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/pass.svg" />
          </svg>
          My Passes
        </a></li>
        <li>
          <a href="/myevents.php">
            <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
              <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/party.svg" />
            </svg>
            My Events
          </a>
        </li>
        <li><a href="/myaccount.php">
          <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
            <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/account.svg" />
          </svg>
          My Account
        </a></li>
      </ul>
    </div>


    <!-- Modal -->
    <div class="modal resell-modal fade" id="resellModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Loading...</h4>
            <p class="ticket-type"></p>
          </div>
          <div class="modal-body">
            <div class="step-1">
              <p id="slider-value"></p>
              <div id="resell-slider"></div>
            </div>
            <div class="step-2" style="display:none">
              <div class="modal-aside">
                <h4>Upgrade - <span>Freedom</span></h4>
                <ul>
                  <li>Buy face value tickets <a href>[?]</a></li>
                  <li>No booking fees <a href>[?]</a></li>
                  <li>Sell your tickets any time <a href>[?]</a></li>
                  <li>Transfer your ticket to a friend <a href>[?]</a></li>
                  <li>Cancel at any time <a href>[?]</a></li>
                </ul>
                <p style="font-weight:700">First month FREE!</p>
                <p><small>Only £3.99/mo afterwards.</small></p>
                <p><button type="button" class="btn btn-success" id="add-freedom"><span class="outer-circle"><span class="inner-circle"></span></span>Yes, add Freedom</button></p>
              </div>
            </div>
            <div class="step-3" style="display:none">
              <h2>Done!</h2>
              <p class="lead">Thanks for booking with tktpass</p>
              <a class="btn btn-outline-green" href="/mytickets.php">Go to My Tickets</a>
            </div>
          </div>
          <div class="modal-footer">
            <div class="step-1">
              <!--button type="button" class="btn btn-default" data-dismiss="modal">Add to basket <i class="fa fa-shopping-basket"></i></button-->
              <button type="button" class="btn btn-success" id="buy-modal-continue">Confirm</button>
            </div>
            <div class="step-2" style="display:none">
              <button type="button" class="btn btn-default" id="buy-modal-back">Back</button>
              <button type="button" class="btn btn-outline-green" id="buy-modal-pay"><span>Pay with card</span></button>
            </div>
            <div class="step-3" style="display:none">
              <p>Our ninjas spotted that these friends are also going!</p>
              <div class="friend-heads">
                <div class="friend">
                  <img src="img/user-1.jpg" class="img-circle img-responsive"/>
                  <small>James</small>
                </div>
                <div class="friend">
                  <img src="img/user-3.jpg" class="img-circle img-responsive"/>
                  <small>Ashley</small>
                </div>
                <div class="friend">
                  <img src="img/user-2.jpg" class="img-circle img-responsive"/>
                  <small>Christopher</small>
                </div>
                <div class="friend">
                  <img src="img/user-4.jpg" class="img-circle img-responsive"/>
                  <small>Daniella</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js\/vendor\/jquery-1.11.2.min.js"><\/script>')</script>

    <script src="js/vendor/bootstrap.min.js"></script>
    <script src="js/vendor/nouislider.min.js"></script>

    <script src="js/mytickets.js"></script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--[if gt IE 9]>
      <script src="js/vendor/ie10-viewport-bug-workaround.js"></script>
    <![endif]-->
  </body>
</html>