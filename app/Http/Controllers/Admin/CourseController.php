<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CourseQuotaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(private readonly CourseQuotaService $quota) {}

    public function index(Request $request): View
    {
        return view('admin.courses.index', $this->quota->for($request->user()));
    }
}
