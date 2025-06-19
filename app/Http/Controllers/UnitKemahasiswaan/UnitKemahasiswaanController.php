<?php

namespace App\Http\Controllers\UnitKemahasiswaan;

use App\Http\Controllers\Controller;

class UnitKemahasiswaanController extends Controller {
    public function index(){
        return view('Pages.UnitKemahasiswaan.index');
    }
}