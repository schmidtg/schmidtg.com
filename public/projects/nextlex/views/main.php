<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:700,300' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="<?= $base_url ?>css/theme/lexpress.min.css" />
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0/jquery.mobile.structure-1.2.0.min.css" />
    <link rel="stylesheet" href="<?= $base_url ?>css/theme/custom.css" />

    <!-- http://jquerymobile.com/themeroller/?ver=1.2.0&style_id=20121207-128 -->
    <script id="nearest_stops_template" type="text/x-handlebars-template">
        {{#each this}}
        <li>
            <h3>Route {{route_num}}</h3>
        </li>
        {{#each this.stops}}
        <li>
            <a href="#stop" class="details" data-transition="slide" data-shortcode="{{shortcode}}" data-route_num="{{route_num}}" data-direction="{{direction}}">
                <p><strong>To:</strong> {{way}}</p>
                <p><strong>Stop:</strong> {{fullname}}</p>
                <p><strong>Arrives:</strong> {{#list arrives}}{{/list}} mins</p>
                <p><strong>Distance:</strong> {{distance_formatted}} miles</p>
            </a>
        </li>
        {{/each}}
        {{/each}}
    </script>

    <script id="stop_detail_template" type="text/x-handlebars-template">
        <li>
            <h3>Route {{route_num}}</h3>
        </li>
        <li>
            <p><strong>To:</strong> {{way}}</p>
            <p><strong>Stop:</strong> {{fullname}}</p>
            <p><strong>Arrives:</strong> {{#list arrival_times}}{{/list}}</p>
            <p><strong>Walking:</strong> <span id="approx_dist"></span> - <span id="approx_time"></span></p>
        </li>
        <li>
            <div id="map_canvas" class="google_map"></div>
        </li>
        <li>
            <p><strong>Walking Directions</strong></p>
            <div id="walking_directions" class="panel"></div>
        </li>
    </script>

    <script id="route_template" type="text/x-handlebars-template">
        {{#each this}}
        <li><a href="#directions" data-route_num="{{route_num}}" data-transition="slide">{{route_num}}</a></li>
        {{/each}}
    </script>

    <script id="directions_template" type="text/x-handlebars-template">
        {{#each this}}
        <li><a href="#stops_list" data-route_num="{{route_num}}" data-direction="0" data-transition="slide">To {{start}}</a></li>
        <li><a href="#stops_list" data-route_num="{{route_num}}" data-direction="1" data-transition="slide">To {{returns}}</a></li>
        {{/each}}
    </script>

    <script id="stops_list_template" type="text/x-handlebars-template">
        {{#each this}}
        <li><a href="#stop" data-route_num="{{route_num}}" data-direction="{{direction}}" data-shortcode="{{shortcode}}" data-transition="slide">{{fullname}}</a></li>
        {{/each}}
    </script>
</head>
<body>

    <div data-role="content">

<!-- Start of first page -->
<div data-role="page" id="home" data-theme="a">
	<div data-role="header">
		<h1>Next Lexpress</h1>
	</div><!-- /header -->

	<div data-role="content">	
        <a href="#menu" data-role="button" data-icon="arrow-r" data-iconpos="right" data-transition="slide">Menu</a>	

        <div data-role="collapsible-set">

            <div id="nearest_stops_set" data-role="collapsible" data-icon="arrow-r" data-collapsed-icon="arrow-r" data-expanded-icon="arrow-d" data-iconpos="left">
                <h3 id="nearest_stops_header">Nearest Stops</h3>
                <ul data-role="listview" id="nearest_stops">
                </ul>
                <ul data-role="listview" id="list_more_routes">
                    <li>
                        <a data-role="button" data-mini="true" href="#">
                            <h3>List more routes...</h3>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div data-role="collapsible" data-icon="arrow-r" data-collapsed-icon="arrow-r" data-expanded-icon="arrow-d" data-iconpos="left" id="location">
                <h3 id="my_location">My Location</h3>
                <div id="my_map_canvas" class="google_map"></div>
            </div>
            
        </div>	

        <a id="refresh" data-role="button" data-icon="refresh" data-mini="true" data-iconpos="right" href="#refresh">Refresh</a>
	</div><!-- /content -->
</div><!-- /page -->


<!-- start of menu page -->
<div data-role="page" id="menu" data-theme="a">

	<div data-role="header">
        <a href="#home" data-role="button" data-transition="slide" data-direction="reverse">Back</a>
        <h1>Menu</h1>
	</div><!-- /header -->

	<div data-role="content">	
        <ul data-role="listview">
            <li><a href="#routes" data-transition="slide" id="find_route">Find a Specific Stop</a></li>
            <li><a href="mailto:lexpress@lexingtonma.gov?subject=NextLexpress%20Feedback" class="ui-link-inherit">Feedback</a></li>
            <li><a href="#contact" data-transition="slide">Contact Us</a></li>
        </ul>
	</div><!-- /content -->
</div><!-- /page -->


<!-- start of stop page -->
<div data-role="page" id="stop" data-theme="a">

	<div data-role="header">
        <a href="#home" data-role="button" data-rel="back" data-transition="slide" data-direction="reverse">Back</a>
        <h1>Selected Stop</h1>
	</div><!-- /header -->

	<div data-role="content">	
        <ul data-role="listview" id="stop_detail">
        </ul>	
	</div><!-- /content -->
</div><!-- /page -->


<!-- start of routes page -->
<div data-role="page" id="routes" data-theme="a">

	<div data-role="header">
        <a href="#home" data-role="button" data-rel="back" data-transition="slide" data-direction="reverse">Back</a>
        <h1>Routes</h1>
	</div><!-- /header -->

	<div data-role="content">	
        <ul data-role="listview" id="routes_list">
        </ul>
	</div><!-- /content -->
</div><!-- /page -->


<!-- start of directions page -->
<div data-role="page" id="directions" data-theme="a">

	<div data-role="header">
        <a href="#home" data-role="button" data-rel="back" data-transition="slide" data-direction="reverse">Back</a>
        <h1>Direction</h1>
	</div><!-- /header -->

	<div data-role="content">	
        <ul data-role="listview" id="directions_list">
        </ul>
	</div><!-- /content -->
</div><!-- /page -->


<!-- start of stops_list page -->
<div data-role="page" id="stops_list" data-theme="a">

	<div data-role="header">
        <a href="#home" data-role="button" data-rel="back" data-transition="slide" data-direction="reverse">Back</a>
        <h1>Stops</h1>
	</div><!-- /header -->

	<div data-role="content">	
        <ul data-role="listview" id="stops_listview">
        </ul>
	</div><!-- /content -->
</div><!-- /page -->


<!-- start of contact-us page -->
<div data-role="page" id="contact" data-theme="a">

	<div data-role="header">
        <a href="#home" data-role="button" data-rel="back" data-transition="slide" data-direction="reverse">Back</a>
        <h1>Contact Us</h1>
	</div><!-- /header -->

	<div data-role="content">	
	<p>Name: Lexpress</p>
	<p>Phone: 781-861-1210</p>
	<p>E-mail: lexpress@lexingtonma.gov</p>
	</div><!-- /content -->
</div><!-- /page -->


    </div> <!-- end content-->

    <script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
    <script src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.2/underscore-min.js"></script>
    <script src="<?= $base_url ?>js/main.js"></script>
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyA5JEWthzvsKoNx3JDKA88i4TuHMAZnvOA&sensor=false"></script>
    <script src="<?= $base_url ?>js/lib/handlebars-1.0.rc.1.js"></script>
    <script src="<?= $base_url ?>js/lib/infobox_packed.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            // provide list of containers and default data
            NextLexpress.init({
                bodyContainer: $('body'),
                locationContainer: $('#my_location'),

                nearestStopsContainer: $('#nearest_stops'),
                nearestStopsTemplate: $('#nearest_stops_template').html(),
                nearestStopsSet: $('#nearest_stops_set'),
                nearestStopsHeader: $('#nearest_stops_header'),

                stopContainer: $('#stop'),
                stopDetailContainer: $('#stop_detail'),
                stopDetailTemplate: $('#stop_detail_template').html(),

                findRouteEl: $('#find_route'),
                routesListContainer: $('#routes_list'),
                routeTemplate: $('#route_template').html(),

                directionsContainer: $('#directions'),
                directionsListContainer: $('#directions_list'),
                directionsTemplate: $('#directions_template').html(),

                stopsListContainer: $('#stops_list'),
                stopsListViewContainer: $('#stops_listview'),
                stopsListTemplate: $('#stops_list_template').html(),

                listMoreRoutesContainer: $('#list_more_routes'),
                refreshButton: $('#refresh'),

                route_num: [1],
                coord: {lat: '42.447211427441275', lng: '-71.23252344157663'}, // depot
                paging: {inc: 2, curr: 0},
                base_url: '<?= $base_url ?>',
                random: 1,
                use_midday: 1
            });
        });    
    </script>
</body>
</html>
