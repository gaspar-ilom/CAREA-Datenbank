<?php
header('Content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:8080');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS"){
  exit;
}

// Check if the user is logged in, if not then redirect to login page
require_once '../config_jwt.php';
if (!isAuthorized()) {
  http_response_code(401);
  echo '[]';
  exit;
}

// Include config file
require_once "../config.php";

$method  = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));

if (!$link) {
  die("Connection failed: " . mysqli_connect_error());
}

$sql           = '';
$query         = '';
$person_query  = '';
$seminar_query = '';
$person_id     = '';
$seminar_id    = '';
$deletion      = '';
$result        = '';

$seminar_queries = array(
  "TN Liste Intern" => "SELECT st.person_id, st.seminar_id, p.Vorname, p.Name, p.Strasse, p.PLZ, p.Ort, p.Telefon, p.Email, r.Verschickt_am AS Reader_verschickt, st.Essen, st.Anmerkung, st.Absage,
                      CASE WHEN s.Bezeichnung LIKE 'NBS%' THEN 'kostenfrei' ELSE st.Bezahlt END AS Bezahlt
                      FROM person AS p LEFT JOIN reader AS r ON p.id=r.Person_id, seminar AS s, seminarteilnahme AS st
                      WHERE st.seminar_id=s.id AND p.id=st.person_id AND s.id = ?
                      ORDER BY st.Absage, p.Vorname, p.Name;",
  "TN Liste Extern" => "SELECT st.person_id, st.seminar_id, p.Vorname, p.Email, p.Ort
                      FROM person AS p
                      JOIN seminarteilnahme AS st ON p.id=st.person_id
                      WHERE st.MFG = 1 AND (st.Absage IS NULL OR NOT st.Absage=1  ) AND st.seminar_id = ?
                      ORDER BY p.Ort",
);

$queries = array(
  "Reader ist bezahlt"               => "SELECT p.Vorname, p.Name, p.Strasse, p.PLZ, p.Ort, p.Telefon, p.Email, r.Verschickt_am AS Reader_verschickt, r.Region, r.Alternativadresse, r.Bezahlt
                                         FROM person AS p
                                         JOIN reader AS r ON p.id = r.Person_id, seminarteilnahme AS st
                                         WHERE p.id = st.person_id AND r.Bezahlt IS NOT NULL ORDER BY st.Seminar_id, p.Name;",
  "Reader noch nicht bezahlt"        => "SELECT p.Vorname, p.Name, p.Strasse, p.PLZ, p.Ort, p.Telefon, p.Email, r.Verschickt_am AS Reader_verschickt, r.Region, r.Alternativadresse 
                                         FROM person AS p JOIN reader AS r ON p.id = r.Person_id, seminarteilnahme AS st 
                                         WHERE p.id = st.person_id AND r.Bezahlt IS NULL 
                                         ORDER BY st.Seminar_id, p.Name;",
  "Unterwegs: beim Aval mitschicken" => "SELECT p.Vorname, p.Reisemail, CONCAT_WS('-', MONTH(a.Abflug), YEAR(a.Abflug)) AS Abflug, CONCAT_WS('-', MONTH(a.Rueckflug), YEAR(a.Rueckflug)) AS Rueckflug, a.Region
                                         FROM person AS p JOIN ausreise AS a ON p.id = a.Person_id, seminar AS s, seminarteilnahme AS st
                                         WHERE st.seminar_id = s.id AND p.id = st.person_id AND a.CdB IS NULL AND ( a.Rueckflug > CURDATE() OR a.Rueckflug IS NULL)
                                         ORDER BY p.Name;",
  "Unterwegs: CdB Checkliste"        => "SELECT p.Vorname, p.Name, p.Strasse, p.PLZ, p.Ort, p.Telefon, p.Email, p.Reisemail, a.Region, a.Anmerkung, a.Abflug, a.Rueckflug 
                                         FROM person AS p JOIN ausreise AS a ON p.id=a.Person_id
                                         WHERE a.CdB IS NULL 
                                         ORDER BY a.Rueckflug, p.Name;",
  "Unterwegs: Liste für Wolfgang"    => "SELECT p.Vorname, p.Name, p.Ort, p.Reisemail, a.Abflug, a.Rueckflug, a.Region
                                         FROM person AS p JOIN ausreise AS a ON p.id = a.Person_id
                                         WHERE a.CdB IS NULL AND ( a.Rueckflug > CURDATE() OR a.Rueckflug IS NULL)
                                         ORDER BY p.Name;",
  "Löschbare Notfallkontakte"        => "SELECT *
                                         FROM notfallkontakt AS n
                                         JOIN ausreise AS a ON n.Ausreise_id=a.id
                                         WHERE a.CdB IS NOT NULL;",
  "NBS Einladungen verschicken"      => "SELECT DISTINCT p.Vorname, p.Name, p.Telefon, p.Email, p.Reisemail, a.Abflug, a.Rueckflug, a.CdB 
                                         FROM person AS p JOIN ausreise AS a ON p.id=a.Person_id, seminar AS s, seminarteilnahme AS st 
                                         WHERE st.seminar_id=s.id AND p.id=st.person_id AND NOT s.Typ='NBS' AND (a.CdB IS NULL OR a.CdB > MAKEDATE(YEAR(DATE_SUB(CURDATE(), INTERVAL 2 YEAR)), 306)) 
                                         ORDER BY p.Name;"
);

