<?php

namespace App\Http\Controllers\Jurusan;

use App\Http\Controllers\Controller;

class JurusanController extends Controller
{
    public function index()
    {
        return view('Pages.Jurusan.index');
    }
}
