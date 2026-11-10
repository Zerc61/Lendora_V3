<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PracticumScheduleService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PracticumScheduleController extends Controller
{
    public function __construct(private readonly PracticumScheduleService $schedules) {}

    public function index(Request $request): View
    {
        return view('admin.practicum.index', $this->schedules->for($request->user()));
    }
}
