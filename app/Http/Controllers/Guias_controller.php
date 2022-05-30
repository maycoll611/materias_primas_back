<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Ventas_export;

class Guias_controller extends Controller

{
    public function export_get_guias(){
        return Excel::download(new Ventas_export, 'Descarga.xlsx');
    }
    public function anular_guia(Request $request){
        try{
            $affected = DB::table('ventas')
            ->where('venta_correlativo',$request->correlativo)
            ->update(['venta_anulado'=>1,
                        'venta_motivo_anulado'=>$request->motivo_descripcion]);
            $resp['affected'] = $affected;
            $resp['status'] = true;
        } catch (\Throwable $th){
            $resp['error'] = $th;
            $resp['status'] = false;
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
    public function generar_guia(Request $request){
    // generar el sigguiente correlativo
        $correlativo = DB::table('ventas')
                        ->max('venta_correlativo');
        $correlativo1 = intval(strval($correlativo + 1));

        if($request->id != ''){
            try{
                $affected = DB::table('ventas')
                ->where('venta_id',$request->id)
                ->update(['venta_correlativo'=>$correlativo1]);
                $message = $affected;
                $status = true;
            } catch (\Throwable $th){
                $message = $th;
                $status = false;
                
            } 
        }else{
            $data = [ 
                'venta_correlativo' => $correlativo1,
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
