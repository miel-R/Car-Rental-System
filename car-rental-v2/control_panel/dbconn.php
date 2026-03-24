<?php

require_once('credentials.php');
$conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
	echo "Connection failed!";
}