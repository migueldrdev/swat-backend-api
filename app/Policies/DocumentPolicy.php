<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    /**
     * Determina si el usuario puede ver la lista de documentos.
     */
    public function viewAny(User $user): bool
    {
        return true; // Cualquier usuario autenticado puede ver su lista
    }

    /**
     * Determina si el usuario puede descargar el documento.
     */
    public function download(User $user, Document $document): bool
    {
        // Admin puede todo, trabajador solo lo suyo
        return $user->role === 'admin' || $user->id === $document->user_id;
    }

    /**
     * Determina si el usuario puede borrar el documento.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->role === 'admin';
    }
}
