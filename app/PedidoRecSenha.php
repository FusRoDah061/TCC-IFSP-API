<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PedidoRecSenha extends Model
{
    protected $hidden = ['id', 'updated_at', 'created_at'];

    protected $fillable = ['email', 'token'];
    
    protected $table = 'pedido_rec_senha';

    public function usuario() {
        return $this->belongsTo('App\Usuario', 'email', 'email');
    }
    
    public function getRouteKeyName()
    {
        return 'token';
    }
}
