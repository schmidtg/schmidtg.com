/*global Handlebars:true, google:true, _:true, InfoBox:true */

/**
 * Next Lexpress
 *
 * @author      Graham Schmidt
 * @released    2012-12-09
 *
 * Load a list of the nearest Lexpress stops based on your current location.
 *
 * API Reference
 * https://developers.google.com/maps/documentation/javascript/
 *
 * InfoBox (styling Google Maps info window)
 * http://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobox/docs/examples.html
 */
var NextLexpress = {
    init: function(config) {
        this.config = config;
        this.bindEvents();
        this.setupTemplates();

        // set jQuery globals
        $.ajaxSetup({
            url: config.base_url + '/index.php',
            type: 'POST'
        });
                
        // setup initial datastores
        this.data = {};
        this.routelines = [];
        this.routes = [];
        this.route_num = this.config.route_num;
        this.data.paging = this.config.paging;
        this.data.nearest_route_nums = [];

        // only load if route is not at index '/'
        if (this.checkURI() === 'home') {
            this.getCurrentLocation();
        }
    },
    
    /**
     * Handlebar templates and helpers
     */
    setupTemplates: function() {
        this.config.nearestStopsTemplate = Handlebars.compile( this.config.nearestStopsTemplate );
        this.config.stopDetailTemplate = Handlebars.compile( this.config.stopDetailTemplate );
        this.config.routeTemplate = Handlebars.compile( this.config.routeTemplate );
        this.config.directionsTemplate = Handlebars.compile( this.config.directionsTemplate );
        this.config.stopsListTemplate = Handlebars.compile( this.config.stopsListTemplate );

        Handlebars.registerHelper('distance_formatted', function() {
            return this.distance.toFixed(3);
        });
        Handlebars.registerHelper('list', function(items) {
            return items.join(", ");
        });
    },

    /**
     * Pub/sub model
     */
    bindEvents: function() {
        this.config.bodyContainer.on( 'map_loaded', this.loadMarkers );
        this.config.nearestStopsContainer.on( 'click', 'a', this.loadStopDetail );
        this.config.nearestStopsHeader.on( 'click', this.refresh );
        this.config.listMoreRoutesContainer.on( 'click', 'a', this.listMoreRoutes );
        this.config.locationContainer.on( 'click', this.setMyLocation );
        this.config.findRouteEl.on( 'click', this.loadRoutes );
        this.config.routesListContainer.on( 'click', 'a', this.loadDirections );
        this.config.directionsListContainer.on( 'click', 'a', this.loadStopsList );
        this.config.stopsListViewContainer.on( 'click', 'a', this.loadStopDetail );
        this.config.refreshButton.on( 'click', this.refresh );
    },

    /**
     * Refresh the nearest location data
     */
    refresh: function() {
        var self = NextLexpress;

        // reset data to home settings
        self.data.nearest_routes = {};
        self.data.nearest_route_nums = [];
        self.data.paging = {inc: 2, curr: 0};
        self.config.listMoreRoutesContainer.show();

        // get current location
        self.getCurrentLocation(1);
    },

    /**
     * Route checking
     */
    checkURI: function() {
        return document.location.hash.split('#')[1] || 'home';
    },

    /**
     * Setup stop detail page
     */
    loadStopDetail: function() {
        var self = NextLexpress,
            $this = $(this),
            curr_time = Helpers.getCurrTime(self.config.use_midday),
            container = self.config.stopDetailContainer;

        var params = {
            shortcode: $this.data('shortcode'),
            route_num: $this.data('route_num'),
            direction: $this.data('direction'),
            time: curr_time
        };

        var response = self.getDataRouteStop(params);
        response.then( function( res ) {

            // process results
            if (!$.isEmptyObject(res)) {

                // clear stop detail, append data via JS template
                container.empty();
                container.append( self.config.stopDetailTemplate( res ) );
                self.route_num = [params.route_num];
                self.setLocation({
                    lat: res.stop_lat,
                    lng: res.stop_long,
                    el: 'map_canvas',
                    draw: 1,
                    fullname: res.fullname,
                    icon: gMaps.MarkerIcons.LARGE_BUS
                });

                // set stop info
                self.data.stop_pos = {
                    lat: res.stop_lat,
                    lng: res.stop_long
                };

                // add marker for our current position
                var pos = gMaps.createLatLng(self.config.coord.lat, self.config.coord.lng);
                res.icon = gMaps.MarkerIcons.LARGE_BLUE;
                res.fullname = "My location";
                self.addMarker(pos, res);

                // provide walking directions based on location
                self.showWalkDirections();
                container.listview( "refresh" );

            } else {
                self.printMessage("Sorry, there is no information available for this stop.", container);
            }

        }, function() {
            self.printMessage("Sorry, there was a problem loading the stop information.", container);
        });

    },

    /**
     * Load list of routes
     */
    loadRoutes: function() {
        var self = NextLexpress,
            response = '',
            container = self.config.routesListContainer;

        response = self.getDataRoutes();
        response.then( function( res ) {

            if (!$.isEmptyObject(res)) {
                
                container.empty();
                container.append( self.config.routeTemplate( res ) );
                container.listview( "refresh" );

            // Clear inputs and display an error message
            } else {
                self.printMessage("Sorry, there are no Routes available.", container);
            }
        }, function() {
            self.printMessage("Sorry, there was a problem retrieving the route list.", container);
        });
    },

    /**
     * Load available directions for a route
     */
    loadDirections: function() {
        var self = NextLexpress,
            route_num = $(this).data('route_num'),
            response = '',
            container = self.config.directionsListContainer;

        response = self.getDataDirections(route_num);
        response.then( function( res ) {

            if (!$.isEmptyObject(res)) {
                
                container.empty();
                container.append( self.config.directionsTemplate( res ) );
                container.listview( "refresh" );

            // Clear inputs and display an error message
            } else {
                self.printMessage("Sorry, there are no directions available.", container);
            }
        }, function() {
            self.printMessage("Sorry, there was a problem retrieving directions.", container);
        });
    },

    /**
     * Load the list of stops available for a route direction
     */
    loadStopsList: function() {
        var self = NextLexpress,
            $this = $(this),
            route_num = $this.data('route_num'),
            direction = $this.data('direction'),
            response = '',
            container = self.config.stopsListViewContainer;

        response = self.getDataStopsList(route_num, direction);
        response.then( function( res ) {

            if (!$.isEmptyObject(res)) {
                container.empty();
                container.append( self.config.stopsListTemplate( res ) );
                container.listview( "refresh" );

            // Clear inputs and display an error message
            } else {
                self.printMessage("Sorry, there are no stop lists available.", container);
            }
        }, function() {
            self.printMessage("Sorry, there was a problem retrieving the stop lists.", container);
        });
    },

    /**
     * Load the route marker data for bus routes
     */
    loadMarkers: function() {
        var self = NextLexpress,
            response = '',
            container = self.config.stopDetailContainer;            

        response = self.getDataMarker();
        response.then( function( res ) {

            if (!$.isEmptyObject(res)) {
                self.route_data = res;
                self.placeRouteMarkers();

            // Clear inputs and display an error message
            } else {
                self.printMessage("Sorry, there are no route markers available.", container);
            }
        }, function() {
            self.printMessage("Sorry, there was a problem retrieving the route markers.", container);
        });

    },

    /**
     * Calculate the nearest stop to a location
     */
    calcNearestStop: function(lat, lng) {
        var self = NextLexpress,
            time = Helpers.getCurrTime(self.config.use_midday);

        var nearest = self.getDataNearestStops(lat, lng, time);
        var nearest_order = self.getDataNearestStopsOrder(lat, lng, time);

        nearest_order.then( function( res ) {

            // display results
            var nearest_route_nums = _.pluck(res, 'route_num');

            // store the route_nums, and page on those
            self.data.nearest_route_nums = nearest_route_nums;
            var display_routes = self.getPage(self.data.paging);

            nearest.then( function( res ) {
                var data = self.buildListOfStops(display_routes, res, time);

                // clear listview
                self.config.nearestStopsContainer.empty();
                self.displayNearestStops(data);

                // cache data to 'list more stops'
                self.data.nearest_routes = res;

            }, function() {
                self.printMessage("Sorry, there was a problem fetching the nearest stops.", self.config.nearestStopsContainer);
            });

        // no locations nearby
        }, function() {
            self.printMessage("Sorry, there are no stops within 1 mile of your current location and time.", self.config.nearestStopsContainer);
        });            
    },

    /**
     * Display the nearest list of stops (paged)
     */
    displayNearestStops: function(data) {
        var self = NextLexpress;

        self.config.nearestStopsContainer.append( self.config.nearestStopsTemplate(data) );
        self.config.nearestStopsContainer.listview( "refresh" );

        // remove "list more routes" button when no more routes hiding
        if (!self.incCurrPage()) {
            self.config.listMoreRoutesContainer.hide();
        }
    },

    /**
     * Page through list of nearest stops
     */
    listMoreRoutes: function(e) {
        var self = NextLexpress,
            time = Helpers.getCurrTime(self.config.use_midday);

        e.preventDefault();
        var display_routes = self.getPage();
        var data = self.buildListOfStops(display_routes, self.data.nearest_routes, time);

        // load more stops
        self.displayNearestStops(data);
    },

    /**
     * Increment the current page counter
     */
    incCurrPage: function() {
        var self = NextLexpress;
        if (self.data.paging.curr < (self.data.nearest_route_nums.length - self.data.paging.inc)) {
            self.data.paging.curr += 2; 
            return true;
        }

        return false;
    },

    /**
     * Take page, return which route nums to display based on page
     */
    getPage: function(paging) {
        var self = NextLexpress,
            routes = self.data.nearest_route_nums;

        var inc = (paging === undefined ? self.data.paging.inc : paging.inc);
        var curr = (paging === undefined ? self.data.paging.curr : paging.curr);

        // pass page num
        // create array up to the increment
        var end = inc + curr;
        var routes_to_display = [];
        for (var i = curr; i < end; i++) {
            routes_to_display.push(routes[i]);
        }

        return routes_to_display;
    },

    /**
     * Place the loaded route markers onto the map
     */
    placeRouteMarkers: function() {
        var self = NextLexpress;

        // retrive markers in case they're not cached
        if (!self.route_data) 
        {
            self.loadMarkers(); 
        } 

        // initialize object of RouteLine()
        var stops = {};
        for (var i = 0, n = self.route_num.length; i < n; i++) {
            stops[self.route_num[i]] = new RouteLine();
        }
        
        // add each marker to map
        $.each(self.route_data, function(stop, marker) {

            // shift marker locations so they do not overlap
            if (marker.direction === 1) {
                marker.stop_lat = marker.stop_lat + 0.00003;
                marker.stop_long = marker.stop_long - 0.00003;
            }

            // set location for marker and provide icon
            var pos = gMaps.createLatLng(marker.stop_lat, marker.stop_long);
            marker.icon = gMaps.MarkerIcons.SMALL;
            self.addMarker(pos, marker);

            stops[marker.route_num].paths.push(pos);
            stops[marker.route_num].strokeColor = marker.route_color;
        });

        // draw each route line on map
        for (i = 0, n = self.route_num.length; i < n; i++) {
            self.drawRouteLine(stops[self.route_num[i]]);
        }

        // need to resize the map to ensure no 'grey' areas
        google.maps.event.trigger(self.map, "resize");
    },

    /**
     * Data: Routes
     *
     * @return  JSON
     */
    getDataRoutes: function() {
        return $.ajax({
            data: {
                get: 'routes'
            },
            dataType: 'json'
        }).promise();
    },

    /**
     * Data: Directions
     *
     * @param   int - route number
     * @return  JSON
     */
    getDataDirections: function(route_num) {
        return $.ajax({
            data: {
                get: 'directions',
                route_num: route_num
            },
            dataType: 'json'
        }).promise();
    },

    /**
     * Data: List of Stops
     *
     * @param   int - route number
     * @param   int - direction - 0 or 1
     *
     * @return  JSON
     */
    getDataStopsList: function(route_num, direction) {
        return $.ajax({
            data: {
                get: 'stops_list',
                route_num: route_num,
                direction: direction
            },
            dataType: 'json'
        }).promise();
    },

    /**
     * Data: Marker Points
     *
     * @return  JSON
     */
    getDataMarker: function() {
        return $.ajax({
            data: {
                get: 'markers',
                route_num: this.route_num
            },
            dataType: 'json'
        }).promise();
    },

    /**
     * Data: Nearest Stops
     *
     * @param   float - latitude coord
     * @param   float - longitude coord
     * @param   string - time 00:00:00
     *
     * @return  JSON
     */
    getDataNearestStops: function(lat, lng, time) {
        var params = {
            lat: lat,
            lng: lng,
            time: time
        };

        return $.ajax({
            data: {
                get: 'closest',
                params: params
            },
            dataType: 'json',

            // show spinner
            beforeSend: function() { 
                $.mobile.showPageLoadingMsg(); 
            }, 

            // hide spinner
            complete: function() { 
                $.mobile.hidePageLoadingMsg();
            } 
        }).promise();
    },

    /**
     * Data: Nearest Stop order
     *
     * @param   float - latitude coord
     * @param   float - longitude coord
     * @param   string - time 00:00:00
     *
     * @return  JSON
     */
    getDataNearestStopsOrder: function(lat, lng, time) {
        var params = {
            lat: lat,
            lng: lng,
            time: time
        };

        return $.ajax({
            data: {
                get: 'closest_order',
                params: params
            },
            dataType: 'json'
        }).promise();
    },

    /**
     * Data: Route Stop
     *
     * @param   array - shortcode, route_num, direction, time
     *
     * @return  JSON
     */
    getDataRouteStop: function(params) {
        return $.ajax({
            data: {
                get: 'route_stop',
                params: params
            },
            dataType: 'json'
        });
    },

    /**
     * Data: Random Location within 1 mile of Lexington Depot
     *
     * @param   float - latitude coord
     * @param   float - longitude coord
     *
     * @return  JSON
     */
    getDataRandLocation: function(lat, lng) {
        return $.ajax({
            data: {
                get: 'rand_location',
                lat: lat,
                lng: lng
            },
            dataType: 'json'
        }).promise();
    },

    /**
     * Use a random location (for testing)
     */
    useRandLocation: function() {
        var self = NextLexpress,
            rand_pos = self.getDataRandLocation(self.config.coord.lat, self.config.coord.lng);

        rand_pos.then( function (res) {

            // set config coords to new rand_pos
            self.config.coord.lat = res.lat; 
            self.config.coord.lng = res.lng; 

            // find nearest stop
            self.calcNearestStop(res.lat, res.lng);
        });
    },

    /**
     * Get the current location
     */
    getCurrentLocation: function(refresh) {
        var self = NextLexpress;

        // use existing co-ordinate
        if (refresh !== undefined) {
            self.calcNearestStop(self.config.coord.lat, self.config.coord.lng);

        // determine current co-ordinates
        } else {
            // get random location within 1 mile of provided co-ordinates (default is Depot)
            if (self.config.random) {
                self.useRandLocation();

            // use device's position
            } else {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition( function(position) {

                        // set config coords to new rand_pos
                        self.config.coord.lat = position.coords.latitude; 
                        self.config.coord.lng = position.coords.longitude; 

                        // find nearest stop
                        self.calcNearestStop(position.coords.latitude, position.coords.longitude);
                    });
                } else {
                    self.geoLocationFail();
                }
            }
        }
    },

    /**
     * Handle case when no geolocation
     */
    geoLocationFail: function() {
        var self = NextLexpress;
        self.printMessage("Sorry, we cannot determine your location.");
    },

    /**
     * Build list of stops from JSON, route order, and current time
     */
    buildListOfStops: function(route_nums, res, curr_time) {

        // re-order results
        var reorder_res = Helpers.reorder_array('route_num', route_nums, res);

        // initialize route stop object
        var con = {};
        for (var i = 0, n = route_nums.length; i < n; i++) {
            con[route_nums[i]] = new RouteStop();
        }

        // create data structure to hold route stop info indexed by route
        $.each(reorder_res, function(key, stop) {
            // calculate minutes to arrive
            var minutes = Helpers.getArriveMinutes(stop.arrival_time, curr_time);

            // stop info along route
            var s = {
                route_num: stop.route_num,
                way: stop.way,
                fullname: stop.fullname,
                arrives: minutes,
                distance: stop.distance,
                direction: stop.direction,
                shortcode: stop.shortcode
            };
            con[stop.route_num].stops.push(s);
            con[stop.route_num].route_num = stop.route_num;
        });

        // create array with grouped routes
        var conn = [];
        for (i = 0, n = route_nums.length; i < n; i++) {
            conn.push(con[route_nums[i]]);
        }

        return conn;
    },

    /**
     * Set the device's current location for the my location map
     */
    setMyLocation: function() {
        var self = NextLexpress;
        self.setLocation({
            lat: self.config.coord.lat,
            lng: self.config.coord.lng,
            el: 'my_map_canvas',
            draw: 0,
            icon: gMaps.MarkerIcons.LARGE_BLUE
        });
    },

    /**
     * Set a location and create a Google map instance
     */
    setLocation: function(info) {
        var self = NextLexpress;

        // create Google map instance (default center Town of Lexington)
        gMaps.createMap(info);

        // dispatch that map is now available
        if (info.draw) {
            self.config.bodyContainer.trigger('map_loaded');
        }
    },

    /**
     * Add a marker to the Google map
     */
    addMarker: function(myLatlng, info) {
        var self = NextLexpress;

        var marker = new google.maps.Marker({
            position: myLatlng,
            icon: gMaps.getMarkerIcon(info.icon),
            map: self.map,
            animation: google.maps.Animation.DROP
        });

        // create map marker
        /*
        var content = "<span class=\"info_window\">" 
                            + info.fullname
                            + "</span>";

        var infowindow = new google.maps.InfoWindow({
            content: content
        });
        */

        var boxText = document.createElement("div");
        boxText.style.cssText = "background-image: -webkit-linear-gradient(top, #006ECC 0%, #00559E 49%, #004A8A 50%, #00315C 100%); border-radius: 5px; border: 1px solid white; margin-top: 8px; padding: 5px;";
        boxText.innerHTML = "<span class=\"info_window\">" + info.fullname + "</span>";
                
        var myOptions = {
                content: boxText, 
                disableAutoPan: false,
                maxWidth: 0,
                pixelOffset: new google.maps.Size(-50, 0),
                zIndex: null,
                boxStyle: { 
                    opacity: 0.98,
                    width: "210px"
                },
                closeBoxMargin: "10px 2px 2px 2px",
                closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
                infoBoxClearance: new google.maps.Size(1, 1),
                isHidden: false,
                pane: "floatPane",
                enableEventPropagation: false
        };
        var ib = new InfoBox(myOptions);

        // setup event listener upon clicking on map
        google.maps.event.addListener(marker, 'click', function() {
            ib.open(self.map, marker);
            // infowindow.open(self.map, marker);
        });
    },

    // draw polyline of route
    drawRouteLine: function(route) {
        var self = NextLexpress;
        var routeLine = new google.maps.Polyline();  
        var lineSymbol = {
            path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
            scale: 3,
            strokeWeight: 4
        };
                    
        routeLine.setMap(self.map);  
        routeLine.setOptions({
            path: route.paths,
            icons: [{
                    icon: lineSymbol,
                    offset: '50%',
                    repeat: '2%'
                }
            ],
            strokeOpacity: 0.5,
            strokeColor: route.strokeColor
        });  
    },

    // draw multiple polylines of multiple routes
    drawRouteLines: function() {
        var self = NextLexpress;
        for (var i = 0, n = self.routelines.length; i < n; i++) {
            self.drawRouteLine(self.routelines[i]);
        }
    },

    // draw map route using DirectionsRender
    showWalkDirections: function() {
        var self = NextLexpress;
        var directionsRenderer = new google.maps.DirectionsRenderer();  
        directionsRenderer.setMap(self.map);  
        directionsRenderer.setOptions({
            panel: document.getElementById('walking_directions')
        });  
    
        var directionsService = new google.maps.DirectionsService();  

        // manually add way points for Route 1
        var request = {  
            origin: gMaps.createLatLng(self.config.coord.lat, self.config.coord.lng), 
            destination: gMaps.createLatLng(self.data.stop_pos.lat, self.data.stop_pos.lng), 
            travelMode: google.maps.DirectionsTravelMode.WALKING,  
            unitSystem: google.maps.DirectionsUnitSystem.IMPERIAL
        };  

        directionsService.route(request, function(response, status) {  
            if (status === google.maps.DirectionsStatus.OK) {  
                directionsRenderer.setDirections(response);  

                // update time/distance
                var legs = response.routes[0].legs[0];
                var $stop = self.config.stopContainer;
                $stop.find('#approx_dist').html(legs.distance.text);
                $stop.find('#approx_time').html(legs.duration.text);

            } else {  
                self.printMessage("There was an error fetching walking directions. " + status, $('#walking_directions'));
            }  
        });
    },

    printMessage: function(message, container) {
        var self = NextLexpress;

        container.empty();
        container.append("<li>" + message + "</li>");
        container.listview("refresh");
    }
};

