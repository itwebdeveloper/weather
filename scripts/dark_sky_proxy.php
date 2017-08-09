<?php
ini_set('display_startup_errors', 1);
ini_set('display_error', 1);
error_reporting(E_ALL);

$debug = false; // If true the script prints additional debug lines to video
// $curl_request_endpoint = "https://api.darksky.net/forecast/a258a26fcc8493fc074ecd506b71e636/37.8267,-122.4233";
$curl_request_endpoint = "http://10.0.0.10/darksky_fake/response.php";
$log_file = "/var/log/dark_sky_proxy/debug.log";
$email_addresses = "Andrea Cannuni <andreacannuni@gmail.com>";

if (!is_writable($log_file)) {
    $debug_message = "[ERROR] The file '". $log_file ."' is not writable.\n";
    if($debug) {
        echo $debug_message;
    } else {
        // mail($email_addresses, 'Dark Sky Alert - Error alert', "[". date(DATE_ATOM) ."]". $debug_message);
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

    if(!$debug) {
        $curl_response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
    } else {
        $curl_response = file_get_contents($example_response_file_path);
        $err = false;
        $info = "";
    }
    curl_close($curl);

    if ($err) {
        $debug_message = "[ERROR] A cURL error occurred while retrieving the results.\n" . $err . "\n";
        $debug_message .= "Took ". $info['total_time'] ." seconds to send a request to ". $info['url'] ."\n";
        $debug_message .= print_r($info, true). "\n";

        if($debug) {
            echo $debug_message;
        } else {
            // mail($email_addresses, 'Dark Sky Alert - Error alert', "[". date(DATE_ATOM) ."]". $debug_message);
        }
        file_put_contents($log_file, "[". date(DATE_ATOM) ."]". $debug_message, FILE_APPEND);
    } else {
        $debug_message = "[INFO] Successful response:\n". $curl_response. "\n";
        file_put_contents($log_file, "[". date(DATE_ATOM) ."]". $debug_message, FILE_APPEND);

        // Parse JSON response
        $curl_response_array = json_decode($curl_response);
        if (json_last_error() == JSON_ERROR_NONE) {
            // Check for available spaces
            // The format you want to return is: $spots['available']['2017-04-27T15:00:00+01:00'] = 2;
            if(isset($curl_response_array->currently)) {
                echo $curl_response_array->currently->summary.PHP_EOL;
                echo $curl_response_array->currently->temperature.PHP_EOL;
                echo $curl_response_array->currently->icon.PHP_EOL;
            } else {
                $debug_message = "[ERROR] The response has the following content: ". $curl_response ."\n";
                if($debug) {
                    echo $debug_message;
                } else {
                    // mail($email_addresses, 'Dark Sky Alert - Error alert', "[". date(DATE_ATOM) ."]". $debug_message);
                }
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". $debug_message, FILE_APPEND);
                exit;
            }
        } else {
            $debug_message = "[ERROR] Invalid JSON response: ". json_last_error() ."\n";
            if($debug) {
                echo $debug_message;
            } else {
                // mail($email_addresses, 'Dark Sky Alert - Error alert', "[". date(DATE_ATOM) ."]". $debug_message);
            }
            file_put_contents($log_file, "[". date(DATE_ATOM) ."]". $debug_message, FILE_APPEND);
            exit;
        }
    }
} catch (Exception $e) {
    $debug_message = "[ERROR] Caught exception: ". $e->getMessage(). "\n";
    if($debug) {
        echo $debug_message;
    } else {
        // mail($email_addresses, 'Dark Sky Alert - Error alert', "[". date(DATE_ATOM) ."]". $debug_message);
    }
    file_put_contents($log_file, "[". date(DATE_ATOM) ."]". $debug_message, FILE_APPEND);
    exit;
}