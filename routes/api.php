<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TarefaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/signup', [AuthController::class, 'signup']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/logout', [AuthController::class, 'logout']);

Route::post('/add_tarefa', [TarefaController::class, 'add_tarefa']);

Route::get('/lista_tarefas', [TarefaController::class, 'lista_tarefas']);

Route::get('/lista_tarefa/{id}', [TarefaController::class, 'consulta_tarefa']);

Route::get('/tarefas_equipe/{id}', [TarefaController::class, 'tarefas_equipe']);

Route::put('/altera_tarefa/{id}', [TarefaController::class, 'altera_tarefa']);

Route::delete('/delete_tarefa/{id}', [TarefaController::class, 'delete_tarefa']);
