<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Inicia sesión de un usuario.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'message'      => 'Login exitoso',
                'access_token' => $result['token'],
                'token_type'   => 'Bearer',
                'user'         => [
                    'id'              => $result['user']->id,
                    'document_number' => $result['user']->document_number,
                    'role'            => $result['user']->role,
                ]
            ], 200);

        } catch (HttpException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }
}
