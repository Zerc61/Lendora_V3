<?php

namespace App\Models;

use App\Enums\HandoverType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Handover extends Model
{
    protected $fillable = [
        'booking_id',
        'type',
        'performed_by',
        'happened_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => HandoverType::class,
            'happened_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
