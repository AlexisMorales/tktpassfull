<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

require_once '/var/www/includes/db-io.php';
require_once '/var/www/includes/utils-login.php';
require_once '/var/www/includes/fb-setup.php';

if(isLoggedIn() && !user_has_email()){
    header('Location: /fb-email.php');
    die;
}

$events = get_events();

$today = (new DateTime())->setTime(0,0)->getTimestamp();
$selectedDateTime = false;
$eventDates = array();
foreach ($events as $i => $event) {
  $organiser = get_user($event['user_id']);
  if(!$organiser['account_id']){
    continue;
  }
  if(!$selectedDateTime && (new DateTime($event["start"]))->getTimestamp() > $today)
    $selectedDateTime = new DateTime($event["start"]);

  $date = (new DateTime($event["start"]))->format('Y-m-d');
  if(in_array($date, array_keys($eventDates)))
    $eventDates[$date]["number"] += 1;
  else{
    $eventDates[$date]["id"] = $event['id'];
    $eventDates[$date]["number"] = 1;
  }

  $tickets = get_event_ticket_types($event["id"]);
  $events[$i]["tickets"] = $tickets;
}
if(!$selectedDateTime)
  $selectedDateTime = new DateTime();

$helper = $fb->getRedirectLoginHelper();
$prefill = false;

