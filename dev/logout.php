<?php
session_start();

require_once '../includes/utils-login.php';
logout();

if(isset($_GET['next']) &&
   (is_null(parse_url($_GET['next'],PHP_URL_HOST)) || substr(parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST),-11)=='tktpass.com')
  )
  $next = $_GET['next'];
else $next = '/';

header('Location: '.$next);