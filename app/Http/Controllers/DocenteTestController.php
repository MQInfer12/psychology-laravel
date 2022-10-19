<?php

namespace App\Http\Controllers;

use App\Models\DocenteTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocenteTestController extends Controller
{
    public function index($id)
    {
        $showTests = DB::select(
            "SELECT dt.id, u.email, u.perfil,
                    te.id as id_test, u.id as id_usuario
            from docente_tests dt, users u, tests te
            where dt.id_docente=u.id and dt.id_test=te.id and dt.id_test=$id
        ");

        foreach ($showTests as $user) {
            if ($user->perfil != null) {
                $user->perfil = stream_get_contents($user->perfil);
            }
        }
        return response()->json($showTests);
    }

    public function show($id)
    {
        return DocenteTest::find($id);
    }

    public function getProfessorNotAssigning($id)
    {
        $getIdDocente = DB::select("select * from docente_tests where id_test=$id");
        $getIdsany = true;
        $condition = "and u.id!=";
        foreach ($getIdDocente as $idProfessor) {
            $getIdsany = false;
            if ($idProfessor != end($getIdDocente)) {
                $condition = $condition . ' ' . $idProfessor->id_docente . ' ' . $condition;
            } else {
                $condition = $condition . ' ' . $idProfessor->id_docente;
            }
        }
        if ($getIdsany) {
            $getProfessorsNotAssigning = DB::select("SELECT u.id, u.nombre as nombre_user, u.email, 
            u.estado, s.nombre as nombre_sede, u.perfil from users u, sedes s 
            where u.id_rol=2 and u.id_sede=s.id");

            foreach($getProfessorsNotAssigning as $profesor) {
                if($profesor->perfil != null) {
                    $profesor->perfil = stream_get_contents($profesor->perfil);
                }
            }
        } else {
            $getProfessorsNotAssigning = DB::select("SELECT u.id, u.nombre as nombre_user, u.email,
             u.estado, s.nombre as nombre_sede, u.perfil
             from users u, sedes s 
            where u.id_rol=2 and u.id_sede=s.id " . $condition);

            foreach($getProfessorsNotAssigning as $profesor) {
                if($profesor->perfil != null) {
                    $profesor->perfil = stream_get_contents($profesor->perfil);
                }
            }
        }

        return response()->json($getProfessorsNotAssigning);
    }

    public function getProfessorAssigning($id)
    {
        $getIdDocente = DB::select("select * from docente_tests where id_test=$id");
        $vec = [];
        foreach ($getIdDocente as $idProfessor) {
            $aux = DB::select("SELECT u.id, u.nombre as nombre_user, u.email, u.estado, u.perfil, s.nombre as nombre_sede from users u, sedes s 
            where u.id_rol=2 and u.id_sede=s.id and u.id=$idProfessor->id_docente");
            foreach($aux as $profesor) {
                if($profesor->perfil != null) {
                    $profesor->perfil = stream_get_contents($profesor->perfil);
                }
            }
            $vec = array_merge_recursive($vec, $aux);
        }
        return response()->json($vec);
    }

    public function deleteProfessorAssigning(Request $request)
    {
        $request->validate([
            "objeto" => "required",
            "id_test" => "required"
        ]);

        $objeto = $request->objeto;

        foreach ($objeto as $valor) {
            DB::delete("delete from docente_tests where id_test=$request->id_test and id_docente=$valor");
        }
        return response()->json(["msg" => "se ha eliminado"], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            "objeto" => "required",
            "id_test" => "required"
        ]);

        $objeto = $request->objeto;

        foreach ($objeto as $valor) {
            $professorTest = new DocenteTest();
            $professorTest->id_docente = $valor;
            $professorTest->id_test = $request->id_test;
            $professorTest->save();
        }

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }
}
