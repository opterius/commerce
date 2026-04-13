<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $entityName = null,
        ?string $description = null,
        ?array $metadata = null,
    ): void {
        try {
            $staffId = auth('staff')->id();
            $clientId = auth('client')->id();

            ActivityLog::create([
                'staff_id' => $staffId,
                'client_id' => $clientId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'entity_name' => $entityName,
                'description' => $description,
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Never let logging break the calling operation
            report($e);
        }
    }
}
