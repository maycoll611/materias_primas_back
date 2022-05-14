<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use DB;
Use App\Http\Controllers\Enum_controller;
use GuzzleHttp\Client;
use Http;

class Empresas_controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       $resp = DB::table('empresas')
                ->select("*")
                ->get();
        $response['data'] = $resp;
        return $resp;
    
    }

    public function prueba_sunat(Request $request){
        
        if (DB::table('empresas')->where('empresa_ruc',$request->ruc)->exists()) {
            $resp["datos"] = DB::table('empresas')
                    ->where('empresa_ruc',$request->ruc)
                    ->first();
            $resp["lugar"] = "interna";
            
        }else{
            $resp1 = Http::get('https://api.apis.net.pe/v1/ruc?numero='.$request->ruc);
            $resp["datos"] = $resp1->json();
            $resp["lugar"] = "sunat";
        }
       
    return $resp;
    }

    public function detalle_compra_id(Request $request){
        $resp['compra'] = DB::table('compras')
                ->select('*')
                ->where('compra_id',$request->compra_id)
                ->get();
        $resp['detalle_compra']= DB::table('dtcompras')
                ->select('*')
                ->where('compras_compra_id',$request->compra_id)
                ->get();
        return $resp; 
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $resp = DB::table('compras')
        ->select(DB::raw('max(compra_codigo) as codigo'))
        ->get();

        $resp1=DB::table('compras')
        ->select(DB::raw('max(compra_id) as compra_id'))
        ->get();

        $response['compra_id'] = $resp1[0]->compra_id +1;
        $response['codigo']= $resp1[0]->compra_id +1;
        $response['fecha'] = date('Y-m-d');

        // $response['dtcompra_tipo'] = app(Enum_controller::class)->enum_valores('dtcompras','dtcompra_tipo');
        
        return $response;
    }
    public function compras_reportes(){
        $respuesta['compras'] = DB::table('compras')
        ->select('*')
        ->orderBy('compra_codigo', 'desc')
        ->get();
        $respuesta['detalle_compras'] = DB::table('dtcompras')
        ->select('*')
        ->get();
        return $respuesta;
    }    
    //anular una compra
    public function anular_compra(Request $request){
        
        DB::beginTransaction();
        try {
            $id = $request->compra_id;
            DB::table('compras')
              ->where('compra_id', $id)
              ->update(['compra_estado' => 0]);

            DB::commit();
            $message = 'Guardado.';
            $status = true;
        } catch (\Throwable $th) {
            DB::rollback();
            $message = 'Error al Guardar. Intente otra vez.';
            $status = false;
        }
        $response["status"] = $status;
        $response["message"] = $message;
        return $response;
    }
     
    public function guardar_compra_1(Request $request){
        $datos = [["compras_compra_id"=>3,
                "dtcompra_cantidad"=> '1',
                "dtcompra_descripcion"=>'desc',
                "dtcompra_observacion"=>'',
                "dtcompra_precio_unit"=> 5.00,
                "dtcompra_total"=> 5.00,
                "dtcompra_um"=>'UND'],

                ["compras_compra_id"=>3,
                "dtcompra_cantidad"=> '1',
                "dtcompra_descripcion"=>'desc',
                "dtcompra_observacion"=>'',
                "dtcompra_precio_unit"=> 5.00,
                "dtcompra_total"=> 5.00,
                "dtcompra_um"=>'UND']];
            DB::table('dtcompras')
            ->insert($datos);
    }
    public function guardar_compra(Request $request){
        // var_dump($request->compra_detalle);
        DB::beginTransaction();
        try {

            DB::table('compras')
            ->insert($request->compras);
            
            DB::table('dtcompras')
            ->insert($request->compra_detalle);

            DB::commit();
            $message = 'Guardado.';
            $status = true;
        } catch (\Throwable $th) {
            DB::rollback();
            $message = 'Error al Guardar. Intente otra vez.';
            $status = false;
        }

        $response["status"] = $status;
        $response["message"] = $message;
        return $response;
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function  get_transportistas(){
        $resp = DB::table('empresas')
                    ->select('empresa_id','empresa_ruc','empresa_razon_social')
                    ->where('empresa_transporte',true)
                    ->get();
        return $resp;
    }
    public function get_conductores_carros(Request $request){

        $resp["conductores"] = DB::table('conductores')
                                ->select('conductor_licencia','conductor_nombre','conductor_telefono','conductor_id')
                                ->where('empresas_empresa_ruc',$request->empresa_ruc)
                                ->get();
        $resp["carros"] = DB::table('carros')
                                ->select('*')
                                ->where('empresas_empresa_ruc',$request->empresa_ruc)
                                ->get();
        return $resp;
    }

    public function guardar_carro_nuevo(Request $request){
        $data = [
                "carro_marca"=>$request->marca_t,
                "carro_placa"=>$request->placa_t,
                "carro_mtc"=>$request->mtc_t,
                "carro_marca2"=>$request->marca_p,
                "carro_placa2"=>$request->placa_p,
                "carro_mtc2"=>$request->mtc_p,
                "empresas_empresa_ruc"=>$request->ruc,
                "carro_update"=>date('Y-m-d')
        ];
        DB::beginTransaction();
        try {

            DB::table('carros')
            ->insert($data);

            DB::commit();
            $message = 'Guardado.';
            $status = true;
        } catch (\Throwable $th) {
            DB::rollback();
            $message = 'Error al Guardar. Intente otra vez.';
            $status = false;
        }

        $response["status"] = $status;
        $response["message"] = $message;

        // $response = $request;
        return $response;
    }
    public function guardar_conductor_nuevo(Request $request){
        $data = [
                "conductor_nombre"=>$request->nombre_conductor,
                "conductor_telefono"=>$request->telefono,
                "conductor_licencia"=>$request->licencia,
                "empresas_empresa_ruc"=>$request->ruc,
                "conductor_update"=>date('Y-m-d')
        ];
        DB::beginTransaction();
        try {

            DB::table('conductores')
            ->insert($data);

            DB::commit();
            $message = 'Guardado.';
            $status = true;
        } catch (\Throwable $th) {
            DB::rollback();
            $message = 'Error al Guardar. Intente otra vez.';
            $status = false;
        }

        $response["status"] = $status;
        $response["message"] = $message;

        // $response = $request;
        return $response;
    }
    public function store(Request $request)
    {
        $data = [
            "empresa_razon_social"=> $request->razon_social,
            "empresa_ruc"=> $request->ruc,
            "empresa_direccion"=> $request->direccion,
            "empresa_numero"=> $request->numero,
            "empresa_zona"=> $request->zona,
            "empresa_distrito"=> $request->distrito,
            "empresa_provincia"=> $request->provincia,
            "empresa_departamento"=> $request->departamento,
            "empresa_update"=> date('Y-m-d'),
            // "empresa_estado"=> '',
            "empresa_nombre_corto"=> "",
            "empresa_texto"=> "",
            "empresa_tipo"=> "",
            "empresa_direccion_fiscal"=> $request->direccion,
            "empresa_transporte" => $request->transporte
        ];
        DB::beginTransaction();
        try {

            DB::table('Empresas')
            ->insert($data);

            DB::commit();
            $message = 'Guardado.';
            $status = true;
        } catch (\Throwable $th) {
            DB::rollback();
            $message = 'Error al Guardar. Intente otra vez.';
            $status = false;
        }

        $response["status"] = $status;
        $response["message"] = $message;

        // $response = $request;
        return $response;
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
