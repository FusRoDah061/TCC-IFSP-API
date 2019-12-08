<?php

namespace App\Http\Controllers;

use App\Usuario;
use App\Util\Recaptcha;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use ErrorException;

class UsuarioController extends Controller
{
    public function getPranchas(Usuario $usuario) {
        $pranchas = $usuario->pranchas()->getResults();
        
        if($pranchas->isNotEmpty())
            return response()->json($pranchas, 200);
       else
            return response()->json($pranchas, 204);
    }
       
    public function atualizarUsuario(Usuario $usuario, Request $request){
        $dados = $request->json()->all();
        
        try {
            if(!$dados['email'] || !$dados['nome'])
                abort(400, 'Verifique as informações e tente novamente.');
        }
        catch (ErrorException $e) 
        {
            abort(400, 'Verifique as informações e tente novamente.');
        }
        
        try {
            $usuario->update([
                'nome' => $dados['nome'],
                'email' => $dados['email']
            ]);
            
            //TODO: Enviar confirmação de e-mail
        }
        catch(QueryException $e) {
            if(strpos(strtolower($e->getMessage()), 'cannot be null') !== false) {
                abort(400, 'Verifique as informações e tente novamente.');
            }
            else if(strpos(strtolower($e->getMessage()), 'duplicate entry') !== false) {
                abort(409, 'O E-mail informado já está cadastrado.');
            }
        }
    }
    
    public function atualizarSenhaUsuario(Usuario $usuario, Request $request){
        $recuperar = strtolower($request->query('recuperar')) == 'true';
        $recaptcha = $request->query('validation');
        $dados = $request->json()->all();
        
        try {
            if(!$dados['senhaNova']) {
                abort(400, 'Nova senha deve ser informada');
            }
            else if(!$recaptcha){
                abort(400, 'Complete o captcha para realizar a solicitação.');
            }
        }
        catch (ErrorException $e)
        {
            abort(400, 'Nova senha deve ser informada');
        }
        
        if(!Recaptcha::verify($recaptcha)){
            abort(401, 'Captcha inválido. Tente novamente.');
        }
        else if($recuperar) {
            //Desconsiderar a senha atual
            $usuario->update([
                'senha' => Hash::make($dados['senhaNova'])
            ]);            
        }
        else {
            try {
                if(!$dados['senhaAtual'])
                    abort(400, 'Sua senha atual deve ser informada');
            }
            catch (ErrorException $e)
            {
                abort(400, 'Sua senha atual  deve ser informada');
            }
            
            if(!Hash::check($dados['senhaAtual'], $usuario->senha))
                abort(403, 'Senha atual incorreta.');
            
            $usuario->update([
                'senha' => Hash::make($dados['senhaNova'])
            ]);
        }
            
    }
}
