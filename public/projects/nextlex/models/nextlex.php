<?php

/**
 * Get a list of active routes
 */
function getRoutes()
{
	$sql = "
        SELECT      id
        FROM        routes
        WHERE       active = 1
        ORDER BY    id
	";
    $results = query($sql);

	if ($results == FALSE)
    {
    	throw new Exception("There was a problem retrieving the routes.");
    }

    foreach ($results as $row) {
    	$data[] = array(
    	    'route_num' => $row['id']
    	);
    }
	return json_encode($data);
}

function getDirections($route_num)
{
	$sql = "
        SELECT      direction_start
                    , direction_return
                    , id
        FROM        routes
        WHERE       active = 1
        AND         id = ?
        ORDER BY    id
	";
    $results = query($sql, $route_num);

	if ($results == FALSE)
    {
    	throw new Exception("There was a problem retrieving the directions.");
    }

    foreach ($results as $row) {
    	$data[] = array(
    	    'start'     => $row['direction_start'],
    	    'returns'   => $row['direction_return'],
    	    'route_num' => $row['id']
    	);
    }
	return json_encode($data);
}

function getStops($route_num, $direction)
{
	$sql = "
        SELECT      fullname 
                    , s.shortcode
                    , stop_sequence
                    , direction
                    , route_num
        FROM        stops s
        LEFT JOIN   stops_meta sm USING (shortcode) 
        WHERE       1
        AND         route_num = ?
        AND         direction = ? 
        GROUP BY    stop_sequence
        ORDER BY    stop_sequence
	";
    $results = query($sql, $route_num, $direction);

	if ($results == FALSE)
    {
    	throw new Exception("There was a problem retrieving the directions.");
    }

    foreach ($results as $row) {
    	$data[] = array(
    	    'fullname'      => $row['fullname'],
    	    'shortcode'     => $row['shortcode'],
    	    'stop_sequence' => $row['stop_sequence'],
    	    'direction'     => $row['direction'],
    	    'route_num'     => $row['route_num']
    	);
    }
	return json_encode($data);
}

function getMarkers($route_num = 1, $json = true)
{
	// multiple routes
	if (is_array($route_num)) {
        $clause_route_num = "AND r.id IN (".implode(",", $route_num).")";
        $route_num = 0;
        
    // individual routes
    } else {
        $clause_route_num = ($route_num > 0
            ? "AND r.id = ?"
            : ""
        );
    }

	$sql = "
        SELECT
                    s.shortcode
                    , sm.fullname
                    , sm.stop_lat
                    , sm.stop_long
                    , s.stop_sequence
                    , s.route_num
                    , r.route_color
                    , s.direction
        FROM        stops s
        LEFT JOIN   routes r ON s.route_num = r.id
        LEFT JOIN   stops_meta sm USING (shortcode)
        WHERE 1
        {$clause_route_num}
        GROUP BY    s.stop_sequence
        ORDER BY    route_num, stop_sequence
	";

    $results = ($route_num > 0
        ? query($sql, $route_num)
        : query($sql)
    );

	if ($results == FALSE)
    {
    	throw new Exception("There was a problem retrieving ehe route.");
    }

    foreach ($results as $row) {
    	$data[] = array(
    	    'shortcode'     => $row['shortcode'],
    	    'fullname'      => $row['fullname'],
    	    'stop_lat'      => (float) $row['stop_lat'],
    	    'stop_long'     => (float) $row['stop_long'],
    	    'stop_sequence' => (int) $row['stop_sequence'],
    	    'route_num'     => (int) $row['route_num'],
    	    'direction'     => (int) $row['direction'],
    	    'route_color'   => $row['route_color'],
    	);
    }

    return ($json
        ? json_encode($data)
        : $data
    );
}

/**
 * Get specific info about stop
 * within 4 hours from current time
 */
