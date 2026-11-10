<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(private readonly ShiftService $shifts) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
            'shift' => ['nullable', 'string', 'max:20'],
        ]);

        $this->shifts->store($request->user(), $validated['note'], $validated['shift'] ?? null);

        return back()->with('status', __('messages.shift.note_added'));
    }
}
