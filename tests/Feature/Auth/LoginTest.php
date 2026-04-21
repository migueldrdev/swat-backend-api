<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    // Esto asegura que la BD se limpie (en nuestro SQLite temporal) antes de cada test
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials()
    {
        // 1. Preparar el escenario: Crear un usuario de prueba
        $user = User::factory()->create([
            'document_number' => '76543210',
            'password' => Hash::make('password123'),
            'role' => 'agente',
            'is_active' => true,
        ]);

        // 2. Ejecutar la acción: Hacer la petición POST al login
        $response = $this->postJson('/api/login', [
            'document_number' => '76543210',
            'password' => 'password123',
        ]);

        // 3. Afirmar (Assert): Verificar que salió bien
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'user' => ['id', 'document_number', 'role']
            ]);

        // Verificar que se guardó el log de auditoría
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'logged_in'
        ]);
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        $user = User::factory()->create([
            'document_number' => '76543210',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'document_number' => '76543210',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'document_number' => '88888888',
            'password' => Hash::make('password123'),
            'is_active' => false, // Usuario dado de baja
        ]);

        $response = $this->postJson('/api/login', [
            'document_number' => '88888888',
            'password' => 'password123',
        ]);

        // Debe retornar 403 Forbidden y un mensaje específico
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Usuario inactivo. Comuníquese con administración.'
            ]);
    }
}
