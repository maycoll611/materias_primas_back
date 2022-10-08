<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Ventas_export;
use Http;

class Checklist_controller extends Controller

{
    public function consultar_dni(Request $request){

        $resp1 = Http::get('https://api.apis.net.pe/v1/dni?numero='.$request->dni);
            $resp["datos"] = $resp1->json();
            return ($resp);
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
                ->select('*')
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
                "equipos.equipo_tipo");
        
        
      
        $resp=$resp->where('checklist.checklist_id',$request->checklist_id);
        
        $resp = $resp->get();
        $resp1 = [];

        return json_decode($resp);
    }
    public function get_checklist_vacio(Request $request){
        $resp = DB::table('elementos_checklist')
                    ->select('*')
                    ->get();
        return $resp;
    }
    public function get_checklist(Request $request){
        $resp=DB::table('checklist')
        ->join('equipos','checklist.checklist_equipo','=','equipos.equipo_id')
        ->join('usuarios','checklist.checklis_usuario_id','=','usuarios.usuario_id')
        ->select(
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
                "equipos.equipo_tipo")
        ->orderBy('checklist.checklist_id', 'desc');
        
        if(isset($request->desde)&&isset($request->hasta)){
            $resp=$resp->whereBetween('checklist_fecha_hora',[$request->desde,$request->hasta.' 23:59:00']);
        }
        if($request->usuario_cargo != 'administrador'){
            $resp=$resp->where('checklist.checklis_usuario_id',$request->usuario_id);
        }
        $resp = $resp->get();

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

            $data = [ 
                
                "checklis_usuario_id"=> $request->cabecera['usuario_id'],
                "checklist_equipo"=> $request->cabecera['equipo_id'] ,
                "checklist_fecha_hora"=> $request->cabecera['fecha'] ,
                "checklist_kilom_inicial"=> $request->cabecera['equipo_kminicial'] ,
                "checklist_kilom_final"=> $request->cabecera['equipo_kmfinal'] ,
                "checklist_turno"=> $request->cabecera['turno'] ,
                "checklist_autorizado" => false,
                "checklist_usuario_id_autoriza"=> '',
                "checklist_estado" => true,
                "check_1"  =>empty( $request->cuerpo[0]['valor'])?'': $request->cuerpo[0]['valor'],
                "check_2"  =>empty( $request->cuerpo[1]['valor'])?'': $request->cuerpo[1]['valor'],
                "check_3"  =>empty( $request->cuerpo[2]['valor'])?'': $request->cuerpo[2]['valor'],
                "check_4"  =>empty( $request->cuerpo[3]['valor'])?'': $request->cuerpo[3]['valor'],
                "check_5"  =>empty( $request->cuerpo[4]['valor'])?'': $request->cuerpo[4]['valor'],
                "check_6"  =>empty( $request->cuerpo[5]['valor'])?'': $request->cuerpo[5]['valor'],
                "check_7"  =>empty( $request->cuerpo[6]['valor'])?'': $request->cuerpo[6]['valor'],
                "check_8"  =>empty( $request->cuerpo[7]['valor'])?'': $request->cuerpo[7]['valor'],
                "check_9"  =>empty( $request->cuerpo[8]['valor'])?'': $request->cuerpo[8]['valor'],
                "check_10"  =>empty($request->cuerpo[9]['valor'])?'':$request->cuerpo[9]['valor'],
                "check_11"  =>empty($request->cuerpo[10]['valor'])?'':$request->cuerpo[10]['valor'],
                "check_12"  =>empty($request->cuerpo[11]['valor'])?'':$request->cuerpo[11]['valor'],
                "check_13"  =>empty($request->cuerpo[12]['valor'])?'':$request->cuerpo[12]['valor'],
                "check_14"  =>empty($request->cuerpo[13]['valor'])?'':$request->cuerpo[13]['valor'],
                "check_15"  =>empty($request->cuerpo[14]['valor'])?'':$request->cuerpo[14]['valor'],
                "check_16"  =>empty($request->cuerpo[15]['valor'])?'':$request->cuerpo[15]['valor'],
                "check_17"  =>empty($request->cuerpo[16]['valor'])?'':$request->cuerpo[16]['valor'],
                "check_18"  =>empty($request->cuerpo[17]['valor'])?'':$request->cuerpo[17]['valor'],
                "check_19"  =>empty($request->cuerpo[18]['valor'])?'':$request->cuerpo[18]['valor'],
                "check_20"  =>empty($request->cuerpo[19]['valor'])?'':$request->cuerpo[19]['valor'],
                "check_21"  =>empty($request->cuerpo[20]['valor'])?'':$request->cuerpo[20]['valor'],
                "check_22"  =>empty($request->cuerpo[21]['valor'])?'':$request->cuerpo[21]['valor'],
                "check_23"  =>empty($request->cuerpo[22]['valor'])?'':$request->cuerpo[22]['valor'],
                "check_24"  =>empty($request->cuerpo[23]['valor']) ? '':$request->cuerpo[23]['valor'],
                "check_25"  =>empty($request->cuerpo[24]['valor']) ? '':$request->cuerpo[24]['valor'],
                "check_26"  =>empty($request->cuerpo[25]['valor']) ? '':$request->cuerpo[25]['valor'],
                "check_27"  =>empty($request->cuerpo[26]['valor']) ? '':$request->cuerpo[26]['valor'],
                "check_28"  =>empty($request->cuerpo[27]['valor']) ? '':$request->cuerpo[27]['valor'],
                "check_29"  =>empty($request->cuerpo[28]['valor']) ? '':$request->cuerpo[28]['valor'],
                "check_30"  =>empty($request->cuerpo[29]['valor']) ? '':$request->cuerpo[29]['valor'],
                "check_31"  =>empty($request->cuerpo[30]['valor']) ? '':$request->cuerpo[30]['valor'],
                "check_32"  =>empty($request->cuerpo[31]['valor']) ? '':$request->cuerpo[31]['valor'],
                "check_33"  =>empty($request->cuerpo[32]['valor']) ? '':$request->cuerpo[32]['valor'],
                "check_34"  =>empty($request->cuerpo[33]['valor']) ? '':$request->cuerpo[33]['valor'],
                "check_35"  =>empty($request->cuerpo[34]['valor']) ? '':$request->cuerpo[34]['valor'],
                "check_36"  =>empty($request->cuerpo[35]['valor']) ? '':$request->cuerpo[35]['valor'],
                "check_37"  =>empty($request->cuerpo[36]['valor']) ? '':$request->cuerpo[36]['valor'],
                "check_38"  =>empty($request->cuerpo[37]['valor']) ? '':$request->cuerpo[37]['valor'],
                "check_39"  =>empty($request->cuerpo[38]['valor']) ? '':$request->cuerpo[38]['valor'],
                "check_40"  =>empty($request->cuerpo[39]['valor']) ? '':$request->cuerpo[39]['valor'],
                "check_41"  =>empty($request->cuerpo[40]['valor']) ? '':$request->cuerpo[40]['valor'],
                "check_42"  =>empty($request->cuerpo[41]['valor']) ? '':$request->cuerpo[41]['valor'],
                "check_43"  =>empty($request->cuerpo[42]['valor']) ? '':$request->cuerpo[42]['valor'],
                "check_44"  =>empty($request->cuerpo[43]['valor']) ? '':$request->cuerpo[43]['valor'],
                "check_45"  =>empty($request->cuerpo[44]['valor'])? '': $request->cuerpo[44]['valor'],
                "check_46"  =>empty($request->cuerpo[45]['valor'])? '': $request->cuerpo[45]['valor'],
                "check_47"  =>empty($request->cuerpo[46]['valor'])? '': $request->cuerpo[46]['valor'],
                "check_48"  =>empty($request->cuerpo[47]['valor'])? '': $request->cuerpo[47]['valor'],
                "check_49"  =>empty($request->cuerpo[48]['valor'])? '': $request->cuerpo[48]['valor'],
                "check_50"  =>empty($request->cuerpo[49]['valor'])? '': $request->cuerpo[49]['valor'],
                "check_51"  =>empty($request->cuerpo[50]['valor'])? '': $request->cuerpo[50]['valor'],
                "check_52"  =>empty($request->cuerpo[51]['valor'])? '': $request->cuerpo[51]['valor'],
                "check_53"  =>empty($request->cuerpo[52]['valor'])? '': $request->cuerpo[52]['valor'],
                "check_54"  =>empty($request->cuerpo[53]['valor'])? '': $request->cuerpo[53]['valor'],
                "check_55"  =>empty($request->cuerpo[54]['valor'])? '': $request->cuerpo[54]['valor'],
                "check_56"  =>empty($request->cuerpo[55]['valor'])? '': $request->cuerpo[55]['valor'],
                "check_57"  =>empty($request->cuerpo[56]['valor'])? '': $request->cuerpo[56]['valor'],
                "check_58"  =>empty($request->cuerpo[57]['valor'])? '': $request->cuerpo[57]['valor'],
                "check_59"  =>empty($request->cuerpo[58]['valor'])? '': $request->cuerpo[58]['valor'],
                "check_60"  =>empty($request->cuerpo[59]['valor'])? '': $request->cuerpo[59]['valor'],
                "check_61"  =>empty($request->cuerpo[60]['valor'])? '': $request->cuerpo[60]['valor'],
                "check_62"  =>empty($request->cuerpo[61]['valor'])? '': $request->cuerpo[61]['valor'],
                "check_63"  =>empty($request->cuerpo[62]['valor'])?'':$request->cuerpo[62]['valor']
            ];
    
            DB::beginTransaction();
            try {
    
                $id = DB::table('checklist')
                ->insertGetId($data);
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
            // return $request;
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
