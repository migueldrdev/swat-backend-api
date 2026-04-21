<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validar los datos de entrada
        $request->validate([
            'document_number' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Intentar autenticar (usamos document_number en lugar de email)
        if (!Auth::attempt($request->only('document_number', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas.'
            ], 401);
        }

        $user = Auth::user();

        // 3. Validar si el usuario está activo (cesado no entra)
        if (!$user->is_active) {
            // Revocamos la sesión que Auth::attempt acaba de iniciar
            Auth::logout();
            return response()->json([
                'message' => 'Usuario inactivo. Comuníquese con administración.'
            ], 403);
        }

        // 4. Generar el Token para Vue/Quasar
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Registrar la Auditoría (¡El blindaje legal en acción!)
        AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => 'logged_in',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // 6. Retornar la respuesta exitosa
        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'document_number' => $user->document_number,
                'role' => $user->role,
            ]
        ]);
    }
}
