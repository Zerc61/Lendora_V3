<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
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
                'booking_received' => 'ui.notification.type.booking_received',
                'booking_decision' => 'ui.notification.type.booking_decision',
                'loan_pickup' => 'ui.notification.type.loan_pickup',
                'loan_due_reminder' => 'ui.notification.type.loan_due_reminder',
            ],
            'staff' => [
                'asset_damage' => 'ui.notification.type.asset_damage',
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

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }
}
