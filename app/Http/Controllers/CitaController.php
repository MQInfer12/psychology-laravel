<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    public function getAppointmentsSchedule($id) //VER CITAS POR USUARIO
    {
        $appointments = DB::select("SELECT c.id, c.aceptado, h.fecha, h.hora_inicio, h.hora_final, u.email, u.nombre, c.id_horario 
                                    from citas c, horarios h, users u 
                                    where c.id_horario = h.id and h.id_docente=u.id and c.id_usuario=$id");
        foreach($appointments as $appointment) {
            $appointment->fecha = date_create($appointment->fecha);
            $appointment->fecha = date_format($appointment->fecha, "d/m/Y");
        }
        return $appointments;
    }

    public function getAppointmentsByHorario($idHorario)
    {
        $citas = DB::select("SELECT ci.id, u.nombre
                             FROM citas ci, users u
                             WHERE ci.id_usuario=u.id
                             AND ci.id_horario='$idHorario'
                             ORDER BY id");
        return $citas;
    }

    public function allAppointmentsAvailables($email) //LLENAR HORARIOS LIBRES PARA LOS BENEFICIARIOS
    {
        $horarios = DB::select("SELECT DISTINCT on (h.id) h.id, h.fecha, h.hora_inicio, h.hora_final, h.disponible, d.email, d.nombre
                                FROM horarios h, users d, respuestas r, docente_tests dt
                                WHERE r.email_user='$email'
                                AND r.id_docente_test=dt.id
                                AND dt.id_docente=d.id
                                AND h.id_docente=d.id 
                                AND h.disponible=true");

        $newHorarios = [];
        foreach($horarios as $horario) {
            //USUARIOS DE LAS CITAS DE CADA HORARIO
            $citas = DB::select("SELECT u.email
                                 FROM citas c, users u
                                 WHERE c.id_horario='$horario->id' AND c.id_usuario=u.id");

            $flag = true;
            //QUITAR HORARIO SI EXISTE EL USUARIO EN UNA CITA DE ESTE
            foreach($citas as $cita) {
                if($cita->email == $email) {
                    $flag = false;
                }
            }

            if($flag) {
                //FORMATEAR FECHA
                $horario->fecha = date_create($horario->fecha);
                $horario->fecha = date_format($horario->fecha, "d/m/Y");
    
                //PUSHEAR AL NUEVO ARRAY DE HORARIOS
                $newHorarios[] = $horario;
            }
        }

        return $newHorarios;
    }

    public function scheduleAppointment(Request $request, $id) //ASIGNAR UNA CITA PENDIENTE A UN HORARIO
    {
        $request->validate([
            'idUsuario' => 'required',
        ]);

        /* $horario = Horario::findOrFail($id);
        $horario->disponible = false;
        $horario->save(); */

        $cita = new Cita();
        $cita->id_horario = $id;
        $cita->id_usuario = $request->idUsuario;
        $cita->aceptado = false;
        $cita->save();

        return response()->json(["mensaje" => "se asigno las cita correctamente"], 201);
    }

    public function scheduleAccept($idHorario, $idCita) //ACEPTAR UNA CITA
    {
        $citas = DB::select("SELECT id
                            FROM citas
                            WHERE ci.id_horario='$idHorario'");
        foreach($citas as $ct) {
            Cita::destroy($ct->id);
        }

        $cita = Cita::findOrFail($idCita);
        $cita->aceptado = true;
        $cita->save();
        
        return response()->json(["mensaje" => "se modifico correctamente"], 201);
    }

    public function cancelAppointment($idHorario, $idCita)
    {
        $horario = Horario::findOrFail($idHorario);
        $horario->disponible = true;
        $horario->save();

        return Cita::destroy($idCita);
    }

    public function show($id)
    {
        return Cita::find($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_horario' => 'required',
            'id_usuario' => 'required',
        ]);

        $cita = Cita::findOrFail($id);
        $cita->id_horario = $request->id_horario;
        $cita->id_usuario = $request->id_usuario;

        $cita->save();

        return response()->json(["mensaje" => "se modifico correctamente"], 201);
    }
}
