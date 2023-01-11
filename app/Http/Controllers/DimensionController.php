<?php

namespace App\Http\Controllers;

use App\Models\Dimension;
use App\Models\EscalaDimension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DimensionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            "id_test" => "required",
            "descripcion" => "required"
        ]);

        $dimension = new Dimension();
        $dimension->id_test = $request->id_test;
        $dimension->descripcion = $request->descripcion;
        $dimension->save();
        $dimension->preguntas = [];
        $dimension->valores = [];

        $escalas = DB::select("SELECT id FROM escalas WHERE id_test='$request->id_test'");
        foreach($escalas as $escala) {
            $escala_dimension = new EscalaDimension();
            $escala_dimension->id_escala = $escala->id;
            $escala_dimension->id_dimension = $dimension->id;
            $escala_dimension->save();
        }

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $dimension], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "descripcion" => "required"
        ]);

        $dimension = Dimension::findOrFail($id);
        $dimension->descripcion = $request->descripcion;
        $dimension->save();

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $dimension], 201);
    }

    public function destroy($id)
    {
        return Dimension::destroy($id);
    }
}