function getRouteStop($params = array(), $json = true)
{
	extract($params);

	if (empty($route_num)
	 && empty($shortcode)
	 && empty($direction)
	 && empty($time)
	) {
    	throw new Exception("Provide route_num, time, shortcode and direction.");
    }

	$sql = "
        SELECT
                    sm.stop_lat
                    , sm.stop_long
                    , s.shortcode
                    , GROUP_CONCAT(SUBTIME(st.arrival_time, ?)) as arrival_times
                    , s.stop_sequence
                    , s.route_num
                    , s.direction
                    , sm.fullname
                    , r.route_color
                    , r.direction_start
                    , r.direction_return
                    , CONCAT('To ', IF(s.direction = 0, r.direction_start, r.direction_return)) as way
        FROM        stops s
        LEFT JOIN   routes r ON s.route_num = r.id
        LEFT JOIN   stops_meta sm USING (shortcode)
        LEFT JOIN   stop_times st ON st.stop_id = s.id
        WHERE 1
        AND         r.id = ?
        AND         s.shortcode = ?
        AND         s.direction = ?
        AND			st.arrival_time > ? 
        AND			st.arrival_time < ADDTIME(?, '04:00:00')
        GROUP BY	shortcode
        ORDER BY	st.arrival_time
	";
	// TODO clean up data in 'stops' table (need to GROUP for above)

    $results = query($sql, $time, $route_num, $shortcode, $direction, $time, $time);

	if ($results == FALSE)
    {
    	throw new Exception("There was a problem retrieving the route.");
    }

    foreach ($results as $row) {
    	// convert times to minutes
    	$times = timeStringToArray($row['arrival_times']);

    	$data = array(
    	    'shortcode'     => $row['shortcode'],
    	    'stop_lat'      => (float) $row['stop_lat'],
    	    'stop_long'     => (float) $row['stop_long'],
    	    'arrival_times' => $times,
    	    'stop_sequence' => (int) $row['stop_sequence'],
    	    'route_num'     => (int) $row['route_num'],
    	    'direction'     => (int) $row['direction'],
    	    'fullname'      => $row['fullname'],
    	    'route_color'   => $row['route_color'],
    	    'way'           => $row['way'],
    	    'start'         => $row['direction_start'],
    	    'return'        => $row['direction_return'],
    	);
    }

    return ($json
        ? json_encode($data)
        : $data
    );
}

/*
 * Calculate the closest stop location
 *
 * param    array - lat, long, time, direction
 * return   array
 *
 * Reference: http://stackoverflow.com/questions/4645490/get-nearest-places-google-maps-mysql-spatial-data
 * Reference: http://www.arubin.org/files/geo_search.pdf
 */
function getClosestStopsOrder($params = array(), $json = true)
{
	extract($params);

	if (empty($lat)
	 && empty($lng)
	 && empty($time)
	) {
    	throw new Exception("Provide latitude, longitude, and time.");
    }
    
    $sql = buildClosestStopOrder($params);
    $results = query($sql);

	if ($results == FALSE)
    {
    	throw new Exception("There was a problem retrieving the closest stop order.");
    }

    $data = array();
    foreach ($results as $row) {
    	$data[] = array(
    	    'route_num'        => (int) $row['route_num'],
    	    'distance'         => (double) $row['distance'],
    	);
    }

    return ($json
        ? json_encode($data)
        : $data
    );
}

/*
 * Calculate the closest stop location
 *
 * param    array - lat, long, time, direction
 * return   array
 *
 * Reference: http://stackoverflow.com/questions/4645490/get-nearest-places-google-maps-mysql-spatial-data
 * Reference: http://www.arubin.org/files/geo_search.pdf
 */
function getClosestStops($params = array(), $json = true)
{
	extract($params);

	if (empty($lat)
	 && empty($lng)
	 && empty($time)
	) {
    	throw new Exception("Provide latitude, longitude, and time.");
    }
    
    $sql = buildClosestStopAll($params);
    $results = query($sql);

	if ($results == FALSE)
    {
    	throw new Exception("There was a problem retrieving the closest stop.");
    }

    $data = array();
    foreach ($results as $row) {
    	$arrival_times = strToArray($row['arrival_times']);
    	$data[] = array(
    	    'route_num'        => (int) $row['route_num'],
    	    'way'              => $row['way'],
    	    'fullname'         => $row['fullname'],
    	    'arrival_time'     => $arrival_times,
    	    'shortcode'        => $row['shortcode'],
    	    'direction'        => $row['direction'],
    	    'distance'         => (double) $row['distance'],
    	);
    }

    return ($json
        ? json_encode($data)
        : $data
    );
}

