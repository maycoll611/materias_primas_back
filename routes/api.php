<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\User_controller;
use App\Http\Controllers\Login_controller;
// Use App\Http\Controllers\Compras_controller;
Use App\Http\Controllers\Empresas_controller;
Use App\Http\Controllers\Guias_controller;
Use App\Http\Controllers\Usuarios_controller;
Use App\Http\Controllers\Preguntas_controller;
Use App\Http\Controllers\Inventarios_controller;
Use App\Http\Controllers\Checklist_controller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::resource('/login',Login_controller::class);
// Route::middleware(['prueba1'])->group(function () {
    
// });

Route::get('/login1',[Login_controller::class,'index']);
Route::get('/prueba_dtcompra',[Compras_controller::class,'guardar_compra_1']);
Route::get('/compras_listado1',[Compras_controller::class,'index']);
Route::get('/Empresas',[Empresas_controller::class,'index']);
Route::post('/nueva_empresa',[Empresas_controller::class,'store']);
Route::post('/prueba_sunat',[Empresas_controller::class,'prueba_sunat']);
Route::get('/transportistas',[Empresas_controller::class,'get_transportistas']);
Route::post('/conductores_carros',[Empresas_controller::class,'get_conductores_carros']);
Route::post('/guardar_carro_nuevo',[Empresas_controller::class,'guardar_carro_nuevo']);
Route::post('/guardar_conductor_nuevo',[Empresas_controller::class,'guardar_conductor_nuevo']);
Route::post('/guardar_documento',[Guias_controller::class,'guardar_documento']);
Route::post('/generar_guia',[Guias_controller::class,'generar_guia']);
Route::post('/get_guias',[Guias_controller::class,'get_guias']);
Route::get('/export_get_guias',[Guias_controller::class,'export_get_guias']);
Route::post('/get_guias_id',[Guias_controller::class,'get_guias_id']);
Route::post('/get_usuarios',[Usuarios_controller::class,'get_usuarios']);
Route::post('/anular_guia',[Guias_controller::class,'anular_guia']);

//rutas para equipo_movil get_checklist_vacio
Route::post('/login',[Login_controller::class,'verificar']);
Route::post('/get_checklist_vacio',[Checklist_controller::class,'get_checklist_vacio']);
Route::post('/get_checklist_id',[Checklist_controller::class,'get_checklist_id']);
Route::post('/get_equipos',[Checklist_controller::class,'get_equipos']);
Route::post('/guardar_checklist',[Checklist_controller::class,'guardar_checklist']);
Route::post('/get_checklist',[Checklist_controller::class,'get_checklist']);
Route::get('/export_get_checklist',[Checklist_controller::class,'export_get_checklist']);
Route::get('/consultar_dni',[Checklist_controller::class,'consultar_dni']);
Route::post('/autorizar_checklist',[Checklist_controller::class,'autorizar_checklist']);
Route::post('/get_categorias',[Checklist_controller::class,'get_categorias']);
Route::post('/guardar_configuracion',[Checklist_controller::class,'guardar_configuracion']);
Route::post('/get_config_categorias_elementos',[Checklist_controller::class,'get_config_categorias_elementos']);
Route::post('/get_config_categoria_id',[Checklist_controller::class,'get_config_categoria_id']);
Route::post('/buscar_usuario_dni',[Checklist_controller::class,'buscar_usuario_dni']);
Route::post('/guardar_configuracion_usuario',[Checklist_controller::class,'guardar_configuracion_usuario']);
Route::post('/login_qr',[Login_controller::class,'verificar_codeqr']);
Route::post('/guardar_km_final',[Checklist_controller::class,'guardar_km_final']);

Route::post('/login_1',[Login_controller::class,'verificar_1']);
Route::post('/crear_usuario',[Login_controller::class,'crear_usuario']);

Route::post('/cerrar_sesion',[Login_controller::class,'cerrar_sesion']);
Route::get('/get_preguntas',[Preguntas_controller::class,'get_preguntas']);
Route::post('/asignar_transportista',[Guias_controller::class,'asignar_transportista']);

Route::post('/consultar_codigo',[Inventarios_controller::class,'consultar_codigo']);
Route::post('/get_almacenes_racks',[Inventarios_controller::class,'get_almacenes_racks']);


// Route::middleware('prueba1:api')->get('/usuarios',[User_controller::class, 'index']);

// Route::middleware(['prueba1'])->group(function () {
//     //Rutas  de compras
//     Route::post('/cerrar_sesion',[Login_controller::class,'cerrar_sesion']);

//     Route::post('/get_guias',[Guias_controller::class,'get_guias']);
//     Route::get('/compras_listado',[Compras_controller::class,'index']);
//     Route::post('/compras_detalle_id',[Compras_controller::class,'detalle_compra_id']); 
//     Route::get('/nueva_compra',[Compras_controller::class,'create']);
//     Route::post('/guardar_compra',[Compras_controller::class,'guardar_compra']);
//     Route::post('/anular_compra',[Compras_controller::class,'anular_compra']);
//     Route::get('/compras_reportes',[Compras_controller::class,'compras_reportes']);
//     //Rutas de personal

// });