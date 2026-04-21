<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // Método para subir un documento (Solo para Administradores)
    public function store(Request $request)
    {
        // 1. Verificamos que quien intenta subir sea Administrador
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Acceso denegado. Solo administradores.'], 403);
        }

        // 2. Validamos los datos entrantes
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'type' => 'required|string', // boleta, alta, baja, contrato
            'file' => 'required|mimes:pdf|max:5120', // Solo PDF, máximo 5MB
        ]);

        // 3. Subimos el archivo a Supabase (S3)
        // Lo guardamos en una carpeta con el DNI/Documento del trabajador para mantener orden
        $worker = User::query()->findOrFail($request->user_id);
        $folderPath = 'trabajadores/' . $worker->document_number;

        // Esto sube el archivo y nos devuelve la ruta segura
        $path = $request->file('file')->store($folderPath, 's3');

        // 4. Guardamos el registro en la base de datos
        $document = Document::query()->create([
            'user_id' => $worker->id,
            'title' => $request->title,
            'file_path' => $path,
            'type' => $request->type,
        ]);

        // 5. Registramos la auditoría de quién subió el documento
        AuditLog::query()->create([
            'user_id' => $request->user()->id, // El ID del administrador
            'document_id' => $document->id,
            'action' => 'uploaded',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // TODO: Aquí en el futuro llamaremos a la función para enviar el correo al trabajador

        return response()->json([
            'message' => 'Documento subido y registrado con éxito',
            'document' => $document
        ], 201);
    }
}
