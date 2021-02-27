<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtilitiesController extends Controller
{
    /*
     * Convert number of seconds to human readable string
     */
    public static function secondsToString($seconds){
        $periods = array(
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1
        );

        $parts = array();

        foreach ($periods as $name => $duration) {
            $div = floor($seconds / $duration);

            if ($div == 0)
                continue;
            else
                if ($div == 1)
                    $parts[] = $div . " " . $name;
                else
                    $parts[] = $div . " " . $name . "s";
            $seconds %= $duration;
        }

        $last = array_pop($parts);

        if (empty($parts))
            return $last;
        else
            return join(', ', $parts) . " and " . $last;
    }


}
