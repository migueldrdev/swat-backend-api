<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cambiamos 'dni' por 'document_number' y quitamos el límite de 8.
            $table->string('document_number')->unique()->after('id')->nullable();

            // Cambiamos el enum restrictivo por un string flexible.
            // Si mañana entra un "cliente" o "postulante", la BD lo acepta sin quejarse.
            $table->string('role')->default('agente')->after('email');

            // Opcional pero recomendado para escalabilidad (Saber si sigue trabajando)
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['document_number', 'role', 'is_active']);
        });
    }
};
