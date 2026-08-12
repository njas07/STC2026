<?php

session_start();

echo "SESSION START BERHASIL<br>";

echo "Session ID: " . session_id() . "<br>";

echo "User ID: ";

if (isset($_SESSION["user_id"])) {
    echo $_SESSION["user_id"];
} else {
    echo "TIDAK ADA";
}

session_write_close();

echo "<br>SESSION SUDAH DITUTUP";

?>