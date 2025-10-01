<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtarefa extends Model
{
    use hasFactory;

    protected $table = 'subtarefas';

    protected $fillable = [
        'titulo',
        'descricao',
        'prazo',
        'equipe',
        'prioridade',
        'status',
        'projeto',
        'responsavel',
        'tarefa_id'
    ];

    // Cada Subtarefa pertence a uma única Tarefa
    public function tarefa()
    {
        return $this->belongsTo(Tarefa::class, 'tarefa_id');
    }
}
