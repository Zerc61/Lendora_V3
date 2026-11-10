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
        'case_category',
        'chronology',
        'maintenance_id',
        'repair_subtotal',
        'subsidy_percent',
        'subsidy_amount',
        'admin_fee',
        'total_amount',
        'status',
        'installment_months',
        'due_at',
        'payment_terms_days',
        'issued_at',
        'issued_by',
        'paid_at',
        'recovery_step',
        'photo_paths',
        'escalated_at',
        'escalated_by',
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
            'payment_terms_days' => 'integer',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'recovery_step' => 'integer',
            'photo_paths' => 'array',
            'escalated_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(PenaltyItem::class);
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function escalator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
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
