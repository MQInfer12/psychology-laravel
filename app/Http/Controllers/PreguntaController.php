<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Puntuacion;
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
        
        //AÑADIR PUNTUACIONES DE LA PREGUNTA DEPENDIENDO DE CUANTOS REACTIVOS HAYA
        $id_pregunta = $pregunta->id;
        $id_seccion = $request->id_seccion;
        $reactivos = DB::select("SELECT * FROM reactivos WHERE id_seccion='$id_seccion' ORDER BY id");

        $puntuaciones = [];
        foreach($reactivos as $reactivo) {
            $puntuacion = new Puntuacion();
            $puntuacion->id_pregunta = $id_pregunta;
            $puntuacion->id_reactivo = $reactivo->id;
            $puntuacion->asignado = $reactivo->predeterminado;
            $puntuacion->save();
            $puntuaciones[] = $puntuacion;
        }

        $data = array(
            "pregunta" => $pregunta,
            "puntuaciones" => $puntuaciones
        );

        //RETORNAR
        return response()->json(["mensaje" => "se guardo correctamente", "data" => $data], 201);
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

        return response()->json(["mensaje" => "se guardo correctamente", "data" => $pregunta], 201);
    }

    public function destroy($id)
    {
        return Pregunta::destroy($id);
    }

    public function massDestroy(Request $request)
    {
        $objeto = $request->objeto;

        foreach($objeto as $valor) {
            Pregunta::destroy($valor);
        }

        return response()->json(["mensaje" => "se borro correctamente"], 201);
    }
}
