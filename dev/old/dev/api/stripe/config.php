<?php	
		
require_once API.'config.php';
require_once ROOT.'/vendor/autoload.php';

$stripe = array(
  "secret_key"      => "sk_test_tCDEScuiYkGR1Q7T81U1fEiM",
  "publishable_key" => "pk_test_6e2JAHrqsSfADzDHDMeAssBG"
);

\Stripe\Stripe::setApiKey($stripe['secret_key']);
?>