<?php

namespace App\Http\Controllers;

use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeneficiarioDocenteController extends Controller
{
    public function showTestToProffessor($id) //MOSTRAR TESTS A LOS PROFESORES
    {
        $tests = DB::select("SELECT dt.id, dt.id_docente, dt.id_test, t.nombre, t.tiempo, t.descripcion, t.autor
                             from docente_tests as dt, tests as t 
                             where dt.id_docente=$id and dt.id_test=t.id");

        foreach($tests as $test) {
            $id_dt = $test->id;
            $usuarios = DB::select("SELECT u.id, u.nombre, u.perfil
                                    FROM users as u, respuestas as r, docente_tests as dt
                                    WHERE r.email_user = u.email AND r.id_docente_test=dt.id AND dt.id='$id_dt'
                                    LIMIT 10");
            foreach($usuarios as $usuario) {
                if($usuario->perfil != null) {
                    $usuario->perfil = "pendiente...";
                }
            }
            $test->usuarios = $usuarios;
        }  

        return response()->json($tests);
    }

    public function getBenefAssigning($id) //BENEFICIARIOS ASIGNADOS PARA DESASIGNARLOS
    {
        $getIdDocente = DB::select("SELECT bdt.id, bdt.email_user, bdt.id_docente_test, u.email, 
                                    u.id as id_user, u.nombre as nombre_user, s.nombre as sede, u.perfil
                                    from respuestas as bdt, users u, sedes as s
                                    where bdt.email_user=u.email and s.id=u.id_sede and bdt.id_docente_test=$id");

        foreach($getIdDocente as $benef) {
            if($benef->perfil != null) {
                $benef->perfil = "pendiente...";
            }
        }
        return response()->json($getIdDocente);
    }

    public function getBenefNotAssigning($id) //BENEFICIARIOS NO ASIGNADOS PARA ASIGNARLOS
    {
        $getIdBenef = DB::select("SELECT * from respuestas where id_docente_test=$id");
        $getIdsany = true;
        $condition = "and email !=";
        
        foreach ($getIdBenef as $idBenef) {
            $getIdsany = false;
            if ($idBenef != end($getIdBenef)) {
                $condition = $condition . " '" . $idBenef->email_user . "' " . $condition;
            } else {
                $condition = $condition . " '" . $idBenef->email_user . "'";
            }
        }

        if ($getIdsany) {
            $getBenefNotAssigning = DB::select("SELECT u.id, u.nombre as nombre_usuario, u.email, u.estado, u.perfil
            from users as u where u.id_rol=1");
            foreach($getBenefNotAssigning as $benef) {
                if($benef->perfil != null) {
                    $benef->perfil = "pendiente...";
                }
            }
        } else {
            $getBenefNotAssigning = DB::select("SELECT u.id, u.nombre as nombre_usuario, u.email, u.estado, u.perfil
            from users as u where u.id_rol=1 " . $condition);
            foreach($getBenefNotAssigning as $benef) {
                if($benef->perfil != null) {
                    $benef->perfil = "pendiente...";
                }
            }
        }

        return response()->json($getBenefNotAssigning);
    }

   public function assignBenefToTest(Request $request)
    {
        $request->validate([
            "objeto" => "required",
            "id_docente_test" => "required"
        ]);

        $objeto = $request->objeto;

        foreach ($objeto as $valor) {
            $benefTest = new Respuesta();
            $benefTest->email_user = $valor;
            $benefTest->id_docente_test = $request->id_docente_test;
            $benefTest->estado = 0;
            $benefTest->save();
        }

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function deleteBenefAssigning(Request $request)
    {
        $request->validate([
            "objeto" => "required",
            "id_docente_test" => "required"
        ]);

        $objeto = $request->objeto;

        foreach ($objeto as $valor) {
            DB::delete("DELETE from respuestas where email_user='$valor' AND id_docente_test='$request->id_docente_test'");
            $exists = DB::select("SELECT id FROM respuestas WHERE email_user='$valor'");
            dd($exists);
            $valor->exists = count($exists);
        }
        return response()->json(["msg" => "se ha eliminado", "exists" => $objeto], 200);
    }
    
    public function showTestToBenef($id)
    {
        $email = DB::select("SELECT email FROM users WHERE id='$id'");
        $email = $email[0]->email;
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
    }
}
