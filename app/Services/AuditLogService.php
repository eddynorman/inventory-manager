<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function log(
        string $action,
        string $model,
        ?int $modelId = null,
        ?string $description = null,
        ?array $properties = null
    ): void {

        AuditLog::create([

            'user_id' => Auth::id(),

            'action' => $action,

            'model' => $model,

            'model_id' => $modelId,

            'description' => $description,

            'properties' => $properties,
        ]);
    }
}
