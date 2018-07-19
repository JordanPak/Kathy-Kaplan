<?php

require "compatibility.php";
require "config.php";
require "session.php";
require "classes/formBuilder.php";
require "phpmailer/class.phpmailer.php";

define('IS_AJAX',isset($_SERVER['HTTP_X_REQUESTED_WITH']));

?>