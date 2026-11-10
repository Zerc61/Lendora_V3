<?php

namespace App\Models;

use App\Enums\InspectionContext;
use App\Enums\InspectionFollowUp;
use App\Enums\InspectionResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    protected $fillable = [
        'booking_item_id',
        'asset_id',
        'code',
        'inspector_id',
        'context',
        'result',
        'checklist',
        'photo_paths',
        'condition_score',
        'follow_up',
        'maintenance_id',
        'notes',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'result' => InspectionResult::class,
            'context' => InspectionContext::class,
            'follow_up' => InspectionFollowUp::class,
            'checklist' => 'array',
            'photo_paths' => 'array',
            'condition_score' => 'integer',
            'checked_at' => 'datetime',
        ];
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
