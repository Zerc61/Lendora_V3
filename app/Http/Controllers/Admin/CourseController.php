<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferCourseQuotaRequest;
use App\Http\Requests\UpdateCourseQuotaRequest;
use App\Models\Course;
use App\Services\CourseQuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(private readonly CourseQuotaService $quota) {}

    public function index(Request $request): View
    {
        return view('admin.courses.index', $this->quota->for($request->user()));
    }

    public function updateQuota(UpdateCourseQuotaRequest $request, Course $course): RedirectResponse
    {
        $hours = $request->validated('quota_hours');

        $this->quota->updateQuota($request->user(), $course, $hours === null ? null : (int) $hours);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __('messages.course_quota.updated'));
    }

    public function transfer(TransferCourseQuotaRequest $request): RedirectResponse
    {
        $this->quota->transfer(
            $request->user(),
            Course::findOrFail($request->integer('from_course_id')),
            Course::findOrFail($request->integer('to_course_id')),
            $request->integer('hours'),
        );

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __('messages.course_quota.transferred'));
    }
}
