<?php

namespace App\Http\Controllers\Borrower;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBorrowerProfileRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        $counts = Booking::query()
            ->where('borrower_id', $user->id)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $activeBooking = Booking::query()
            ->with(['items.asset.photos'])
            ->where('borrower_id', $user->id)
            ->where('status', BookingStatus::Active->value)
            ->latest('pickup_at')
            ->first();

        return view('borrower.profile.edit', [
            'user' => $user,
            'notificationTypes' => $user->notificationAudience(),
            'counts' => [
                'active' => (int) ($counts[BookingStatus::Active->value] ?? 0),
                'pending' => (int) ($counts[BookingStatus::Pending->value] ?? 0),
                'returned' => (int) ($counts[BookingStatus::Returned->value] ?? 0),
                'total' => (int) $counts->sum(),
            ],
            'activeBooking' => $activeBooking,
        ]);
    }

    public function update(UpdateBorrowerProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = [
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
        ];

        if ($request->has('notifications')) {
            $data['notification_preferences'] = collect(array_keys($user->notificationAudience()))
                ->mapWithKeys(fn (string $key) => [$key => $request->boolean("notifications.{$key}")])
                ->all();
        }

        if ($request->filled('password')) {
            $data['password'] = (string) $request->input('password');
        }

        $user->update($data);

        return redirect()
            ->route('borrower.profile.edit')
            ->with('status', __('messages.profile.updated'));
    }
}
