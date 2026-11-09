<?php

namespace App\Models;

use App\Enums\BookingActivityType;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'organization_id',
        'borrower_id',
        'status',
        'purpose',
        'course_id',
        'activity_type',
        'pickup_location_id',
        'usage_location',
        'supervisor_name',
        'supervisor_letter_path',
        'sop_agreed_at',
        'submitted_at',
        'return_token',
        'requested_start_date',
        'requested_end_date',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'pickup_at',
        'due_at',
        'returned_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'activity_type' => BookingActivityType::class,
            'sop_agreed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'requested_start_date' => 'date',
            'requested_end_date' => 'date',
            'approved_at' => 'datetime',
            'pickup_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(PickupLocation::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(BookingExtension::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }

    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'booking_items')->withPivot('status');
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(Handover::class)->latest('happened_at');
    }

    public function durationInDays(): int
    {
        return (int) $this->requested_start_date->diffInDays($this->requested_end_date);
    }

    public function totalAssetValue(): int
    {
        return (int) $this->items->sum(fn (BookingItem $item) => (int) $item->asset->purchase_price);
    }

    public function isLongDuration(int $maxLoanDays): bool
    {
        return $this->durationInDays() > $maxLoanDays;
    }
}
