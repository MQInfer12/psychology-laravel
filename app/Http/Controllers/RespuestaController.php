<?php

namespace App\Http\Controllers;

use App\Models\Respuesta;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RespuestaController extends Controller
{
    public function index()
    {
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
    }

    public function indexPdf()
    {
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

       
        //PDF
        $pdf = PDF::loadView('indexPdf', ['respuestas' => $respuestas]);
        return $pdf->stream();
    }

    public function myProffessors($email)
    {
        $proffessors = DB::select("SELECT d.id, d.nombre, d.email
                                   FROM users d, respuestas r, docente_tests dt
                                   WHERE r.email_user='$email'
                                   AND r.id_docente_test=dt.id
                                   AND dt.id_docente=d.id");
        return $proffessors;
    }

    public function getRespuestasByDocente($id)
    {
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

    public function store(Request $request)
    {
        $request->validate([
            'email_user' => 'required',
            'id_docente_test' => 'required',
            'puntuaciones' => 'required'
        ]);

        $respuesta = new Respuesta();
        $respuesta->email_user = $request->email_user;
        $respuesta->id_docente_test = $request->id_docente_test;
        $respuesta->estado = 1;
        $respuesta->save();

        $puntuaciones = $request->puntuaciones;
        foreach ($puntuaciones as $puntuacion) {
            $resultado = new Resultado();
            $resultado->id_respuesta = $respuesta->id;
            $resultado->id_puntuacion = $puntuacion;
            $resultado->save();
        }

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function assignAndReturnId(Request $request)
    {
        $request->validate([
            'email_user' => 'required',
            'id_docente_test' => 'required'
        ]);

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
            $resultado = new Resultado();
            $resultado->id_respuesta = $respuesta->id;
            $resultado->id_puntuacion = $puntuacion;
            $resultado->save();
        }

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function getIdTest($id)
    {
        $respuesta = Respuesta::find($id);
        $id_test = DB::select("SELECT id_test FROM docente_tests WHERE id='$respuesta->id_docente_test'");
        $respuesta->id_test = $id_test[0]->id_test;
        return $respuesta;
    }

    public function show($id)
    {
        //$respuesta = Respuesta::find($id);
        $respuesta = DB::select(
            "SELECT r.email_user, r.estado, r.id, r.id_docente_test, 
            u.nombre as nombre_user, u.edad, u.genero,
            t.nombre as nombre_test
            FROM respuestas r, users u, docente_tests dt, tests t
            WHERE r.id='$id' 
            AND r.email_user=u.email AND r.id_docente_test=dt.id AND dt.id_test=t.id"
        );

        $respuesta = $respuesta[0];

        $id_respuesta = $respuesta->id;
        $resultados = DB::select("SELECT * FROM resultados WHERE id_respuesta='$id_respuesta'");
        foreach ($resultados as $resultado) {
            $id_puntuacion = $resultado->id_puntuacion;
            $puntuacion = DB::select("SELECT * FROM puntuacions WHERE id='$id_puntuacion'");
            $resultado->puntuacion = $puntuacion;
        }
        $respuesta->resultados = $resultados;

        return $respuesta;
    }

    public function destroy($id)
    {
        return Respuesta::destroy($id);
    }
}
