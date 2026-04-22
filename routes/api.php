<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;

Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (Requieren Token de sesión)
Route::middleware('auth:sanctum')->group(function () {

    // Obtener los datos del usuario logueado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rutas de Documentos
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);

});
