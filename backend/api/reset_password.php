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

$user_data = getUserData();
if (!$user_data || !$user_data->id || !$user_data->username) {
  http_response_code(401);
  echo '[]';
  exit;
}

// Include config file
require_once "../config.php";

// Define variables and initialize with empty values
$old_password     = $old_password_err     = "";
$new_password     = $confirm_password     = "";
$new_password_err = $confirm_password_err = "";
$err              = "";
$success          = false;

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Validate old password
  if (empty(trim($_POST["old_password"]))) {
    $old_password_err = "Please enter the old password.";
  } elseif (strlen(trim($_POST["old_password"])) < 8) {
    $old_password_err = "Password must have at least 8 characters.";
  } else {
    $old_password = trim($_POST["old_password"]);
  }

  // Validate new password
  if (empty(trim($_POST["new_password"]))) {
    $new_password_err = "Please enter the new password.";
  } elseif (strlen(trim($_POST["new_password"])) < 8) {
    $new_password_err = "Password must have at least 8 characters.";
  } else {
    $new_password = trim($_POST["new_password"]);
  }

  // Validate confirm password
  if (empty(trim($_POST["confirm_password"]))) {
    $confirm_password_err = "Please confirm the password.";
  } else {
    $confirm_password = trim($_POST["confirm_password"]);
    if (empty($new_password_err) && ($new_password != $confirm_password)) {
      $confirm_password_err = "Password did not match.";
    }
  }

  if (empty($old_password_err) && empty($new_password_err) && empty($confirm_password_err)) {

    $old_password_err = "Incorrect old password.";

    // TODO: check old_password!
    $sql = "SELECT password FROM users WHERE id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "i", $param_id);

      // Set parameters
      $param_id = $user_data->id;

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        // Store result
        mysqli_stmt_store_result($stmt);

        // Check if username exists, if yes then verify password
        if (mysqli_stmt_num_rows($stmt) == 1) {
          // Bind result variables
          mysqli_stmt_bind_result($stmt, $hashed_password);
          if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($old_password, $hashed_password)) {
              $old_password_err = "";

              // Close statement
              mysqli_stmt_close($stmt);

              // Check input errors before updating the database
              if (empty($new_password_err) && empty($confirm_password_err)) {
                // Prepare an update statement
                $sql = "UPDATE users SET password = ? WHERE id = ?";

                if ($stmt = mysqli_prepare($link, $sql)) {
                  // Bind variables to the prepared statement as parameters
                  mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

                  // Set parameters
                  $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                  $param_id       = $user_data->id;

                  // Attempt to execute the prepared statement
                  if (mysqli_stmt_execute($stmt)) {
                    $success = true;
                  } else {
                    $err = "Oops! Something went wrong. Please try again later.";
                  }

                  // Close statement
                  mysqli_stmt_close($stmt);
                }
              }
            }
          }
        }
      }
    }
  }
}
// Close connection
mysqli_close($link);

if ($success) {
  http_response_code(200);
  echo json_encode(array("message" => "Password was updated."));
  exit;
}

http_response_code(400);
echo json_encode(array(
  "message"          => "Password not changed.",
  "old_password"     => $old_password_err,
  "new_password"     => $new_password_err,
  "confirm_password" => $confirm_password_err,
  "error"            => $err,
));
