<?php

namespace App\Http\Controllers;

use App\Models\Puntuacion;
use App\Models\Reactivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReactivoController extends Controller
{
    public function index()
    {
        return Reactivo::all();
    }

    public function reactivosBySeccion($idSeccion)
    {
        return DB::select("SELECT * FROM reactivos WHERE id_seccion='$idSeccion' ORDER BY id");
    }

    public function store(Request $request)
    {
        $request->validate([
            "id_seccion" => "required",
            "descripcion" => "required"
        ]);

        $reactivo = new Reactivo();
        $reactivo->id_seccion = $request->id_seccion;
        $reactivo->descripcion = $request->descripcion;
        $reactivo->save();

        //AÑADIR PUNTUACIONES DEL REACTIVO DEPENDIENDO DE CUANTAS PREGUNTAS HAYA
        $id_reactivo = $reactivo->id;
        $id_seccion = $request->id_seccion;
        $preguntas = DB::select("SELECT * FROM preguntas WHERE id_seccion='$id_seccion' ORDER BY id");

        foreach($preguntas as $pregunta) {
            $puntuacion = new Puntuacion();
            $puntuacion->id_pregunta = $pregunta->id;
            $puntuacion->id_reactivo = $id_reactivo;
            $puntuacion->asignado = 0;
            $puntuacion->save();
        }

        //RETORNAR
        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "id_seccion" => "required",
            "descripcion" => "required"
        ]);

        $pregunta = Reactivo::findOrFail($id);
        $pregunta->id_seccion = $request->id_seccion;
        $pregunta->descripcion = $request->descripcion;
        $pregunta->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function destroy($id)
    {
        return Reactivo::destroy($id);
    }

    public function changePredeterminado(Request $request, $id)
    {
        $request->validate([
            "predeterminado" => "required",
        ]);

        DB::update("UPDATE reactivos SET predeterminado='$request->predeterminado' WHERE id='$id'");
        DB::select("UPDATE puntuacions SET asignado='$request->predeterminado' WHERE id_reactivo='$id'");

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }
}
