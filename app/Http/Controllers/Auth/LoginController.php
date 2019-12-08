<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $email = $request->input()['email'];
        $password = $request->input()['password'];

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            // Authentication passed...
            $user = Auth::user();
            
            $tk = hash('sha256', Str::random(60));
            $user->setApiTokenAttribute($tk);

            $user->save();

            return response()->json($user);
        }
        else {
            throw new AuthenticationException("Credenciais inválidas");
        }
    }
}
