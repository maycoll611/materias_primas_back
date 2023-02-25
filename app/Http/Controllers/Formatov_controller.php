<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Ventas_export;
use Http;

class Formatov_controller extends Controller

{
    public function consultar_dni(Request $request){

        $resp1 = Http::get('https://api.apis.net.pe/v1/dni?numero='.$request->dni);
            $resp["datos"] = $resp1->json();
            return ($resp);
    }

    public function guardar_cabecera(Request $request){
        
         $data = [
                    'formato_v_fecha' =>$request->fecha_hora,
                    'formato_v_equipo_id' =>$request->equipo_id,
                    'formato_v_usuario_id' =>$request->usuario_id,
                    'formato_v_turno' =>$request->turno,
                    'formato_v_km_inicial' =>$request->km_inicio,
                    'formato_v_km_final' =>$request->km_fin,
                    'formato_v_observacion' =>$request->observacion,
                    'formato_v_usuario_id_autoriza' =>NULL,
                    'formato_v_estado' =>NULL,
                    'formato_v_fecha_hora_autorizado' =>NULL,
                    'formato_v_hm' =>NULL,
                    'formato_v_estado_anulado' =>NULL,
                    'formato_v_total_viajes' =>NULL,
                ];


        DB::beginTransaction();
        try {
                $id = DB::table('formato_v')
                ->insertGetId($data);
            DB::commit();
            $resp['id'] = $id;
            $resp['status'] = true;
            
        }catch(\Throwable $th){
            DB::rollback(); 
            $resp['status'] = $th;
        }
           
        return $resp;
    }

    public function export_get_checklist(){
        return Excel::download(new Ventas_export, 'Descarga.xlsx');
    }
    public function autorizar_checklist(Request $request){
        
        try{
            $affected = DB::table('checklist')
            ->where('checklist_id',$request->checklist_id)
            ->update(
                [   
                    'checklist_usuario_id_autoriza'=>$request->usuario_autorizante,
                    'checklist_autorizado'=>'1'
                ]
            );
            $resp['affected'] = $affected;
            $resp['status'] = true;
        } catch (\Throwable $th){
            $resp['error'] = $th;
            $resp['status'] = false;
        }
        return $resp;
    }

    public function get_equipos(Request $request){
        $resp = DB::table('equipos')
                ->select('equipo_id','equipo_codigo','equipo_descripcion','equipo_modelo')
                ->orderBy('equipo_codigo', 'asc')
                ->get();
        return $resp;
    }

