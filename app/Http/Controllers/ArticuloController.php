<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Svg\Gradient\Stop;

class ArticuloController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $file = $request->documento;
        $name = $request->titulo.'.'.$file->extension();
        $file->storeAs('', $name, 'public');
        
        $articulo = new Articulo();
        $articulo->id_docente = $request->id_docente;
        $articulo->titulo = $request->titulo;
        $articulo->descripcion = $request->descripcion;
        $articulo->documento = $name;
        $articulo->save();

        return response()->json(["message" => "Se guardo correctamente"], 201);
    }

    public function show($id)
    {
        //
    }
    
    public function getArticlesByDocente($id_docente)
    {
        $articles = DB::select("SELECT * FROM articulos WHERE id_docente='$id_docente' ORDER BY id");
        return $articles;
    }

    public function getArticlePdf($id)
    {
        $article = Articulo::findOrFail($id);
        $name = $article->documento;
        $file = "storage/" + $name;
        return response()->file($file);
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
