<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\Request;
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
            $downloadLink = route("download", $name);
            $files[] = [
                "name" => $name,
                "downloadLink" => $downloadLink
            ];
        }

        return response()->json(["files" => $files]);
    }

    public function show($id)
    {
        //
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
