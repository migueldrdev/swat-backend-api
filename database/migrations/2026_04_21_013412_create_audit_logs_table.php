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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // ¿Quién hizo la acción?
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ¿Sobre qué documento? (nullable porque pueden haber acciones como "inició sesión" que no requieren documento)
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();

            // Qué hizo: 'downloaded', 'uploaded', 'logged_in'
            $table->string('action');

            // Datos legales vitales para rastreo
            $table->string('ip_address')->nullable(); // Desde qué internet lo hizo
            $table->text('user_agent')->nullable(); // Desde qué celular o navegador (Ej: Chrome en Android)

            $table->timestamps(); // Esto nos da la fecha y hora exacta (created_at)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
