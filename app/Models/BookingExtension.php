<?php

namespace App\Models;

use App\Enums\BookingExtensionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtension extends Model
{
    protected $fillable = [
        'booking_id',
        'requested_by',
        'requested_days',
        'requested_due_at',
        'reason',
        'status',
        'decided_by',
        'decided_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'requested_days' => 'integer',
            'requested_due_at' => 'datetime',
            'status' => BookingExtensionStatus::class,
            'decided_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
