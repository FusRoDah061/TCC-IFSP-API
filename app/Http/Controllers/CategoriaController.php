<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function getCategorias(Request $request){
        return response()->json(App\Categoria::all());
    }
   
}