function buildClosestStopOrder($params)
{
    $sql = "
	SELECT route_num, MIN(distance) as distance
    FROM (
    ";
    $sql .= buildClosestStop($params);
    $sql .= "
    ) as t
    GROUP BY route_num
    ORDER BY distance
    ";

    return $sql;
}

function buildClosestStopAll($params)
{
    $sql = "
    SELECT route_num, way, fullname, GROUP_CONCAT(arrival_time) as arrival_times, shortcode, direction, distance 
    FROM (
    ";
    $sql .= buildClosestStop($params);
    $sql .= "
    ) as t
    GROUP BY route_num, shortcode, direction
    ORDER BY route_num, distance, arrival_time 
    ";

    return $sql;
}

function buildClosestStop($params)
{
    $sql = '';
    $routes = array(1, 2, 3, 4, 5, 6);
    $directions = array(0, 1);
    foreach($routes as $route)
    {
        $params['route_num'] = $route;
        foreach ($directions as $direction) 
        {
            $params['direction'] = $direction;
            $sql .= "(" . buildClosestStopSql($params) . ") UNION ALL ";
        }
    }
    // remove last 'union all'
    $sql = substr($sql, 0, -10);

    return $sql;
}

/**
 * Calculates (in miles) distance from provided location to the
 * closest Lexpress stops within 1h 30m of the current time and
 * stops that are within 1 mile of the current location
 *
 * Using 'UNION ALL' suggestion from
 * http://www.xaprb.com/blog/2006/12/07/how-to-select-the-firstleastmax-row-per-group-in-sql/
 */
function buildClosestStopSql($params)
{
	extract($params);

	$sql = "
        SELECT 
                    s.route_num
                    , CONCAT('To ', IF(s.direction = 0, r.direction_start, r.direction_return)) as way
                    , sm.fullname
                    , st.arrival_time  
                    , s.shortcode 
                    , s.direction 
                    , ( 3959 * acos( cos( radians({$lat}) ) 
                                 * cos( radians( sm.stop_lat ) ) 
                                 * cos( radians( sm.stop_long ) 
                                    - radians({$lng}) ) 
                                 + sin( radians({$lat}) ) 
                                 * sin( radians( sm.stop_lat ) ) 
                                 )
                    ) AS distance 
        FROM        stops s
        LEFT JOIN   routes r ON s.route_num = r.id
        LEFT JOIN   stops_meta sm USING (shortcode)
        LEFT JOIN   stop_times st ON s.id = st.stop_id
        WHERE       1
        AND         st.arrival_time > '{$time}'
        AND			st.arrival_time < ADDTIME('{$time}', '01:30:00')
        AND			s.route_num = {$route_num}
        AND			s.direction = {$direction}
        HAVING      distance < 1 
        ORDER BY    distance, st.arrival_time, s.stop_sequence DESC 
        LIMIT       0, 2 
	";

	return $sql;
}

/**
 * http://blog.fedecarg.com/2009/02/08/geo-proximity-search-the-haversine-equation/
 */
function getRandomLocation($lat, $lng)
{
    if (!isset($lat) && !isset($lng)) {
    	$lat = CENTER_TOWN_LAT;
    	$lng = CENTER_TOWN_LNG;
    }

    $radius = 1; // in miles

    $lng_min = $lng - $radius / abs(cos(deg2rad($lat)) * 69);
    $lng_max = $lng + $radius / abs(cos(deg2rad($lat)) * 69);
    $lat_min = $lat - ($radius / 69);
    $lat_max = $lat + ($radius / 69);

    $r_lat = mt_rand(coordToInt($lat_min), coordToInt($lat_max));
    $r_lng = mt_rand(coordToInt($lng_min), coordToInt($lng_max));

    return json_encode(array(
        'lat' => intToCoord($r_lat),
        'lng' => intToCoord($r_lng),
    ));
}
