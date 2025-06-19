<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;

class MahasiswaController extends Controller
{
    public function index()
    {
        return view('Pages.Mahasiswa.index');
    }
}
