<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function __construct(private readonly QueueService $queue) {}

    public function index(Request $request): View
    {
        $organization = $this->organization();

        return view('kiosk.queue', [
            'board' => $this->queue->board($organization->id),
            'ticket' => $request->session()->get('kiosk.ticket'),
            'organization' => $organization,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['nullable', 'string', 'max:30'],
            'service_type' => ['required', 'in:pickup,return,consultation'],
        ]);

        $organization = $this->organization();
        $user = null;

        if (($validated['nim'] ?? null) !== null) {
            $user = User::query()
                ->where('organization_id', $organization->id)
                ->where('nim', $validated['nim'])
                ->first();
        }

        $ticket = $this->queue->issue(
            $organization->id,
            $validated['service_type'],
            $user,
            $validated['nim'] ?? null,
        );

        return redirect()
            ->route('kiosk.queue')
            ->with('kiosk.ticket', [
                'number' => $ticket->number,
                'service_type' => $ticket->service_type->value,
                'waiting' => $this->queue->board($organization->id)['waiting_count'],
            ]);
    }

    private function organization(): Organization
    {
        return Organization::query()->orderBy('id')->firstOrFail();
    }
}
