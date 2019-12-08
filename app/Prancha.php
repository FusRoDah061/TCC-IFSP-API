<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Prancha extends Model
{
    protected $hidden = ['id', 'id_usuario', 'created_at', 'updated_at'];

    protected $fillable =['nome', 'id_usuario'];
    
    protected $table = 'prancha';

    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'id_usuario');
    }

    public function simbolos()
    {
        return $this->belongsToMany('App\Simbolo', 'rel_simbolo_prancha', 'id_prancha', 'id_simbolo');
    }
    
    public function getRouteKeyName()
    {
        return 'hid';
    }
}
