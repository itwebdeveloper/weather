<?php
require_once(__DIR__ .'/config.php');

function send_request($curl_request_endpoint)
{
    global $debug, $log_file, $email_addresses;

    if($debug) {
        if (!is_writable($log_file)) {
            $response['status'] = "error";
            $response['message'] = "The file '". $log_file ."' is not writable.";
            return $response;
        }
    }

    try {
        // Build cURL request
        // Get cURL resource
        $curl = curl_init();

        /* Example:
            curl -X GET \
              'https://api.darksky.net/forecast/a258a26fcc8493fc074ecd506b71e636/37.8267,-122.4233' \
              -H 'cache-control: no-cache'
        */

        $curl_opt_array = array(
            CURLOPT_URL => $curl_request_endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            )
        );

        curl_setopt_array($curl, $curl_opt_array);

        $curl_response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($err) {
            $response['status'] = "error";
            $response['message'] = "A cURL error occurred while retrieving the results.\n" . $err;
            $response['message'] .= "Took ". $info['total_time'] ." seconds to send a request to ". $info['url'];
            $response['message'] .= print_r($info, true) . PHP_EOL;
            if($debug) {
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }
            return $response;
        } else {
            if($debug) {
                $response['status'] = "info";
                $response['message'] =  "Successful response:\n". $curl_response;
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
            }

            // Parse JSON response
            $curl_response_array = json_decode($curl_response);
            if (json_last_error() == JSON_ERROR_NONE) {
                if(isset($curl_response_array->currently)) {
                    $response = insert_result($curl_response_array->latitude, $curl_response_array->longitude, $curl_response_array->currently->summary, $curl_response_array->currently->temperature, $curl_response_array->flags->units, $curl_response_array->currently->icon);

                    return $response;
                } else {
                    $response['status'] = "error";
                    $response['message'] = "The response has the following content: ". $curl_response;
                    if($debug) {
                        file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
                    }
                    return $response;
                }
            } else {
                $response['status'] = "error";
                $response['message'] = "Invalid JSON response: ". json_last_error();
                if($debug) {
                    file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
                }
                return $response;
            }
        }
    } catch (Exception $e) {
        $response['status'] = "error";
        $response['message'] = "Caught exception: ". $e->getMessage();
        if($debug) {
            file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
        }
        return $response;
    }
}

function insert_result($latitude, $longitude, $summary, $temperature, $units, $icon)
{
    global $db_host, $db_user, $db_pass, $db_name, $charset, $debug;

    try {
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
        $db = new PDO($dsn, $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $query = <<<EOF
INSERT INTO results
(latitude, longitude, summary, temperature, units, icon, created_on) 
VALUES (:latitude, :longitude, :summary, :temperature, :units, :icon, :created_on);
EOF;
        $stmt = $db->prepare($query);
        $created_on = time();
        $stmt->bindValue(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindValue(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->bindValue(':summary', $summary, PDO::PARAM_STR);
        $stmt->bindValue(':temperature', $temperature, PDO::PARAM_STR);
        $stmt->bindValue(':units', $units, PDO::PARAM_STR);
        $stmt->bindValue(':icon', $icon, PDO::PARAM_STR);
        $stmt->bindValue(':created_on', $created_on, PDO::PARAM_INT);
        $stmt->execute();

        $response['status'] = "success";
        $result = array(
            'latitude' => $latitude,
            'longitude' => $longitude,
            'summary' => $summary,
            'temperature' => $temperature,
            'units' => $units,
            'icon' => $icon,
            'created_on' => $created_on
        ); 
        $response['message'] = $result;
        if($debug) {
            file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
        }
        return $response;

        $stmt = null;
        $db = null;
    } catch (PDOException $e) {
        $response['status'] = "error";
        $response['message'] = "Caught PDO exception: ". $e->getCode() ."]: ". $e->getMessage();
        if($debug) {
            file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
        }
        return $response;
    }
}

function validateGeo($req_latitude, $req_longitude)
{
  return preg_match('/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/', $req_latitude.",".$req_longitude);
}

// For 4.3.0 <= PHP <= 5.4.0
if (!function_exists('http_response_code'))
{
    function http_response_code($newcode = NULL)
    {
        static $code = 200;
        if($newcode !== NULL)
        {
            header('X-PHP-Response-Code: '.$newcode, true, $newcode);
            if(!headers_sent())
                $code = $newcode;
        }       
        return $code;
    }
}

header('Content-Type: application/json');

if (isset($_GET['latitude']) && isset($_GET['longitude'])) {
    $req_latitude = $_GET['latitude'];
    $req_longitude = $_GET['longitude'];
    if (validateGeo($req_latitude, $req_longitude)) {
        $curl_request_endpoint = "https://api.darksky.net/forecast/$private_key/$req_latitude,$req_longitude";
        $response = send_request($curl_request_endpoint);
        if ($response['status'] == "error"){
            http_response_code(400);
        } else {
            http_response_code(200);
        }

        if($debug) {
            file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
        }
        echo json_encode($response);
    } else {
        $response['status'] = "error";
        http_response_code(400);
        $response['message'] =  "You should specify a valid latitude and longitude";
        if($debug) {
            file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
        }
        echo json_encode($response);
    }
} else {
    $response['status'] = "error";
    http_response_code(400);
    $response['message'] =  "You should specify a latitude and longitude";
    file_put_contents($log_file, "[". date(DATE_ATOM) ."]". print_r($response, true), FILE_APPEND);
    echo json_encode($response);
}