<?php
/**
 * @file db-setup.php
 * File used to initalise the global `$db` PDO instance used to handle all direct communication with the `tktpass` database.
 */

/**
 * @global array An associative array containg the settings described in config.ini
 */
$config = parse_ini_file('config.ini',true);

/**
* @global PDO A <a href="//php.net/manual/en/book.pdo.php" target="_blank">PHP Data Object</a>, this global is used to handle all direct communication with the `tktpass` database.
*/
$db;

/**
* @cond try
*/
try {
    $db = new PDO('mysql:host='.$config["db"]["host"].';dbname='.$config["db"]["dbname"].'', $config["db"]["user"], $config["db"]["pass"]);
} catch (PDOException $e) {
	header("HTTP/1.1 500 Internal Server Error");
    print "Error connecting to datebase: " . $e->getMessage() . "<br/>";
    die();
}
/**
* @endcond
*/