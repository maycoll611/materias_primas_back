<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;
class prueba1
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->api_token && $request->usuario_id){
           $resp = DB::table('usuarios')
                    ->select('*')
                    ->where('usuario_id',$request->usuario_id)
                    ->where('api_token',$request->api_token)
                    ->get();
           if(sizeof($resp) == 1){
               return $next($request);
           }else{
               return redirect('/');
           }
        }else{
            return 'no existe token ni usuario';
        }
        
    }
}

