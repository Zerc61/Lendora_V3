<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const DEFAULTS = [
        'max_loan_days' => '7',
        'operator_approval_limit' => '5000000',
        'return_reminder_enabled' => '1',
        'borrower_announcement' => '',
    ];

    public function maxLoanDays(?int $organizationId = null): int
    {
        return (int) $this->get('max_loan_days', 7, $organizationId);
    }

    public function operatorApprovalLimit(?int $organizationId = null): int
    {
        return (int) $this->get('operator_approval_limit', 5_000_000, $organizationId);
    }

    public function returnReminderEnabled(?int $organizationId = null): bool
    {
        return filter_var(
            $this->get('return_reminder_enabled', true, $organizationId),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    public function borrowerAnnouncement(?int $organizationId = null): string
    {
        return (string) $this->get('borrower_announcement', '', $organizationId);
    }

    public function get(string $key, mixed $default = null, ?int $organizationId = null): mixed
    {
        $organizationId = $this->resolveOrganizationId($organizationId);

        if ($organizationId === null) {
            return $default;
        }

        $value = Cache::remember(
            $this->cacheKey($organizationId, $key),
            now()->addHour(),
            fn () => Setting::query()
                ->where('organization_id', $organizationId)
                ->where('key', $key)
                ->value('value'),
        );

        return $value ?? $default;
    }

    public function set(string $key, mixed $value, ?int $organizationId = null): void
    {
        $organizationId = $this->resolveOrganizationId($organizationId);

        if ($organizationId === null) {
            throw new \RuntimeException('Organisasi tidak diketahui untuk menyimpan pengaturan.');
        }

        Setting::updateOrCreate(
            ['organization_id' => $organizationId, 'key' => $key],
            ['value' => $value === null ? null : (string) $value],
        );

        Cache::forget($this->cacheKey($organizationId, $key));
    }

    /**
     * @return array<string, string|null>
     */
    public function all(?int $organizationId = null): array
    {
        $organizationId = $this->resolveOrganizationId($organizationId);
        $settings = self::DEFAULTS;

        if ($organizationId === null) {
            return $settings;
        }

        $stored = Setting::query()
            ->where('organization_id', $organizationId)
            ->pluck('value', 'key');

        foreach ($stored as $key => $value) {
            $settings[$key] = $value;
        }

        return $settings;
    }

    private function resolveOrganizationId(?int $organizationId): ?int
    {
        return $organizationId ?? auth()->user()?->organization_id;
    }

    private function cacheKey(int $organizationId, string $key): string
    {
        return "settings:{$organizationId}:{$key}";
    }
}
