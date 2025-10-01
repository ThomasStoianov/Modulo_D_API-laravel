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
        // Validação básica dos campos
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'equipe' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'senha' => 'required|string',
        ]);

        // Campos faltando
        if ($validator->fails()) {
            // Verifica se algum campo está vazio ou não enviado
            if ($validator->errors()->hasAny(['nome','email','equipe','username','senha'])) {
                return response()->json([
                    "Message" => "Verifique novamente, campos faltando"
                ], 422);
            }
        }

        // Email inválido
        if ($validator->errors()->has('email')) {
            return response()->json([
                "Message" => "Verifique o e-mail, tente novamente"
            ], 422);
        }

        // Verifica se já existe usuário com email ou username
        if(Usuario::where('email', $request->email)->orWhere('username', $request->username)->exists()) {
            return response()->json([
                "Message" => "Usuário já cadastrado!"
            ], 422);
        }

        // Cria o usuário com senha criptografada
        Usuario::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'equipe' => $request->equipe,
            'username' => $request->username,
            'senha' => Hash::make($request->senha),
        ]);

        return response()->json([
            "Message" => "Cadastro efetuado com sucesso"
        ], 201);
    }

    // cria o metodo Login
    public function login(Request $request) {
        // prepara a validação se os campos foram enviados
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'senha' => 'required',
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

        $user->token = $token;
        $user->save();

        // retorna o token
        return response()->json([
            'token' => $token, 200,
        ]);
    }

    public function logout(Request $request) {
        // Pega o valor do header authorization
        $token = $request->header('Authorization');

        // remove o Bearer, restando so o token puro
        $token = str_replace('Bearer ', '', $token);

        // Se não veio nada ho header
        if(!$token) {
            return response()->json(['Message' => 'Atenção, token não informado'], 422);
        }

        // Procura no banco um usuario que tenha esse token salvo
        $user = Usuario::where('token', $token)->first();

        // se não encontrou esse token, então ele é invalido
        if(!$user) {
            return response()->json(['Message' => 'Atenção, token inválido'], 401);
        }

        // Apaga o token do banco
        $user->token = null;
        $user->save();

        return response()->json(['Message' => 'Logout efetuado com sucesso'], 200);
    }

}
