<?php

require 'isValidEmail.php';

$email = $_GET['email'];

var_dump(isValidEmail($email));