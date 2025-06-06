<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            Log::channel('inventario')->info("{$model->getTable()}.created", [
                'id' => $model->id,
                'changes' => $model->getChanges(),
            ]);
        });

        static::updated(function (Model $model) {
            Log::channel('inventario')->info("{$model->getTable()}.updated", [
                'id' => $model->id,
                'original' => $model->getOriginal(),
                'changes' => $model->getChanges(),
            ]);
        });

        static::deleted(function (Model $model) {
            Log::channel('inventario')->info("{$model->getTable()}.deleted", [
                'id' => $model->id,
                'data' => $model->toArray(),
            ]);
        });
    }

    public function getActivityLogs(): array
    {
        return Log::channel('inventario')
            ->where(fn($log) => str_contains($log->message, "{$this->getTable()}.{$this->id}"))
            ->all();
    }
}