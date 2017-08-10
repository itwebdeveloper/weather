<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Weather App - Locations</title>

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
                        <h2>Locations</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-0 col-md-5"></div>
                    <div class="col-sm-12 col-md-2" id="locations">
                        <ul>
                            <li><a href="current.php?location=London">London</a></li>
                            <li><a href="current.php?location=Paris">Paris</a></li>
                            <li><a href="current.php?location=New-York">New-York</a></li>
                            <li><a href="current.php?location=Singapore">Singapore</a></li>
                            <li><a href="current.php?location=Sydney">Sydney</a></li>
                        </ul>
                    </div>
                    <div class="col-sm-0 col-md-5"></div>
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

        <!-- Main JavaScript -->
        <script src="js/scripts.js"></script>
    </body>
</html>