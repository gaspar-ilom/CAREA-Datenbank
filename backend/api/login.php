<?php
header('Content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:8080');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS"){
  exit;
}

// Include config file
require_once "../config.php";

// Define variables and initialize with empty values
$username     = $password     = "";
$username_err = $password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Check if username is empty
  if (empty(trim($_POST["username"]))) {
    $username_err = "Please enter username.";
  } else {
    $username = trim($_POST["username"]);
  }

  // Check if password is empty
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter your password.";
  } else {
    $password = trim($_POST["password"]);
  }

  // Validate credentials
  if (empty($username_err) && empty($password_err)) {
    // Prepare a select statement
    $sql = "SELECT id, username, password FROM users WHERE username = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "s", $param_username);

      // Set parameters
      $param_username = $username;

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        // Store result
        mysqli_stmt_store_result($stmt);

        // Check if username exists, if yes then verify password
        if (mysqli_stmt_num_rows($stmt) == 1) {
          // Bind result variables
          mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
          if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $hashed_password)) {
              // Password is correct, so start a new session

              // generate json web token
              require_once '../config_jwt.php';
              $data = array(
                "id"       => $id,
                "username" => $username,
              );
              $jwt = createToken($data);
              if ($jwt) {
                // set response code
                http_response_code(200);
                echo json_encode(
                  array(
                    "message" => "Successful login.",
                    "jwt"     => $jwt,
                  )
                );
              } else {
                // Display an error message if password is not valid
                // set response code
                http_response_code(401);
                // tell the user login failed
                echo json_encode(array("message" => "Login failed.7"));
              }

            } else {
              // Display an error message if password is not valid
              // set response code
              http_response_code(401);
              // tell the user login failed
              echo json_encode(array("message" => "Login failed.6"));
            }
          }
        } else {
          // Display an error message if username doesn't exist
          // set response code
          http_response_code(401);
          // tell the user login failed
          echo json_encode(array("message" => "Login failed.5"));
        }
      } else {
        // set response code
        http_response_code(401);
        // tell the user login failed
        echo json_encode(array("message" => "Login failed.4"));
      }

      // Close statement
      mysqli_stmt_close($stmt);
    } else {
      // set response code
      http_response_code(401);
      // tell the user login failed
      echo json_encode(array("message" => "Login failed.3"));
    }
  } else {
    // set response code
    http_response_code(401);
    // tell the user login failed
    echo json_encode(array("message" => "Login failed.2"));
  }

  // Close connection
  mysqli_close($link);
} else {
  // set response code
  http_response_code(401);
  // tell the user login failed
  echo json_encode(array("message" => "Login failed.1"));
}
