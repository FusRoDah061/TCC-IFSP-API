<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

class Usuario extends Model implements Authenticatable
{
    protected $hidden = ['id', 'senha', 'created_at', 'updated_at'];

    public $fillable = ['nome', 'email', 'senha', 'api_token', 'hid'];

    protected $appends = ['picture'];
    
    protected $table = 'usuario';

    public function pranchas()
    {
        return $this->hasMany('App\Prancha', 'id_usuario', 'id');
    }

    public function simbolos()
    {
        return $this->hasMany('App\Simbolo', 'id_usuario');
    }

    public function pedidoRecSenha()
    {
        return $this->hasMany('App\PedidoRecSenha', 'id_usuario');
    }

    public function getAuthIdentifierName(){
        return 'id';
    }

    public function getAuthIdentifier(){
        return $this->id;
    }

    public function getAuthPassword() {
        return $this->senha;
    }

    public function getRememberToken() {}
    public function setRememberToken($value){}
    public function getRememberTokenName(){}

    public function getRouteKeyName()
    {
        return 'hid';
    }
    
    public function setApiTokenAttribute($token) {
        $this->attributes['api_token'] = $token ?: null;
    }
    
    public function getPictureAttribute() {
        return config('app.gravatar_url') . md5(strtolower(trim($this->email)));
    }
}
