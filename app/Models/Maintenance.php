<?php

namespace App\Models;

use App\Enums\MaintenanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    protected $fillable = [
        'asset_id',
        'reported_by',
        'assigned_to',
        'status',
        'description',
        'started_at',
        'finished_at',
        'cost',
        'result_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MaintenanceStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'cost' => 'integer',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
