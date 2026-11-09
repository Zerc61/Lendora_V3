<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function log(
        string $action,
        Model $subject,
        string $description,
        ?User $actor = null,
        array $properties = [],
    ): void {
        ActivityLog::create([
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}
