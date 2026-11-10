<?php

namespace App\Models;

use App\Enums\AssetCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandoverItem extends Model
{
    protected $fillable = [
        'handover_id',
        'booking_item_id',
        'condition',
        'accessories',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'condition' => AssetCondition::class,
            'accessories' => 'array',
        ];
    }

    public function handover(): BelongsTo
    {
        return $this->belongsTo(Handover::class);
    }

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class);
    }
}
