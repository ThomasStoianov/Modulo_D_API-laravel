<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Model implements JWTSubject
{
    use HasFactory;

    // Define a tabela do banco que este model representa
    protected $table = 'usuarios';

    // Campos que podem ser preenchidos
    protected $fillable = [
        'nome',
        'email',
        'equipe',
        'username',
        'senha',
        'token',
    ];

    // Campos que não serão exibidos quando o model for convertido em array ou JSON
    protected $hidden = [
        'senha',
        'token',
    ];

    // Funções que fazem o model Usuario seguir as regras JWT, isso impede que o model
    // de erro quando gerar o token, com essas duas funções o model Usuario agora consegue
    // pegar id e gerar o token

    // Retorna o id do usuário que vai dentro do token
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    // Permite adicionar dados extras no token JWT
    public function getJWTCustomClaims() {
        return [];
    }
}
