<?php

namespace App\Http\Controllers\Borrower;

use App\Enums\BookingStatus;
use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $activeBooking = Booking::query()
            ->with(['items.asset.photos'])
            ->where('borrower_id', $user->id)
            ->where('status', BookingStatus::Active->value)
            ->latest('pickup_at')
            ->first();

        $categoryIds = BookingItem::query()
            ->whereHas('booking', fn ($query) => $query->where('organization_id', $user->organization_id))
            ->join('assets', 'assets.id', '=', 'booking_items.asset_id')
            ->selectRaw('assets.category_id, count(*) as total')
            ->groupBy('assets.category_id')
            ->orderByDesc('total')
            ->limit(3)
            ->pluck('assets.category_id');

        $recommendations = Asset::query()
            ->with(['category', 'photos'])
            ->where('organization_id', $user->organization_id)
            ->where('status', 'available')
            ->when($categoryIds->isNotEmpty(), fn ($query) => $query->whereIn('category_id', $categoryIds))
            ->latest()
            ->limit(6)
            ->get();

        if ($recommendations->isEmpty()) {
            $recommendations = Asset::query()
                ->with(['category', 'photos'])
                ->where('organization_id', $user->organization_id)
                ->where('status', 'available')
                ->latest()
                ->limit(6)
                ->get();
        }

        $dueInfo = null;

        if ($activeBooking?->due_at !== null) {
            $dueInfo = [
                'due_at' => $activeBooking->due_at,
                'days_left' => (int) now()->startOfDay()->diffInDays($activeBooking->due_at->copy()->startOfDay(), false),
                'is_overdue' => $activeBooking->due_at->isPast(),
            ];
        }

        $statusCounts = Booking::query()
            ->where('borrower_id', $user->id)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $summary = [
            'active' => (int) ($statusCounts[BookingStatus::Active->value] ?? 0),
            'pending' => (int) ($statusCounts[BookingStatus::Pending->value] ?? 0),
            'approved' => (int) ($statusCounts[BookingStatus::Approved->value] ?? 0),
            'returned' => (int) ($statusCounts[BookingStatus::Returned->value] ?? 0),
        ];

        $availableAssets = Asset::query()
            ->where('organization_id', $user->organization_id)
            ->where('status', AssetStatus::Available->value)
            ->count();

        return view('borrower.home', [
            'user' => $user,
            'activeBooking' => $activeBooking,
            'dueInfo' => $dueInfo,
            'recommendations' => $recommendations,
            'announcement' => app(SettingService::class)->borrowerAnnouncement($user->organization_id),
            'unreadNotifications' => $user->unreadNotifications()->count(),
            'summary' => $summary,
            'availableAssets' => $availableAssets,
        ]);
    }
}
