<?php

namespace App\Http\Controllers;

use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeneficiarioDocenteController extends Controller
{
    public function getBenefAssigning($id) //BENEFICIARIOS ASIGNADOS PARA DESASIGNARLOS
    {
        $getIdDocente = DB::select("SELECT bdt.id, bdt.email_user, bdt.id_docente_test, u.email, 
                                    u.id as id_user, u.nombre as nombre_user, s.nombre as sede, u.perfil
                                    from respuestas as bdt, users u, sedes as s
                                    where bdt.email_user=u.email and s.id=u.id_sede and bdt.id_docente_test=$id");
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
        } else {
            $getBenefNotAssigning = DB::select("SELECT u.id, u.nombre as nombre_usuario, u.email, u.estado, u.perfil
            from users as u where u.id_rol=1 " . $condition);
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

        $newObj = [];
        foreach ($objeto as $index => $valor) {
            DB::delete("DELETE from respuestas where email_user='$valor' AND id_docente_test='$request->id_docente_test'");
            $exists = DB::select("SELECT id FROM respuestas WHERE email_user='$valor' AND id_docente_test='$request->id_docente_test'");
            $newObj[$index] = (object)[];
            $newObj[$index]->from = $valor;
            $newObj[$index]->exists = count($exists);
        }
        return response()->json(["msg" => "se ha eliminado", "result" => $newObj], 200);
    }
}
