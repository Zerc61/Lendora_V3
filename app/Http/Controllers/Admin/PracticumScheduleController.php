<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePracticumScheduleRequest;
use App\Models\PracticumSchedule;
use App\Services\PracticumScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PracticumScheduleController extends Controller
{
    public function __construct(private readonly PracticumScheduleService $schedules) {}

    public function index(Request $request): View
    {
        return view('admin.practicum.index', [
            ...$this->schedules->for($request->user()),
            ...$this->schedules->options($request->user()),
        ]);
    }

    public function store(StorePracticumScheduleRequest $request): RedirectResponse
    {
        $this->schedules->create($request->user(), [
            'course_id' => $request->integer('course_id'),
            'room_id' => $request->integer('room_id'),
            'day_of_week' => $request->integer('day_of_week'),
            'start_time' => $request->string('start_time')->toString(),
            'end_time' => $request->string('end_time')->toString(),
        ]);

        return redirect()
            ->route('admin.practicum.index')
            ->with('status', __('messages.practicum.created'));
    }

    public function destroy(Request $request, PracticumSchedule $schedule): RedirectResponse
    {
        $this->schedules->remove($request->user(), $schedule);

        return redirect()
            ->route('admin.practicum.index')
            ->with('status', __('messages.practicum.deleted'));
    }
}
