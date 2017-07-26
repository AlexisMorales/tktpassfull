<?php
/**
 * @file utils-login.php
 * This file contains utility functions to ask about a user's login status and also the `logout` function.
 *
 * @defgroup utils-login Login Utility Functions
 * @brief Functions for asking about a user's login status or logging them out.
 * @{
 */

/**
* Checks whether the current user is logged into the site or not.
*
*
* @author  Alex Taylor <alex@taylrr.co.uk>
*
* @since 1.0
*
* @return boolean Returns `true` if the user is logged in, `false` if not.
*/
function isLoggedIn(){
	if (session_status() == PHP_SESSION_NONE)
		session_start();
	return isset($_SESSION['user']) && $_SESSION['user'];
}

/**
* Checks whether the current user has their Facebook account connected to their tktpass login (most likely as a result of logging in with Facebook)
*
*
* @author  Alex Taylor <alex@taylrr.co.uk>
*
* @since 1.0
*
* @return boolean Returns `true` if the user has their Facebook account connected (they've authorised the tktpass application), `false` if not.
*/
function isFbConnected(){
	if (session_status() == PHP_SESSION_NONE)
		session_start();
	if(!isLoggedIn()) return null;
	return isset($_SESSION['user']['fb_access_token']) && $_SESSION['user']['fb_access_token'];
}

/**
* Logs out the current user by destroying the session and clearing the session cookie.
*
*
* @author  Alex Taylor <alex@taylrr.co.uk>
*
* @since 1.0
*
* @return void
*/
function logout(){
	if (session_status() == PHP_SESSION_NONE)
		session_start();
	$_SESSION = array();
	if (ini_get("session.use_cookies")) {
	  $params = session_get_cookie_params();
	  setcookie(session_name(), '', time() - 42000,
	      $params["path"], $params["domain"],
	      $params["secure"], $params["httponly"]
	  );
	}
	session_destroy();
	session_regenerate_id(true);
}

/** @}*/