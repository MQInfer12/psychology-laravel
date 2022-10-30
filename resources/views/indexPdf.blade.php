<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        table {
            table-layout: fixed;
            border-collapse: collapse;
            width: 100%;
        }

        table>thead {
            height: 40px;
        }

        table>tbody>tr {
            max-width: 622px;
            height: 64px;
            background-color: #ffffff;
            position: relative;
            text-align: center;
        }

        table>tbody>tr:nth-child(2n) {
            background-color: #ebf0fa;
        }

        /* ================== */
        .ThNumberal {
            font-size: 11px;
            color: #171c26;
            padding-left: 11px;
            width: 47px;
            text-align: start;
            font-weight: 600;
        }

        .ThAnswer {
            width: 100%;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            text-align: start;
            color: #464f60;
        }

        .DivDouble {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding-right: 20px;
            text-align: start;
        }

        .PNombre {
            font-size: 14px;
            color: #171c26;
            font-weight: 500;
        }

        .plight {
            color: #687182;
            font-size: 12px;
            font-weight: 400;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ppuntaje {
            font-size: 14px;
            font-weight: 400;
            color: #464f60;
            width: 100%;
            text-align: end;
        }

        .psobre {
            font-size: 12px;
            font-weight: 400;
            color: #687182;
            width: 100%;
            text-align: end;
        }

        .statusContainer {
            width: fit-content;
            border-radius: 10px;
            padding: 1px 10px;
            font-size: 12px;
            font-weight: 500;
            background-color: "#FAF0F3";
            color: "#4F5AED";
        }
    </style>
</head>

<body>


    <table>
        <thead>
            <tr>
                <td class="ThNumberal">#</td>
                <td class="ThAnswer">Nombre</td>
                <td class="ThAnswer">Test</>
                <td class="ThAnswer">Docente</td>
                <td class="ThAnswer">Estado</td>
                <td class="ThAnswer">Puntuación</td>
                <td class="ThAnswer">Controles</td>
            </tr>
        </thead>
        <tbody>
            @foreach($respuestas as $v){
            <tr>
                <td>
                    <div class="DivDouble">
         º               <p class="PNombre">{{$v->nombre_user}}</p>
                        <p class="plight">{{$v->email_user}}</p>
                    </div>
                </td>
                <td>
                    <div class="DivDouble">
                        <p class="plight">{{$v->nombre_test}}</p>
                        <p class="plight">{{$v->descripcion}}</p>
                    </div>
                </td>
                <td>
                    <div class="DivDouble">
                        <p class="PNombre">{{$v->nombre_docente}}</p>
                        <p class="plight">{{$v->email_docente}}</p>
                    </div>
                </td>
                <td>
                    <div class="DivDouble">
                        <div class="statusContainer">
                            {{$v->case}}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="DivDouble">
                        <p class="ppuntaje">{{$v->puntuacion}}</p>
                        <p class="psobre">/{{$v->total}}</p>
                    </div>
                </td>
            </tr>
            }
        </tbody>
    </table>
</body>

</html>