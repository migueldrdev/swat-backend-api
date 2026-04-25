<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuditController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Rutas protegidas (Requieren Token de sesión)
    Route::middleware('auth:sanctum')->group(function () {

        // Obtener los datos del usuario logueado sanitizados
        Route::get('/user', function (Request $request) {
            return new \App\Http\Resources\UserResource($request->user());
        });

        // Rutas de Documentos
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::post('/documents', [DocumentController::class, 'store']);
        Route::get('/documents/{id}/download', [DocumentController::class, 'download']);
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);

        // Rutas de Auditoría
        Route::get('/audit-logs', [AuditController::class, 'index']);

    });
});
