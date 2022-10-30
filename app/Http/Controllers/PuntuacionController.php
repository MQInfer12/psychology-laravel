<?php

namespace App\Http\Controllers;

use App\Models\Puntuacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PuntuacionController extends Controller
{
    public function puntuacionesByReactivos(Request $request)
    {
        $reactivos = $request->reactivos;

        if(count($reactivos) != 0) {
            $query = "SELECT * FROM puntuacions WHERE ";
            foreach($reactivos as $index => $reactivo) {
                $query = $query . " id_reactivo = '$reactivo' OR";
            }
            $query = $query . "DER BY id_reactivo";
    
            $puntuaciones = DB::select($query);
            return $puntuaciones;
        } else {
            return [];
        }
    }

    public function massUpdate(Request $request)
    {
        $puntuaciones = $request->puntuaciones;
        foreach($puntuaciones as $puntuacion) {
            $new = Puntuacion::findOrFail($puntuacion['id']);
            $new->asignado = $puntuacion['asignado'];
            $new->save();
        }
        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }
}
