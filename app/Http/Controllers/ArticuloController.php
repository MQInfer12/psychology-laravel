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
        $file->storeAs('', 'doc1'.'.'.$file->extension(), 'public');
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
