<?php

namespace App\Http\Controllers\Borrower;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BorrowerScoreService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()->latest()->get();

        $categories = $notifications
            ->map(fn ($n) => $n->data['category'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->mapWithKeys(fn ($category) => [
                $category => Lang::has('ui.notification.type.'.$category)
                    ? __('ui.notification.type.'.$category)
                    : ucfirst(str_replace('_', ' ', $category)),
            ]);

        $category = $request->query('category');

        if ($category !== null && ! $categories->has($category)) {
            $category = null;
        }

        $filtered = $category
            ? $notifications->filter(fn ($n) => ($n->data['category'] ?? null) === $category)
            : $notifications;

        $groups = [
            'today' => $filtered->filter(fn ($notification) => $notification->created_at?->isToday()),
            'earlier' => $filtered->reject(fn ($notification) => $notification->created_at?->isToday()),
        ];

        return view('borrower.notifications.index', [
            'groups' => $groups,
            'unreadCount' => $user->unreadNotifications()->count(),
            'categories' => $categories,
            'category' => $category,
            'activeLoans' => Booking::where('borrower_id', $user->id)
                ->where('status', BookingStatus::Active->value)
                ->count(),
            'compliance' => app(BorrowerScoreService::class)->summary($user),
            'operatorPhone' => $this->settings->get('operator_contact_phone', 'Ext. 402'),
            'operatorWhatsApp' => $this->settings->get('operator_contact_whatsapp', '+62 812-3456-7890'),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $record->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', __('messages.notification.all_read'));
    }
}
