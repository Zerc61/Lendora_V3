<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PenaltyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penalty extends Model
{
    protected $fillable = [
        'organization_id',
        'booking_id',
        'asset_id',
        'borrower_id',
        'bap_number',
        'incident_at',
        'inspector_name',
        'damage_description',
        'repair_subtotal',
        'subsidy_percent',
        'subsidy_amount',
        'admin_fee',
        'total_amount',
        'status',
        'installment_months',
        'due_at',
        'paid_at',
        'recovery_step',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'incident_at' => 'datetime',
            'repair_subtotal' => 'integer',
            'subsidy_percent' => 'integer',
            'subsidy_amount' => 'integer',
            'admin_fee' => 'integer',
            'total_amount' => 'integer',
            'status' => PenaltyStatus::class,
            'installment_months' => 'integer',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'recovery_step' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paidAmount(): int
    {
        return (int) $this->payments
            ->where('status', PaymentStatus::Verified)
            ->sum('amount');
    }

    public function remainingAmount(): int
    {
        return max(0, $this->total_amount - $this->paidAmount());
    }
}
