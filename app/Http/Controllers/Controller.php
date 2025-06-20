<?php

namespace App\Http\Controllers;

abstract class Controller
{
    function setLog($caused, $performed, $log, $properties = null, $content = 'default')
    {
        activity()
            ->causedBy($caused)
            ->performedOn($performed)
            ->withProperties($properties)
            ->useLog($content)
            ->log($log);
    }
}
