<?php

namespace App\Models;

use App\Enums\PenaltyStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'nim',
        'study_program',
        'semester',
        'lab_tier',
        'lab_member_id',
        'cohort_year',
        'study_concentration',
        'dpa_name',
        'dpa_nidn',
        'compliance_score',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'email_otp_enabled',
        'whatsapp_opt_in',
        'academic_verified_at',
        'password',
        'phone',
        'role_id',
        'organization_id',
        'status',
        'notification_preferences',
    ];

    /**
     * Notification keys a user can switch off, grouped by audience.
     *
     * @return array<string, array<string, string>>
     */
    public static function notificationTypes(): array
    {
        return [
            'borrower' => [
                'booking_received' => 'notification.type.booking_received',
                'booking_decision' => 'notification.type.booking_decision',
                'loan_pickup' => 'notification.type.loan_pickup',
                'loan_due_reminder' => 'notification.type.loan_due_reminder',
            ],
            'staff' => [
                'asset_damage' => 'notification.type.asset_damage',
            ],
        ];
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'academic_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'cohort_year' => 'integer',
            'compliance_score' => 'integer',
            'email_otp_enabled' => 'boolean',
            'whatsapp_opt_in' => 'boolean',
            'password' => 'hashed',
            'notification_preferences' => 'array',
        ];
    }

    public function wantsNotification(string $type): bool
    {
        return (bool) ($this->notification_preferences[$type] ?? true);
    }

    /**
     * Notification keys relevant to this user's role.
     *
     * @return array<string, string>
     */
    public function notificationAudience(): array
    {
        return $this->hasRole('borrower')
            ? self::notificationTypes()['borrower']
            : self::notificationTypes()['staff'];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(AssetFavorite::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(UserCertification::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AssetReview::class, 'borrower_id');
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class, 'borrower_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'borrower_id');
    }

    public function openPenalties(): HasMany
    {
        return $this->penalties()
            ->whereIn('status', [
                PenaltyStatus::WaitingPayment,
                PenaltyStatus::Partial,
                PenaltyStatus::Overdue,
                PenaltyStatus::InstallmentRequested,
            ]);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }
}
