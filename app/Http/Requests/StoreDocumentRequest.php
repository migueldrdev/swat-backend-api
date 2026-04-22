<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Document;
use Illuminate\Validation\Validator;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string|max:255',
            'type'    => 'required|string',
            'file'    => 'required|mimes:pdf|max:5120',
        ];
    }

    /**
     * Configura el validador con reglas adicionales (Anti-Duplicidad).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $documentExists = Document::query()
                ->where('user_id', $this->user_id)
                ->where('type', $this->type)
                ->where('title', $this->title)
                ->exists();

            if ($documentExists) {
                // Agregar un error manual general o al campo file
                $validator->errors()->add('file', 'Error: Ya existe un documento de tipo "' . $this->type . '" con el título "' . $this->title . '" para este trabajador.');
            }
        });
    }

    /**
     * Mensajes de error personalizados para autorización fallida.
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json(['message' => 'Acceso denegado. Solo administradores pueden subir documentos.'], 403)
        );
    }
}
