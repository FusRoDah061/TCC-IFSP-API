<?php
namespace App\Http\Controllers\Auth;

use App\Usuario;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    
    public function logout (Usuario $usuario) {
        $usuario->setApiTokenAttribute('');
        $usuario->save();
        
        Auth::logout();
    }
    
}

