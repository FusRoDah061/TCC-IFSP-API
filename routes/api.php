<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Util\Hasher;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::any('/', function (Request $request) {

    $hashids = [
        Hasher::encode(1),
        Hasher::encode(2),
        Hasher::encode(3),
        Hasher::encode(4),
        Hasher::encode(5),
        Hasher::encode(6),
    ];

    return response()->json([
        'message' => 'TCC API',
        'hashids_categorias' => $hashids,
        'input' => $request->input(),
        'query' => $request->query(),
        'headers' => $request->headers->all(),
        'simbolo_video' => config('app.simbolo_video'),
        'simbolo_imagem' => config('app.simbolo_imagem'),
        'video_file_size_mb' => config('app.video_file_size_mb'),
        'img_file_size_mb' => config('app.img_file_size_mb')
    ]);
});

Route::get('/auth', 'Auth\LoginController@authenticate');

// Obter categorias
Route::middleware('auth:api')->get('/categorias', 'CategoriaController@getCategorias');
// Obter simbolos
Route::middleware('auth:api')->get('/usuarios/{usuario}/simbolos', 'SimboloController@getSimbolos');
// Obter pranchas temáticas sem símbolos
Route::middleware('auth:api')->get('/usuarios/{usuario}/pranchas', 'UsuarioController@getPranchas');
// Obter símbolos independente de categoria
Route::middleware('auth:api')->get('/usuarios/{usuario}/categorias/simbolos', 'CategoriaController@getTodosSimbolos');
// Obter prancha
Route::middleware('auth:api')->get('/usuarios/{usuario}/pranchas/{prancha}', 'PranchaController@getPrancha');
// Verifica pedido de recuperação senha
Route::get('/senha/recuperar/{pedido}', 'RecuperarSenhaController@verificarPedido');

//Cadastro
Route::post('/usuarios', 'Auth\RegisterController@create');
// Logout
Route::post('/usuarios/{usuario}/logout', 'Auth\LogoutController@logout');
// Criar nova prancha temática
Route::middleware('auth:api')->post('/usuarios/{usuario}/pranchas', 'PranchaController@criarPrancha');
//Registrar uso de símbolo
Route::middleware('auth:api')->post('/usuarios/{usuario}/simbolos/{simbolo}/uso', 'SimboloController@usarSimbolo');
// Criar novo símbolo
Route::middleware('auth:api')->post('/usuarios/{usuario}/simbolos', 'SimboloController@criarSimbolo');
// Envio email de recuperação de senha
Route::post('/senha/recuperar', 'RecuperarSenhaController@enviarPedido');

// Atualizar usuário
Route::middleware('auth:api')->put('/usuarios/{usuario}', 'UsuarioController@atualizarUsuario');
// Atualizar senha do usuário
Route::middleware('auth:api')->put('/usuarios/{usuario}/senha', 'UsuarioController@atualizarSenhaUsuario');
// Atualizar prancha
Route::middleware('auth:api')->put('/usuarios/{usuario}/pranchas/{prancha}', 'PranchaController@atualizarPrancha');
// Atualizar símbolo
Route::middleware('auth:api')->put('/usuarios/{usuario}/simbolos/{simbolo}', 'SimboloController@atualizarSimbolo');

// Deletar prancha temática
Route::middleware('auth:api')->delete('/usuarios/{usuario}/pranchas/{prancha}', 'PranchaController@deletarPrancha');
// Remover símbolo criado
Route::middleware('auth:api')->delete('/usuarios/{usuario}/simbolos/{simbolo}', 'SimboloController@deletarSimbolo');
