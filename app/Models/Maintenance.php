<?php

namespace App\Models;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Maintenance extends Model
{
    protected $fillable = [
        'asset_id',
        'code',
        'reported_by',
        'assigned_to',
        'vendor',
        'status',
        'priority',
        'description',
        'started_at',
        'due_at',
        'finished_at',
        'cost',
        'labor_cost',
        'result_notes',
        'final_condition',
        'progress',
    ];

    protected function casts(): array
    {
        return [
            'status' => MaintenanceStatus::class,
            'priority' => MaintenancePriority::class,
            'started_at' => 'datetime',
            'due_at' => 'datetime',
            'finished_at' => 'datetime',
            'cost' => 'integer',
            'labor_cost' => 'integer',
            'progress' => 'array',
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

    public function parts(): HasMany
    {
        return $this->hasMany(MaintenancePart::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }

    public function partsTotal(): int
    {
        return (int) $this->parts->sum(fn (MaintenancePart $part) => $part->subtotal());
    }

    public function totalCost(): int
    {
        return $this->partsTotal() + $this->labor_cost;
    }
}
