<?php
/* Database credentials. Assuming you are running MySQL server */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'XXXX');
define('DB_PASSWORD', 'XXXXXX');
define('DB_NAME', 'XXXXX');

/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($link === false) {
  die("ERROR: Could not connect. " . mysqli_connect_error());
}
mysqli_set_charset($link, "utf8");