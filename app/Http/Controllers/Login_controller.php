<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Str;

class Login_controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $consulta = DB::table('usuarios')->get();
       return $consulta ;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    // public function verificar_1(Request $request){
    //     $consulta = DB::table('usuarios_r')
    //     ->select('*')
    //     // ->where('usuario_nombre',$request->usuario_nombre)
    //     ->where('usuario_dni',$request->usuario_dni)
    //     ->where('usuario_pw',$request->usuario_pw)
    //     ->get();
    //     // if(sizeof($consulta) === 1){
    //     //     $token = Str::random(50);
    //     //     DB::table('usuarios')
    //     //     ->where('usuario_id',$consulta[0]->usuario_id)
    //     //     ->update(['api_token' => $token]);
    //     //     $consulta[0]->api_token = $token;
    //     // }
    //        $respuesta['cantidad']=sizeof($consulta);
    //        $respuesta['resp']=$consulta;
    //        return $respuesta;
    // }

    // public function crear_usuario(Request $request){
    //     DB::beginTransaction();
    //     try {
    //         $dataa = [
    //             'usuario_nombre'=>$request->usuario_nombre,
    //             'usuario_pw'=>$request->usuario_pw,
    //             'usuario_correo'=>$request->usuario_correo,
    //             'color'=> $request->color
    //         ];
    //         DB::table('usuarios_r')
    //         ->insert($dataa);

    //         DB::commit();
    //         $message = 'Guardado.';
    //         $status = true;
    //     } catch (\Throwable $th) {
    //         DB::rollback();
    //         $message = $th;
    //         $status = false;
    //     }

    //     $response["status"] = $status;
    //     $response["message"] = $message;
        
        
    //     // $response = $request;
    //     return $response;
    // }

    public function verificar_codeqr(Request $request){
        $consulta = DB::table('usuarios')
        ->select(['usuario_id','api_token','usuario_detalle_nombre','usuario_correo','usuario_area','usuario_cargo','usuario_dni'])
        ->where('codigo_qr',$request->qr)
        ->get();
        if(sizeof($consulta) === 1){
            $token = Str::random(50);
            DB::table('usuarios')
            ->where('usuario_id',$consulta[0]->usuario_id)
            ->update(['api_token' => $token]);
            $consulta[0]->api_token = $token;
        }
           $respuesta['cantidad']=sizeof($consulta);
           $respuesta['resp']=$consulta;
           return $respuesta;

        // $usuarios = DB::table('usuarios')
        // ->select(['usuario_id','usuario_pw','api_token','usuario_detalle_nombre','usuario_correo','usuario_area','usuario_cargo','usuario_dni'])
        // ->get();
        // $Array_nuevo = [];
        // foreach ($usuarios as  $value) {
        //     $temp = '';
        //     $temp = $value->usuario_id.$value->usuario_dni.$value->usuario_pw;
        //     $temp = password_hash($temp, PASSWORD_DEFAULT, [200]);;
        //     $affected = DB::table('usuarios')
        //     ->where('usuario_id',$value->usuario_id)
        //     ->update(['codigo_qr' => $temp]);

        //     array_push($Array_nuevo,$affected);
        // }
        // var_dump($Array_nuevo);
    }

    public function verificar(Request $request){
        $consulta = DB::table('usuarios')
        ->select(['usuario_id','api_token','usuario_detalle_nombre','usuario_correo','usuario_area','usuario_cargo','usuario_dni'])
        ->where('usuario_dni',$request->usuario_dni)
        ->where('usuario_pw',$request->usuario_pw)
        ->get();
        if(sizeof($consulta) === 1){
            $token = Str::random(50);
            DB::table('usuarios')
            ->where('usuario_id',$consulta[0]->usuario_id)
            ->update(['api_token' => $token]);
            $consulta[0]->api_token = $token;
        }
           $respuesta['cantidad']=sizeof($consulta);
           $respuesta['resp']=$consulta;
           return $respuesta;
    }

    public function cerrar_sesion(Request $request)
    {
        try{
        $afected['afectado'] = DB::table('usuarios')
                ->where('usuario_id',$request->usuario_id)
                ->update(['api_token' => '']);
        }catch (\Throwable $th) {
            $afected['error'] = $th;
        }
        return $afected;
    }
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
