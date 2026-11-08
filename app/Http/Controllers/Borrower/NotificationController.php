<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()->latest()->get();

        $groups = [
            'today' => $notifications->filter(fn ($notification) => $notification->created_at?->isToday()),
            'earlier' => $notifications->reject(fn ($notification) => $notification->created_at?->isToday()),
        ];

        return view('borrower.notifications.index', [
            'groups' => $groups,
            'unreadCount' => $user->unreadNotifications()->count(),
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
