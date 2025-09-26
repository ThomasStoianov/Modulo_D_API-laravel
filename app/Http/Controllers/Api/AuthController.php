<?php

namespace App\Http\Controllers\Api; // Mostra onde o AuthController está

use App\Http\Controllers\Controller; // importa o controller base
use Illuminate\Http\Request; // importa a classe request, representa a requisição HTTP
use App\Models\Usuario; // importa o model Usuario que representa a tabela usuarios no banco, permite criar,deletar,buscar,atualizar usuarios
use Illuminate\Support\Facades\Validator; // importa o validator, serve para validar dados de entrada antes de salvar no banco, tipo verificar o formato do email, se todos os campos foram enviados etc
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

// importa o hash para criptografar senhas

// Define a classe AuthController, herda funcionalidades do Controller base
class AuthController extends Controller
{
    // cria um metodo publico chamado signup que qualquer parte do laravel ou cliente HTTP pode chamar, recebe um parametro ($request), que contém os dados enviados na requisição
    public function signup(Request $request) {
        // cria uma variável que recebe a classe validator e prepara uma validação dos campos
        // Validator::make(dados, regras para aplicar)
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:usuarios',
            'equipe' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:usuarios',
            'senha' => 'required|string',
        ]);

        // Executa a validação e se falhar mostra mensagem de erro
        if ($validator->fails()) {
            return response()->json(["Message" => "Campos faltando ou inválidos"]);
        }

        // Vai no model e faz uma consulta no banco de dados se já existe uma usuários cadastrado
        // ->exists() retorna true se já existe um registro, ou false se não existe nenhum
        if(Usuario::where('email', $request->email)->orWhere('username', $request->username)->exists()) {
            return response()->json(['Message' => 'Usuário já cadastrado!'], 422);
        }

        // Pega o a tabela de usuarios e cria um novo registro com os dados enviados na requisição
        Usuario::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'equipe' => $request->equipe,
            'username' => $request->username,
            'senha' => Hash::make($request->senha),
        ]);

        return response()->json(['Message' => 'Cadastro efetuado com sucesso!'], 201);
    }

    // cria o metodo Login
    public function login(Request $request) {
        // prepara a validação se os campos foram enviados
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'senha' => 'required|string',
        ]);

        // Se algum campo estiver faltando ou incorreto
        if ($validator->fails()) {
            return response()->json(['Message' => 'Verifique novamente, campos faltando'], 200);
        }

        // Pega no banco de dados o registro onde a coluna username seja a mesma que enviado na requisição
        // $user passa a ser um objeto com todos os dados do username, como nome, senha, equipe
        $user = Usuario::where('username', $request->username)->first();

        // verifica se o usuário não foi encontrado ou se a senha enviada não bateu com a do banco
        if(!$user || !Hash::check($request->senha, $user->senha)) {
            return response()->json(['Message' => 'Login inválido, tente novamente!'], 401);
        }

        // gera o token, JWTAuth é a biblioteca para gerar os tokens
        // fromUser($user), recebe como parametro o usuario do banco,
        // pega os dados, tipo id, nome etc e codifica em formato JWT e assina com a chave secreta
        $token = JWTAuth::fromUser($user);

        // retorna o token
        return response()->json([
            'token' => $token, 200,
        ]);
    }
}
