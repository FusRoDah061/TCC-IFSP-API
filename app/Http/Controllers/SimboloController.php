<?php

namespace App\Http\Controllers;

use App\Simbolo;
use App\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ErrorException;
use App\Util\Hasher;
use App\Prancha;

class SimboloController extends Controller
{

    public function deletarSimbolo(Usuario $usuario, Simbolo $simbolo, Request $request){
        if ($simbolo->id_usuario) {
            $simbolo->delete();
        }
        else{
            abort(403, 'O símbolo não pode ser removido.');
        }
    }
       
    public function criarSimbolo(Usuario $usuario, Request $request) {
        $novoSimbolo = $request->input();
        $arquivo = $request->file('arquivo');

        if(!$novoSimbolo['nome'] ||
            !$arquivo ||
            !$novoSimbolo['tipo'] ||
            !$novoSimbolo['hid_categoria']
        ) {
            abort(400, 'Todos os campos devem ser informados.');
        }

        // Determina o tipo do símbolo
        if($arquivo->getMimeType() == "video/mp4") {
            $tipo = config('app.simbolo_video');
        }
        else if($arquivo->getMimeType() == "image/jpeg" ||
            $arquivo->getMimeType() == "image/png" ||
            $arquivo->getMimeType() == "image/gif"
        ) {
            $tipo = config('app.simbolo_imagem');
        }
        
        if (!$tipo) {
            abort(400, "Arquivo inválido. Apenas arquivos de imagem e vídeo com as seguintes extensões são permitidos: .jpg, .jpeg, .png, .gif, .mp4");
        }
        
        // Se o tipo determinado no front-end for diferente do que chegou, rejeita o símbolo
        if($tipo != $novoSimbolo['tipo']) {
            abort(400, 'Não foi possível salvar o símbolo. Tente novamente.');
        }
        
        $tamArquivo = ($arquivo->getSize() /1024) / 1024;
        
        //Verifica se o tamanho do arquivo está nos limites
        if(
            ($novoSimbolo['tipo'] == config('app.simbolo_video') && $tamArquivo > config('app.video_file_size_mb')) ||
            ($novoSimbolo['tipo'] == config('app.simbolo_imagem') && $tamArquivo > config('app.img_file_size_mb'))
        ) {
            abort(413, 'O tamanho do arquivo ultrapassa o permitido.');
        }
        
        try {
            
            //Salva o arquivo de vídeo ou imagem
            $path = 'users/' . $usuario->hid;
            $arquivoSalvo = config('filesystems.disks.s3.url') . $arquivo->store($path, 's3');
            
            //Se é vídeo, salva uma thumbnail que vai aparecer nos símbolos
            if($novoSimbolo['tipo'] == config('app.simbolo_video')) {
                $thumbnail = $request->file('thumbnail'); 
                
                //A thumbnail é salva com o mesmo nome do arquvivo de vídeo
                preg_match('/([A-z0-9]){40}/', $arquivoSalvo, $matches);
                $nomeArq = $matches[0] . '.jpeg';
                $thumbnail->storeAs($path . '/thumbnails', $nomeArq, 's3');
            }
            
            $simbolo = Simbolo::create([
                'nome' => $novoSimbolo['nome'],
                'arquivo' => $arquivoSalvo,
                'tipo' => $novoSimbolo['tipo'],
                'id_usuario' => $usuario->id,
                'id_categoria' => Hasher::decode($novoSimbolo['hid_categoria'])
            ]);
            
             return response()->json($simbolo, 201);
        
        }
        catch(QueryException $e) {
            if(strpos(strtolower($e->getMessage()), 'cannot be null') !== false) {
                abort(400, 'Verifique as informações e tente novamente.');
            }
            else if(strpos(strtolower($e->getMessage()), 'duplicate entry') !== false) {
                abort(409, 'Símbolo já existe.');
            }
        }
    }

    public function getSimbolos(Usuario $usuario, Request $request) {
        $busca = strtolower($request->query('busca', null));
        $prancha = $request->query('prancha', null);
        $categoria = $request->query('categoria', null);
        
        $query = Simbolo::with('categoria');
        
        if($prancha) {
            $prancha = Hasher::decode($prancha);
            
            $query = Prancha::find($prancha)->simbolos()->with('categoria');
        }
        
        if($categoria) {
            if ($categoria == config('app.categoria_meus')) {
                $query = $query->where('id_usuario', '=', $usuario->id);
            }
            else if ($categoria != config('app.categoria_todos')) {
                try {
                    $categoria = Hasher::decode($categoria);
                }
                catch (ErrorException $e) {
                    abort(400, 'Categoria inválida');
                }
                
                $query = $query->where('id_categoria', '=', $categoria);
            }            
        }
        
        if($busca) {
            $query = $query
                ->where(function($where) use ($busca){
                    $where->where(DB::raw('lower(nome)'), 'like', $busca.'%')
                    ->orWhere(DB::raw('lower(nome)'), 'like', '%'.$busca.'%');
                });
        }
        
        $simbolos = $query->paginate(config('app.pagination_size'))->items();
        
        if(sizeof($simbolos) > 0){
            return response()->json($simbolos);
        }
        else {
            abort(204);
        }
    }
    
    public function atualizarSimbolo(Usuario $usuario, Simbolo $simbolo, Request $request) {
        $dados = $request->json()->all();
        
        if(!$simbolo->id_usuario)
            abort(403, 'O símbolo não pode ser modificado.');
        
        
        try {
            if(!$dados['nome'] || !$dados['arquivo'] || !$dados['tipo'] || !$dados['categoria'])
                abort(400, 'Verifique as informações e tente novamente.');
        }
        catch (ErrorException $e)
        {
            abort(400, 'Verifique as informações e tente novamente.');
        }
        
        try {
            $nomeArquivo = config('app.user_simbolos_path') . '/' . $usuario->hid . '/' . md5(pathinfo($dados['arquivo'], PATHINFO_FILENAME)) . '.' . pathinfo($dados['arquivo'], PATHINFO_EXTENSION);
            
            $simbolo->update([
                'nome' => $dados['nome'],
                'arquivo' => $nomeArquivo,
                'tipo' => $dados['tipo'],
                'id_categoria' => Hasher::decode($dados['categoria'])
            ]);
        }
        catch(QueryException $e) {
            if(strpos(strtolower($e->getMessage()), 'cannot be null') !== false) {
                abort(400, 'Verifique as informações e tente novamente.');
            }
            else if(strpos(strtolower($e->getMessage()), 'duplicate entry') !== false) {
                abort(409, 'Um símbolo idêntico já existe.');
            }
        }
    }
}
