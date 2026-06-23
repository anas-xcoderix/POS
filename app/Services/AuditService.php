<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $documentNo = null,
        ?string $remarks = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $model ? $model::class : null,
            'auditable_id' => $model?->getKey(),
            'document_no' => $documentNo,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'remarks' => $remarks,
        ]);
    }

    public function logModelChange(string $action, Model $model, array $old, array $new, ?string $documentNo = null): AuditLog
    {
        $changed = [];
        foreach ($new as $key => $value) {
            if (array_key_exists($key, $old) && $old[$key] != $value) {
                $changed[$key] = ['from' => $old[$key], 'to' => $value];
            }
        }

        return $this->log($action, $model, $old, $changed ?: $new, $documentNo);
    }
}
