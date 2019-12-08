<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Simbolo extends Model
{
    protected $hidden = ['id', 'id_usuario', 'id_categoria', 'created_at', 'updated_at'];
    
    protected $fillable = ['id_usuario', 'id_categoria', 'hid', 'nome', 'arquivo', 'tipo'];

    protected $table = 'simbolo';

    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'id_usuario');
    }

    public function categoria()
    {
        return $this->belongsTo('App\Categoria', 'id_categoria');
    }

    public function pranchas()
    {
        return $this->belongsToMany('App\Prancha', 'rel_simbolo_prancha', 'id_simbolo', 'id_prancha');
    }
    public function getRouteKeyName()
    {
        return 'hid';
    }
}
