<?php

namespace App\Models;

use App\Enums\HandoverType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Handover extends Model
{
    protected $fillable = [
        'booking_id',
        'code',
        'type',
        'performed_by',
        'shift_label',
        'borrower_signature_path',
        'operator_signature_path',
        'photo_paths',
        'checklist',
        'happened_at',
        'due_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => HandoverType::class,
            'photo_paths' => 'array',
            'checklist' => 'array',
            'happened_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(HandoverItem::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