    public function get_checklist_id(Request $request){
        $resp=DB::table('checklist')
        ->join('equipos','checklist.checklist_equipo','=','equipos.equipo_id')
        ->join('usuarios','checklist.checklis_usuario_id','=','usuarios.usuario_id')
        ->select(
                "checklist.*", 
                "checklist.checklist_id", 
                "checklist.checklis_usuario_id",
                "checklist.checklist_equipo" ,
                "checklist.checklist_fecha_hora" ,
                "checklist.checklist_kilom_inicial" ,
                "checklist.checklist_kilom_final" ,
                "checklist.checklist_turno" ,
                "checklist.checklist_autorizado" ,
                "checklist.checklist_usuario_id_autoriza",
                "checklist.checklist_estado",
                "usuarios.usuario_detalle_nombre",
                "equipos.equipo_descripcion",
                "equipos.equipo_placa",
                "equipos.equipo_modelo",
                "equipos.equipo_serie",
                "equipos.equipo_año",
                "equipos.equipo_tipo",
                "equipos.equipo_categoria_id");
        $resp=$resp->where('checklist.checklist_id',$request->checklist_id);
        $resp = $resp->get();

        if($resp[0]->checklist_usuario_id_autoriza){
            $resp1 = DB::table('checklist')
            ->join('equipos','checklist.checklist_equipo','=','equipos.equipo_id')
            ->join('usuarios','checklist.checklist_usuario_id_autoriza','=','usuarios.usuario_id')
            ->select('usuarios.usuario_detalle_nombre as autorizador');
            $resp1=$resp1->where('checklist.checklist_id',$request->checklist_id);
            $resp1= $resp1->get();
            $resp[0]->usuario_autoriza = $resp1[0]->autorizador;
        }
        $resp[0]->usuario_autoriza = "";
        return $resp;
    }
    public function get_checklist_vacio(Request $request){
        $resp = DB::table('elementos_checklist')
                    ->select('*')
                    ->get();
        return $resp;
    }
    public function buscar_usuario_dni(Request $request){
        $resp = DB::table('usuarios')
                    ->select('*')
                    ->where('usuario_dni',"=",$request->dni)
                    ->first();
        return $resp;
    }
    public function get_formato_v(Request $request){
        $resp=DB::table('formato_v')
        ->join('equipos','formato_v.formato_v_equipo_id','=','equipos.equipo_id')
        ->join('usuarios','formato_v.formato_v_usuario_id','=','usuarios.usuario_id')
        ->select(
                "formato_v.formato_v_id",     
                "formato_v.formato_v_fecha",
                "formato_v.formato_v_equipo_id" ,
                "formato_v.formato_v_turno" ,
                "formato_v.formato_v_km_inicial" ,
                "formato_v.formato_v_km_final" ,
                "formato_v.formato_v_observacion" ,
                "formato_v.formato_v_usuario_id_autoriza" ,
                "formato_v.formato_v_estado",
                "formato_v.formato_v_hm",
                "formato_v.formato_v_total_viajes",
                "usuarios.usuario_detalle_nombre",
                "equipos.equipo_codigo",
                "equipos.equipo_descripcion",
                "equipos.equipo_modelo",
                )
        ->orderBy('formato_v.formato_v_id', 'desc');
        
        if(isset($request->desde)&&isset($request->hasta)){
            $resp=$resp->whereBetween('formato_v_fecha',[$request->desde,$request->hasta.' 23:59:00']);
        }
        if($request->usuario_cargo == 'empleado'){
            $resp=$resp->where('formato_v.formato_v_usuario_id',$request->usuario_id);
        }
        // if($request->usuario_cargo == 'administrador'){
        //     $resp=$resp->where('usuarios.usuario_area',$request->usuario_area);
        // }
        
        $resp = $resp->get();

        return $resp;
    }
    public  function guardar_configuracion_usuario(Request $request){
         date_default_timezone_set('America/Lima');
         $data = ['usuario_cargo' => $request->data_config['usuario_cargo'],
                    'usuario_update' => date('Y-m-d H:i:s'),
                    'usuario_area' => $request->data_config['usuario_area']];
        DB::beginTransaction();
        try {
                $affected = DB::table('usuarios')
                ->where('usuario_id',$request->data_config['usuario_id'])
                ->update($data);
            DB::commit();
            $resp['affected'] = $affected;
            $resp['status'] = true;
            
        }catch(\Throwable $th){
            DB::rollback();
            
            $resp['status'] = $th;

        }
           
        return $resp;
    }


    public  function guardar_configuracion(Request $request){
        $data = $request->data_config;
        DB::beginTransaction();
        $contador = 0;
        date_default_timezone_set('America/Lima');
        try {
            
            foreach ($data as $value) {
                $affected = DB::table('categorias_elementos')
                ->where('ce_id',$value['ce_id'])
                ->update(['ce_valor' => $value['ce_valor'],
                            'ce_ultimo_cambio' => date('Y-m-d H:i:s'),
                                'ce_usuario_id' => $request->usuario['usuario_id']]);
                            $contador++;
            }
            DB::commit();
            $resp['affected'] = $contador;
            $resp['status'] = true;
            
        }catch(\Throwable $th){
            DB::rollback();
            $resp['affected'] = $contador;
            $resp['status'] = false;

        }
           
        return $resp;
    }
    public function get_config_categorias_elementos(Request $request){
        try {
            $resp = DB::table('categorias_elementos')
                ->select('*')
                ->get();
        } catch (\Throwable $th) {
            $resp =  $th;
        }

        return $resp;
    }

