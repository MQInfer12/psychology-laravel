<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\DocenteTestController;
use App\Http\Controllers\GrupoBeneficiarioController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\PuntuacionController;
use App\Http\Controllers\ReactivoController;
use App\Http\Controllers\SeccionController;
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
    Route::get('user/getProfessors/{id}', [UserController::class, 'getProfessor']);

    //GRUPO ROUTES
    Route::apiResource("grupo", GrupoController::class);
    Route::get('grupo/docente/{id_docente}', [GrupoController::class, 'indexDocente']);
    Route::put('grupo/able/{id}', [GrupoController::class, 'able']);
    
    //GRUPOBENEFICIARIO ROUTES
    Route::apiResource("grupobeneficiario", GrupoBeneficiarioController::class);

    //TEST ROUTES
    Route::apiResource("test", TestController::class);
    Route::post("test/assignateProfessor", [DocenteTestController::class, 'store']);
    
    //SECCIONES ROUTES
    Route::apiResource("seccion", SeccionController::class);
    Route::get("seccion/test/{idTest}", [SeccionController::class, 'seccionByTest']);
    
    //PREGUNTAS ROUTES
    Route::apiResource("pregunta", PreguntaController::class);
    Route::get("pregunta/seccion/{idSeccion}", [PreguntaController::class, 'preguntasBySeccion']);
    Route::post("pregunta/destroy", [PreguntaController::class, 'massDestroy']);
    
    //SECCIONES ROUTES
    Route::apiResource("reactivo", ReactivoController::class);
    Route::get("reactivo/seccion/{idSeccion}", [ReactivoController::class, 'reactivosBySeccion']);

    //PUNTUACIONES ROUTES
    Route::apiResource("puntuacion", PuntuacionController::class);
    Route::post("puntuacion/reactivo", [PuntuacionController::class, 'puntuacionesByReactivos']);

    //HORARIOS
    Route::apiResource("horario", HorarioController::class);
    Route::get('horario/show/{id_docente}', [HorarioController::class, 'showById']);
    Route::get('horario/showWho/{id_docente}', [HorarioController::class, 'showWhoHaveDateTheProfessor']);

    //Citas
 /*    Route::apiResource("cita", CitaController::class); */
    Route::put('cita/schedule/{id_schedule}', [CitaController::class, 'scheduleAppointment']);
    Route::get('cita/schedule/{id_user}', [CitaController::class, 'getAppointmentsSchedule']);
    Route::get('cita/allschedule', [CitaController::class, 'allAppointmentsAvailables']);
    Route::put('cita/allschedule/{idHorario}/{idCita}', [CitaController::class, 'cancelAppointment']);


});