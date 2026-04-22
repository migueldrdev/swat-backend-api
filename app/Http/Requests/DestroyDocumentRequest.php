<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyDocumentRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar la solicitud.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Reglas de validación (el ID en la ruta se validará en otro punto, o no requiere body).
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Manejo de autorización fallida personalizado.
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json(['message' => 'Acceso denegado. Solo administradores pueden borrar documentos.'], 403)
        );
    }
}
