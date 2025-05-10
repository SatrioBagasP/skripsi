<?php

use App\Http\Controllers\TestingTwilloController;
use Illuminate\Support\Facades\Route;

Route::get('',[TestingTwilloController::class,'index']);

