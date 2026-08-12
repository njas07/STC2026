<?php

session_start();

$_SESSION["user_id"] = 2;

$_POST["registration_id"] = 1;
$_POST["status"] = "DIVERIFIKASI";

require "admin/update_status.php";

?>