    public function get_categorias(Request $request){
        try {
            $resp = DB::table('categorias')
                ->select('*')
                ->get();
        } catch (\Throwable $th) {
            $resp =  $th;
        }
        
        return $resp;
    }
    public function guardar_km_final(Request $request){

        try{
            $affected = DB::table('checklist')
            ->where('checklist_id',$request->checklist_id)
            ->update(['checklist_kilom_final'=>$request->kilom_final]);

            $resp['affected'] = $affected;
            $resp['status'] = true;
        } catch (\Throwable $th){
            $resp['error'] = $th;
            $resp['status'] = false;
        }
        return $resp;
    }

   public function get_config_categoria_id(Request $request){
    try {
        $resp = DB::table('categorias_elementos')
            ->select('*')
            ->where('ce_categoria_id','=',$request->categoria_id)
            ->get();
    } catch (\Throwable $th) {
        $resp =  $th;
    }
    
    return $resp;
   }

    public function get_guias_id(Request $request){
        try{    
            $resp['cliente'] = DB::table('ventas')
                                ->join('empresas','ventas.ventas_cliente_ruc','=','empresas.empresa_ruc')
                                ->select('empresas.empresa_razon_social','empresas.empresa_ruc','empresas.empresa_direccion')
                                ->where('ventas.venta_id','=',$request->id)
                                ->first();
            $resp['empresa_origen'] = DB::table('ventas')
                                ->join('empresas','ventas.venta_empresa_partida_ruc','=','empresas.empresa_ruc')
                                ->select('empresas.empresa_razon_social','empresas.empresa_ruc','empresas.empresa_direccion','empresas.empresa_numero','empresas.empresa_zona','empresas.empresa_distrito','empresas.empresa_provincia','empresas.empresa_departamento')
                                ->where('ventas.venta_id','=',$request->id)
                                ->first();
            $resp['empresa_transporte'] = DB::table('ventas')
                                ->join('empresas','ventas.venta_transportista_ruc','=','empresas.empresa_ruc')
                                ->select('empresas.empresa_razon_social','empresas.empresa_ruc')
                                ->where('ventas.venta_id','=',$request->id)
                                ->first();
            $resp['conductor'] = DB::table('ventas')
                                ->join('conductores','ventas.venta_licencia_conductor','=','conductores.conductor_licencia')
                                ->select('conductores.conductor_nombre','conductores.conductor_licencia','conductores.conductor_telefono')
                                ->where('ventas.venta_id','=',$request->id)
                                ->first();
            $resp['carro'] = DB::table('ventas')
                                ->join('carros','ventas.venta_placa_carro','=','carros.carro_placa')
                                ->select('carros.*')
                                ->where('ventas.venta_id','=',$request->id)
                                ->first();
            $resp['materiales'] = DB::table('dtventas')
                                ->select('*')
                                ->where('ventas_ventas_id','=',$request->id)
                                ->get();
            $resp['usuario_envia'] = DB::table('ventas')
                                ->join('usuarios','ventas.venta_usuario_envia','=','usuarios.usuario_id')
                                ->select('usuarios.usuario_id','usuarios.usuario_detalle_nombre')
                                ->where('ventas.venta_id','=',$request->id)
                                ->first();                    
            $resp['venta'] = DB::table('ventas')
                            ->join('empresas','ventas.ventas_cliente_ruc','=','empresas.empresa_ruc')
                            ->join('usuarios','ventas.venta_usuario_envia','=','usuarios.usuario_id')
                            ->select('ventas.venta_id',
                                    'ventas.venta_correlativo',
                                    'ventas.venta_fecha_registro',
                                    'ventas.venta_motivo_nro',
                                    'ventas.venta_motivo_detalle',
                                    'ventas.ventas_cliente_ruc',
                                    'empresas.empresa_razon_social',
                                    'empresas.empresa_direccion',
                                    'ventas.venta_empresa_partida_ruc',
                                    'ventas.venta_llegada_direccion',
                                    'ventas.venta_llegada_nro',
                                    'ventas.venta_llegada_zona',
                                    'ventas.venta_llegada_distrito',
                                    'ventas.venta_llegada_provincia',
                                    'ventas.venta_llegada_departamento',
                                    'ventas.venta_contacto_nombre',
                                    'ventas.venta_contacto_telefono',
                                    'ventas.venta_contacto_correo',
                                    'ventas.venta_transportista_ruc',
                                    'ventas.venta_licencia_conductor',
                                    'ventas.venta_placa_carro',
                                    'ventas.venta_paquete_cantidad_tipo',
                                    'ventas.venta_paquete_peso',
                                    'ventas.venta_paquete_medidas',
                                    'ventas.venta_f_retorno',
                                    'ventas.venta_cantidad_total',
                                    'ventas.venta_peso_total',
                                    'ventas.venta_usuario_envia',
                                    'ventas.venta_descripcion',
                                    'ventas.venta_fecha',
                                    'ventas.usuarios_usuario_id',
                                    'ventas.ventas_tipo_comprobante',
                                    )
                                    ->where('ventas.venta_id','=',$request->id)
                                    ->first();
            
            $resp['status'] = true;
        } catch (\Throwable $th){

            $resp['error'] = $th;
            $resp['status'] = false;
        }
        return $resp;                    
    }
    public function get_guias(Request $request){
        $resp=DB::table('ventas')
        ->join('empresas','ventas.ventas_cliente_ruc','=','empresas.empresa_ruc')
        ->join('usuarios','ventas.venta_usuario_envia','=','usuarios.usuario_id')
        ->select('ventas.venta_id',
                    'ventas.venta_correlativo',
                    'ventas.venta_fecha_registro',
                    'ventas.venta_motivo_nro',
                    'ventas.venta_empresa_partida_ruc',
                    'ventas.venta_llegada_direccion',
                    'ventas.venta_llegada_departamento',
                    'ventas.venta_f_retorno',
                    'ventas.venta_paquete_peso',
                    'ventas.venta_anulado',
                    'empresas.empresa_ruc',
                    'empresas.empresa_razon_social',
                    'usuarios.usuario_detalle_nombre')
        ->orderBy('ventas.venta_id', 'desc');
        if($request->usuario_cargo == 'empleado'){
            $resp = $resp->where('usuarios.usuario_area','=',$request->usuario_area);
        }
        if(isset($request->desde)&&isset($request->hasta)){
            $resp=$resp->whereBetween('venta_fecha_registro',[$request->desde,$request->hasta.' 23:59:00']);
        }
        if(isset($request->usuario_area)){
            $resp=$resp->whereIn('usuarios.usuario_area',$request->usuario_area);
        }
        $resp = $resp->get();

        return $resp;
        // return $request->usuario_cargo;
    }
    public function guardar_checklist(Request $request){


            $data2 = [ "checklis_usuario_id"=> $request->cabecera['usuario_id'],
            "checklist_equipo"=> $request->cabecera['equipo_id'] ,
            "checklist_fecha_hora"=> $request->cabecera['fecha'] ,
            "checklist_kilom_inicial"=> $request->cabecera['equipo_kminicial'] ,
            "checklist_kilom_final"=> $request->cabecera['equipo_kmfinal'] ,
            "checklist_turno"=> $request->cabecera['turno'] ,
            "checklist_autorizado" => false,
            "checklist_usuario_id_autoriza"=> '',
            "checklist_estado" => true];

            $i=1;
            foreach ($request->cuerpo as $value) {
                $data2["check_".$i] = $value['valor'];
               $i++; 
            }

            DB::beginTransaction();
            try {
    
                $id = DB::table('checklist')
                ->insertGetId($data2);
                // $correlativo =DB::table('ventas')
                //                 ->select('venta_correlativo')
                //                 ->where('ventas_id','=',$id)
                //                 ->first();
            
    
                DB::commit();
                $message = 'Guardado.';
                $status = true;
            }catch(\Throwable $th){
                DB::rollback();
                $message = $th;
                $status = false;
            }
    
            $response["status"] = $status;
            $response["message"] = $message;
            $response["id"] = $id;
               
            return $response;
            //  return $data2;
    }
    public function asignar_transportista(Request $request){
        $data_transp =[
            'venta_transportista_ruc'=> $request->transportista['ruc'],
            'venta_licencia_conductor'=> $request->transportista['licencia'],
            'venta_placa_carro'=> $request->transportista['placa_t'],
        ];
        try{
            $affected = DB::table('ventas')
            ->where('venta_id',$request->id)
            ->update($data_transp);
            $resp['affected'] = $affected;
            $resp['status'] = true;
        } catch (\Throwable $th){
            $resp['error'] = $th;
            $resp['status'] = false;
        }
        return $resp;
    }
    public function guardar_documento(Request $request){

        // $correlativo = DB::table('ventas')
        //                 ->max('venta_correlativo');

        // $correlativo1 = intval(strval($correlativo + 1));

        $data = [ 
            'venta_correlativo' => '',
            'venta_fecha_registro' => $request->fecha_hoy,
            'venta_motivo_nro' => $request->motivo,
            'venta_motivo_detalle'=> $request->motivo_detalle, 
            'ventas_cliente_ruc' => $request->destinatario['ruc'],
            'venta_empresa_partida_ruc' => $request->datos_partida['ruc'], 
            'venta_llegada_direccion'=> $request->datos_llegada['direccion'],
            'venta_llegada_nro'=> $request->datos_llegada['nro'],
            'venta_llegada_zona'=> $request->datos_llegada['zona'],
            'venta_llegada_distrito'=> $request->datos_llegada['distrito'],
            'venta_llegada_provincia'=>$request->datos_llegada['provincia'],
            'venta_llegada_departamento'=> $request->datos_llegada['departamento'],
            'venta_contacto_nombre'=> $request->datos_llegada['contacto_nombre'],
            'venta_contacto_telefono'=> $request->datos_llegada['contacto_telefono'],
            'venta_contacto_correo'=> $request->datos_llegada['contacto_correo'],
            'venta_transportista_ruc'=> $request->transportista['ruc'],
            'venta_licencia_conductor'=> $request->transportista['licencia'],
            'venta_placa_carro'=> $request->transportista['placa_t'],
            'venta_paquete_cantidad_tipo'=> $request->paquete['cantidad_tipo'], 
            'venta_paquete_peso'=> $request->paquete['peso'],
            'venta_paquete_medidas'=> $request->paquete['medidas'],
            'venta_f_retorno' => $request->f_retorno,
            'venta_cantidad_total'=>$request->cantidad_total,
            'venta_peso_total' =>$request->peso_total,
            'venta_usuario_envia' => $request->usuario_envia['usuario_id']?$request->usuario_envia['usuario_id']:'',
            'venta_descripcion' => $request->descripcion,
            'venta_fecha' => date('Y-m-d'),
            'usuarios_usuario_id' => $request->usuario,
            'ventas_tipo_comprobante' =>'gr',
        ];

        DB::beginTransaction();
        try {

            $id = DB::table('ventas')
            ->insertGetId($data);
            // $correlativo =DB::table('ventas')
            //                 ->select('venta_correlativo')
            //                 ->where('ventas_id','=',$id)
            //                 ->first();

            foreach($request->materiales as $valor){
            $data_1 = [
                'dtventa_correlativo'=> '',
                'dtventa_codigo'=>$valor['codigo'],
                'dtventa_cantidad'=>$valor['cantidad'],
                'dtventa_um' =>$valor['um'],
                'dtventa_descripcion' =>$valor['descripcion'], 
                'dtventa_con_retorno' =>$valor['con_retorno'], 
                'dtventa_marca' => $valor['marca'],
                'dtventa_modelo' => $valor['modelo'],
                'dtventa_serie' => $valor['serie'],
                'dtventa_observacion' => $valor['observacion'], 
                'dtventa_peso' => $valor['peso'],
                'dtventa_update' => date('Y-m-d H:i:s'),
                'ventas_ventas_id' => $id,
            ];
            DB::table('dtventas')
            ->insert($data_1);
            // array_push($array,$data_1);
        }

            DB::commit();
            $message = 'Guardado.';
            $status = true;
        } catch (\Throwable $th) {
            DB::rollback();
            $message = $th;
            $status = false;
        }

        $response["status"] = $status;
        $response["message"] = $message;
        
        
        // $response = $request;
        return $response;
    }
}
