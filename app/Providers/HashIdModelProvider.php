<?php

namespace App\Providers;

use App\Categoria;
use App\Prancha;
use App\Simbolo;
use App\Usuario;
use Illuminate\Support\ServiceProvider;
use App\Util\Hasher;
use App\PedidoRecSenha;

class HashIdModelProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Usuario::created(function($model) {
            $model->hid = Hasher::encode($model->id);
            $model->save();
        });
        
        Prancha::created(function($model) {
            $model->hid = Hasher::encode($model->id);
            $model->save();
        });
        
        Simbolo::created(function($model) {
            $model->hid = Hasher::encode($model->id);
            $model->save();
        });
        
        Categoria::created(function($model) {
            $model->hid = Hasher::encode($model->id);
            $model->save();
        });
    }
}
