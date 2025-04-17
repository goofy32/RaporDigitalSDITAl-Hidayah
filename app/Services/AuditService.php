<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action in the audit trail
     *
     * @param string $action The action being performed (login, create, update, delete, etc.)
     * @param string|null $modelType The type of model being affected
     * @param int|null $modelId The ID of the model being affected
     * @param string|null $description A description of the action
     * @param array|null $oldValues Previous values (for updates)
     * @param array|null $newValues New values (for updates)
     * @return AuditLog
     */
    public static function log(
        string $action, 
        ?string $modelType = null, 
        ?int $modelId = null, 
        ?string $description = null, 
        ?array $oldValues = null, 
        ?array $newValues = null
    ): AuditLog {
        // Determine the authenticated user type and ID
        $userType = null;
        $userId = null;
        
        if (Auth::guard('web')->check()) {
            $userType = 'App\\Models\\User';
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('guru')->check()) {
            $userType = 'App\\Models\\Guru';
            $userId = Auth::guard('guru')->id();
        }
        
        // Create the audit log entry
        return AuditLog::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
    
    /**
     * Log a login attempt
     * 
     * @param string $status 'success' or 'failed'
     * @param string $username The username that attempted to login
     * @return AuditLog
     */
    public static function logLogin(string $status, string $username): AuditLog
    {
        $action = $status === 'success' ? 'login_success' : 'login_failed';
        return self::log(
            $action,
            null,
            null,
            "Login attempt with username: {$username}"
        );
    }
    
    /**
     * Log a logout event
     * 
     * @return AuditLog
     */
    public static function logLogout(): AuditLog
    {
        return self::log('logout');
    }
    
    /**
     * Log model creation
     * 
     * @param Model $model The model that was created
     * @param string|null $description Additional description
     * @return AuditLog
     */
    public static function logCreated($model, ?string $description = null): AuditLog
    {
        $modelType = get_class($model);
        $modelName = class_basename($modelType);
        
        if (!$description) {
            $description = "{$modelName} created";
        }
        
        return self::log(
            'created',
            $modelType,
            $model->id,
            $description,
            null,
            $model->toArray()
        );
    }
    
    /**
     * Log model update
     * 
     * @param Model $model The model after being updated
     * @param array $oldValues The old values before update
     * @param string|null $description Additional description
     * @return AuditLog
     */
    public static function logUpdated($model, array $oldValues, ?string $description = null): AuditLog
    {
        $modelType = get_class($model);
        $modelName = class_basename($modelType);
        
        if (!$description) {
            $description = "{$modelName} updated";
        }
        
        return self::log(
            'updated',
            $modelType,
            $model->id,
            $description,
            $oldValues,
            $model->toArray()
        );
    }
    
    /**
     * Log model deletion
     * 
     * @param Model $model The model being deleted
     * @param string|null $description Additional description
     * @return AuditLog
     */
    public static function logDeleted($model, ?string $description = null): AuditLog
    {
        $modelType = get_class($model);
        $modelName = class_basename($modelType);
        
        if (!$description) {
            $description = "{$modelName} deleted";
        }
        
        return self::log(
            'deleted',
            $modelType,
            $model->id,
            $description,
            $model->toArray(),
            null
        );
    }
}