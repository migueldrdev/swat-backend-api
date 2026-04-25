<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user'        => new UserResource($this->whenLoaded('user')),
            'document_id' => $this->document_id,
            'action'      => $this->action,
            'ip_address'  => $this->ip_address,
            'user_agent'  => $this->user_agent,
            'created_at'  => $this->created_at->toIso8601String(),
        ];
    }
}
