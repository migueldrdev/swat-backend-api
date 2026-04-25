<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Http\Resources\AuditLogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AuditController extends Controller
{
    use AuthorizesRequests;

    /**
     * Muestra todos los logs de auditoría (Solo Admins)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Acceso denegado.');
        }

        $logs = AuditLog::with(['user', 'document'])
            ->latest()
            ->paginate(50);

        return AuditLogResource::collection($logs);
    }
}
