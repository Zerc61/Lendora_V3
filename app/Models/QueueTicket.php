<?php

namespace App\Models;

use App\Enums\QueueServiceType;
use App\Enums\QueueTicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueTicket extends Model
{
    protected $fillable = [
        'organization_id',
        'number',
        'service_type',
        'status',
        'counter',
        'user_id',
        'booking_id',
        'nim',
        'issued_at',
        'called_at',
        'served_at',
        'service_date',
    ];

    protected function casts(): array
    {
        return [
            'service_type' => QueueServiceType::class,
            'status' => QueueTicketStatus::class,
            'issued_at' => 'datetime',
            'called_at' => 'datetime',
            'served_at' => 'datetime',
            'service_date' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
