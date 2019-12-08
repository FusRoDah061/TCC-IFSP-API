<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{

    protected $hidden = ['id', 'created_at', 'updated_at'];

    protected $table = 'categoria';

    public function simbolos()
    {
        return $this->hasMany('App\Simbolo', 'id_categoria');
    }
    
    public function getRouteKeyName()
    {
        return 'hid';
    }
}
