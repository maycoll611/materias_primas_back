<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Preguntas_controller extends Controller
{
    public function get_preguntas(Request $request){
        $resp = DB::table('preguntas')->get();
        return $resp;
    }
}
