<?php

    /***********************************************************************
     * constants.php
     *
     * Computer Science 50
     * Final Project
     *
     * Global constants.
     **********************************************************************/

    define("DATABASE", "nextlex");
    define("USERNAME", "lexpress");
    define("PASSWORD", "F8=r*vA&A8aHabaj");

    define("SERVER", "localhost");
    if ( stristr($_SERVER['HTTP_HOST'], 'local') )
    {
        define("BASE_URL", "http://local.nextlex.com/");
    }
    else
    {
        define("BASE_URL", "http://www.schmidtg.com/projects/nextlex/");
    }

    define("CENTER_TOWN_LAT", 42.448211);
    define("CENTER_TOWN_LNG", -71.228729);
