<?php
/**
 * Helper to seed data
 */
function insertTimes($route_num = 1, $start_time = '07:30:00')
{
	$sql = "
    INSERT      stop_times (shortcode, arrival_time, stop_id)
    SELECT      st.shortcode
	            , ADDTIME(st.arrival_time, '01:00:00') as arrival_time
	            , st.stop_id 
	FROM        stop_times st
	LEFT JOIN   stops s ON st.stop_id = s.id
	WHERE       1
	AND         route_num = ?
	AND         st.arrival_time > ?
	";
    $results = query($sql, $route_num, $start_time);

	if ($results == FALSE)
    {
    	throw new Exception("There was a problem retrieving the closest stop.");
    }

    return $results;
}

function getStops($filename = '', $json = true)
{
	// sanity check
	if ($filename == '')
		throw new Exception("Please provide a filename.");

	// load csv
	$data = csvToArray(file_get_contents($filename));

	return ($json
        ? json_encode($data)
        : $data
	);
}

/**
 * Insert stop meta info from the CSV feed
 */
function insertStopMetaAll($data)
{
	// sanity check
	if (empty($data))
		throw new Exception("No stop meta data to insert.");

    // insert each shortcode/fullname into db
    $params = array();
    foreach ($data as $row)
    {
        $params = array(
            'shortcode' => $row['stop_id'],
            'fullname'  => $row['stop_name'],
        );
        insertStopMetaRow($params);
        unset($params);
    }
}

/**
 * Insert stop meta info from the CSV feed
 */
function insertStopMetaRow($data)
{
	// sanity check
	if (empty($data))
		throw new Exception("No stop meta data to insert row.");

    // get local vars for shortcode and fullname
    extract($data);

    $sql = "
        INSERT INTO `stops_meta` SET 
            shortcode = ?
            , fullname = ?
        ON DUPLICATE KEY UPDATE
            fullname = ?
    ";
    $result = query($sql, $shortcode, $fullname, $fullname);
    if ($result === FALSE)
    {
    	throw new Exception("There was problem inserting.");
    }
    return true;
}

/**
 * Insert stop info from the CSV feed
 */
function insertStopAll($data)
{
	// sanity check
	if (empty($data))
		throw new Exception("No stop data to insert.");

    // insert each shortcode/fullname into db
    $params = array();
    foreach ($data as $row)
    {
        // arrive time
        preg_match_all("/[0-9]{1,}/", $row['ArrivTime'], $ma);
        $arrival_time = implode(":", $ma[0]);

        // departure time
        preg_match_all("/[0-9]{1,}/", $row['DeparTime'], $md);
        $depart_time = implode(":", $md[0]);
        
        $params = array(
            'shortcode'     => $row['stop_id'],
            'stop_lat'      => $row['stop_lat'],
            'stop_long'     => $row['stop_lon'],
            'stop_sequence' => $row['stop_seque'],
            'arrival_time'  => $arrival_time,
            'depart_time'   => $depart_time,
            'route_num'     => $row['trip_id'],
        );

        insertStopRow($params);
        // print_a($params);
        unset($params);
    }
}

/**
 * Insert stop meta info from the CSV feed
 */
function insertStopRow($data)
{
	// sanity check
	if (empty($data))
		throw new Exception("No stop data to insert row.");

    // get local vars for shortcode and fullname
    extract($data);

    $sql = "
        INSERT INTO `stops` SET 
            shortcode = ?
            , stop_lat = ?
            , stop_long = ?
            , stop_sequence = ?
            , arrival_time = ?
            , depart_time = ?
            , route_num = ?
        ON DUPLICATE KEY UPDATE
            stop_lat = ?
            , stop_long = ?
            , arrival_time = ?
            , depart_time = ?
    ";
    $result = query($sql
        , $shortcode
        , $stop_lat
        , $stop_long
        , $stop_sequence
        , $arrival_time
        , $depart_time
        , $route_num
        , $stop_lat
        , $stop_long
        , $arrival_time
        , $depart_time
    );

    if ($result === FALSE)
    {
    	throw new Exception("There was problem inserting.");
    }
    return true;
}
