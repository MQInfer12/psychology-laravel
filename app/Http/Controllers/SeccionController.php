<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeccionController extends Controller
{
    public function index()
    {
        return Seccion::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_test' => 'required',
        ]);

        $seccion = new Seccion();
        $seccion->id_test = $request->id_test;
        $seccion->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function destroy($id)
    {
        return Seccion::destroy($id);
    }

    public function seccionByTest($idTest) 
    {
        return DB::select("SELECT *
                           FROM seccions
                           WHERE id_test='$idTest'
                           ORDER BY id");
    }

    public function changeMultimarcado($id) 
    {
        $seccion = Seccion::findOrFail($id);
        $multimarcado = $seccion->multimarcado;
        $seccion->multimarcado = !$multimarcado;
        $seccion->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }
} 
