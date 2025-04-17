<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Define relationships
    public function user()
    {
        if ($this->user_type === 'App\\Models\\User') {
            return $this->belongsTo(User::class, 'user_id');
        } elseif ($this->user_type === 'App\\Models\\Guru') {
            return $this->belongsTo(Guru::class, 'user_id');
        }
        
        return null;
    }

    // Scopes for filtering
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId, $userType = null)
    {
        if ($userType) {
            return $query->where('user_id', $userId)->where('user_type', $userType);
        }
        
        return $query->where('user_id', $userId);
    }

    public function scopeByModel($query, $modelId, $modelType)
    {
        return $query->where('model_id', $modelId)->where('model_type', $modelType);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
