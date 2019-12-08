<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use ErrorException;
use App\Util\Hasher;
use App\Prancha;
use App\Usuario;

class PranchaController extends Controller
{
    public function criarPrancha(Usuario $usuario, Request $request) {
        
        $prancha = $request->json()->all();
        
        try {
            $novaPrancha = Prancha::create([
                'nome' => $prancha['nome'],
                'id_usuario' => $usuario['id'],
            ]);
            
            $simbolos = Hasher::decodeAll($prancha['simbolos']);
            
            $novaPrancha->simbolos()->attach($simbolos);
            
            return response()->json($novaPrancha, 201);
        }
        catch(QueryException $e) {
            if(strpos(strtolower($e->getMessage()), 'cannot be null') !== false) {
                abort(400, 'Verifique as informações e tente novamente.');
            }
            else{ 
                throw $e;
            }
        }
    }
    
    public function deletarPrancha(Usuario $usuario, Prancha $prancha, Request $request) {
       $prancha->delete();
    }
    
    public function getPrancha(Usuario $usuario, Prancha $prancha, Request $request) {
        return response()->json($prancha->with('simbolos.categoria')->whereKey($prancha->id)->get()->first(), 200);
    }
        
    public function atualizarPrancha(Usuario $usuario, Prancha $prancha, Request $request) {
        $dados = $request->json()->all();
        
        try {
            if(!$dados['nome'] || !$dados['simbolos'])
                abort(400, 'Verifique as informações e tente novamente.');
        }
        catch (ErrorException $e)
        {
            abort(400, 'Verifique as informações e tente novamente.');
        }
        
        try {
            $prancha->update([
                'nome' => $dados['nome']
            ]);
            
            $simbolos = Hasher::decodeAll($dados['simbolos']);
            
            $prancha->simbolos()->detach();
            $prancha->simbolos()->attach($simbolos);
        }
        catch(QueryException $e) {
            if(strpos(strtolower($e->getMessage()), 'cannot be null') !== false) {
                abort(400, 'Verifique as informações e tente novamente.');
            }
            else if(strpos(strtolower($e->getMessage()), 'duplicate entry') !== false) {
                abort(409, 'O nome informado já está em uso.');
            }
        }
        
    }
}
