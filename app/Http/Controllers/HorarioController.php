<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    public function index()
    {
        return DB::select("SELECT h.id, h.fecha, h.hora_inicio, h.hora_final, h.disponible, u.email,u.nombre 
        from horarios h, users u 
        where h.id_docente=u.id
        ");
    }

    public function showById($id) //MOSTRAR HORARIOS A LOS DOCENTES
    {
        $horarios = DB::select("SELECT h.id, h.fecha, h.hora_inicio, h.hora_final, h.disponible, u.email, u.nombre 
                                from horarios h, users u 
                                where h.id_docente=u.id and u.id=$id");

        $newHorarios = [];
        foreach($horarios as $horario) {
            //USUARIOS DE LAS CITAS DE CADA HORARIO
            $citas = DB::select("SELECT u.id
                                 FROM citas c, users u
                                 WHERE c.id_horario='$horario->id' AND c.id_usuario=u.id");

            if(count($citas) == 0) {
                //FORMATEAR FECHA
                $horario->fecha = date_create($horario->fecha);
                $horario->fecha = date_format($horario->fecha, "d/m/Y");
    
                //PUSHEAR AL NUEVO ARRAY DE HORARIOS
                $newHorarios[] = $horario;
            }
        }

        return $newHorarios;
    }

    public function showWhoHaveDateTheProfessor($id) //MOSTRAR CITAS A LOS DOCENTES
    {
        $appointments = DB::select("SELECT c.id, c.aceptado, c.id_usuario, h.fecha , h.id as id_horario, h.hora_inicio, h.hora_final, h.disponible, 
                                    h.id_docente, u.email, u.nombre
                                    from citas c, horarios h, users u where h.id_docente=$id and c.id_horario=h.id and c.id_usuario = u.id");
        foreach($appointments as $appointment) {
            $appointment->fecha = date_create($appointment->fecha);
            $appointment->fecha = date_format($appointment->fecha, "d/m/Y");
        }
        return $appointments;
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_final' => 'required',
            'id_docente' => 'required',
        ]);

        $grupo = new Horario();
        $grupo->fecha = $request->fecha;
        $grupo->hora_inicio = $request->hora_inicio;
        $grupo->hora_final = $request->hora_final;
        $grupo->id_docente = $request->id_docente;
        $grupo->disponible = true;

        $grupo->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function show($id)
    {
        return Horario::find($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required',
            'hora_inicio' => 'required',
            'hora_final' => 'required',
        ]);

        $grupo = Horario::findOrFail($id);
        $grupo->fecha = $request->fecha;
        $grupo->hora_inicio = $request->hora_inicio;
        $grupo->hora_final = $request->hora_final;
        $grupo->save();

        return response()->json(["mensaje" => "se guardo correctamente"], 201);
    }

    public function destroy($id)
    {
        return Horario::destroy($id);
    }
}
