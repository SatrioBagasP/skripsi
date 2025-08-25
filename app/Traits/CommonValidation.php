<?php

namespace App\Traits;

use Exception;

trait CommonValidation
{
    public function validateExistingData($data){
        if(!$data){
            throw new Exception('Data tidak ditemukan!');
        }
    }

    public function validateRangeDate($request){

    }

}
