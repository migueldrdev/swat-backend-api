<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\DestroyDocumentRequest;
use App\Services\DocumentService;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Lista los documentos del usuario (o todos si es admin)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Document::query();

        // Si no es admin, solo ve los suyos
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return DocumentResource::collection($query->latest()->paginate(20));
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
            'document' => new DocumentResource($document)
        ], 201);
    }

    /**
     * Genera link de descarga y registra auditoría
     */
    public function download(Request $request, $id): JsonResponse
    {
        $document = Document::findOrFail($id);

        $this->authorize('download', $document);

        $url = $this->documentService->getDownloadUrl(
            $id,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'download_url' => $url
        ]);
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
