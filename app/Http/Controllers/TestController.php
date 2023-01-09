<?php

namespace App\Http\Controllers;

use App\Models\PreguntaDimension;
use App\Models\Test;
use App\Traits\PuntuacionesNaturales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    use PuntuacionesNaturales;

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'tiempo' => 'required',
        ]);

        $test = new Test();
        $test->nombre = $request->nombre;
        $test->descripcion = $request->descripcion;
        $test->autor = "Admin";
        $test->tiempo = $request->tiempo;
        $test->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function getTestToEdit($id)
    {
        $test = DB::select(
            "SELECT *
            FROM tests
            WHERE id='$id'"
        )[0];

        $dimensiones = DB::select("SELECT * FROM dimensions WHERE id_test='$id' ORDER BY id");
        $test->dimensiones = $dimensiones;
        foreach($dimensiones as $dimension) {
            $preguntasPorDimension = PreguntaDimension::where('id_dimension',$dimension->id)->pluck('id_pregunta')->toArray();
            $dimension->preguntas = $preguntasPorDimension;

            //CALCULAR ESCALA NATURAL DE CADA DIMENSION
            $natural = $this->getPuntuacionNatural($dimension->id);
            $dimension->escalas = [array("nombre" => "Natural", "valores" => $natural)];
        }

        $secciones = DB::select(
            "SELECT *
            FROM seccions
            WHERE id_test='$id'
            ORDER BY orden"
        );
        foreach($secciones as $seccion) {
            $idSeccion = $seccion->id;
            $seccion->preguntas = DB::select("SELECT * FROM preguntas WHERE id_seccion='$idSeccion' ORDER BY id");
            $seccion->reactivos = DB::select("SELECT * FROM reactivos WHERE id_seccion='$idSeccion' ORDER BY id");
            $seccion->puntuaciones = DB::select(
                "SELECT DISTINCT on (pu.id) pu.id, pu.id_pregunta, pu.id_reactivo, pu.asignado 
                FROM puntuacions as pu, preguntas as pr, reactivos as r
                WHERE (pu.id_pregunta=pr.id AND pr.id_seccion='$id') OR (pu.id_reactivo=r.id AND r.id_seccion='$idSeccion')"
            );
        }
        $test->secciones = $secciones;

        return response()->json($test);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
            'tiempo' => 'required',
        ]);

        $test = Test::findOrFail($id);
        $test->nombre = $request->nombre;
        $test->descripcion = $request->descripcion;
        $test->autor = "Admin";
        $test->tiempo = $request->tiempo;
        $test->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function destroy($id)
    {
        return Test::destroy($id);
    }

    public function getFullTest($idUser, $idRespuestaTest)
    {
        $user = DB::select("SELECT id_rol FROM users WHERE id='$idUser'");
        $rol = $user[0]->id_rol;

        if($rol == 1) {
            $respuesta = DB::select(" 
                SELECT r.estado, dt.id_test, d.email as email_docente
                FROM respuestas as r, docente_tests as dt, users as d
                WHERE r.id='$idRespuestaTest' AND r.id_docente_test=dt.id AND dt.id_docente=d.id
            ");
            $idTest = $respuesta[0]->id_test;
        } else {
            $idTest = $idRespuestaTest;
        }

        $test = DB::select("SELECT * FROM tests WHERE id='$idTest'");
        $test = $test[0];

        $caracteristicas = DB::select("SELECT * FROM caracteristicas WHERE id_test='$idTest' ORDER BY id");
        $test->caracteristicas = $caracteristicas;

        if($rol == 1) {
            $test->email_docente = $respuesta[0]->email_docente;
            $test->estado = $respuesta[0]->estado;
        }

        $secciones = DB::select("SELECT id, multimarcado, vacio FROM seccions WHERE id_test='$idTest' ORDER BY orden");
        foreach($secciones as $seccion) {
            $id_seccion = $seccion->id;
            $preguntas =DB::select("SELECT id, descripcion FROM preguntas WHERE id_seccion='$id_seccion' ORDER BY id");
            $reactivos =DB::select("SELECT id, descripcion FROM reactivos WHERE id_seccion='$id_seccion' ORDER BY id");
            foreach($preguntas as $pregunta) {
                $id_pregunta = $pregunta->id;
                $puntuaciones = DB::select("SELECT id, id_reactivo, asignado FROM puntuacions WHERE id_pregunta='$id_pregunta' ORDER BY id_reactivo");
                $pregunta->puntuaciones = $puntuaciones;
            }
            $seccion->preguntas = $preguntas;
            $seccion->reactivos = $reactivos;
        }

        $test->secciones = $secciones;
        return $test;
    }

    public function getTestsForUser($id)
    {
        $data = DB::select("SELECT id_rol, email FROM users WHERE id='$id'");
        $rol = $data[0]->id_rol;

        switch($rol) {
            case 1:
                $email = $data[0]->email;
                $respuestas = DB::select("SELECT * FROM respuestas WHERE email_user='$email'");

                foreach($respuestas as $respuesta) {
                    $docente_test = DB::select("SELECT t.id, t.nombre, t.descripcion, t.autor, t.tiempo, u.nombre as nombre_docente
                                                FROM tests as t, users as u, docente_tests as dt
                                                WHERE t.id=dt.id_test AND u.id=dt.id_docente AND dt.id='$respuesta->id_docente_test'");
                    $respuesta->nombre_docente = $docente_test[0]->nombre_docente;
                    $respuesta->id_test = $docente_test[0]->id;
                    $respuesta->nombre_test = $docente_test[0]->nombre;
                    $respuesta->descripcion_test = $docente_test[0]->descripcion;
                    $respuesta->autor_test = $docente_test[0]->autor;
                    $respuesta->tiempo_test = $docente_test[0]->tiempo;
                }

                return $respuestas;
            case 2:
                $tests = DB::select("SELECT dt.id, dt.id_docente, dt.id_test, t.nombre, t.tiempo, t.descripcion, t.autor
                             from docente_tests as dt, tests as t 
                             where dt.id_docente=$id and dt.id_test=t.id");

                foreach($tests as $test) {
                    $id_dt = $test->id;
                    $usuarios = DB::select("SELECT u.id, u.nombre, u.perfil
                                            FROM users as u, respuestas as r, docente_tests as dt
                                            WHERE r.email_user = u.email AND r.id_docente_test=dt.id AND dt.id='$id_dt'
                                            LIMIT 10");
                    $test->usuarios = $usuarios;
                }  

                return response()->json($tests);
            case 3:
                $tests = DB::select("SELECT *
                             FROM tests
                             ORDER BY id");

                foreach($tests as $test) {
                    $id_test = $test->id;
                    $usuarios = DB::select("SELECT u.id, u.nombre, u.perfil
                                            FROM users as u, docente_tests as dt
                                            WHERE dt.id_test = '$id_test' AND dt.id_docente = u.id
                                            LIMIT 10");
                    $test->usuarios = $usuarios;
                }        

                return response()->json($tests);
        }
    }
}
