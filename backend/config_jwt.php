<?php
include_once 'vendor/firebase/php-jwt/src/BeforeValidException.php';
include_once 'vendor/firebase/php-jwt/src/ExpiredException.php';
include_once 'vendor/firebase/php-jwt/src/SignatureInvalidException.php';
include_once 'vendor/firebase/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;

date_default_timezone_set('Europe/Berlin');

$key = "#this_key_should_be_replaced!";

function createToken($data)
{
  global $key;
  $token = array(
    "iat"  => time(),
    "nbf"  => time(),
    "exp"  => time() + 3600,
    // "jti"  => base64_encode(mcrypt_create_iv(32)),
    "data" => $data,
  );
  return JWT::encode($token, $key, 'HS512');
}

function isAuthorized()
{
  global $key;
  $headers = apache_request_headers();
  $auth    = explode(' ', $headers['Authorization']);
  if (count($auth) < 2 || $auth[0] !== 'Bearer') {
    return false;
  }
  $jwt = $auth[1];
  // if decode succeed, show user details
  try {
    // decode jwt
    $decoded = JWT::decode($jwt, $key, array('HS512'));
    if ($decoded->exp < time() || $decoded->nbf > time()) {
      return false;
    }
    return true;
  }
  // if decode fails, it means jwt is invalid
   catch (Exception $e) {
    return false;
  }
  return false;
}
