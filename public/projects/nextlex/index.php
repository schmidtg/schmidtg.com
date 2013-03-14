<?php
    include_once("inc/config.php");

    // load models
    include_once("models/nextlex.php");

    // load bus stop data
    try {
        // load route num data
        if ( isXHR() 
        	&& isset($_POST['get'])
        	&& $_POST['get'] == 'routes' ) 
        {
            echo getRoutes();
            return;
        }

        // load directions
        if ( isXHR() 
        	&& isset($_POST['get'])
        	&& $_POST['get'] == 'directions' ) 
        {
            echo getDirections($_POST['route_num']);
            return;
        }

        // load stops
        if ( isXHR() 
        	&& isset($_POST['get'])
        	&& $_POST['get'] == 'stops_list' ) 
        {
            echo getStops($_POST['route_num'], $_POST['direction']);
            return;
        }

        // load route num markers
        if ( isXHR() 
        	&& isset($_POST['get'])
        	&& $_POST['get'] == 'markers' ) 
        {
            echo getMarkers($_POST['route_num']);
            return;
        }

        // get closest stops 
        if ( isXHR() 
        	&& isset($_POST['get'])
        	&& $_POST['get'] == 'closest' ) 
        {
            echo getClosestStops($_POST['params']);
            return;
        }

        // get ordering of stops
        if ( isXHR()
        	&& isset($_POST['get'])
        	&& $_POST['get'] == 'closest_order' ) 
        {
            echo getClosestStopsOrder($_POST['params']);
            return;
        }

        // load stop detail on a route
        if ( isXHR() 
        	&& isset($_POST['get'])
        	&& $_POST['get'] == 'route_stop' ) 
        {
            echo getRouteStop($_POST['params']);
            return;
        }

        // get random location
        if ( isXHR()
        	&& isset($_POST['get'])
        	&& $_POST['get'] == 'rand_location' ) 
        {
            echo getRandomLocation($_POST['lat'], $_POST['lng']);
            return;
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
        
    // only display title
    $parameters['title'] = "Next Lexpress";
    $parameters['base_url'] = BASE_URL;

    // render main home page
    render("main.php", $parameters);
