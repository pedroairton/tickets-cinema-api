<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $user = User::create($request->validated());

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(LoginRequest $request) {
        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Usuário ou senha inválidos.'
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário logado com sucesso',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout() {
        auth()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Usuário deslogado com sucesso'
        ], 200);
    }

    public function me(){
        return response()->json([
            'user' => auth()->user()
        ], 200);
    }
}
