<?php
// I know a monolithic file is wrong but I would like to satisfy the specs and go to bed

require_once(__DIR__ .'/config.php');

function getResult($latitude, $longitude)
{
    global $db_host, $db_user, $db_pass, $db_name, $charset;
    global $valid_result_ttl, $internal_proxy_endpoint;

    try {
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
        $db = new PDO($dsn, $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $query = <<<EOF
SELECT summary, temperature, units, icon
FROM results
WHERE latitude = :latitude
AND longitude = :longitude
AND created_on >= :created_on_min
ORDER BY created_on DESC
LIMIT 1;
EOF;
        $stmt = $db->prepare($query);
        $created_on_min = time() - $valid_result_ttl;
        $stmt->bindValue(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindValue(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->bindValue(':created_on_min', $created_on_min, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $stmt = null;
            $db = null;

            $endpoint = $internal_proxy_endpoint ."?latitude=$latitude&longitude=$longitude";
            $api_call_result = file_get_contents($endpoint);
            $api_call_result_json = json_decode($api_call_result);
   
            $result['summary'] = $api_call_result_json->message->summary;
            $result['temperature'] = $api_call_result_json->message->temperature;
            $result['units'] = $api_call_result_json->message->units;
            $result['icon'] = $api_call_result_json->message->icon;
        } else {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result['summary'] = $row['summary'];
                $result['temperature'] = $row['temperature'];
                $result['units'] = $row['units'];
                $result['icon'] = $row['icon'];
            }
        }

        return $result;

        $stmt = null;
        $db = null;
    } catch (PDOException $e) {
        echo "ERROR [". $e->getCode() ."]: ". $e->getMessage();
        die();
    }
}

$errors = array();

$city_mapping = array(
    'London' => '51.528308,-0.3817812',
    'Paris' => '48.8588377,2.2770199',
    'New-York' => '40.6971494,-74.2598712',
    'Singapore' => '1.3139961,103.7041613',
    'Sydney' => '-33.847927,150.6517805'
);

if (isset($_GET['location'])) {
    $location = $_GET['location'];
} else {
    $location = 'London';
}

if (isset($city_mapping[$location])) {
    list($latitude, $longitude) = explode(",", $city_mapping[$location]);
    // Format the number with only 4 decimal digits as accepted by Dark Sky API and DB fields
    $latitude = number_format($latitude, 4, '.', '');
    $longitude = number_format($longitude, 4, '.', '');
    $result = getResult($latitude, $longitude);

    if(isset($result['units'])) {
        if ($result['units'] == 'SI') {
            $temperature_unit = '&deg;C';
        } else {
            $temperature_unit = '&deg;F';
        }
    }
} else {
    $errors[] = 'location NOT found.';
}

$smarty->assign('location', $location);
$smarty->assign('temperature_unit', $temperature_unit);
$smarty->assign('errors', $errors);
$smarty->assign('result', $result);

$smarty->assign('page', 'current');
$smarty->display('main'.TEMPLATE_EXT);