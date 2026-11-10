<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProdiDashboardService
{
    public function __construct(
        private readonly ApprovalQueueService $queue,
        private readonly SettingService $settings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function for(User $user): array
    {
        $organizationId = $user->organization_id;
        $maxLoanDays = $this->settings->maxLoanDays($organizationId);

        $pending = Booking::query()
            ->with(['borrower', 'items.asset.category'])
            ->where('organization_id', $organizationId)
            ->where('status', BookingStatus::Pending->value)
            ->whereDate('requested_start_date', '>=', now()->toDateString())
            ->orderBy('requested_start_date')
            ->get();

        $special = $pending
            ->filter(fn (Booking $booking): bool => $booking->isLongDuration($maxLoanDays))
            ->values();

        $queue = $special->map(fn (Booking $booking): array => $this->queue->card($user, $booking));

        $borrowers = User::query()
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->where('slug', 'borrower'));

        $semesterStart = (string) $this->settings->get('semester_start', '2026-08-01', $organizationId);
        $semesterEnd = (string) $this->settings->get('semester_end', '2026-12-31', $organizationId);

        return [
            'kpis' => [
                'pending' => (int) Booking::query()
                    ->where('organization_id', $organizationId)
                    ->where('status', BookingStatus::Pending->value)
                    ->count(),
                'special' => $special->count(),
                'active' => (int) Booking::query()
                    ->where('organization_id', $organizationId)
                    ->where('status', BookingStatus::Active->value)
                    ->count(),
                'returned_this_month' => (int) Booking::query()
                    ->where('organization_id', $organizationId)
                    ->where('status', BookingStatus::Returned->value)
                    ->whereBetween('returned_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'students' => (int) (clone $borrowers)->count(),
                'low_compliance' => (int) (clone $borrowers)
                    ->whereNotNull('compliance_score')
                    ->where('compliance_score', '<', 60)
                    ->count(),
            ],
            'queue' => $queue,
            'categories' => $this->categoryDemand($queue),
            'rules' => [
                'max_days' => $maxLoanDays,
                'operator_limit' => $this->settings->operatorApprovalLimit($organizationId),
            ],
            'semester' => [
                'label' => Carbon::parse($semesterStart)->locale('id')->translatedFormat('M Y')
                    .' – '
                    .Carbon::parse($semesterEnd)->locale('id')->translatedFormat('M Y'),
            ],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $queue
     * @return Collection<int, array{name: string, count: int}>
     */
    private function categoryDemand(Collection $queue): Collection
    {
        return $queue
            ->flatMap(fn (array $card) => $card['booking']->items->pluck('asset.category.name'))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->map(fn (int $count, string $name): array => ['name' => $name, 'count' => $count])
            ->values();
    }
}
