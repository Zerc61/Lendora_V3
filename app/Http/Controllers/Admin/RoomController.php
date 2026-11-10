<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use App\Services\PracticumScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(private readonly PracticumScheduleService $schedules) {}

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $this->schedules->createRoom($request->user(), $this->payload($request->validated()));

        return redirect()
            ->route('admin.practicum.index')
            ->with('status', __('messages.practicum.room_created'));
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $this->schedules->updateRoom($request->user(), $room, $this->payload($request->validated()));

        return redirect()
            ->route('admin.practicum.index')
            ->with('status', __('messages.practicum.room_updated'));
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $this->schedules->deleteRoom($request->user(), $room);

        return redirect()
            ->route('admin.practicum.index')
            ->with('status', __('messages.practicum.room_deleted'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{code: string, name: string, capacity: ?int, notes: ?string}
     */
    private function payload(array $data): array
    {
        return [
            'code' => (string) $data['code'],
            'name' => (string) $data['name'],
            'capacity' => isset($data['capacity']) ? (int) $data['capacity'] : null,
            'notes' => isset($data['notes']) ? (string) $data['notes'] : null,
        ];
    }
}
