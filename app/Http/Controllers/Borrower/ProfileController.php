<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBorrowerProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('borrower.profile.edit', [
            'user' => $request->user(),
            'notificationTypes' => $request->user()->notificationAudience(),
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
