<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Intenta autenticar a un usuario, validando su estado activo
     * y genera su token de acceso junto con el registro en auditoría.
     *
     * @param array $credentials
     * @param string $ipAddress
     * @param string|null $userAgent
     * @return array
     * @throws HttpException
     * @throws \Throwable
     */
    public function login(array $credentials, string $ipAddress, ?string $userAgent): array
    {
        // 1. Protección contra ataques de fuerza bruta (Rate Limiting)
        // Usamos el número de documento y la IP para generar la llave del limitador
        $throttleKey = Str::lower($credentials['document_number']) . '|' . $ipAddress;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw new HttpException(429, 'Demasiados intentos de inicio de sesión. Por favor, intente de nuevo en ' . $seconds . ' segundos.');
        }

        // Usamos una transacción por si falla el registro en auditoría no dejar el intento a medias
        return DB::transaction(function () use ($credentials, $ipAddress, $userAgent, $throttleKey) {
            if (!Auth::attempt($credentials)) {
                // Si falla, incrementamos el contador de intentos
                RateLimiter::hit($throttleKey, 60); // Bloqueo temporal de 60 segundos si llega al límite
                throw new HttpException(401, 'Credenciales incorrectas.');
            }

            // Si es correcto, borramos los intentos previos
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                throw new HttpException(403, 'Usuario inactivo. Comuníquese con administración.');
            }

            // Generar Token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Registrar Auditoría
            AuditLog::query()->create([
                'user_id'    => $user->id,
                'action'     => 'logged_in',
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return [
                'token' => $token,
                'user'  => $user,
            ];
        });
    }
}
