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

if(!$user['fb_id']){
  require_once '../includes/fb-setup.php';
  $helper = $fb->getRedirectLoginHelper();
  $fbLoginUrl = $helper->getLoginUrl('https://'.$_SERVER['HTTP_HOST'].'/fb-callback.php?next=/myaccount.php', $fb_permissions);
}

$customerId = get_customer_id($user['id']);
$customer = null;
if($customerId){
    if($customerId && is_array($customerId) && $customerId["err"]){
        die('Error occurred: '.$customerId["err"]);
    }
    try{
        $customer = \Stripe\Customer::retrieve(array("id" => $customerId, "expand" => array("default_source")));
    } catch(Exception $e){
        die("Error occurred: Invalid customer ID");
    }
}
if(!$customer){
    $customerDeets = array(
      "description" => $user['first_name'].' '.$user['last_name']." (".$user['email'].")",
      "email" => $user['email'],
      "metadata" => array("user_id" => $user['id'])
    );
    if($user['mobile'])
        $customerDeets["shipping"] = array("phone"=>$user['mobile']);
    $customer = \Stripe\Customer::create($customerDeets);
    update_user($user['id'],array("customer_id"=>$customer->id));
    $_SESSION['user']['customer_id'] = $customer->id;
}
$plan = $customer->subscriptions->total_count ? $customer->subscriptions->data[0]->id : null;

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>My Account | tktpass</title>
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
    <link rel="stylesheet" href="css/myaccount.css">

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
        <!-- Nav tabs -->
        <ul class="nav nav-tabs nav-tabs-centered" role="tablist">
          <li role="presentation" class="active"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Profile</a></li>
          <li role="presentation"><a href="#membership" aria-controls="membership" role="tab" data-toggle="tab">Membership</a></li>
          <li role="presentation"><a href="#cards" aria-controls="cards" role="tab" data-toggle="tab">Payment Methods</a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
          <div role="tabpanel" class="tab-pane active" id="profile">
            <h3>Profile</h3>
            <form class="form-horizontal" id="profile-form" method="POST" action="https://api.tktpass.com/me">
              <input type="text" style="display:none">
              <input type="password" style="display:none">
              <div class="form-group">
                <label for="facebookConnect" class="col-sm-2 control-label">Facebook</label>
                <div class="col-sm-6">
                  <?php if(!$user['fb_id']) : ?><a href="<?php echo $fbLoginUrl; ?>" class="btn btn-block btn-facebook" id="facebookConnect">Connect with Facebook<i class="fa fa-facebook"></i></a><?php else :
                  ?><a href="https://www.facebook.com/app_scoped_user_id/<?php echo $user['fb_id']; ?>/" target="_blank" style="line-height:34px" >Connected</a><?php endif; ?>
                </div>
                <p class="col-sm-4">Transfer tickets to friends and see who is also going.</p>
              </div>
              <div class="form-group">
                <label for="inputName" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-3">
                  <input type="text" class="form-control" id="inputFname" name="first_name" placeholder="First Name" value="<?php echo $user['first_name']; ?>">
                </div>
                <div class="col-sm-3">
                  <input type="text" class="form-control" id="inputLname" name="last_name" placeholder="Last Name" value="<?php echo $user['last_name']; ?>">
                </div>
                <p class="col-sm-4"></p>
              </div>
              <div class="form-group">
                <label for="inputEmail" class="col-sm-2 control-label">Email</label>
                <div class="col-sm-6">
                  <input type="email" class="form-control" id="inputEmail" name="email" placeholder="Email" value="<?php echo $user['email']; ?>">
                </div>
              </div>
              <div class="form-group">
                <label for="inputPhone" class="col-sm-2 control-label">Mobile</label>
                <div class="col-sm-6">
                  <input type="telephone" class="form-control" id="inputPhone" name="mobile" placeholder="07..." value="<?php echo $user['mobile']; ?>">
                </div>
              </div>
              <div class="form-group">
                <label for="inputGender" class="col-sm-2 control-label">Gender</label>
                <div class="col-sm-6">
                  <select name="gender" id="inputGender" name="gender" class="form-control col-sm-6"<?php if($user['fb_id']) echo ' disabled'; ?>>
                    <option value="">Prefer not to say</option>
                    <option value="0"<?php if($user['gender'] === '0') echo ' selected'; ?>>Male</option>
                    <option value="1"<?php if($user['gender'] === '1') echo ' selected'; ?>>Female</option>
                  </select>
                </div>
                <p class="col-sm-4">This data is used anonomously for statistics.</p>
              </div>
              <div class="form-group">
                <label for="inputDOBdd" class="col-sm-2 control-label">DOB</label>
                <div class="col-sm-2">
                  <input type="number" class="form-control" id="inputDOBdd" name="birthday[]" placeholder="dd" value="<?php echo explode('-',$user['birthday'])[2]; ?>"<?php if($user['fb_id']) echo ' disabled'; ?>/>
                </div>
                <div class="col-sm-2">
                  <input type="number" class="form-control" id="inputDOBmm" name="birthday[]" placeholder="mm" value="<?php echo explode('-',$user['birthday'])[1]; ?>"<?php if($user['fb_id']) echo ' disabled'; ?>/>
                </div>
                <div class="col-sm-2">
                  <input type="number" class="form-control" id="inputDOByyyy" name="birthday[]" placeholder="yyyy" value="<?php echo explode('-',$user['birthday'])[0]; ?>"<?php if($user['fb_id']) echo ' disabled'; ?>/>
                </div>
                <p class="col-sm-4">This data is used anonomously for statistics.</p>
              </div>
              <div class="form-group">
                <label for="inputNewPassword" class="col-sm-2 control-label">New Password</label>
                <div class="col-sm-6">
                  <input type="password" class="form-control" id="inputNewPassword" name="newPassword" placeholder="New Password" autocompelete="new-password">
                </div>
              </div>
              <div class="form-group">
                <label for="inputNewPassword2" class="col-sm-2 control-label">Confirm Password</label>
                <div class="col-sm-6">
                  <input type="password" class="form-control" id="inputNewPassword2" name="newPassword2" placeholder="Confirm Password" autocompelete="new-password">
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                  <button type="submit" class="btn btn-success">Save</button>
                </div>
              </div>
            </form>
          </div>
          <div role="tabpanel" class="tab-pane" id="membership">
            <h3>Membership Type</h3>
            <div style="overflow:hidden">
              <div style="float:right">
                Pay Monthly <label class="switch"><input type="checkbox" id="annual-switch"><div class="slider round"></div></label> Pay Annual
              </div>
            </div>
            <table class="membership-types" style="width:100%">
              <?php if($plan==='tktpass_freedom') : ?> Freedom
              <?php elseif($plan==='tktpass_flexible') : ?> Flexible
              <?php else : ?>
              <tr>
                <td class="title">Freedom</td>
                <td>
                  <div class="pay-monthly">
                    <div><small>£</small> 7 <small>/ month</small></div>
                    <div><small>23p per day</small></div>
                  </div>
                  <div class="pay-annually hidden">
                    <div><small style="text-decoration:line-through;">£7.00</small> <span style="color:red;padding-left:0.6em"><big>5.</big><small>60</small></span> <small>/ month</small></div>
                    <div><small>19p per day</small></div>
                    <div><small style="color:#5ac336">Save 4.4 months</small></div>
                  </div>
                </td>
                <td></td>
                <td><a class="btn btn-outline-green" href="javascript:alert('Coming soon');">Switch</a></td>
              </tr>
              <tr>
                <td class="title">Flexible</td>
                <td>
                  <div class="pay-monthly">
                    <div><small>£</small> 3 <small>/ month</small></div>
                    <div><small>10p per day</small></div>
                  </div>
                  <div class="pay-annually hidden">
                    <div><small style="text-decoration:line-through;">£3.00</small> <span style="color:red;padding-left:0.6em"><big>2.</big><small>40</small></span> <small>/ month</small></div>
                    <div><small>8p per day</small></div>
                    <div><small style="color:#5ac336">Save 2.5 months</small></div>
                  </div>
                </td>
                <td>Most popular</td>
                <td><a class="btn btn-outline-green" href="javascript:alert('Coming soon');">Switch</a></td>
              </tr>
              <tr class="active">
                <td class="title">Square</td>
                <td><strong>FREE</strong></td>
                <td></td>
                <td></td>
              </tr>
            <?php endif; ?>
            </table>
          </div>
          <div role="tabpanel" class="tab-pane" id="cards">
            <h3>Cards</h3>
            <div class="row">
              <div class="col-sm-4">
                <div class="savedCard" id="<?php echo $customer->default_source->id; ?>">
                  <form class="form-horizontal">
                    <div class="form-group">
                      <div class="col-xs-12 cc-brand <?php $cardTypes = array("Visa"=>"visa","MasterCard"=>"mastercard","American Express"=>"amex"); echo (in_array($customer->default_source->brand,array_keys($cardTypes)) ? $cardTypes[$customer->default_source->brand] : $customer->default_source->brand); ?>"></div>
                      <?php if($customer->default_source->name) : ?>
                      <div class="col-xs-12">
                        <input type="text" value="<?php echo $customer->default_source->name; ?>" readonly tabIndex="-1" class="form-control">
                      </div>
                      <?php endif; ?>
                      <div class="col-xs-12">
                        <input type="text" value="XXXX XXXX XXXX <?php echo $customer->default_source->last4; ?>" readonly tabIndex="-1" class="form-control">
                      </div>
                      <div class="col-xs-6">
                        <input type="text" value="<?php echo $customer->default_source->exp_month<10?'0'.$customer->default_source->exp_month:$customer->default_source->exp_month; ?>/<?php echo $customer->default_source->exp_year-2000; ?>" readonly tabIndex="-1" class="form-control col-xs-6">
                      </div>
                      <div class="col-xs-6" style="line-height: 34px;font-style:italic">Default</div>
                    </div>
                  </form>
                </div>
              </div>
              <?php
                $cards = $customer->sources->all(array('object' => 'card'));
                foreach($cards->data as $card) {
                  if($card->fingerprint === $customer->default_source->fingerprint) continue;
              ?>
              <div class="col-sm-4">
                <div class="savedCard" id="<?php echo $card->id; ?>>
                  <form class="form-horizontal">
                    <div class="form-group">
                      <div class="col-xs-12 cc-brand <?php echo in_array($card->brand,array_keys($cardTypes)) ? $cardTypes[$card->brand] : $card->brand; ?>"></div>
                      <?php if($card->name) : ?>
                      <div class="col-xs-12">
                        <input type="text" value="<?php echo $card->name; ?>" readonly tabIndex="-1" class="form-control">
                      </div>
                      <?php endif; ?>
                      <div class="col-xs-12">
                        <input type="text" value="XXXX XXXX XXXX <?php echo $card->last4; ?>" readonly tabIndex="-1" class="form-control">
                      </div>
                      <div class="col-xs-6">
                        <input type="text" value="<?php echo $card->exp_month<10?'0'.$card->exp_month:$card->exp_month; ?>/<?php echo $card->exp_year-2000; ?>" readonly tabIndex="-1" class="form-control col-xs-6">
                      </div>
                      <a class="col-xs-6" style="line-height: 34px;">Make default</a>
                    </div>
                  </form>
                </div>
                <a href="javascript:;" class="delete" title="Remove card">X</a>
              </div><?php } ?>
              <div class="col-sm-4">
                <div class="savedCard text-center">
                  <a style="display: block; width: 100%; height: 100%;" href="javascript:alert('Coming soon');"><span style="width:100%;position: absolute; top: 50%; left: 0;margin-top: -10px;">+ Add another card</span></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <hr />
        <a class="btn btn-outline-green" href="#">Give us feedback</a>
        <a class="btn btn-outline-green" href="#">Help &amp; support</a>
        <a class="btn btn-default" href="/logout.php" style="float:right">Logout</a>
      </div>
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
        <li>
          <a href="/myevents.php">
            <svg id="fb-import-icon" height="60" width="60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="fill: #888">
              <image x="0" y="0" height="60" width="60"  xlink:href="/img/icon/party.svg" />
            </svg>
            My Events
          </a>
        </li>
        <li class="active"><a>
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
    <script src="js/vendor/nouislider.min.js"></script>

    <script src="js/myaccount.js"></script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--[if gt IE 9]>
      <script src="js/vendor/ie10-viewport-bug-workaround.js"></script>
    <![endif]-->
  </body>
</html>