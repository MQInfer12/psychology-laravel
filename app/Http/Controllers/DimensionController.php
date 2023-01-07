<?php

namespace App\Http\Controllers;

use App\Models\Dimension;
use Illuminate\Http\Request;

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
        $dimension->escalas = [array("nombre" => "Natural", "valores" => [])];

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
