<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreguntaController extends Controller
{
    public function index()
    {
        return Pregunta::all();
    }

    public function preguntasBySeccion($idSeccion)
    {
        return DB::select("SELECT * FROM preguntas WHERE id_seccion='$idSeccion' ORDER BY id");
    }

    public function store(Request $request)
    {
        $request->validate([
            "id_seccion" => "required",
            "descripcion" => "required"
        ]);

        $pregunta = new Pregunta();
        $pregunta->id_seccion = $request->id_seccion;
        $pregunta->descripcion = $request->descripcion;
        $pregunta->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "id_seccion" => "required",
            "descripcion" => "required"
        ]);

        $pregunta = Pregunta::findOrFail($id);
        $pregunta->id_seccion = $request->id_seccion;
        $pregunta->descripcion = $request->descripcion;
        $pregunta->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function destroy($id)
    {
        return Pregunta::destroy($id);
    }
}
