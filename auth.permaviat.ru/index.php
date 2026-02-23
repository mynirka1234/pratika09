<?php
require_once("config.php");
require_once("settings/connect.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type, token");
header("Access-Control-Expose-Headers: token");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

if(!isset($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_USER'])) { header('HTTP/1.0 403 Forbidden'); exit; }
if(!isset($_SERVER['PHP_AUTH_PW']) || empty($_SERVER['PHP_AUTH_PW'])) { header('HTTP/1.0 403 Forbidden'); exit; }

$login = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login` = '$login' AND `password` = '$password';");

if($read_user = $query_user->fetch_assoc()) {
    $header = array("typ" => "JWT", "alg" => "sha256");

    $payload = array(
        "userId" => $read_user['id'], 
        "role" => $read_user['roll'], 
        "exp" => time() + 3600
    );

    $SECRET_KEY = 'cAtwa1kKEy';

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

    $unsignedToken = $base64UrlHeader . "." . $base64UrlPayload;
    $signature = hash_hmac('sha256', $unsignedToken, $SECRET_KEY, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    $token = $unsignedToken . "." . $base64UrlSignature;
    
    header("token: ".$token);
} else {
    header('HTTP/1.0 401 Unauthorized');
}
?>