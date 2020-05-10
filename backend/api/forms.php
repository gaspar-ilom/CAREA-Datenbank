<?php
header('Content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:8080');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
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

$form = '';
$sql  = '';

$method  = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));

if (!$link) {
  die("Connection failed: " . mysqli_connect_error());
}

// List the tables required
$entry_forms = array(
  "Neue Person eintragen"   => ["person"],
  "Seminaranmeldung"        => ["seminarteilnahme"],
  "Readerbestellung"        => ["reader"],
  "Ausreisedaten eintragen" => ["ausreise", "notfallkontakt"],
  "Neues Seminar erstellen" => ["seminar"],
);

function getTableDesc($tableName)
{
  global $link;
  $sql        = "DESCRIBE " . $tableName . ";";
  $foundRow   = 0;
  $descriptor = array();

  // run SQL statement
  $result = mysqli_query($link, $sql);

  // die if SQL statement failed
  if (!$result) {
    http_response_code(404);
    die(mysqli_error($link));
  }

  for ($i = 0; $i < mysqli_num_rows($result); $i++) {
    $row   = mysqli_fetch_object($result);
    $field = $row->Field;
    if (substr($row->Type, 0, 7) === "varchar") {
      $type = "string";
    } elseif (substr($row->Type, 0, 3) === "int") {
      $type = "number";
    } elseif ($row->Type === "tinyint(1)") {
      $type = "bool";
    } else {
      $type = $row->Type;
    }
    if ($row->Null === "NO" || $row->Key !== "") {
      $required = true;
    } else {
      $required = false;
    }
    if ($row->Default === "NULL") {
      $default = "";
    } else {
      $default = $row->Default;
    }
    if ($row && $row->Extra != 'auto_increment') {
      $info = array(
        "Field"    => $field,
        "Type"     => $type,
        "Required" => $required,
        "Default"  => $default,
      );
      $descriptor[$foundRow] = $info;
      $foundRow++;
    }
  }
  return $descriptor;
}

switch ($method) {
  case 'GET':
    $form = $_GET['form'];
    if (!$form || !$entry_forms[$form]) {
      echo json_encode(array_keys($entry_forms));
      exit;
    }
    $result = array();
    for ($i = 0; $i < count($entry_forms[$form]); $i++) {
      $result[$entry_forms[$form][$i]] = getTableDesc($entry_forms[$form][$i]);
    }
    echo json_encode($result);
    $result = '';
    break;
  case 'POST':
    http_response_code(404);
    break;
}

$link->close();
