<?php

namespace App\Http\Controllers\Auth;

use App\Usuario;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuario'],
            'senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Usuario
     */
    protected function create(Request $request)
    {
        $data = $request->json()->all();

        try {
            return response(Usuario::create([
                'nome' => $data['nome'],
                'email' => $data['email'],
                'senha' => Hash::make($data['senha']),
                'api_token' => hash('sha256', Str::random(60)),
            ]), 201);
            
            //TODO: Enviar confirmação de e-mail
        }
        catch(QueryException $e) {
            if(strpos(strtolower($e->getMessage()), 'duplicate entry') !== false) {
                abort(409, 'Usuário já cadastrado.');
            }
            else if(strpos(strtolower($e->getMessage()), 'cannot be null') !== false) {
                abort(400, 'Verifique as informações e tente novamente.');
            }
        }
    }
}
