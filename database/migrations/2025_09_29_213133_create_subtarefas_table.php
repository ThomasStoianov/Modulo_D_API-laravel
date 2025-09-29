<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtarefas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->date('prazo')->nullable();
            $table->string('equipe');
            $table->string('prioridade')->default('normal');
            $table->string('status')->default('pendente');
            $table->string('projeto')->nullable();
            $table->unsignedBigInteger('responsavel')->nullable();

            // relação com a tarefa principal
            $table->unsignedBigInteger('tarefa_id');
            $table->foreign('tarefa_id')->references('id')->on('tarefas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtarefas');
    }
};
