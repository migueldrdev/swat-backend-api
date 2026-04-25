<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'title', 'file_path', 'type'];

    // Un documento pertenece a un usuario
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Un documento tiene muchos registros de auditoría (quiénes lo han descargado)
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
