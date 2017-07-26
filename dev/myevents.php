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

$events = get_user_events();
if($user['account_id'])
  $account = \Stripe\Account::retrieve($user['account_id']);
//header('Content-type: text/plain');
//var_dump($account->legal_entity->additional_owners->__toJSON());die;

if($events){
  $today = (new DateTime())->setTime(0,0);
  $lastDateTime = (new DateTime())->setTimestamp(0);
  $lastEvent = false;
  $nextDateTime = new DateTime("2099-01-01");
  $nextEvent = false;
  $selectedDateTime = new DateTime($events[0]["start"]);
  $selectedEvent = 0;
  $eventDates = array();
  foreach ($events as $i => $event) {
    $dateTime = new DateTime($event["start"]);
    if($dateTime < $today && $dateTime > $lastDateTime){
      $lastDateTime = $dateTime;
      $lastEvent = $i;
    }
    if($dateTime > $today && $dateTime < $nextDateTime){
      $nextDateTime = $dateTime;
      $nextEvent = $i;
    }
    if($selectedDateTime < $today && $dateTime > $today){
      $selectedDateTime = $dateTime;
      $selectedEvent = $i;
    }
    if($selectedDateTime < $today && $dateTime < $today && $selectedDateTime < $dateTime){
      $selectedDateTime = $dateTime;
      $selectedEvent = $i;
    }
    if($selectedDateTime > $today && $dateTime > $today && $selectedDateTime > $dateTime){
      $selectedDateTime = $dateTime;
      $selectedEvent = $i;
    }

    $date = $dateTime->format('Y-m-d');
    if(in_array($date, array_keys($eventDates)))
      $eventDates[$date]["number"] += 1;
    else{
      $eventDates[$date]["id"] = $event['id'];
      $eventDates[$date]["number"] = 1;
    }

    $tickets = get_event_ticket_types($event["id"]);
    $events[$i]["tickets"] = $tickets;
  }
  if($lastEvent !== false){
    $lastEvent = $events[$lastEvent];
  }
  if($nextEvent !== false){
    $nextEvent = $events[$nextEvent];
  }
  $selectedEvent = $events[$selectedEvent];
}
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Events | tktpass</title>
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
    <link rel="stylesheet" href="css/my.css">

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

    <div class="main-content-wrap">
      <?php if($events) { ?>
      <div class="container-fluid main-content">
          <ul class="events-nav-tabs">
            <li class="active"><a style="cursor:pointer" id="nav-dashboard">
              <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
                <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/presentation.svg" />
              </svg>
              Event Dashboard
            </a></li>
            <li class=""><a style="cursor:pointer" id="nav-payments">
              <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
                <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/bank.svg" />
              </svg>
              Your Payments
            </a></li>
          </ul>
          <div class="container-fluid" id="dashboard">
            <h2 class="dashboard-event-title"><?php echo $selectedEvent["name"] ?> <small><?php echo (new DateTime($selectedEvent["start"]))->format('d/m/y'); ?></small></h2>
            <div class="row">
              <div class="col-sm-4">
                <div class="card">
                  <h3 class="card-header" style="margin-bottom:0">Activity</h3>
                  <ul class="card-block timeline" style="margin-bottom:0">
                  <?php
                    $activity = get_event_activity($selectedEvent["id"]);
                    foreach ($activity as $item){
                      $html = <<<EOF
                    <li class="item">
                      <div class="circle"></div>
                      <p class="message">
                        User {$item["user_id"]} <em>{$item["action"]}</em> 
EOF;
                      if($item["event_ticket_type_ids"]){
                        $count = count(explode(",",$item["event_ticket_type_ids"]));
                        $html .= ($count > 1 ? $count : "a")." ticket".($count > 1 ? "s" : "").(in_array($item["action"],array("resold","transferred")) ? " to user ".$item["to"] : "");
                      }
                      $html .= " <span class=\"moment\" data-date=\"".$item["time"]."\"></span></p>\n                    </li>";
                      echo $html;
                    } ?>
                  </ul>
                </div>
              </div>
              <div class="col-sm-8">
                <div class="card" id="share-your-event">
                  <h3 class="card-header">Share Your Event</h3>
                  <div class="card-block">
                    <div class = "btn-group-vertical">
                       <a href="https://www.facebook.com/sharer/sharer.php?u=https%3A//tktpass.com/events/<?php echo $selectedEvent["id"]; ?>/" target="_blank" class="btn btn-facebook">Share your event on Facebook<i class="fa fa-facebook"></i></a>
                       <a href="https://twitter.com/home?status=<?php echo urlencode($selectedEvent["name"]); ?>%20https%3A//tktpass.com/events/<?php echo $selectedEvent["id"]; ?>/" target="_blank" class="btn btn-twitter">Tweet out your event<i class="fa fa-twitter"></i></a>
                       <a href="mailto:?&subject=York%20VegFest%202016&body=York%20VegFest%202016%0D%0A%0D%0Ahttps%3A//tktpass.com/events/1224091720/" target="_blank" class="btn btn-email">Send emails<i class="fa fa-envelope"></i></a>
                    </div>
                    <p id="event-link-p">Your event link:</p>
                    <input id="event-link" type="text" readonly class="form-control" value="https://tktpass.com/events/<?php echo $selectedEvent["id"]; ?>/">
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-4" style="opacity:0.2">
                <div class="card">
                  <h3 class="card-header">Revenue <i class="fa fa-money" style="padding-left: 0.33em;color: #aaa;"></i></h3>
                  <div class="card-block">
                    <div class="flot-chart" style="height: 80px">
                        <div class="flot-chart-content" id="flot-revenue-chart"></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-5">
                            <small class="stat-label">Today</small>
                            <h4>£230</h4>
                        </div>
                        <div class="col-xs-7">
                            <small class="stat-label">Last 7 days</small>
                            <h4>£1980 <i class="fa fa-level-up text-success"></i></h4>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-8" style="opacity:0.2">
                <div class="card">
                  <div class="card-header" style="padding-bottom:0">
                    <h3 style="margin-bottom: 0.5em">Ticket Sales</h3>
                    <ul class="nav nav-tabs card-header-tabs">
                      <li class="nav-item">
                        <a class="nav-link" href="#">This month</a>
                      </li>
                      <li class="nav-item active">
                        <a class="nav-link active" href="#">This week</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="#">Today</a>
                      </li>
                    </ul>
                  </div>
                  <div class="card-block">
                    <div class="flot-chart" style="height: 160px">
                        <div class="flot-chart-content" id="flot-sales-chart"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-4">
                <div class="card">
                  <h3 class="card-header">Tickets Sold</h3>
                  <div id="sold" class="card-block"><?php
                    $selectedEventSold = 0;
                    $selectedEventTotal = 0;
                    foreach($selectedEvent["tickets"] as $i=>$ticket_type){
                      $selectedEvent["tickets"][$i]["sold"] = get_ticket_type_sold($ticket_type["id"]);
                      $selectedEventSold += $selectedEvent["tickets"][$i]["sold"];
                      $selectedEventTotal += $ticket_type["quantity"];
                    } ?>
                    <p style="font-size:2.3em;text-align:center"><?php echo $selectedEventSold ?> <small style="color:#aaa">/ <?php echo $selectedEventTotal ?></small></p>
                    <dl><?php
                      foreach($selectedEvent["tickets"] as $ticket_type){
                        $progress = $ticket_type["sold"]*100/$ticket_type["quantity"];
                        $html = <<<EOT
                      <dt>{$ticket_type["name"]}</dt>
                      <dd class="progress" title="{$ticket_type["sold"]}/{$ticket_type["quantity"]}" data-toggle="tooltip">
                        <div class="progress-bar progress-bar-success" style="width:{$progress}%;"></div>
                      </dd>
EOT;
                        echo $html;
                      }
                      ?>
                    </dl>
                  </div>
                </div>
              </div>
              <div class="col-sm-8" style="opacity:0.2">
                <div class="row">
                  <div class="col-sm-4">
                    <div class="card">
                      <h3 class="card-header">Gender</h3>
                      <div class="card-block">
                        <div class="flot-chart">
                            <div class="flot-chart-content" id="flot-gender-chart" style="height: 120px"></div>
                            <div class="flot-legend" id="flot-gender-legend"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-8">
                    <div class="card">
                      <h3 class="card-header">Age</h3>
                      <div class="card-block">
                        <div class="flot-chart" style="height: 120px">
                            <div class="flot-chart-content" id="flot-age-chart"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="container-fluid" id="payments" style="display:none">
            <h3>Past Payments</h3>
            <table class="table table-scrollable past-payments" style="opacity:0.2">
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Tickets Sold</th>
                  <th>Revenue</th>
                  <th>Payment Sent</th>
                </tr>
              </thead>
              <tbody style="max-height:7.5em">
                <tr>
                  <td>JagerMonster 28/06/16</td>
                  <td>412</td>
                  <td>£3,009</td>
                  <td>Pending</td>
                </tr>
                <tr>
                  <td>Smack 21/06/16</td>
                  <td>321</td>
                  <td>£2,354</td>
                  <td>23/06/16</td>
                </tr>
                <tr>
                  <td>JagerMonster 14/06/16</td>
                  <td>502</td>
                  <td>£3,897</td>
                  <td>16/06/16</td>
                </tr>
                <tr>
                  <td>JagerMonster 07/06/16</td>
                  <td>412</td>
                  <td>£3,009</td>
                  <td>09/07/16</td>
                </tr>
                <tr>
                  <td>Smack 31/05/16</td>
                  <td>321</td>
                  <td>£2,354</td>
                  <td>02/06/16</td>
                </tr>
                <tr>
                  <td>JagerMonster 24/06/16</td>
                  <td>502</td>
                  <td>£3,897</td>
                  <td>26/05/16</td>
                </tr>
              </tbody>
            </table>
            <h3>Organiser Information</h3>
            <div class="row">
              <span class="clearfix"></span>
              <form id="managed-account-form"<?php if($account && $account->legal_entity->type === "company") echo ' class="company"'; ?>>
                <div class="col-sm-6 col-lg-4 col-lg-push-1">
                  <label for="type" style="margin-right: 1em;position: relative;top: -0.5em;">Type</label>
                  <div class="form-group btn-group" data-toggle="buttons" id="type-btn-group">
                    <label class="btn btn-<?php echo (!$account || $account->legal_entity->type !== "company") ? 'success active' : 'default'; ?>">
                      <input type="radio" name="legal_entity[type]" value="individual"<?php
                      if(!$account || $account->legal_entity->type !== "company") echo ' checked=""'; ?>> Individual
                    </label>
                    <label class="btn btn-<?php echo ($account && $account->legal_entity->type === "company") ? 'success active' : 'default'; ?>">
                      <input type="radio" name="legal_entity[type]" value="company" id="type-button-company"<?php
                      if($account && $account->legal_entity->type === "company")
                        echo ' checked=""';?>> Company
                    </label>
                  </div>
                  <label for="legal_entity[first_name]" class="company">Owners</label>
                  <div class="owners">
                    <div class="form-group row">
                      <div class="col-xs-6">
                        <input type="text" name="legal_entity[first_name]" class="form-control" placeholder="First name" required="" autofocus="" value="<?php
                        echo $account ? $account->legal_entity->first_name : ($_SESSION['user']['first_name'] ? $_SESSION['user']['first_name'] : '');
                        ?>">
                      </div>
                      <div class="col-xs-6">
                        <input type="text" name="legal_entity[last_name]" class="form-control" placeholder="Last name" required="" value="<?php
                        echo $account ? $account->legal_entity->last_name : ($_SESSION['user']['last_name'] ? $_SESSION['user']['last_name'] : '');
                        ?>">
                      </div>
                    </div><?php
if($account && $account->legal_entity->additional_owners && count($account->legal_entity->additional_owners->keys())){
  foreach ($account->legal_entity->additional_owners->keys() as $key) {
    echo "\n";
    echo '                    <div class="form-group row">'."\n";
    echo '                      <div class="col-xs-6">'."\n";
    echo '                        <input type="text" name="legal_entity[additional_owners]['.$key.'][first_name]" class="form-control" placeholder="First name" required="" autofocus="" value="'.$account->legal_entity->additional_owners[$key]["first_name"].'">'."\n";
    echo '                      </div>'."\n";
    echo '                      <div class="col-xs-6">'."\n";
    echo '                        <input type="text" name="legal_entity[additional_owners]['.$key.'][last_name]" class="form-control" placeholder="Last name" required="" value="'.$account->legal_entity->additional_owners[$key]["last_name"].'">'."\n";
    echo '                      </div>'."\n";
    echo '                    </div>';
  }
}                 ?>
                  </div>
                  <p class="company"><a style="cursor:pointer" id="legal-entity-add-owner" title="An owner is anyone who owns 25% or more of the company" data-toggle="tooltip"<?php
if($account && $account->legal_entity->additional_owners && count($account->legal_entity->additional_owners->keys())){
  $num = count($account->legal_entity->additional_owners->keys());
  if($num > 3) echo ' style="display:none"';
}
                  ?>>More than <span><?php
if($account && $account->legal_entity->additional_owners && count($account->legal_entity->additional_owners->keys())){
  $num = count($account->legal_entity->additional_owners->keys());
  echo ($num+1).' owner'.($num>1 ? 's':'');
} else
  echo 'one owner';
                    ?></span>?</a><span<?php
if(!$account || !$account->legal_entity->additional_owners || !count($account->legal_entity->additional_owners->keys()))
  echo ' style="display:none"';
                  ?>> Or</span> <a style="cursor:pointer;<?php
if(!$account || !$account->legal_entity->additional_owners || !count($account->legal_entity->additional_owners->keys()))
  echo 'display:none;';
                  ?>" id="legal-entity-remove-owner"><span><?php
if($account && $account->legal_entity->additional_owners && count($account->legal_entity->additional_owners->keys()) && count($account->legal_entity->additional_owners->keys()) > 2)
    echo 'R';
  else
    echo 'r';
                  ?></span>emove an owner.</a></p>
                  <label for="dob[day]">Date of birth</label>
                  <div class="form-group row">
                      <div class="col-xs-4"><input type="tel" name="legal_entity[dob][day]" class="form-control" placeholder="dd" required="" value="<?php
function addLeadingZero($num) {
  if ($num < 10) {
    return "0".$num;
  } else {
    return "".$num;
  }
}

if($account)
  echo addLeadingZero($account->legal_entity->dob->day);
                      ?>"></div>
                      <div class="col-xs-4"><input type="tel" name="legal_entity[dob][month]" class="form-control" placeholder="mm" required="" value="<?php
if($account)
  echo addLeadingZero($account->legal_entity->dob->month);
                      ?>"></div>
                      <div class="col-xs-4"><input type="tel" name="legal_entity[dob][year]" class="form-control" placeholder="yyyy" required="" value="<?php
if($account)
  echo $account->legal_entity->dob->year;
                      ?>"></div>
                  </div>
                  <label for="email">Contact Details</label>
                  <div class="form-group">
                      <input type="email" name="email" class="form-control" placeholder="Email address" required="" value="<?php
if($account && $account->email)
  echo $account->email;
else echo $_SESSION['user']['email'] ? $_SESSION['user']['email'] : '';
                      ?>">
                  </div>
                  <div class="form-group">
                      <input type="tel" name="legal_entity[phone_number]" class="form-control" placeholder="Phone number" value="<?php
if($account && $account->legal_entity->phone_number)
  echo $account->legal_entity->phone_number;
                      ?>">
                  </div>
                  <label for="legal_entity[personal_address][line1]" class="company">Personal Address</label>
                  <div class="form-group address company">
                      <input type="text" name="legal_entity[personal_address][line1]" class="form-control" placeholder="Address Line 1" value="<?php
if($account && $account->legal_entity->personal_address && $account->legal_entity->personal_address->line1)
  echo $account->legal_entity->personal_address->line1;
                      ?>">
                      <input type="text" name="legal_entity[personal_address][line2]" class="form-control" placeholder="Address Line 2" value="<?php
if($account && $account->legal_entity->personal_address && $account->legal_entity->personal_address->line2)
  echo $account->legal_entity->personal_address->line2;
                      ?>">
                      <div class="row">
                          <div class="col-xs-6"><input type="text" name="legal_entity[personal_address][city]" class="form-control" placeholder="City" value="<?php
if($account && $account->legal_entity->personal_address && $account->legal_entity->personal_address->city)
  echo $account->legal_entity->personal_address->city;
                          ?>"></div>
                          <div class="col-xs-6"><input type="text" name="legal_entity[personal_address][postal_code]" class="form-control" placeholder="Post code" value="<?php
if($account && $account->legal_entity->personal_address && $account->legal_entity->personal_address->postal_code)
  echo $account->legal_entity->personal_address->postal_code;
                      ?>"></div>
                      </div>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4 col-lg-push-2">
                  <label for="legal_entity[address][line1]"><span class="company">Business </span>Address</label>
                  <div class="form-group address">
                      <input type="text" name="legal_entity[business_name]" class="form-control company" placeholder="Business name" value="<?php
if($account && $account->legal_entity->business_name)
  echo $account->legal_entity->business_name;
                      ?>">
                      <input type="text" name="legal_entity[address][line1]" class="form-control" placeholder="Address Line 1" required="" value="<?php
if($account && $account->legal_entity->address && $account->legal_entity->address->line1)
  echo $account->legal_entity->address->line1;
                      ?>">
                      <input type="text" name="legal_entity[address][line2]" class="form-control" placeholder="Address Line 2" value="<?php
if($account && $account->legal_entity->address && $account->legal_entity->address->line2)
  echo $account->legal_entity->address->line2;
                      ?>">
                      <div class="row">
                          <div class="col-xs-6"><input type="text" name="legal_entity[address][city]" class="form-control" placeholder="City" required="" value="<?php
if($account && $account->legal_entity->address && $account->legal_entity->address->city)
  echo $account->legal_entity->address->city;
                      ?>"></div>
                          <div class="col-xs-6"><input type="text" name="legal_entity[address][postal_code]" class="form-control" placeholder="Post code" required="" value="<?php
if($account && $account->legal_entity->address && $account->legal_entity->address->postal_code)
  echo $account->legal_entity->address->postal_code;
                      ?>"></div>
                      </div>
                  </div>
                  <div class="form-group company">
                      <label for="business_vat_id">Business VAT ID</label>
                      <input type="text" name="legal_entity[business_vat_id]" class="form-control" placeholder="" value="<?php
if($account && $account->metadata && $account->metadata->business_vat_id)
  echo $account->metadata->business_vat_id;
                      ?>">
                  </div>
                  <div class="form-group company">
                      <label for="business_vat_id">Business Tax ID</label>
                      <input type="text" name="legal_entity[business_tax_id]" class="form-control" placeholder="" value="<?php
if($account && $account->metadata && $account->metadata->business_tax_id)
  echo $account->metadata->business_tax_id;
                      ?>">
                  </div>
                  <label for="account_holder_name">Bank Account</label> <a id="account-swap-international" style="cursor:pointer"><small><?php
$international = false;
if($account && $account->metadata && $account->metadata->international==='true'){
  $international = true;
  echo 'Change to account number and sort code';
}
else echo 'Change to IBAN and SWIFT'
                  ?></small></a>
                  <div class="external-account">
                    <input type="text" name="external_account[account_holder_name]" class="form-control" placeholder="Account holder name" required="" value="<?php
if($account && $account->external_accounts && $account->external_accounts->data[0] && $account->external_accounts->data[0]->account_holder_name)
  echo $account->external_accounts->data[0]->account_holder_name;
else echo $_SESSION['user']['name'] ? $_SESSION['user']['name'] : ''; ?>">
                    <input type="<?php echo $international ? 'hidden' : 'tel'; ?>" id="account-sc" name="external_account[routing_number]" class="form-control" placeholder="Sort code" required="" autocomplete="off" autofill="false" value="<?php
function numDigits($str){
    return preg_match_all("/[0-9]/", $str);
}
function get_starred($str, $unstarred=4, $star='*'){
  $numDigits = numDigits($str);
  if($unstarred >= $numDigits)
    return $str;
  $chars = str_split($str);
  $result = '';
  $toStar = $numDigits - $unstarred;
  $starred = 0;
  foreach ($chars as $i=>$char){
    if($starred >= $toStar || !preg_match("/[0-9]/", $char))
      $result .= $char;
    else {
      $result .= $star;
      $starred += 1;
    }
  }
  return $result;
}
if($account && $account->external_accounts && $account->external_accounts->data[0] && $account->external_accounts->data[0]->routing_number
   && !$international)
  echo get_starred($account->external_accounts->data[0]->routing_number,3);
                    ?>">
                    <input type="<?php echo $international ? 'hidden' : 'tel'; ?>" id="account-acc" name="external_account[account_number]" class="form-control" placeholder="Account number" required="" autocomplete="off" autofill="false" value="<?php
if($account && $account->external_accounts && $account->external_accounts->data[0] && $account->external_accounts->data[0]->last4
   && !$international){
  $length = ($account->metadata && $account->metadata->account_number_length) ?
    $account->metadata->account_number_length : 8;
  echo str_repeat('•', $length-4).$account->external_accounts->data[0]->last4;
}
                    ?>">
                    <input type="<?php echo $international ? 'tel' : 'hidden'; ?>" id="account-swift" name="external_account[routing_number_swift]" class="form-control" placeholder="SWIFT" autocomplete="off" autofill="false" value="<?php
if($account && $account->external_accounts && $account->external_accounts->data[0] && $account->external_accounts->data[0]->routing_number
   && $international)
  echo get_starred($account->external_accounts->data[0]->routing_number,4);
                    ?>">
                    <input type="<?php echo $international ? 'tel' : 'hidden'; ?>" id="account-iban" name="external_account[account_number_iban]" class="form-control" placeholder="IBAN" autocomplete="off" autofill="false" value="<?php
if($account && $account->external_accounts && $account->external_accounts->data[0] && $account->external_accounts->data[0]->last4
   && $international){
  $length = $account->metadata && $account->metadata->account_number_length ?
    $account->metadata->account_number_length : 34;
  echo str_repeat('•', $length-4).$account->external_accounts->data[0]->last4;
}
                    ?>">
                    <input type="hidden" id="account-international" name="metadata[international]" value="<?php echo $international ? 'true' : 'false'; ?>" required="">
                  </div>
                </div>
                <span class="clearfix"></span>
                <div class="alert alert-warning col-sm-6 col-sm-push-3" role="alert" style="display:none;"></div>
                <span class="clearfix"></span>
                <div class="col-sm-6 col-sm-push-3">
                  <button class="btn btn-lg btn-block btn-success" style="font-size: 1.2em;margin-bottom: 0.5em;" type="submit">Update Information</button><?php if(!$account) { ?>
                  <p id="tos_acceptance">By registering your account, you agree to our <a href="/tcs">Services Agreement</a> and the <a target="_blank" href="https://stripe.com/connect-account/legal">Stripe Connected Account Agreement</a>.</p><?php } ?>
                </div>
              </form><!-- /managed-account-form -->
              <div class="spinner-wrap" id="updating-spinner" style="display:none"><div class="spinner"></div></div>
            </div>
          </div>
      </div>
      <?php } else { ?>
      <div class="container-fluid main-content display-table">
        <div class="display-tablecell">
          <p class="lead text-center" style="padding-left: 1rem;padding-right: 1rem;font-weight:600;">Oops! You haven't held any events recently.</p>
          <a class="btn btn-block btn-success" style="max-width: 200px; margin: 0 auto 1em;" href="/organisers#magic">+ Create event</a>
          <p class="lead text-center" style="padding-left: 1rem;padding-right: 1rem;">This is the easiest way to make money from your events.<br>The perfect solution for all event organisers.<br><br><a class="btn" href="/organisers">Tell me more</a></p></div>
      </div>
      <?php } ?>
    </div>

    <div class="hidden-xs sidebar">
      <ul>
        <li><a href="/mytickets.php">
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
        <li class="active">
          <a>
            <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
              <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/party.svg" />
            </svg>
            My Events
          </a>
          <?php if($events) { ?>
          <!-- Responsive calendar - START -->
          <div id="calendar" class="responsive-calendar">
            <div class="cal-controls">
                <a class="pull-left" data-go="prev"><div class="btn"><i class="fa fa-chevron-left"></i></div></a>
                <h4><span data-head-year style="display:none"></span><span data-head-month style="text-transform:uppercase"></span></h4>
                <a class="pull-right" data-go="next"><div class="btn"><i class="fa fa-chevron-right"></i></div></a>
            </div><span class="clearfix"></span>
            <!--div class="day-headers">
              <div class="day cal-header">Mon</div>
              <div class="day cal-header">Tue</div>
              <div class="day cal-header">Wed</div>
              <div class="day cal-header">Thu</div>
              <div class="day cal-header">Fri</div>
              <div class="day cal-header">Sat</div>
              <div class="day cal-header">Sun</div>
            </div-->
            <div class="days" data-group="days">
              <!-- the place where days will be generated -->
            </div>
          </div>
          <!-- Responsive calendar - END -->
          <?php } ?>
        </li>
        <li><a href="/myaccount.php">
          <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
            <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/account.svg" />
          </svg>
          My Account
        </a></li>
      </ul>
    </div>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js\/vendor\/jquery-1.11.2.min.js"><\/script>')</script>

    <script src="js/vendor/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    
    <script src="js/vendor/responsive-calendar.min.js"></script>
    <script src="js/vendor/moment.js"></script>

    <!--script src="//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js"></script>
    <script>window.jQuery.slick || document.write('<script src="js\/vendor\/slick.min.js"><\/script>')</script>
    <script src="js/vendor/slick.min.js"></script-->

    <script language="javascript" type="text/javascript" src="/js/vendor/jquery.flot.js"></script>
    <script src="/js/vendor/jquery.flot.resize.js"></script>
    <script src="/js/vendor/jquery.flot.pie.js"></script>
    <script src="/js/vendor/jquery.flot.curvedLines.js"></script>
    <script src="/js/vendor/jquery.flot.tooltip.min.js"></script>


    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <!--script src="js/vendor/jquery.payment.min.js"></script-->

    <script src="js/myevents.js"></script>
    <script>
      (function($, window, document, undefined) {
        $(function(){
          if(window.location.hash === "#payments"){
            $('#nav-payments').click();
          }
          <?php if($events) { ?>
          $('span.moment').each(function(i,e){
            $(e).html(moment(new Date($(e).data("date"))).fromNow());
          });

          var data = <?php echo json_encode(get_event_stats($selectedEvent["id"])); ?>;

          function drawGraphs(data){
            /*
             * Flot week sales chart, data and options
             */
            var chartUsersOptions = {
                series: {
                    curvedLines: {
                        active: true,
                        monotonicFit: true
                    },
                },
                grid: {
                    tickColor: "#f0f0f0",
                    borderWidth: 1,
                    borderColor: 'f0f0f0',
                    color: '#6a6c6f',
                    hoverable: true
                },
                colors: ["#efefef","rgb(149,211,120)","#fff"],
                shadowSize: 0,
                xaxis: {
                    min: 1.0,
                    max: 7.0,
                    //mode: null,
                    ticks: [[1.0,"Mon"], [2.0,"Tue"], [3.0,"Wed"], [4.0,"Thu"], [5.0,"Fri"], [6.0,"Sat"], [7.0,"Sun"]],
                    tickLength: 0, // hide gridlines
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Lato, Helvetica, Arial, sans-serif',
                    axisLabelPadding: 5
                }
            };

            $.plot($("#flot-sales-chart"), [{
                data: data.weekSales[0],
                lines: { show: true, fill: 0.5, lineWidth: 1},
                curvedLines: { apply: true }
            }, {
                data: data.weekSales[1],
                lines: { show: true, fill: 0.5, lineWidth: 1},
                curvedLines: { apply: true }
            }, { 
                data: data.weekSales[1],
                points: { show: true, lineWidth: 1, fill:true, fillColor: "rgb(90, 195, 54)" }
            }], chartUsersOptions);

            /**
             * Flot revenue chart, data and options
             */
            var chartRevenueData = [
                {
                    label: "line",
                    data: data.revenue
                }
            ];

            var chartRevenueOptions = {
                series: {
                    lines: {
                        show: true,
                        lineWidth: 0,
                        fill: 0.6,
                        fillColor: "#5ac336"

                    }
                },
                colors: ["#62cb31"],
                grid: {
                    show: false
                },
                legend: {
                    show: false
                }
            };

            $.plot($("#flot-revenue-chart"), chartRevenueData, chartRevenueOptions);

            /**
             * Flot gender chart, data and options
             */
            var chartGenderData = [
                {
                    label: "Male",
                    data: data.gender.male
                }, 
                {
                    label: "Female",
                    data: data.gender.female
                }, 
                {
                    label: "Unknown",
                    data: data.gender.unknown
                }
            ];

            var chartGenderOptions = {
                series: {
                    pie: {
                        innerRadius: 0.65,
                        show: true,
                        label: {
                          show:false
                        }
                    }
                },
                colors: ["#5ac336","#b8d13a","#ccc"],
                grid: {
                    show: false
                },
                legend: {
                    show: true, 
                    container: document.getElementById("flot-gender-legend")
                }
            };

            $.plot($("#flot-gender-chart"), chartGenderData, chartGenderOptions);

            /**
             * Flot age chart, data and options
             */
            var chartAgeDataset = [
                { label: "Age Range of Buyers", data: data.age, color: "#5ac336" }
            ];
            var ticks = [[0, "Under 18"], [1, "18-21"], [2, "22-25"], [3, "26-30"],[4, "Over 30"]];

            var chartAgeOptions = {
                series: {
                    bars: {
                        show: true
                    }
                },
                bars: {
                    align: "center",
                    barWidth: .84,
                    fill: 0.9
                },
                xaxis: {
                    axisLabel: "Age Groups",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Lato,Helvetica Neue,Helvetica,Arial,sans-serif',
                    axisLabelPadding: 5,
                    ticks: ticks,
                    tickLength: 0 // hide gridlines
                },
                yaxis: {
                    axisLabel: "# of buyers",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Lato,Helvetica Neue,Helvetica,Arial,sans-serif',
                    axisLabelPadding: 2,
                    tickLength: 0 // hide gridlines
                },
                legend: {
                  show: false
                },
                grid: {
                    borderWidth: 0,
                    hoverable: true
                }
            };
            $.plot($("#flot-age-chart"), chartAgeDataset, chartAgeOptions);
          }
          drawGraphs(data);
            
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
          // $('#calendar').on('click','[data-go="prev"]',function(e){
          //   var $firstDay = $('#calendar').find('.days .day:first-child a');
          //   var goingToMonth = $firstDay.data('month')-($firstDay.parent().hasClass('not-current') ? 0 : 1);
          //   var goingToYear = $firstDay.data('year');
          //   var today = new Date();
          //   if((goingToYear < today.getYear()+1900) || 
          //      (goingToYear == today.getYear()+1900 && goingToMonth < today.getMonth()+1)){
          //     e.stopImmediatePropagation();
          //     e.preventDefault();
          //     $('#calendar').responsiveCalendar('curr');
          //     return false;
          //   }
          // });
          $('#calendar').responsiveCalendar({
            events: {<?php
              foreach($eventDates as $date=>$info) {
                echo "\"$date\": {\"number\": ".$info["number"].", \"badgeClass\": \"tag\", \"id\": ".$info["id"]."}";
                if(next($eventDates)==true) echo ",\n";
              } ?>
            },
            activateNonCurrentMonths: true,
            onDayClick: function(events) {
              var $day = $(this).parent();
              if($day.hasClass('active')) {
                selectedDay = $(this).data('day');
                selectedMonth = $(this).data('month');
                selectedYear = $(this).data('year');
                var date, event, id;
                date = $(this).data('year')+'-'+addLeadingZero($(this).data('month'))+'-'+addLeadingZero($(this).data('day'));
                event = events[date];
                //Load that event page
                $.ajax({
                  method: 'GET',
                  url: 'https://api.tktpass.com/events/'+event.id,
                  success: function(data, textStatus, jqXHR){
                    $day.parent().find('.selected').removeClass('selected');
                    $day.addClass('selected');
                    var date = new Date(data.start);
                    $('#dashboard .dashboard-event-title').html(data.name+' <small>'+addLeadingZero(date.getDate())+'/'+addLeadingZero(date.getMonth()+1)+'/'+(date.getFullYear()-2000)+'</small>');
                    $("#event-link").val('https://tktpass.com/events/'+data.id+'/');
                    $("#share-your-event .btn-facebook").attr('href','https://www.facebook.com/sharer/sharer.php?u=https%3A//tktpass.com/events/'+data.id+'/');
                    $("#share-your-event .btn-twitter").attr('href','https://twitter.com/home?status='+encodeURIComponent(data.name)+'%20https%3A//tktpass.com/events/'+data.id+'/');
                    $("#share-your-event .btn-email").attr('href','mailto:?&subject='+encodeURIComponent(data.name)+'&body='+encodeURIComponent(data.name)+'%0D%0A%0D%0Ahttps%3A//tktpass.com/events/'+data.id+'/');
                  },
                  error: function(jqXHR, textStatus, errorThrown){
                    var res = $.parseJSON(jqXHR.responseText);
                    alert('Error occurred: '+((res && res.err) ? res.err : errorThrown));
                  }
                });
                $.ajax({
                  method: 'GET',
                  url: 'https://api.tktpass.com/events/'+event.id+'/activity',
                  success: function(data, textStatus, jqXHR){
                    var $ul = $('ul.timeline').empty();
                    $.each(data,function(index,item){
                      var count = item.event_ticket_type_ids ? item.event_ticket_type_ids.split(',').length : 0;
                      var li = '<li class="item"><div class="circle"></div><p class="message">User '+item.user_id+' <em>'+item.action+'</em> ';
                      if(count)
                        li += (count > 1 ? count : 'a')+' ticket'+(count > 1 ? 's ' : ' ');
                      li += '<span class="moment" data-date="'+item.time+'">'+moment(item.time, "YYYY-MM-DD HH:II:SS").fromNow()+'</span></p> </li>';
                      $ul.append(li);
                    });
                  },
                  error: function(jqXHR, textStatus, errorThrown){
                    var res = $.parseJSON(jqXHR.responseText);
                    alert('Error occurred: '+((res && res.err) ? res.err : errorThrown));
                  }
                });
                $.ajax({
                  method: 'GET',
                  url: 'https://api.tktpass.com/events/'+event.id+'/tickets',
                  success: function(data, textStatus, jqXHR){
                    var totalSold = 0;
                    var totalTotal = 0;
                    $.each(data,function(i,ticket){
                      totalSold += parseInt(ticket.sold);
                      totalTotal += parseInt(ticket.quantity);
                    });
                    var $sold = $("#sold");
                    $sold.find('p').html(totalSold+' <small style="color:#aaa">/ '+totalTotal+'</small></p>');
                    var html = '';
                    $.each(data,function(i,ticket){
                      html += '<dt>'+ticket.name+'</dt>'+
                        '<dd class="progress" title="'+ticket.sold+'/'+ticket.quantity+'" data-toggle="tooltip">'+
                        '<div class="progress-bar progress-bar-success" style="width:'+(parseInt(ticket.sold)*100/parseInt(ticket.quantity))+'%;"></div>'+
                      '</dd>';
                    });
                    $sold.find('dl').html(html);
                  },
                  error: function(jqXHR, textStatus, errorThrown){
                    var res = $.parseJSON(jqXHR.responseText);
                    alert('Error occurred: '+((res && res.err) ? res.err : errorThrown));
                  }
                });
                $.ajax({
                  method: 'GET',
                  url: 'https://api.tktpass.com/events/'+event.id+'/stats',
                  success: function(data, textStatus, jqXHR){
                    drawGraphs(data);
                  },
                  error: function(jqXHR, textStatus, errorThrown){
                    var res = $.parseJSON(jqXHR.responseText);
                    alert('Error occurred: '+((res && res.err) ? res.err : errorThrown));
                  }
                });
              }
              if($day.hasClass('not-current')){
                if($day.index() < 7)
                  $('#calendar').responsiveCalendar('prev');
                else
                  $('#calendar').responsiveCalendar('next');
              }
            },
            onMonthChange: function(events) {
              var today = new Date();
              // if(this.currentYear == today.getYear()+1900 && this.currentMonth == today.getMonth()) $('#calendar .cal-controls .pull-left').css('visibility','hidden');
              // else $('#calendar .cal-controls .pull-left').css('visibility','visible');
              var that = this;
              setTimeout(function(){
                that.$element.find('.day [data-day='+selectedDay+'][data-month='+selectedMonth+'][data-year='+selectedYear+']').parent().addClass('selected');
              }),0;
            },
            onInit: function(events) {
              // $('#calendar .cal-controls .pull-left').css('visibility','hidden');
              var that = this;
              setTimeout(function(){
                that.$element.find('.day [data-day='+selectedDay+'][data-month='+selectedMonth+'][data-year='+selectedYear+']').parent().addClass('selected');
              },0);
            },
            monthChangeAnimation: false
          });
          <?php } ?>
        });

      })(this.jQuery, this, this.document);
    </script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--[if gt IE 9]>
      <script src="js/vendor/ie10-viewport-bug-workaround.js"></script>
    <![endif]-->
  </body>
</html>