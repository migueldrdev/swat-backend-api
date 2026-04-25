<?php

namespace App\Services;

use App\Models\Document;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    /**
     * Sube un documento a S3 y lo registra.
     *
     * @param array $data Los datos validados
     * @param UploadedFile $file El archivo físico
     * @param User $admin El usuario administrador realizando la acción
     * @param string $ipAddress La IP del cliente
     * @param string|null $userAgent El User Agent del cliente
     * @return Document
     * @throws \Throwable
     */
    public function storeDocument(array $data, UploadedFile $file, User $admin, string $ipAddress, ?string $userAgent): Document
    {
        $worker = User::query()->findOrFail($data['user_id']);
        $folderPath = 'trabajadores/' . $worker->document_number;

        // Subir a S3
        $path = $file->store($folderPath, 's3');

        // Utilizamos una transacción de Base de Datos para garantizar Atomicidad (Rollbacks integrados)
        return DB::transaction(function () use ($worker, $data, $path, $admin, $ipAddress, $userAgent) {
            // Crear registro en la base de datos
            $document = Document::query()->create([
                'user_id'   => $worker->id,
                'title'     => $data['title'],
                'file_path' => $path,
                'type'      => $data['type'],
            ]);

            // Registrar auditoría
            $this->logAction($admin->id, $document->id, 'uploaded', $ipAddress, $userAgent);

            // Disparar notificación asíncrona
            \App\Jobs\NotifyWorkerOfNewDocument::dispatch($document);

            return $document;
        });
    }

    /**
     * Elimina el documento de manera lógica o lanza excepciones si hay conflictos.
     *
     * @param mixed $id El ID del documento
     * @param User $admin El administrador ejecutador
     * @param string $ipAddress La IP
     * @param string|null $userAgent El user agent
     * @return void
     *
     * @throws HttpException Para respuestas como 404 o 409
     * @throws \Throwable
     */
    public function destroyDocument($id, User $admin, string $ipAddress, ?string $userAgent): void
    {
        $document = Document::withTrashed()->find($id);

        if (!$document) {
            throw new HttpException(404, 'Documento no encontrado.');
        }

        if ($document->trashed()) {
            throw new HttpException(409, 'El documento ya ha sido eliminado previamente.');
        }

        // Transacción para garantizar que la auditoría y el borrado lógico ocurran sí o sí juntos
        DB::transaction(function () use ($document, $admin, $ipAddress, $userAgent) {
            // Registrar la auditoría de borrado antes del soft delete
            $this->logAction($admin->id, $document->id, 'deleted', $ipAddress, $userAgent);

            // Borrado Lógico
            $document->delete();
        });

        // Hacemos el borrado físico de S3 *después* de que la base de datos confirmó los cambios exitosamente
        if (Storage::disk('s3')->exists($document->file_path)) {
            Storage::disk('s3')->delete($document->file_path);
        }
    }

    /**
     * Genera una URL temporal para descarga y registra la acción en auditoría.
     */
    public function getDownloadUrl($id, User $user, string $ipAddress, ?string $userAgent): string
    {
        $document = Document::findOrFail($id);

        // Registro de auditoría (Prueba de entrega legal)
        $this->logAction($user->id, $document->id, 'downloaded', $ipAddress, $userAgent);

        // Generar URL firmada válida por 5 minutos
        return Storage::disk('s3')->temporaryUrl(
            $document->file_path,
            now()->addMinutes(5)
        );
    }

    /**
     * Guarda el log en auditoría.
     */
    private function logAction(int $userId, int $documentId, string $action, string $ip, ?string $userAgent): void
    {
        AuditLog::query()->create([
            'user_id'     => $userId,
            'document_id' => $documentId,
            'action'      => $action,
            'ip_address'  => $ip,
            'user_agent'  => $userAgent,
        ]);
    }
}
