<?php
// I know a monolithic file is wrong but I would like to satisfy the specs and go to bed

require_once(__DIR__ .'/config.php');

function getResult($latitude, $longitude)
{
    global $db_host, $db_user, $db_pass, $db_name, $charset;
    global $valid_result_ttl;

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

            $endpoint = "http://10.0.0.10/weather/scripts/dark_sky_proxy.php?latitude=$latitude&longitude=$longitude";
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
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Weather App - Current weather conditions</title>

        <!-- Bootstrap compiled and minified CSS -->
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/main.css">
        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.php">Weather App</a>
                </div>
                <div id="navbar" class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="index.php">Home page</a></li>
                        <li class="active"><a href="index.php">Locations</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>
        <div class="container">
            <div class="starter-template">
                <div class="row">
                    <div class="col-md-12">
                        <h1>Weather App</h1>
                        <h2>Current weather conditions</h2>
                        <h3><?php echo $location ?></h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <h3>Current weather summary</h3>
                        <div><?php echo $result['summary'] ?></div>
                    </div>
                    <div class="col-md-4">
                        <h3>Current weather temperature</h3>
                        <div><?php echo $result['temperature'] ." ". $temperature_unit ?></div>
                    </div>
                    <div class="col-md-4">
                        <h3>Current weather icon</h3>
                        <div><?php echo $result['icon'] ?></div>
                    </div>
                </div>
            </div>
        </div><!-- /.container -->

        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->

        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="js/ie10-viewport-bug-workaround.js"></script>
        <script src="js/jquery.min.js"></script>
        <!-- Bootstrap compiled and minified JavaScript -->
        <script src="js/bootstrap.min.js"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>