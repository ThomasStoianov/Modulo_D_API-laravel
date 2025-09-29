<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 255);
            $table->string('descricao', 255);
            $table->date('prazo', 255);
            $table->string('equipe', 255);
            $table->string('prioridade', 255);
            $table->string('status', 255);
            $table->string('projeto', 255);
            $table->unsignedBigInteger('responsavel');
            $table->timestamps();

            // Relacionamentos
            $table->foreign('tarefa_id')->references('id')->on('tarefas')->onDelete('cascade');
            $table->foreign('responsavel')->references('id')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
