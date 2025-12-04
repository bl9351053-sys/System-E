<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->record('created', $model, [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->record('updated', $model, $model->getOriginal(), $model->getAttributes());
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, $model->getAttributes(), []);
    }

    protected function record(string $action, Model $model, array $oldValues, array $newValues): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'auditable_type' => $model::class,
                'auditable_id' => $model->getKey(),
                'old_values' => $oldValues ?: null,
                'new_values' => $newValues ?: null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            logger()->error('Unable to record audit: ' . $e->getMessage());
        }
    }
}
