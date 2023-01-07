<?php

namespace App\Traits;

use App\Models\PreguntaDimension;
use App\Models\Puntuacion;
use Illuminate\Support\Facades\DB;

  trait PuntuacionesNaturales {
    public function getPuntuacionesNaturales($dimensiones) {
        $naturales = [];
        foreach($dimensiones as $dimension) {
            $natural = $this->getPuntuacionNatural($dimension->id);
            $naturales[] = array(
                "id" => $dimension->id,
                "valores" => $natural
            );
        }
        return $naturales;
    }

    public function getPuntuacionNatural($id) {
        $preguntas = PreguntaDimension::where('id_dimension',$id)->pluck('id_pregunta')->toArray();
        $natural = [];
        if(count($preguntas)) {
            $arrayDeArrays = [];
            foreach($preguntas as $pregunta) {
                $array = Puntuacion::where('id_pregunta', $pregunta)->pluck('asignado')->toArray();
                $vacio = DB::select("SELECT s.vacio FROM seccions as s, preguntas as p WHERE p.id_seccion=s.id AND p.id='$pregunta'")[0]->vacio;
                if($vacio) {
                    $array[] = 0;
                    $array = array_unique($array);
                }
                $arrayDeArrays[] = $array;
            }
            $posibilidades = $this->calcularPosibilidad($arrayDeArrays, 0);
            sort($posibilidades);
            foreach($posibilidades as $posibilidad) {
                $natural[] = array("natural" => $posibilidad);
            }
        }
        return $natural;
    }

    public function calcularPosibilidad($arrayDeArrays, $index) {
      if(count($arrayDeArrays) - 1 === $index) {
          return array_unique($arrayDeArrays[$index]);
      } else {
          $posibilidadesNuevas = [];
          $primerVector = $arrayDeArrays[$index];
          $segundoVector = $this->calcularPosibilidad($arrayDeArrays, $index + 1);
          foreach($primerVector as $primero) {
              foreach($segundoVector as $segundo) {
                  $posibilidad = $primero + $segundo;
                  if(!in_array($posibilidad, $posibilidadesNuevas)) {
                      $posibilidadesNuevas[] = $posibilidad;
                  }
              }
          }
          return $posibilidadesNuevas;
      }
    }
  }