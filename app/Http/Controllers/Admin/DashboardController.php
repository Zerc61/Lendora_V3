<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Maintenance;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('role');
        $role = $user->role->slug;
        $organizationId = $user->organization_id;

        $kpis = match ($role) {
            'operator' => $this->operatorKpis($organizationId),
            'organization_admin' => $this->adminKpis($organizationId),
            'admin_prodi' => $this->prodiKpis($organizationId),
            'technician' => $this->technicianKpis($user->id),
            default => [],
        };

        $recentActivity = $role === 'organization_admin'
            ? ActivityLog::query()
                ->with('user')
                ->where(function ($query) use ($organizationId) {
                    $query->whereNull('user_id')
                        ->orWhereHas('user', fn ($query) => $query->where('organization_id', $organizationId));
                })
                ->latest()
                ->limit(10)
                ->get()
            : collect();

        $specialQueue = $role === 'admin_prodi'
            ? Booking::query()
                ->with(['borrower', 'items.asset'])
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Pending->value)
                ->whereDate('requested_start_date', '>=', now()->toDateString())
                ->get()
                ->filter(fn (Booking $booking) => $booking->isLongDuration(
                    app(SettingService::class)->maxLoanDays($organizationId),
                ))
                ->values()
            : collect();

        $tasks = $role === 'technician'
            ? Maintenance::query()
                ->with(['asset.photos', 'reportedBy'])
                ->where('assigned_to', $user->id)
                ->whereIn('status', [MaintenanceStatus::Scheduled->value, MaintenanceStatus::InProgress->value])
                ->latest()
                ->get()
            : collect();

        $operatorQueue = $role === 'operator'
            ? Booking::query()
                ->with(['borrower', 'items.asset'])
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Pending->value)
                ->orderBy('requested_start_date')
                ->limit(6)
                ->get()
            : collect();

        $operatorDueToday = $role === 'operator'
            ? Booking::query()
                ->with(['borrower', 'items.asset'])
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Active->value)
                ->whereDate('due_at', now()->toDateString())
                ->orderBy('due_at')
                ->limit(6)
                ->get()
            : collect();

        return view('admin.dashboard', compact(
            'user',
            'role',
            'kpis',
            'recentActivity',
            'specialQueue',
            'tasks',
            'operatorQueue',
            'operatorDueToday',
        ));
    }

    /**
     * @return array<string, int>
     */
    private function operatorKpis(int $organizationId): array
    {
        $counts = Booking::query()
            ->where('organization_id', $organizationId)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'pending' => (int) ($counts[BookingStatus::Pending->value] ?? 0),
            'approved' => (int) ($counts[BookingStatus::Approved->value] ?? 0),
            'active' => (int) ($counts[BookingStatus::Active->value] ?? 0),
            'due_today' => Booking::query()
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Active->value)
                ->whereDate('due_at', now()->toDateString())
                ->count(),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function adminKpis(int $organizationId): array
    {
        $assets = Asset::query()
            ->where('organization_id', $organizationId)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = (int) $assets->sum();

        return [
            'assets_total' => $total,
            'available' => (int) ($assets['available'] ?? 0),
            'borrowed' => (int) ($assets['borrowed'] ?? 0),
            'maintenance' => (int) ($assets['maintenance'] ?? 0),
            'pending' => Booking::query()
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Pending->value)
                ->count(),
            'utilization' => $total > 0
                ? round(((int) ($assets['borrowed'] ?? 0) + (int) ($assets['reserved'] ?? 0)) / $total * 100, 1)
                : 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function prodiKpis(int $organizationId): array
    {
        $maxLoanDays = app(SettingService::class)->maxLoanDays($organizationId);

        $pending = Booking::query()
            ->where('organization_id', $organizationId)
            ->where('status', BookingStatus::Pending->value)
            ->get();

        return [
            'pending' => $pending->count(),
            'special' => $pending->filter(fn (Booking $booking) => $booking->isLongDuration($maxLoanDays))->count(),
            'active' => Booking::query()
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Active->value)
                ->count(),
            'returned_this_month' => Booking::query()
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Returned->value)
                ->whereBetween('returned_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function technicianKpis(int $userId): array
    {
        $tasks = Maintenance::query()->where('assigned_to', $userId);

        return [
            'scheduled' => (clone $tasks)->where('status', MaintenanceStatus::Scheduled->value)->count(),
            'in_progress' => (clone $tasks)->where('status', MaintenanceStatus::InProgress->value)->count(),
            'done_this_month' => (clone $tasks)
                ->where('status', MaintenanceStatus::Done->value)
                ->whereBetween('finished_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ];
    }
}
