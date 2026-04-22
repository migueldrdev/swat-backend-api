<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\DestroyDocumentRequest;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Sube un nuevo documento (Solo para Administradores)
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        // La validación de permisos (authorize) y reglas está delegada en StoreDocumentRequest
        $document = $this->documentService->storeDocument(
            $request->validated(),
            $request->file('file'),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'message'  => 'Documento subido y registrado con éxito',
            'document' => $document
        ], 201);
    }

    /**
     * Borra lógicamente el documento por ID (Solo Administradores)
     */
    public function destroy(DestroyDocumentRequest $request, $id): JsonResponse
    {
        try {
            $this->documentService->destroyDocument(
                $id,
                $request->user(),
                $request->ip(),
                $request->userAgent()
            );

            return response()->json(['message' => 'Documento eliminado correctamente.'], 200);

        } catch (HttpException $e) {
            // Manejamos el error emitido por el Service con su código HTTP
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
