<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetQcCheck extends Model
{
    protected $fillable = [
        'asset_id',
        'technician_id',
        'checked_at',
        'result',
        'sensor_cleanliness',
        'shutter_accuracy',
        'firmware_version',
        'notes',
        'certificate_path',
        'next_due_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'next_due_at' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