if(!isLoggedIn())
  $fbLoginUrl = $helper->getLoginUrl('https://'.$_SERVER['HTTP_HOST'].'/fb-callback.php', $fb_permissions);

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>tktpass</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <!--link href="css/fonts.css" rel="stylesheet"-->
    <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="css/vendor/bootstrap.css" rel="stylesheet">
    <link href="css/vendor/bootstrap-v4-buttons.css" rel="stylesheet">
    <link href="css/vendor/bootstrap-v4-tags.css" rel="stylesheet">

    <!--link rel="stylesheet" href="//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css"/-->
    <link rel="stylesheet" href="css/vendor/slick.css"/>
    <link rel="stylesheet" href="css/vendor/slick-theme.css"/>

    <link href="css/vendor/responsive-calendar.css" rel="stylesheet" media="screen">

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
    <link rel="stylesheet" href="css/home.css">

    <!-- HTML5 shim for IE8 support of HTML5 elements (for media queries Respond.js is included below regardless) -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <![endif]-->
    <!--script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script-->

    <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
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
                  <img src="img/logo_w.svg">
                </a>
                <a class="navbar-brand navbar-brand-black" href="#">
                  <img src="img/brand-black.png">
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
echo '<a class="btn" href="login.php" style="padding-top: 15px;padding-bottom: 14px;">Login</a>';
else {
  echo '<a class="btn btn-account'.($_SESSION['user']['picture'] ? '' : ' default-pic').'" id="account-btn" href="mytickets.php" data-obj="'.base64_encode(array("id"=>$_SESSION['user']['id'])).'">'.$_SESSION['user']['first_name'].'<img src="';
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

    <header>
        <div class="dim"></div>
        <div class="header-content">
            <div class="header-content-inner">
                <h1 id="homeHeading">Experience new events.</h1>
                <p style="font-weight:600">For lifetime memories.</p>
                <a href="#events" class="btn btn-outline-white">Find Events</a>
                <!-- <a href="javascript:alert('Coming soon');" x-href="/membership.php" class="btn btn-outline-white">Get Freedom</a> -->
                <a href="/organisers/" class="btn btn-outline-white filled">Create yours</a>
            </div>
        </div>
        <img src="/img/reath.png" class="reath" />
    </header>

    <section id="events">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-heading">Events</h2>
                    <hr class="primary">
                </div>
            </div>
        </div>
        <div class="container"><?php if($events) { ?>
            <div class="row">

              <div class="col-sm-6 col-sm-push-6">
                
                <!-- Responsive calendar - START -->
                <div id="calendar" class="responsive-calendar">
                  <div class="cal-controls">
                      <a class="pull-left" data-go="prev"><div class="btn"><i class="fa fa-chevron-left"></i></div></a>
                      <h4><span data-head-year style="display:none"></span><span data-head-month style="text-transform:uppercase"></span></h4>
                      <a class="pull-right" data-go="next"><div class="btn"><i class="fa fa-chevron-right"></i></div></a>
                  </div><hr/>
                  <div class="day-headers">
                    <div class="day cal-header">Mon</div>
                    <div class="day cal-header">Tue</div>
                    <div class="day cal-header">Wed</div>
                    <div class="day cal-header">Thu</div>
                    <div class="day cal-header">Fri</div>
                    <div class="day cal-header">Sat</div>
                    <div class="day cal-header">Sun</div>
                  </div>
                  <div class="days" data-group="days">
                    <!-- the place where days will be generated -->
                  </div>
                </div>
                <!-- Responsive calendar - END -->

              </div>

              <div class="col-sm-6 col-sm-pull-6">
                <div  id="event-slider"><?php

foreach($events as $event) {
  $organiser = get_user($event['user_id']);
  if(!$organiser['account_id']){
    continue;
  }
  $allFree = true;
  foreach ($event['tickets'] as $ticket) {
    if($ticket['price']){
      $allFree = false;
      break;
    }
   } ?>
                
                <div class="event-card <?= (new DateTime($event["start"]))->format('Y-m-d'); ?>" id="<?php $event["id"]; ?>" data-obj="<?= base64_encode(json_encode($event)); ?>">
                  <div class="cover">
                    <div class="cover-img" style="background-image:url(<?= $event["image"] ?>)">
                      <div class="dim">
                        <h4><?= $event["name"] ?></h4>
                      </div>
                    </div>
                  </div>
                  <div class="info">
                    <div class="time">
                      <p class="location">
                        <i class="fa fa-map-marker"></i> <?= $event["venue"] ?>, <?= $event["city"] ?>
                      </p>
                      <p class="date">
                        <i class="fa fa-calendar"></i> <?= (new DateTime($event["start"]))->format('H:i D d M'); ?>
                      </p>
                    </div>
                    <div class="numbers">
                      <p class="going">
                        <i class="fa fa-group"></i> <?= rand(2,18); ?> friends going
                      </p>
                      <p class="tickets-left">
                        <i class="fa fa-ticket"></i> <?= rand(2,90); ?> tickets left
                      </p>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <a class="btn btn-block btn-success"><?= $allFree ? 'Confirm attendance' : 'Need a ticket?'; ?></a>
                  <a href="mytickets.php" class="btn btn-block btn-default" data-toggle="modal" data-target="#sellModal"><?= $allFree ? 'Can\'t make it' : 'Selling a ticket'; ?>?</a>
                  <div class="share">
                    <button class="btn btn-tell-a-friend"><i class="fa fa-bullhorn"></i> Tell a friend</button>
                    <a class="btn btn-social t col-xs-4" href="https://twitter.com/home?status=Get%20tickets%20for%20<?php echo $event['name']; ?>%20at%20<?php echo $event['venue']; ?>%20https%3A//tktpass.com/events/<?php echo $event['id']; ?>" target="_blank"><i class="fa fa-twitter"></i> Tweet</a>
                    <a class="btn btn-social f col-xs-4" href="https://www.facebook.com/sharer/sharer.php?u=https%3A//tktpass.com/events/<?php echo $event['id']; ?>/" target="_blank"><i class="fa fa-facebook"></i> Facebook</a>
                    <a class="btn btn-social e col-xs-4" href="mailto:?&subject=<?php echo $event['name']; ?>&body=<?php echo $event['name']; ?>%20at%20<?php echo $event['venue']; ?>%0A%0Ahttps%3A//tktpass.com/events/<?php echo $event['id']; ?>" target="_blank"><i class="fa fa-envelope"></i> Email</a>
                  </div>
                </div>
<?php } ?>
                
                </div>

              </div>


            </div>
        <?php } else { ?>
            <img src="/img/Next-Event-Coming-soon_570x300px.jpg" style="display: block;margin: 0 auto;">
        <?php } ?></div>
    </section>

    

    <!--section id="hot">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="section-heading">Hot Among Your Friends</h2>
                    <hr class="primary">
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
              <div class="col-sm-12" id="hot-slider">

                <div class="event-card" id="event-1" data-obj="eyJpZCI6MSwiaW1nIjoiaW1nL2V2ZW50LWltYWdlLmpwZyIsInRpdGxlIjoiV0JTUyBTb2NpYWwgMSIsImxvYyI6IlNtYWNrLCBMZWFtaW5ndG9uIiwiZGF0ZSI6MTQ2OTkxNjAwMCwiZ29pbmciOjEzLCJmcmllbmRzIjo0LCJzb2xkIjo1MywibGVmdCI6NDcsInRpY2tldHMiOlt7Im5hbWUiOiJHZW5lcmFsIiwicHJpY2UiOjYwMH0seyJuYW1lIjoiVklQIiwicHJpY2UiOjEyMDB9XX0">
                  <div class="cover">
                    <div class="cover-img" style="background-image:url(img/event-image.jpg)">
                      <div class="dim">
                        <h4>WBSS Social 1</h4>
                      </div>
                    </div>
                  </div>
                  <div class="info">
                    <div class="time">
                      <p class="location">
                        <i class="fa fa-map-marker"></i> Smack, Leamington
                      </p>
                      <p class="date">
                        <i class="fa fa-calendar"></i> 23:00 Sat 21 Aug
                      </p>
                    </div>
                    <div class="numbers">
                      <p class="going">
                        <i class="fa fa-group"></i> 4 friends going
                      </p>
                      <p class="tickets-left">
                        <i class="fa fa-ticket"></i> 47 tickets left
                      </p>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <a class="btn btn-block btn-success">Need a ticket?</a>
                  <a class="btn btn-block btn-default" data-toggle="modal" data-target="#sellModal">Reselling a ticket?</a>
                  <div class="share">
                    <button class="btn btn-tell-a-friend"><i class="fa fa-bullhorn"></i> Tell a friend</button>
                    <a class="btn btn-social t col-xs-4" href="https://twitter.com/intent/tweet" target="_blank"><i class="fa fa-twitter"></i> Tweet</a>
                    <a class="btn btn-social f col-xs-4" href="https://facebook.com/sharer.php" target="_blank"><i class="fa fa-facebook"></i> Facebook</a>
                    <a class="btn btn-social e col-xs-4" href="mailto:?Subject=tktpass" target="_blank"><i class="fa fa-envelope"></i> Email</a>
                  </div>
                  <div class="click-protect"></div>
                </div>

                <div class="event-card" id="event-2" data-obj="eyJpZCI6MiwiaW1nIjoiaW1nL2V2ZW50LWltYWdlLmpwZyIsInRpdGxlIjoiV0JTUyBTb2NpYWwgMiIsImxvYyI6IlNtYWNrLCBMZWFtaW5ndG9uIiwiZGF0ZSI6MTQ3MDUyMDgwMCwiZ29pbmciOjEyLCJmcmllbmRzIjoyLCJzb2xkIjozNiwibGVmdCI6NjQsInRpY2tldHMiOlt7Im5hbWUiOiJHZW5lcmFsIiwicHJpY2UiOjYwMH0seyJuYW1lIjoiVklQIiwicHJpY2UiOjEyMDB9XX0=">
                  <div class="cover">
                    <div class="cover-img" style="background-image:url(img/event-image.jpg)">
                      <div class="dim">
                        <h4>WBSS Social 2</h4>
                      </div>
                    </div>
                  </div>
                  <div class="info">
                    <div class="time">
                      <p class="location">
                        <i class="fa fa-map-marker"></i> Smack, Leamington
                      </p>
                      <p class="date">
                        <i class="fa fa-calendar"></i> 23:00 Sat 6 Sep
                      </p>
                    </div>
                    <div class="numbers">
                      <p class="going">
                        <i class="fa fa-group"></i> 2 friends going
                      </p>
                      <p class="tickets-left">
                        <i class="fa fa-ticket"></i> 64 tickets left
                      </p>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <a class="btn btn-block btn-success">Need a ticket?</a>
                  <a class="btn btn-block btn-default" data-toggle="modal" data-target="#sellModal">Reselling a ticket?</a>
                  <div class="share">
                    <button class="btn btn-tell-a-friend"><i class="fa fa-bullhorn"></i> Tell a friend</button>
                    <a class="btn btn-social t col-xs-4" href="https://twitter.com/intent/tweet" target="_blank"><i class="fa fa-twitter"></i> Tweet</a>
                    <a class="btn btn-social f col-xs-4" href="https://facebook.com/sharer.php" target="_blank"><i class="fa fa-facebook"></i> Facebook</a>
                    <a class="btn btn-social e col-xs-4" href="mailto:?Subject=tktpass" target="_blank"><i class="fa fa-envelope"></i> Email</a>
                  </div>
                  <div class="click-protect"></div>
                </div>

                <div class="event-card" id="event-3" data-obj="eyJpZCI6MywiaW1nIjoiaW1nL2V2ZW50LWltYWdlLmpwZyIsInRpdGxlIjoiV0JTUyBTb2NpYWwgMyIsImxvYyI6IlNtYWNrLCBMZWFtaW5ndG9uIiwiZGF0ZSI6MTQ3MDg2NjQwMCwiZ29pbmciOjE1LCJmcmllbmRzIjozLCJzb2xkIjo0NCwibGVmdCI6NTYsInRpY2tldHMiOlt7Im5hbWUiOiJHZW5lcmFsIiwicHJpY2UiOjYwMH0seyJuYW1lIjoiVklQIiwicHJpY2UiOjEyMDB9XX0=">
                  <div class="cover">
                    <div class="cover-img" style="background-image:url(img/event-image.jpg)">
                      <div class="dim">
                        <h4>WBSS Social 3</h4>
                      </div>
                    </div>
                  </div>
                  <div class="info">
                    <div class="time">
                      <p class="location">
                        <i class="fa fa-map-marker"></i> Smack, Leamington
                      </p>
                      <p class="date">
                        <i class="fa fa-calendar"></i> 23:00 Wed 10 Sep
                      </p>
                    </div>
                    <div class="numbers">
                      <p class="going">
                        <i class="fa fa-group"></i> 3 friends going
                      </p>
                      <p class="tickets-left">
                        <i class="fa fa-ticket"></i> 56 tickets left
                      </p>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <a class="btn btn-block btn-success">Need a ticket?</a>
                  <a class="btn btn-block btn-default" data-toggle="modal" data-target="#sellModal">Reselling a ticket?</a>
                  <div class="share">
                    <button class="btn btn-tell-a-friend"><i class="fa fa-bullhorn"></i> Tell a friend</button>
                    <a class="btn btn-social t col-xs-4" href="https://twitter.com/intent/tweet" target="_blank"><i class="fa fa-twitter"></i> Tweet</a>
                    <a class="btn btn-social f col-xs-4" href="https://facebook.com/sharer.php" target="_blank"><i class="fa fa-facebook"></i> Facebook</a>
                    <a class="btn btn-social e col-xs-4" href="mailto:?Subject=tktpass" target="_blank"><i class="fa fa-envelope"></i> Email</a>
                  </div>
                  <div class="click-protect"></div>
                </div>

                <div class="event-card" id="event-4" data-obj="eyJpZCI6MSwiaW1nIjoiaW1nL2V2ZW50LWltYWdlLmpwZyIsInRpdGxlIjoiV0JTUyBTb2NpYWwgMSIsImxvYyI6IlNtYWNrLCBMZWFtaW5ndG9uIiwiZGF0ZSI6MTQ2OTkxNjAwMCwiZ29pbmciOjEzLCJmcmllbmRzIjo0LCJzb2xkIjo1MywibGVmdCI6NDcsInRpY2tldHMiOlt7Im5hbWUiOiJHZW5lcmFsIiwicHJpY2UiOjYwMH0seyJuYW1lIjoiVklQIiwicHJpY2UiOjEyMDB9XX0">
                  <div class="cover">
                    <div class="cover-img" style="background-image:url(img/event-image.jpg)">
                      <div class="dim">
                        <h4>WBSS Social 4</h4>
                      </div>
                    </div>
                  </div>
                  <div class="info">
                    <div class="time">
                      <p class="location">
                        <i class="fa fa-map-marker"></i> Smack, Leamington
                      </p>
                      <p class="date">
                        <i class="fa fa-calendar"></i> 23:00 Sat 30 Jul
                      </p>
                    </div>
                    <div class="numbers">
                      <p class="going">
                        <i class="fa fa-group"></i> 4 friends going
                      </p>
                      <p class="tickets-left">
                        <i class="fa fa-ticket"></i> 47 tickets left
                      </p>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <a class="btn btn-block btn-success">Need a ticket?</a>
                  <a class="btn btn-block btn-default" data-toggle="modal" data-target="#sellModal">Reselling a ticket?</a>
                  <div class="share">
                    <button class="btn btn-tell-a-friend"><i class="fa fa-bullhorn"></i> Tell a friend</button>
                    <a class="btn btn-social t col-xs-4" href="https://twitter.com/intent/tweet" target="_blank"><i class="fa fa-twitter"></i> Tweet</a>
                    <a class="btn btn-social f col-xs-4" href="https://facebook.com/sharer.php" target="_blank"><i class="fa fa-facebook"></i> Facebook</a>
                    <a class="btn btn-social e col-xs-4" href="mailto:?Subject=tktpass" target="_blank"><i class="fa fa-envelope"></i> Email</a>
                  </div>
                  <div class="click-protect"></div>
                </div>

                <div class="event-card" id="event-5" data-obj="eyJpZCI6MiwiaW1nIjoiaW1nL2V2ZW50LWltYWdlLmpwZyIsInRpdGxlIjoiV0JTUyBTb2NpYWwgMiIsImxvYyI6IlNtYWNrLCBMZWFtaW5ndG9uIiwiZGF0ZSI6MTQ3MDUyMDgwMCwiZ29pbmciOjEyLCJmcmllbmRzIjoyLCJzb2xkIjozNiwibGVmdCI6NjQsInRpY2tldHMiOlt7Im5hbWUiOiJHZW5lcmFsIiwicHJpY2UiOjYwMH0seyJuYW1lIjoiVklQIiwicHJpY2UiOjEyMDB9XX0=">
                  <div class="cover">
                    <div class="cover-img" style="background-image:url(img/event-image.jpg)">
                      <div class="dim">
                        <h4>WBSS Social 5</h4>
                      </div>
                    </div>
                  </div>
                  <div class="info">
                    <div class="time">
                      <p class="location">
                        <i class="fa fa-map-marker"></i> Smack, Leamington
                      </p>
                      <p class="date">
                        <i class="fa fa-calendar"></i> 23:00 Sat 6 Aug
                      </p>
                    </div>
                    <div class="numbers">
                      <p class="going">
                        <i class="fa fa-group"></i> 2 friends going
                      </p>
                      <p class="tickets-left">
                        <i class="fa fa-ticket"></i> 64 tickets left
                      </p>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <a class="btn btn-block btn-success">Need a ticket?</a>
                  <a class="btn btn-block btn-default" data-toggle="modal" data-target="#sellModal">Reselling a ticket?</a>
                  <div class="share">
                    <button class="btn btn-tell-a-friend"><i class="fa fa-bullhorn"></i> Tell a friend</button>
                    <a class="btn btn-social t col-xs-4" href="https://twitter.com/intent/tweet" target="_blank"><i class="fa fa-twitter"></i> Tweet</a>
                    <a class="btn btn-social f col-xs-4" href="https://facebook.com/sharer.php" target="_blank"><i class="fa fa-facebook"></i> Facebook</a>
                    <a class="btn btn-social e col-xs-4" href="mailto:?Subject=tktpass" target="_blank"><i class="fa fa-envelope"></i> Email</a>
                  </div>
                  <div class="click-protect"></div>
                </div>

                <div class="event-card" id="event-6" data-obj="eyJpZCI6MywiaW1nIjoiaW1nL2V2ZW50LWltYWdlLmpwZyIsInRpdGxlIjoiV0JTUyBTb2NpYWwgMyIsImxvYyI6IlNtYWNrLCBMZWFtaW5ndG9uIiwiZGF0ZSI6MTQ3MDg2NjQwMCwiZ29pbmciOjE1LCJmcmllbmRzIjozLCJzb2xkIjo0NCwibGVmdCI6NTYsInRpY2tldHMiOlt7Im5hbWUiOiJHZW5lcmFsIiwicHJpY2UiOjYwMH0seyJuYW1lIjoiVklQIiwicHJpY2UiOjEyMDB9XX0=">
                  <div class="cover">
                    <div class="cover-img" style="background-image:url(img/event-image.jpg)">
                      <div class="dim">
                        <h4>WBSS Social 6</h4>
                      </div>
                    </div>
                  </div>
                  <div class="info">
                    <div class="time">
                      <p class="location">
                        <i class="fa fa-map-marker"></i> Smack, Leamington
                      </p>
                      <p class="date">
                        <i class="fa fa-calendar"></i> 23:00 Wed 10 Aug
                      </p>
                    </div>
                    <div class="numbers">
                      <p class="going">
                        <i class="fa fa-group"></i> 3 friends going
                      </p>
                      <p class="tickets-left">
                        <i class="fa fa-ticket"></i> 56 tickets left
                      </p>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <a class="btn btn-block btn-success">Need a ticket?</a>
                  <a class="btn btn-block btn-default" data-toggle="modal" data-target="#sellModal">Reselling a ticket?</a>
                  <div class="share">
                    <button class="btn btn-tell-a-friend"><i class="fa fa-bullhorn"></i> Tell a friend</button>
                    <a class="btn btn-social t col-xs-4" href="https://twitter.com/intent/tweet" target="_blank"><i class="fa fa-twitter"></i> Tweet</a>
                    <a class="btn btn-social f col-xs-4" href="https://facebook.com/sharer.php" target="_blank"><i class="fa fa-facebook"></i> Facebook</a>
                    <a class="btn btn-social e col-xs-4" href="mailto:?Subject=tktpass" target="_blank"><i class="fa fa-envelope"></i> Email</a>
                  </div>
                  <div class="click-protect"></div>
                </div>

              </div>


            </div>
        </div>
    </section-->

    

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

    <!-- Modal -->
    <div class="modal buy-modal fade" id="buyModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-image:url(img/event-image.jpg)">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">WBSS Social</h4>
            <div class="info">
              <p class="location">
                <i class="fa fa-map-marker"></i> <span>Smack, Leamington</span>
              </p>
              <p class="date">
                <i class="fa fa-calendar"></i> <span>30 Jul, 2016</span>
              </p>
            </div>
          </div>
          <div class="modal-body">
            <div class="step-1">
              <table>
                <thead>
                  <tr>
                    <th>Tickets</th>
                    <th>Price</th>
                    <th>Quantity</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
              <script type="text/template" id="ticket-row-template">
              <tr data-obj="<%= btoa('{"name":"'+name+'","price":"'+price+'","id":"'+id+'"}') %>">
                <td class="tickets"><%= name %></td>
                <td class="price"><%= price > 0 ? '£'+(price/100).toFixed(2) : 'Free' %></td>
                <td class="quantity">
                    <a href="javascript:void(0);" data-type="minus"><img src="img/minus-icon.png" /></a>
                    <span>0</span>
                    <a href="js:" data-type="plus"><img src="img/plus-icon.png" /></a>
                </td>
              </tr>
              <tr class="spacer"><td class="spacer"></td></tr>
              </script>
              <p>Subtotal: £<span class="total">0.00</span></p>
            </div>
            <div class="step-2" style="display:none">
              <table>
                <thead>
                  <tr>
                    <th>Summary:</th>
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody></tbody>
                <tfoot></tfoot>
              </table>
              <script type="text/template" id="sub-row-template">
              <tr>
                 <td class="ticket"><%= name %> x<%= quantity %></td>
                 <td class="saving"></td>
                 <td class="sub">£<%= (quantity*price/100).toFixed(2) %></td>
              </tr>
              </script>
              <div class="cc-brand-wrap"><div class="cc-brand"></div></div>
              <form role="form" id="payment-form" method="POST" action="javascript:void(0);" novalidate="novalidate">
                  <div class="form-group">
                      <div class="input-group overlay">
                          <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                          <input type="tel" class="form-control cc-number" name="cc-number" id="cc-number" placeholder="Card Number" autocomplete="cc-number" required="" aria-required="true" data-stripe="number">
                      </div>
                  </div>
                  <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                  <input style="display:none" type="text" name="fakeusernameremembered"/>
                  <input style="display:none" type="password" name="fakepasswordremembered"/>
                  <div class="row">
                      <div class="col-xs-6">
                          <div class="form-group">
                              <div class="input-group overlay">
                                  <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                  <input type="tel" class="form-control cc-exp" name="cc-exp" id="cc-exp"  autocomplete="cc-exp" placeholder="mm / yy" required="" aria-required="true" data-stripe="exp">
                              </div>
                          </div>
                      </div>
                      <div class="col-xs-6">
                          <div class="form-group">
                              <div class="input-group overlay">
                                  <span class="input-group-addon"><i class="fa fa-lock" onmousedown="$(this).removeClass('fa-lock').addClass('fa-unlock-alt').parent().next().attr('type','tel')" onmouseup="$(this).removeClass('fa-unlock-alt').addClass('fa-lock').parent().next().attr('type','password')" title="Show CVC"></i></span>
                                  <input type="password" pattern="[0-9]*" inputmode="numeric" class="form-control cc-cvc" name="cc-cvc" id="cc-cvc" autocomplete="new-password" placeholder="CVC" required="" aria-required="true" data-stripe="cvc">
                              </div>
                          </div>
                      </div>
                  </div>
                  <!--div class="row">
                      <div class="col-xs-12">
                          <div class="form-group">
                              <label for="couponCode">COUPON CODE</label>
                              <input type="text" class="form-control" name="couponCode">
                          </div>
                      </div>                        
                  </div-->
                  <div class="spinner" style="width:48px;height:48px;margin: 0 auto;display:none"></div>
                  <p class="payment-errors" style="display:none"></p>
              </form>
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
              <button type="button" class="btn btn-success" id="buy-modal-continue">Continue &gt;&gt;</button>
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

<?php if(!isLoggedIn()) { ?>
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
<?php } ?>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js\/vendor\/jquery-1.11.2.min.js"><\/script>')</script>

    <script src="js/vendor/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    
    <script src="js/vendor/responsive-calendar.min.js"></script>

    <!--script src="//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js"></script>
    <script>window.jQuery.slick || document.write('<script src="js\/vendor\/slick.min.js"><\/script>')</script-->
    <script src="js/vendor/slick.min.js"></script>

    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script src="js/vendor/jquery.payment.min.js"></script>

    <script src="js/main.js"></script>
    <script src="js/home.js"></script>
    <script>
      (function($, window, document, undefined) {
        $(function(){
          
          /* ##################
             # Event Calendar #
             ################## */
          function addLeadingZero(num) {
            if (num < 10) {
              return "0" + num;
            } else {
              return "" + num;
            }
          }
          var selectedDay = <?php echo intval($selectedDateTime->format('d')) ?>;
          var selectedMonth = <?php echo intval($selectedDateTime->format('m')) ?>;
          var selectedYear = <?php echo intval($selectedDateTime->format('Y')) ?>;
          $('#calendar').on('click','[data-go="prev"]',function(e){
            var $firstDay = $('#calendar').find('.days .day:first-child a');
            var goingToMonth = $firstDay.data('month')-($firstDay.parent().hasClass('not-current') ? 0 : 1);
            var goingToYear = $firstDay.data('year')-(goingToMonth == 12 ? 1 : 0);
            var today = new Date();
            if(goingToMonth < today.getMonth()+1 && goingToYear <= today.getYear()+1900){
              e.stopImmediatePropagation();
              e.preventDefault();
              $('#calendar').responsiveCalendar('curr');
              return false;
            }
          });
          $('#calendar').responsiveCalendar({
            events: {
<?php foreach($eventDates as $date=>$info) {
              echo "\"$date\": {\"number\": ".$info["number"].", \"badgeClass\": \"tag\", \"id\": ".$info["id"]."}";
              if(next($eventDates)==true) echo ",\n";
} ?>
            },
            activateNonCurrentMonths: true,
            onDayClick: function(events) {
              var $day = $(this).parent();
              if($day.hasClass('past')) return;
              if($day.hasClass('active')) {
                selectedDay = $(this).data('day');
                selectedMonth = $(this).data('month');
                selectedYear = $(this).data('year');
                $day.parent().find('.selected').removeClass('selected');
                $day.addClass('selected');
                var date, event, id;
                date = $(this).data('year')+'-'+addLeadingZero($(this).data('month'))+'-'+addLeadingZero($(this).data('day'));
                event = events[date];
                $('#event-slider').slick('slickUnfilter');
                $('#event-slider').slick('slickFilter','.'+date);
              }
              if($day.hasClass('not-current')){
                if($day.index() < 7){
                  /*if($day.hasClass('active')) {
                    var getMonthDays = function(month,year){
                      if($.inArray(month,[1,3,5,7,8,10,12])) return 31;
                      if($.inArray(month,[4,6,9,11])) return 30;
                      if(year % 4 === 0 && year % 100 !== 0) return 29;
                      else return 28;
                    }
                    var $firstDayOfCurrentMonth = $day.parent().find('.day:not(.not-current)').eq(0);
                    selectedMonth = $firstDayOfCurrentMonth.data('month')-1;
                    var selectedMonthYear = $firstDayOfCurrentMonth.data('year');
                    if(selectedMonth < 1){
                      selectedMonth = 12;
                      selectedMonthYear = selectedMonthYear-1;
                    }
                    selectedDay = getMonthDays(selectedMonth, selectedMonthYear)-($firstDayOfCurrentMonth.index()-$day.index()-1);
                  }*/
                  $('#calendar').responsiveCalendar('prev');
                } else {
                  /*if($day.hasClass('active')) {
                    var $lastDayOfCurrentMonth = $day.parent().find('.day:not(.not-current)').last();
                    selectedDay = $day.index()-$lastDayOfCurrentMonth.index();
                  }*/
                  $('#calendar').responsiveCalendar('next')
                }
              }
            },
            onMonthChange: function(events) {
              var today = new Date();
              if(this.currentYear == today.getYear()+1900 && this.currentMonth == today.getMonth()) $('#calendar .cal-controls .pull-left').hide();
              else $('#calendar .cal-controls .pull-left').show();
              var that = this;
              setTimeout(function(){
                that.$element.find('.day [data-day='+selectedDay+'][data-month='+selectedMonth+'][data-year='+selectedYear+']').parent().addClass('selected');
              }),0;
            },
            onInit: function(events) {
              $('#calendar .cal-controls .pull-left').hide();
              var that = this;
              setTimeout(function(){
                that.$element.find('.day [data-day='+selectedDay+'][data-month='+selectedMonth+'][data-year='+selectedYear+']').parent().addClass('selected');
              },0);
              $('#event-slider').slick('slickFilter','.'+selectedYear+'-'+addLeadingZero(selectedMonth)+'-'+addLeadingZero(selectedDay));
            },
            monthChangeAnimation: false
          });

        });

      })(this.jQuery, this, this.document);
    </script>

    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.8&appId=1616269921948808";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--[if gt IE 9]>
      <script src="js/vendor/ie10-viewport-bug-workaround.js"></script>
    <![endif]-->
  </body>
</html>