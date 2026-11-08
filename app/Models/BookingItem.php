<?php

namespace App\Models;

use App\Enums\BookingItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'asset_id',
        'status',
        'condition_out',
        'condition_in',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingItemStatus::class,
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class)->latestOfMany();
    }
}
