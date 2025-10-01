<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subtarefa;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Tarefa;


class TarefaController extends Controller
{
    // CRIA TAREFA
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

    // LISTA TAREFAS
    public function lista_tarefas(Request $request)
    {
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);

        if (!$token) {
            return response()->json([
                "Message" => "Atenção, token não informado"
            ], 422);
        }

        $usuario = Usuario::where('token', $token)->first();
        if (!$usuario) {
            return response()->json([
                "Message" => "Atenção, token inválido"
            ], 401);
        }

        $equipe = strtolower($usuario->equipe);

        if ($equipe === 'gerente de projeto') {
            $tarefas = Tarefa::orderBy('created_at', 'asc')->get();
        } elseif ($equipe === 'desenvolvimento') {
            $tarefas = Tarefa::orderBy('created_at', 'asc')->get();
        } elseif ($equipe === 'design') {
            $tarefas = Tarefa::where('equipe', 'design')
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            return response()->json([
                "Message" => "Equipe não reconhecida"
            ], 403);
        }

        return response()->json([
            "Tarefas" => $tarefas
        ], 200);
    }

    // CONSULTA TAREFA PELO ID
    public function consulta_tarefa(Request $request, $id)
    {
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);

        if (!$token) {
            return response()->json([
                "Message" => "Atenção, token não informado"
            ], 422);
        }

        $usuario = Usuario::where('token', $token)->first();
        if(!$usuario){
            return response()->json([
                "Message" => "Atenção, token inválido"
            ], 401);
        }

        $tarefa = Tarefa::with('subtarefas')->find($id);
        if(!$tarefa){
            return response()->json([
                "Message" => "Tarefa não encontrada"
            ], 404);
        }

        if($usuario->equipe == 'Gerente de Projeto'){
            return response()->json([
                "Tarefas" => $tarefa
            ]);
        }

        // Desenvolvedor
        if ($usuario->equipe === 'desenvolvimento') {
            if ($tarefa->equipe !== 'desenvolvimento') {
                return response()->json([
                    "Message" => "Você não tem permissão para visualizar esta tarefa"
                ], 403);
            }
            return response()->json([
                "Tarefas" => $tarefa
            ], 200);
        }

        // Designer
        if ($usuario->equipe === 'design') {
            if ($tarefa->equipe !== 'design') {
                return response()->json([
                    "Message" => "Você não tem permissão para visualizar esta tarefa"
                ], 403);
            }
            return response()->json([
                "Tarefas" => $tarefa
            ], 200);
        }
    }

    public function tarefas_equipe(Request $request, $equipe)
    {
        // Pega o token do header
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);

        if (!$token) {
            return response()->json([
                "Message" => "Atenção, token não informado"
            ], 422);
        }

        // Pega o usuário pelo token
        $usuario = Usuario::where('token', $token)->first();
        if (!$usuario) {
            return response()->json([
                "Message" => "Atenção, token inválido"
            ], 401);
        }

        // Converte para minúsculas para evitar problema de maiúsculas/minúsculas
        $usuarioEquipe = strtolower($usuario->equipe);
        $equipe = strtolower($equipe);

        // Checa permissões
        if ($usuarioEquipe === 'gerente de projeto') {
            // Gerente vê qualquer equipe
        } elseif ($usuarioEquipe === 'desenvolvimento' && $equipe !== 'desenvolvimento') {
            return response()->json([
                "Message" => "Você não tem permissão para visualizar esta equipe"
            ], 403);
        } elseif ($usuarioEquipe === 'design' && $equipe !== 'design') {
            return response()->json([
                "Message" => "Você não tem permissão para visualizar esta equipe"
            ], 403);
        }

        // Busca todas as tarefas da equipe com subtarefas
        $tarefas = Tarefa::with('subtarefas')
            ->where('equipe', $equipe)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            "Tarefas" => $tarefas
        ], 200);
    }

    public function altera_tarefa(Request $request, $id)
    {
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);

        if (!$token) {
            return response()->json(["Message" => "Atenção, token não informado"], 422);
        }

        $usuario = Usuario::where('token', $token)->first();
        if (!$usuario) {
            return response()->json(["Message" => "Atenção, token inválido"], 401);
        }

        // Padroniza campos
        if ($request->has('status')) $request->merge(['status' => strtolower(trim($request->status))]);
        if ($request->has('prioridade')) $request->merge(['prioridade' => strtolower(trim($request->prioridade))]);

        // Validação dos campos opcionais
        $validator = Validator::make($request->all(), [
            'descricao' => 'nullable|string',
            'prazo' => 'nullable|date',
            'status' => 'nullable|in:pendente,em andamento,concluída',
            'prioridade' => 'nullable|in:alta,média,baixa',
            'responsavel' => 'nullable|integer|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return response()->json(["Message" => "Erro de validação", "Erros" => $validator->errors()], 422);
        }

        // Busca a tarefa pelo id da rota
        $tarefa = Tarefa::find($id);
        if (!$tarefa) {
            return response()->json(["Message" => "Tarefa não encontrada"], 404);
        }

        $equipe = strtolower($usuario->equipe);
        if (($equipe === 'desenvolvimento' && strtolower($tarefa->equipe) !== 'desenvolvimento') ||
            ($equipe === 'design' && strtolower($tarefa->equipe) !== 'design')) {
            return response()->json(["Message" => "Você não pode alterar tarefas de outra equipe"], 403);
        }

        // Atualiza apenas os campos enviados
        $campos = ['descricao', 'prazo', 'status', 'prioridade', 'responsavel'];
        foreach ($campos as $campo) {
            if ($request->has($campo)) {
                $tarefa->$campo = $request->$campo;
            }
        }

        $tarefa->save();

        return response()->json([
            "Message" => "Tarefa atualizada com sucesso!",
            "Tarefa" => $tarefa
        ], 200);
    }

    public function delete_tarefa(Request $request, $id)
    {
        // Pega o token do header
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);

        if (!$token) {
            return response()->json([
                "Message" => "Atenção, token não informado"
            ], 422);
        }

        // Verifica se o usuário existe
        $usuario = Usuario::where('token', $token)->first();
        if (!$usuario) {
            return response()->json([
                "Message" => "Atenção, token inválido"
            ], 401);
        }

        // Só Gerente de Projeto pode deletar
        if (strtolower($usuario->equipe) !== 'gerente de projeto') {
            return response()->json([
                "Message" => "Apenas o Gerente de Projeto pode deletar tarefas"
            ], 403);
        }

        // Busca a tarefa
        $tarefa = Tarefa::find($id);
        if (!$tarefa) {
            return response()->json([
                "Message" => "Tarefa não encontrada"
            ], 404);
        }

        // Verifica se a tarefa tem subtarefas
        $subtarefas = Subtarefa::where('tarefa_id', $id)->count();
        if ($subtarefas > 0) {
            return response()->json([
                "Message" => "Não é possível deletar uma tarefa que contenha subtarefas"
            ], 422);
        }

        // Deleta a tarefa
        $tarefa->delete();

        return response()->json([
            "Message" => "Tarefa deletada com sucesso!"
        ], 200);
    }

}
