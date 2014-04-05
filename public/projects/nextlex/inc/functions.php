<?php
    /***********************************************************************
     * functions.php
     *
     * Graham Schmidt
     *
     * Helper functions.
     **********************************************************************/

    /**
     * Executes SQL statement, possibly with parameters, returning
     * an array of all rows in result set or false on (non-fatal) error.
     */
    function query(/* $sql [, ... ] */)
    {
        // SQL statement
        $sql = func_get_arg(0);

        // parameters, if any
        $parameters = array_slice(func_get_args(), 1);

        // try to connect to database
        static $handle;
        if (!isset($handle))
        {
            try
            {
                // connect to database
                $handle = new PDO("mysql:dbname=" . DATABASE . ";host=" . SERVER, USERNAME, PASSWORD);

                // ensure that PDO::prepare returns false when passed invalid SQL
                $handle->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); 
            }
            catch (Exception $e)
            {
                // trigger (big, orange) error
                trigger_error($e->getMessage(), E_USER_ERROR);
                exit;
            }
        }

        // prepare SQL statement
        $statement = $handle->prepare($sql);
        if ($statement === false)
        {
            // trigger (big, orange) error
            $error = $handle->errorInfo();
            trigger_error($error[2], E_USER_ERROR);
            exit;
        }

        // execute SQL statement
        $results = $statement->execute($parameters);

        // return result set's rows, if any
        if ($results !== false)
        {
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return false;
        }
    }

    /**
     * Renders template, passing in values.
     */
    function render($template, $values = array())
    {
        // if template exists, render it
        if (file_exists("views/$template"))
        {
            // extract variables into local scope
            extract($values);

            // render template
            require("views/$template");
        }

        // else err
        else
        {
            trigger_error("Invalid template: $template", E_USER_ERROR);
        }
    }

    /**
     * CSV to array
     *
     * From http://www.php.net/manual/en/function.str-getcsv.php
     */
    function csvToArray($input, $delimiter = ',', $enclosure = '"') 
    { 
        $header = null; 
        $data = array(); 
        $csvData = str_getcsv($input, "\n"); 
        
        foreach($csvData as $csvLine){ 
            if (is_null($header)) 
            {
                $header = removeEnclosure(explode($delimiter, trim($csvLine)));
            }
            else
            { 
                $items = explode($delimiter, $csvLine); 
                
                for ($n = 0, $m = count($header); $n < $m; $n++)
                { 
                    $prepareData[$header[$n]] = removeEnclosure($items[$n]);
                }
                
                $data[] = $prepareData; 
            } 
        } 

        return $data; 
    } 

    /**
     * Remove enclosures around fields
     */
    function removeEnclosure($str = '', $enclosure = '"')
    {
    	return str_replace(array($enclosure), array(), $str);
    }

    // ajax helper
    function isXHR() {
        return isset( $_SERVER['HTTP_X_REQUESTED_WITH'] );
    }

    /**
     * Pretty print array
     */
    function print_a($arr)
    {
    	if (empty($arr))
        	return false;

    	echo "<pre>";
    	print_r($arr);
    	echo "</pre>";
    }

    /**
     * Take a time string delimited by commas
     * and break up into an array of times (as minutes)
     *
     * e.g. 00:01:03,01:01:03,02:01:03,03:01:03
     */
    function timeStringToArray($time_str)
    {
    	$time_x = explode(",", $time_str);
        // only capture the hour and minutes
        $time_arr = array();
        $str = '';
        foreach ($time_x as $time)
        {
        	unset($str);
        	$seg = explode(":", $time);
            // hours
            if ($seg[0] > 0) {
            	$str = (int) $seg[0] . "h ";
            } else {
            	$str = '';
            }
            // minutes
            if ($seg[1] >= 0) {
            	$min = ($seg[1] > 10 ? $seg[1] : (int) $seg[1]);
            	$str .= $min . "m";
            }
            // add string to time array
            $times_arr[] = $str;
        }
        return $times_arr;
    }

    function coordToInt($coord)
    {
    	return $coord*10000000;
    }

    function intToCoord($coord)
    {
    	return $coord/10000000;
    }

    function strToArray($arr = array(), $delimiter = ',')
    {
    	return explode($delimiter, $arr);
    }
