<?php

namespace App\Http\Controllers;

use App\Models\Respuesta;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RespuestaController extends Controller
{
    public function getRespuestasByUser($id)
    {
        $user = DB::select("SELECT id_rol FROM users WHERE id='$id'");
        $rol = $user[0]->id_rol;

        if($rol == 3) {
            $respuestas = DB::select(
                "SELECT r.id, r.email_user, r.id_docente_test, r.estado, 
                        u.nombre as nombre_user,
                        d.nombre as nombre_docente, d.email as email_docente,
                        t.id as id_test, t.nombre as nombre_test, t.descripcion,
                        CASE WHEN r.estado=1 THEN 'Recibido' WHEN r.estado=0 THEN 'Pendiente' END
                FROM respuestas as r, docente_tests as dt, users as d, users as u, tests as t
                WHERE dt.id=r.id_docente_test AND d.id=dt.id_docente AND u.email=r.email_user AND t.id=dt.id_test
                ORDER BY id"
            );
    
            foreach ($respuestas as $respuesta) {
                //CONSEGUIR PUNTUACION TOTAL
                $secciones = DB::select("SELECT id FROM seccions WHERE id_test='$respuesta->id_test'");
                $total = 0;
                foreach ($secciones as $seccion) {
                    $preguntas = DB::select("SELECT id FROM preguntas WHERE id_seccion='$seccion->id'");
                    foreach ($preguntas as $pregunta) {
                        $max = DB::select("SELECT MAX(asignado) FROM puntuacions WHERE id_pregunta='$pregunta->id'");
                        $total = $total + $max[0]->max;
                    }
                }
                $respuesta->total = $total;
    
                //CONSEGUIR PUNTUACION DEL TEST
                $resultados = DB::select("SELECT * FROM resultados WHERE id_respuesta='$respuesta->id'");
                $cont = 0;
                foreach ($resultados as $resultado) {
                    $puntuacion = DB::select("SELECT asignado FROM puntuacions WHERE id='$resultado->id_puntuacion'");
                    $cont = $cont + $puntuacion[0]->asignado;
                }
                $respuesta->puntuacion = $cont;
            }
    
            return $respuestas;
        } else {
            $respuestas = DB::select(
                "SELECT r.id, r.email_user, r.id_docente_test, r.estado, 
                        u.nombre as nombre_user,
                        d.nombre as nombre_docente, d.email as email_docente,
                        t.id as id_test, t.nombre as nombre_test, t.descripcion,
                        CASE WHEN r.estado=1 THEN 'Recibido' WHEN r.estado=0 THEN 'Pendiente' END
                FROM respuestas as r, docente_tests as dt, users as d, users as u, tests as t
                WHERE dt.id=r.id_docente_test AND d.id=dt.id_docente AND u.email=r.email_user AND t.id=dt.id_test AND dt.id_docente='$id'
                ORDER BY id"
            );
    
            foreach ($respuestas as $respuesta) {
                //CONSEGUIR PUNTUACION TOTAL
                $secciones = DB::select("SELECT id FROM seccions WHERE id_test='$respuesta->id_test'");
                $total = 0;
                foreach ($secciones as $seccion) {
                    $preguntas = DB::select("SELECT id FROM preguntas WHERE id_seccion='$seccion->id'");
                    foreach ($preguntas as $pregunta) {
                        $max = DB::select("SELECT MAX(asignado) FROM puntuacions WHERE id_pregunta='$pregunta->id'");
                        $total = $total + $max[0]->max;
                    }
                }
                $respuesta->total = $total;
    
                //CONSEGUIR PUNTUACION DEL TEST
                $resultados = DB::select("SELECT * FROM resultados WHERE id_respuesta='$respuesta->id'");
                $cont = 0;
                foreach ($resultados as $resultado) {
                    $puntuacion = DB::select("SELECT asignado FROM puntuacions WHERE id='$resultado->id_puntuacion'");
                    $cont = $cont + $puntuacion[0]->asignado;
                }
                $respuesta->puntuacion = $cont;
            }
    
            return $respuestas;
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'email_user' => 'required',
            'id_docente_test' => 'required'
        ]);

        $existe = DB::select(
            "SELECT id FROM respuestas WHERE email_user='$request->email_user' AND id_docente_test='$request->id_docente_test'"
        );
        if($existe != []) {
            return response()->json(["mensaje" => "ya existe la respuesta", "id" => $existe[0]->id], 201);
        }

        $respuesta = new Respuesta();
        $respuesta->email_user = $request->email_user;
        $respuesta->id_docente_test = $request->id_docente_test;
        $respuesta->estado = 0;
        $respuesta->save();

        return response()->json(["mensaje" => "se guardo correctamente", "id" => $respuesta->id], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'puntuaciones' => 'required'
        ]);

        $respuesta = Respuesta::find($id);
        $respuesta->estado = 1;
        $respuesta->save();

        $puntuaciones = $request->puntuaciones;
        foreach ($puntuaciones as $puntuacion) {
            if(gettype($puntuacion) == "array") {
                foreach($puntuacion as $pt) {
                    $resultado = new Resultado();
                    $resultado->id_respuesta = $respuesta->id;
                    $resultado->id_puntuacion = $pt;
                    $resultado->save();
                }
            } else {
                $resultado = new Resultado();
                $resultado->id_respuesta = $respuesta->id;
                $resultado->id_puntuacion = $puntuacion;
                $resultado->save();
            }
        }

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function show($id)
    {
        $respuesta = DB::select(
            "SELECT r.email_user, r.estado, r.id, r.id_docente_test, 
            u.nombre as nombre_user, u.edad, u.genero,
            t.nombre as nombre_test, t.id as id_test
            FROM respuestas r, users u, docente_tests dt, tests t
            WHERE r.id='$id' 
            AND r.email_user=u.email AND r.id_docente_test=dt.id AND dt.id_test=t.id"
        )[0];

        //CONSEGUIR TEST
        $test = DB::select("SELECT * FROM tests WHERE id='$respuesta->id_test'");
        $test = $test[0];

        $secciones = DB::select("SELECT id, multimarcado, nombre FROM seccions WHERE id_test='$test->id' ORDER BY orden");
        $idsPreguntas = [];
        foreach($secciones as $seccion) {
            $preguntas =DB::select("SELECT id, descripcion FROM preguntas WHERE id_seccion='$seccion->id' ORDER BY id");
            $reactivos =DB::select("SELECT id, descripcion FROM reactivos WHERE id_seccion='$seccion->id' ORDER BY id");
            foreach($preguntas as $pregunta) {
                $idsPreguntas[] = $pregunta->id;
                $puntuaciones = DB::select("SELECT id, id_reactivo, asignado FROM puntuacions WHERE id_pregunta='$pregunta->id' ORDER BY id_reactivo");
                $pregunta->puntuaciones = $puntuaciones;
            }
            $seccion->preguntas = $preguntas;
            $seccion->reactivos = $reactivos;
        }

        $test->secciones = $secciones;

        $resultados = DB::select(
            "SELECT r.id, r.id_respuesta, r.id_puntuacion, p.id_pregunta, p.id_reactivo, p.asignado
            FROM resultados as r, puntuacions as p
            WHERE r.id_respuesta='$respuesta->id' AND r.id_puntuacion=p.id"
        );

        //IDENTIFICAR PREGUNTAS SIN RESPUESTA PARA PONERLES 0 DE ASIGNADO Y REPETIDAS PARA SUMAR SUS PUNTUACIONES
        $idsRepetidos = [];
        $idsPreguntasInResultados = [];
        foreach($resultados as $resultado) 
        {
            if(in_array($resultado->id_pregunta, $idsPreguntasInResultados) && !in_array($resultado->id_pregunta, $idsRepetidos)) {
                $idsRepetidos[] = $resultado->id_pregunta;
            }
            $idsPreguntasInResultados[] = $resultado->id_pregunta;
        }
        $repetidosPorId = [];
        foreach($resultados as $resultado) {
            if(in_array($resultado->id_pregunta, $idsRepetidos)) {
                $repetidosPorId[$resultado->id_pregunta][] = $resultado;
            }
        }
        foreach($repetidosPorId as $idRepetido) {
            $asignados = array_column($idRepetido, "asignado");
            foreach($idRepetido as $resultado) {
                $resultado->asignado = array_sum($asignados);
            }
        }

        foreach($idsPreguntas as $idPregunta) {
            if(!in_array($idPregunta, $idsPreguntasInResultados)) {
                $resultados[] = array(
                    "id_pregunta" => $idPregunta,
                    "asignado" => 0
                );
            }
        }

        $respuesta->resultados = $resultados;
        $respuesta->test = $test;

        return $respuesta;
    }

    public function destroy($id)
    {
        return Respuesta::destroy($id);
    }
}
