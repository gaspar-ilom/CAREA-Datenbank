<?php
header('Content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:8080');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS"){
  exit;
}

// Check if the user is logged in
require_once '../config_jwt.php';
if (!isAuthorized()) {
  http_response_code(401);
  echo '[]';
  exit;
}

// Include config file
require_once "../config.php";

$table     = '';
$id        = '';
$person_id = '';
$sql       = '';

$method  = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));

if (!$link) {
  die("Connection failed: " . mysqli_connect_error());
}

switch ($method) {
  case 'GET':
    $person_id = $_GET['person_id'];
    $table     = $_GET['table'];
    if (!$table) {
      $sql = "SHOW TABLES;";
    } else if ($table == 'users') {
      // Do not return info about admin users
      $table = '';
      echo '[]';
      exit;
    } else {
      $sql = "select * from " . mysqli_real_escape_string($link, $table);
    }
    break;
  case 'POST':
    $table = $_POST["table"];
    if ($table) {
      $escaped_table = mysqli_real_escape_string($link, $table);
      $keys = array_keys($_POST);
      $sql = "INSERT INTO $escaped_table (";
      for ($i=0; $i<count($keys); $i++) {
        if ($keys[$i] !== 'table') {
          $sql = $sql . mysqli_real_escape_string($link, $keys[$i]) . ", ";
        }
      }
      $sql = rtrim($sql, ", ") . ") values (";
      for ($i=0; $i<count($keys); $i++) {
        if ($keys[$i] !== 'table') {
          $sql = $sql . "'" . mysqli_real_escape_string($link, $_POST[$keys[$i]]) . "'" . ", ";
        }
      }
      $sql = rtrim($sql, ", ") . ");";
      echo $sql;
    }
    break;
}

// run SQL statement
$result = mysqli_query($link, $sql);

// die if SQL statement failed
if (!$result) {
  http_response_code(404);
  die(mysqli_error($link));
}

$foundRow = 0;

if ($method == 'GET') {
  if (mysqli_num_rows($result) > 0) {
    echo '[';
  }
  for ($i = 0; $i < mysqli_num_rows($result); $i++) {
    if (!$table) {
      $row = json_encode(mysqli_fetch_object($result)->Tables_in_buero_db);
    } else {
      $row = json_encode(mysqli_fetch_object($result));
    }
    if ($row) {
      echo ($foundRow > 0 ? ',' : '') . $row;
      $foundRow++;
    }
  }
  if (mysqli_num_rows($result) > 0) {
    echo ']';
  }

} elseif ($method == 'POST') {
  echo json_encode($result);
} else {
  echo mysqli_affected_rows($link);
}

$link->close();
