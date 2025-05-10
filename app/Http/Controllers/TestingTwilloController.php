<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client as Twillo;

class TestingTwilloController extends Controller
{
    public function index(){

        $test = new Twillo(env('TWILLO_API'),env('TWILLO_AUTH'));
        $message = $test->messages
        ->create("whatsapp:+6281291131117", 
          array(
            "from" => "whatsapp:+14155238886",
            "body" => "PALDPLASPDLASLPD"
          )
        );
        return ($message);
    }
}
