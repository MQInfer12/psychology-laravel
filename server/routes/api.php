<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GrupoBeneficiarioController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'api'], function(){
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/me', [AuthController::class, 'me']);

    //USER ROUTES
    Route::apiResource("user", UserController::class);
    Route::put('user/able/{id}', [UserController::class, 'able']);

    //GRUPO ROUTES
    Route::apiResource("grupo", GrupoController::class);
    Route::get('grupo/docente/{id_docente}', [GrupoController::class, 'indexDocente']);
    Route::put('grupo/able/{id}', [GrupoController::class, 'able']);
    
    //GRUPOBENEFICIARIO ROUTES
    Route::apiResource("grupobeneficiario", GrupoBeneficiarioController::class);

    //TEST ROUTES
    Route::apiResource("test", TestController::class);

    //HORARIOS
    Route::apiResource("horario", HorarioController::class);
    Route::get('horario/show/{id_docente}', [HorarioController::class, 'showById']);

});