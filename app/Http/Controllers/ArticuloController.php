<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArticuloController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $file = $request->documento;
        $file->storeAs('', $request->titulo.'.'.$file->extension(), 'public');

        $files = [];

        foreach(Storage::disk("public")->files() as $file) {
            $name = str_replace("public/","",$file);
            $asset = asset("storage/".$name);
            $files[] = [
                "name" => $name,
                "asset" => $asset
            ];
        }

        return response()->json(["files" => $files]);
    }

    public function show($id)
    {
        //
    }
    
    public function showArticlesByDocente($id_docente)
    {
        /* $articles = DB::select("SELECT * FROM articulos WHERE id_docente='$id_docente' ORDER BY id");
        return $articles; */
        $files = Storage::disk("public")->files();
        return $files[2];
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
