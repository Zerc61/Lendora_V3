<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PenaltyStatus;
use App\Models\Booking;
use App\Models\Penalty;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StudentComplianceService
{
    public const TIER_RANGES = [
        'a_plus' => [90, null],
        'a' => [80, 89],
        'b_plus' => [70, 79],
        'b' => [60, 69],
        'c' => [50, 59],
        'd' => [0, 49],
    ];

    private const UNPAID_STATUSES = [
        PenaltyStatus::WaitingPayment->value,
        PenaltyStatus::Partial->value,
        PenaltyStatus::InstallmentRequested->value,
        PenaltyStatus::Overdue->value,
    ];

    public function __construct(private readonly BorrowerScoreService $scores) {}

    /**
     * @param  array{search?: string|null, tier?: string|null, status?: string|null, sort?: string|null}  $filters
     * @return array{students: LengthAwarePaginator<int, User>, summary: array<string, int|float|null>, filters: array<string, mixed>}
     */
    public function for(User $prodi, array $filters = []): array
    {
        $organizationId = $prodi->organization_id;

        $students = $this->query($organizationId, $filters)->paginate(15)->withQueryString();
        $usage = $this->usageFor($students->getCollection());

        $students->getCollection()->each(function (User $student) use ($usage): void {
            $student->setAttribute('usage_sessions', $usage[$student->id]['sessions'] ?? 0);
            $student->setAttribute('usage_days', $usage[$student->id]['days'] ?? 0);
            $student->setAttribute('tier', $this->scores->tierFor($student->compliance_score));
        });

        return [
            'students' => $students,
            'summary' => $this->summary($organizationId),
            'filters' => $filters,
        ];
    }

    /**
     * @param  array{search?: string|null, tier?: string|null, status?: string|null, sort?: string|null}  $filters
     */
    private function query(int $organizationId, array $filters): \Illuminate\Database\Eloquent\Builder
    {
        return User::query()
            ->where('organization_id', $organizationId)
            ->whereHas('role', fn ($query) => $query->where('slug', 'borrower'))
            ->withCount(['penalties as bap_count'])
            ->withCount(['penalties as unpaid_count' => fn ($query) => $query->whereIn('status', self::UNPAID_STATUSES)])
            ->withSum('penalties as penalty_total', 'total_amount')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['tier'] ?? null, fn ($query, string $tier) => $this->applyTier($query, $tier))
            ->when(($filters['sort'] ?? 'lowest') === 'highest', function ($query): void {
                $query->orderByRaw('compliance_score IS NULL')->orderByDesc('compliance_score');
            }, function ($query) use ($filters): void {
                ($filters['sort'] ?? 'lowest') === 'name'
                    ? $query->orderBy('name')
                    : $query->orderByRaw('compliance_score IS NULL')->orderBy('compliance_score');
            });
    }

    private function applyTier($query, string $tier): void
    {
        $range = self::TIER_RANGES[$tier] ?? null;

        if ($range === null) {
            return;
        }

        [$min, $max] = $range;

        $max === null
            ? $query->where('compliance_score', '>=', $min)
            : $query->whereBetween('compliance_score', [$min, $max]);
    }

    /**
     * @param  Collection<int, User>  $students
     * @return array<int, array{sessions: int, days: int}>
     */
    private function usageFor(Collection $students): array
    {
        if ($students->isEmpty()) {
            return [];
        }

        return Booking::query()
            ->whereIn('borrower_id', $students->pluck('id'))
            ->whereIn('status', [
                BookingStatus::Approved->value,
                BookingStatus::Active->value,
                BookingStatus::Returned->value,
            ])
            ->get(['borrower_id', 'requested_start_date', 'requested_end_date'])
            ->groupBy('borrower_id')
            ->map(fn (Collection $bookings): array => [
                'sessions' => $bookings->count(),
                'days' => (int) $bookings->sum(
                    fn (Booking $booking): int => $booking->durationInDays(),
                ),
            ])
            ->all();
    }

    /**
     * @return array<string, int|float|null>
     */
    private function summary(int $organizationId): array
    {
        $students = User::query()
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->where('slug', 'borrower'));

        $penalties = Penalty::query()->where('organization_id', $organizationId);

        return [
            'total' => (int) (clone $students)->count(),
            'avg_score' => (clone $students)->whereNotNull('compliance_score')->avg('compliance_score'),
            'teladan' => (int) (clone $students)->where('compliance_score', '>=', 90)->count(),
            'pembinaan' => (int) (clone $students)->where('compliance_score', '<', 60)->count(),
            'penalty_count' => (int) (clone $penalties)->count(),
            'penalty_total' => (int) (clone $penalties)->sum('total_amount'),
            'penalty_unpaid' => (int) (clone $penalties)->whereIn('status', self::UNPAID_STATUSES)->count(),
        ];
    }
}
