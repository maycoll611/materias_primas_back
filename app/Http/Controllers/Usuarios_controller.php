<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class Usuarios_controller extends Controller
{
    public function get_usuarios(Request $request){
        try{
            $resp['data'] = DB::table('usuarios')
                    ->select('usuario_id','usuario_detalle_nombre')
                    ->get();

            $resp['status'] = true;
        } catch (\Throwable $th){

            $resp['error'] = $th;
            $resp['status'] = false;
        }
        return $resp;
    }
}
