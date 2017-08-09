<?php
require_once(__DIR__ .'/../config.php');

function send_request($curl_request_endpoint) {
    global $debug, $log_file, $email_addresses;

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
            if($debug) {
                $debug_message = "[INFO] Successful response:\n". $curl_response. "\n";
                file_put_contents($log_file, "[". date(DATE_ATOM) ."]". $debug_message, FILE_APPEND);
            }

            // Parse JSON response
            $curl_response_array = json_decode($curl_response);
            if (json_last_error() == JSON_ERROR_NONE) {
                if(isset($curl_response_array->currently)) {
                    insert_result($curl_response_array->latitude, $curl_response_array->longitude, $curl_response_array->currently->summary, $curl_response_array->currently->temperature, $curl_response_array->flags->units, $curl_response_array->currently->icon);
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
}

function insert_result($latitude, $longitude, $summary, $temperature, $units, $icon) {
    global $db_host, $db_user, $db_pass, $db_name, $charset;

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

        $stmt = null;
        $db = null;
    } catch (PDOException $e) {
        echo "ERROR [". $e->getCode() ."]: ". $e->getMessage();
        die();
    }
}

send_request($curl_request_endpoint);