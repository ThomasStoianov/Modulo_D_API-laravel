<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Usuario;
use App\Models\Tarefa;


class TarefaController extends Controller
{
    public function add_tarefa(Request $request) {

        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);

        // Pega o usuário pelo token
        $user = Usuario::where('token', $token)->first();

        // Verifica se existe
        if (!$user) {
            return response()->json(['Message' => 'Token inválido ou usuário não encontrado'], 401);
        }

        // Verifica se o usuário é gerente de projeto
        if ($user->equipe != 'Gerente de Projeto') {
            return response()->json(['Message' => 'Você não tem privilégio para incluir uma nova tarefa'], 422);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string|max:255',
            'prazo' => 'required|date',
            'equipe' => 'nullable|string|max:255',
            'prioridade' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'projeto' => 'required|string|max:255',
            'responsavel' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $failed = $validator->failed();

            // verifica se alguma regra 'Required' falhou
            foreach ($failed as $field => $rules) {
                if (isset($rules['Required'])) {
                    return response()->json([
                        'Message' => 'Verifique e tente novamente, campos faltando'
                    ], 422);
                }
            }

            // se chegou aqui, é erro de tipo/dados
            return response()->json([
                'Message' => 'Verifique e tente novamente, dados incorretos'
            ], 422);
        }

        Tarefa::create($request->all());

        return response()->json([
            'Message' => 'Nova tarefa registrada com sucesso'
        ], 201);
    }
}
