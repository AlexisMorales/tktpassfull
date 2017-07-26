<?php
	
session_start();	
	
require_once 'paths.php';
require_once API.'config.php';
require_once ROOT.'/vendor/autoload.php';
require_once 'sendmail.php';
require_once 'IO.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// Set your secret key: remember to change this to your live secret key in production
// See your keys here https://dashboard.stripe.com/account/apikeys
\Stripe\Stripe::setApiKey("sk_test_7hKBarIyyojeNOkds9jSUBcQ");

// Get the credit card details submitted by the form
$token = $_POST['stripeToken'];

// Create a Customer
$customer = \Stripe\Customer::create(array(
  "source" => $token,
  "description" => "Example customer")
);w

// Charge the Customer instead of the card
\Stripe\Charge::create(array(
  "amount" => 1000, // amount in cents, again
  "currency" => "gbp",
  "customer" => $customer->id)
);

// YOUR CODE: Save the customer ID and other info in a database for later!

// YOUR CODE: When it's time to charge the customer again, retrieve the customer ID!

\Stripe\Charge::create(array(
  "amount"   => 1500, // £15.00 this time
  "currency" => "gbp",
  "customer" => $customerId // Previously stored, then retrieved
  ));


?>