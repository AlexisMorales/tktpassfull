<?php
/**
 * @file stripe-setup.php
 * This file set the static Stripe API key used in all \\Stripe\\Stripe calls.
 *
 * @note This file requires the Stripe SDK is already loaded, it is not loaded here as it is usually loaded by including composer's `vendor/autoload.php` file, this file can then be included after that to set the API key.
 *
 * @see <a href="https://github.com/stripe/stripe-php" target="_blank">PHP library for the Stripe API on Github</a>
 * @see <a href="https://stripe.com/docs/api?lang=php" target="_blank">Stripe API Reference</a>
 */

\Stripe\Stripe::setApiKey("sk_test_7hKBarIyyojeNOkds9jSUBcQ");