$person_queries = array(
  "notfallkontakt"   => "SELECT n.*
                         FROM notfallkontakt AS n
                         JOIN ausreise AS a ON n.Ausreise_id=a.id
                         JOIN person AS p ON a.Person_id=p.id
                         WHERE p.id=?;",
  "ausreise"         => "SELECT a.*
                         FROM ausreise AS a
                         JOIN person AS p ON a.Person_id=p.id
                         WHERE p.id=?;",
  "person"           => "SELECT *
                         FROM person AS p
                         WHERE p.id=?;",
  "reader"           => "SELECT r.*
                         FROM reader AS r
                         JOIN person AS p ON r.Person_id=p.id
                         WHERE p.id = ? ;",
  "seminar"          => "SELECT st.*, s.*
                         FROM seminarteilnahme AS st
                         JOIN person AS p ON st.Person_id=p.id
                         JOIN seminar AS s ON st.Seminar_id=s.id
                         WHERE p.id=?;",
  "seminarteilnahme" => "SELECT st.*, s.*
                         FROM seminarteilnahme AS st
                         JOIN person AS p ON st.Person_id=p.id
                         JOIN seminar AS s ON st.Seminar_id=s.id
                         WHERE p.id=?;",
);

$deletions = array(
  "Löschbare Notfallkontakte"           => "DELETE n
                                            FROM notfallkontakt AS n
                                            JOIN ausreise AS a ON n.Ausreise_id=a.id
                                            WHERE a.CdB IS NOT NULL;",
  "Bezahlte Readerbestellungen löschen" => "DELETE r
                                            FROM reader AS r
                                            WHERE r.Bezahlt IS NOT NULL;",
  "Person löschen"                      => "DELETE FROM person
                                            WHERE id=?;",
);

switch ($method) {
  case 'GET':
    $query         = $_GET['query'];
    $person_query  = $_GET['person_query'];
    $seminar_query = $_GET['seminar_query'];
    $person_id     = $_GET['person_id'];
    $seminar_id    = $_GET['seminar_id'];
    if ($query) {
      $sql = $queries[$query];
      if (!$sql) {
        exit;
      }
    } else if ($person_query) {
      if (!$person_id || $person_query == 'list') {
        // List all available person data queries
        echo json_encode(array_keys($person_queries));
        exit;
      }
      $sql = $person_queries[$person_query];
      if (!$sql || !$person_id) {
        exit;
      } else {
        // run prepared statement
        $stmt      = $link->prepare($sql);
        $person_id = 1 * $person_id;
        $stmt->bind_param("i", $person_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo '[';
        for ($i = 0; $i < mysqli_num_rows($result); $i++) {
          $obj = mysqli_fetch_object($result);
          echo ($i > 0 ? ',' : '') . json_encode($obj);
        }
        echo ']';
        $stmt->close;
        $link->close;
        exit;
      }
    } else if ($seminar_query) {
      if (!$seminar_id || $seminar_query == 'list') {
        // List all available seminar data queries
        echo json_encode(array_keys($seminar_queries));
        exit;
      }
      $sql = $seminar_queries[$seminar_query];
      if (!$sql || !$seminar_id) {
        exit;
      } else {
        // run prepared statement
        $stmt       = $link->prepare($sql);
        $seminar_id = 1 * $seminar_id;
        $stmt->bind_param("i", $seminar_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo '[';
        for ($i = 0; $i < mysqli_num_rows($result); $i++) {
          $obj = mysqli_fetch_object($result);
          echo ($i > 0 ? ',' : '') . json_encode($obj);
        }
        echo ']';
        $stmt->close;
        $link->close;
        exit;
      }
    } else {
      // List all available queries
      echo json_encode(array_keys($queries));
      exit;
    }
    break;
  case 'POST':
    $deletion = $_POST['deletion'];
    if ($deletion) {
      $sql = $deletions[$deletion];
      if (!$sql) {
        exit;
      }
      if (strpos($sql, '?') !== false) {
        $person_id = $_POST['person_id'];
        if (!$person_id) {
          exit;
        }
        // run prepared statement
        $stmt      = $link->prepare($sql);
        $person_id = 1 * $person_id;
        $stmt->bind_param("i", $person_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result);
        $stmt->close;
        $link->close;
        exit;
      }
    } else {
      exit;
    }
    break;
}

// run SQL statement
if (!$result) {
  $result = mysqli_query($link, $sql);
}

// die if SQL statement failed
if (!$result) {
  http_response_code(404);
  die(mysqli_error($link));
}

$foundRow = 0;

if ($method == 'GET') {
  echo '[';
  for ($i = 0; $i < mysqli_num_rows($result); $i++) {
    $row = json_encode(mysqli_fetch_object($result));
    if ($row) {
      echo ($foundRow > 0 ? ',' : '') . $row;
      $foundRow++;
    }
  }
  echo ']';
} elseif ($method == 'POST') {
  echo json_encode($result);
} else {
  echo mysqli_affected_rows($link);
}

$link->close();