function RouteStop() {
    this.stops = [];
    this.route_num = '';
}

function RouteLine() {
    this.paths = [];
    this.strokeColor = '';
}

/**
 * Common Google Maps API related functions
 */
var gMaps = {

    createMap: function(p) {
        var self = NextLexpress,
            pos = this.createLatLng(p.lat, p.lng),
            options = this.createOpts(pos);

        // generate map
        var map = new google.maps.Map( document.getElementById( p.el ), options );

        self.map = map;
        self.addMarker(pos, p);
    },

    createLatLng: function(lat, lng) {
        return new google.maps.LatLng( lat, lng );
    },

    createOpts: function(pos) {
        return {
            zoom: 16,
            center: pos,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
    },

    createCoordsListener: function() {
        var self = NextLexpress;

        google.maps.event.addListener(self.map, 'click', function(event) {
            new google.maps.InfoWindow({
                position: event.latLng,
                content: event.latLng.toString() 
            }).open(self.map);
            self.calcNearestStop(event.latLng.lat(), event.latLng.lng());
        });
    },

    getMarkerIcon: function(p) {
        var self = NextLexpress;
        var image = new google.maps.MarkerImage(
            self.config.base_url + 'images/' + p.img,
            new google.maps.Size(p.w,p.h),
            new google.maps.Point(0,0),
            new google.maps.Point(6,p.h)
        );
        return image;
    },

    MarkerIcons: {
        LARGE_BUS: {
            w: 30,
            h: 40,
            img: 'marker-bus-location.png'
        },
        LARGE_BLUE: {
            w: 30,
            h: 40,
            img: 'marker-curr-location.png'
        },
        SMALL: {
            w: 11,
            h: 31,
            img: 'marker-blue.png'
        }
    }
};

/**
 * Utility Helpers
 */
var Helpers = {

    // Reference: http://stackoverflow.com/questions/1944810/javascript-subtracting-time-and-getting-its-number-of-minutes
    parseTime: function (s) {
        var c = s.split(':');
        return parseInt(c[0], 10) * 60 + parseInt(c[1], 10);
    },

    getCurrTime: function(use_midday) {

        if (use_midday === 1)
            return "10:00:00";

        var now = new Date();

        var h = now.getHours();
        h = this.getTimeLessThanTen(h);

        var m = now.getMinutes();
        m = this.getTimeLessThanTen(m);

        var s = now.getSeconds();
        s = this.getTimeLessThanTen(s);

        var t = h + ":" + m + ":" + s;

        return t;
    },

    getTimeLessThanTen: function(t) {
        return (t < 10 ? "0" + t : t);
    },

    getArriveMinutes: function(arrival_time, curr_time)
    {
        var minutes = [];
        for (var i = 0, n = arrival_time.length; i < n; i++) {
            var m = this.parseTime(arrival_time[i]) - this.parseTime(curr_time);
            minutes.push(m);
        }
        return minutes;
    },

    // sort an array based on a given indexing
    reorder_array: function(index, order, orig)
    {
        var reorder = [];
        for (var i = 0, n = order.length; i < n; i++) {
            $.each(orig, function(key, val) {
                if (val[index] === order[i]) {
                    reorder.push(val);
                }
            });
        }
        return reorder;
    }
};
