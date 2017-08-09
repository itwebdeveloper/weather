<?php
ini_set('display_startup_errors', 1);
ini_set('display_error', 1);
error_reporting(E_ALL);
date_default_timezone_set("UTC"); 

$debug = false; // If true the script prints additional debug lines to video
$private_key='a258a26fcc8493fc074ecd506b71e636';
$req_latitude=37.8267;
$req_longitude=-122.4233;
// $curl_request_endpoint = "https://api.darksky.net/forecast/$private_key/$req_latitude,$req_longitude";
$curl_request_endpoint = "http://10.0.0.10/darksky_fake/response.php";
$log_file = "/var/log/dark_sky_proxy/debug.log";
$email_addresses = "Andrea Cannuni <andreacannuni@gmail.com>";

$db_host = "10.0.0.10";
$db_user = "foouser";
$db_pass = "abc";
$db_name = "weather";
$charset = 'utf8';