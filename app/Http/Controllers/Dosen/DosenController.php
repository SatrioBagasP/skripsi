<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;

class DosenController extends Controller
{
    public function index()
    {
        return view('Pages.Dosen.index');
    }
}
