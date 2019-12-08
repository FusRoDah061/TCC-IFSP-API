<?php

namespace App\Http\Controllers;

use App\PedidoRecSenha;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecuperacaoSenha;
use App\Util\Recaptcha;

class RecuperarSenhaController extends Controller
{
    public function enviarPedido (Request $request) {
        $email = $request->query('email');
        $recaptcha = $request->query('validation');
        
        if(!$email) {
            abort(400, 'Um e-mail válido deve ser informado.');
        }
        else if(!$recaptcha){
            abort(400, 'Complete o captcha para realizar a solicitação.');
        }
        else if(Recaptcha::verify($recaptcha)){
            $token = sha1($email . time());
                
            $pedido = PedidoRecSenha::create([
                'email' => $email,
                'token' => $token
            ]);
            
            Mail::to($email)->send(new RecuperacaoSenha($pedido));
        }
        else{
            abort(401, 'Captcha inválido. Tente novamente.');
        }
    }
    
    public function verificarPedido(PedidoRecSenha $pedido, Request $request) {
        
        date_default_timezone_set('America/Sao_Paulo'); 
        
        $dataExpiracao = date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s") . " +1 days"));
                
        if(strtotime($pedido->created_at) < strtotime($dataExpiracao)) {
            
            $user = $pedido->usuario;
            
            Auth::loginUsingId($user->id);            
            
            $tk = hash('sha256', Str::random(60));
            $user->setApiTokenAttribute($tk);
            
            $user->save();
            
            return response()->json($user);
        }
        else{
            $pedido->delete();
            abort(403, 'O pedido expirou');
        }
    }
}
