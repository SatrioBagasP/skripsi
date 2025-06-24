<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;

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

    function getJurusanOption(){
        $data = [];
        $data = Jurusan::get()->map(function($item){
            return [
                'value' => $item->id,
                'label' => $item->name,
            ];
        });
        
        return $data;
    }
}
