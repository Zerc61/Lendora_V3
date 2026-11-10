<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\TransferCourseQuotaRequest;
use App\Http\Requests\UpdateCourseQuotaRequest;
use App\Http\Requests\UpdateCourseRequest;
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

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $this->quota->createCourse($request->user(), $this->payload($request->validated()));

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __('messages.course_quota.created'));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->quota->updateCourse($request->user(), $course, $this->payload($request->validated()));

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __('messages.course_quota.updated'));
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->quota->deleteCourse($request->user(), $course);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', __('messages.course_quota.deleted'));
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

    /**
     * @param  array<string, mixed>  $data
     * @return array{code: string, name: string, lecturer_name: ?string, semester: int, credits: int, quota_hours: ?int}
     */
    private function payload(array $data): array
    {
        return [
            'code' => (string) $data['code'],
            'name' => (string) $data['name'],
            'lecturer_name' => isset($data['lecturer_name']) ? (string) $data['lecturer_name'] : null,
            'semester' => (int) $data['semester'],
            'credits' => (int) $data['credits'],
            'quota_hours' => isset($data['quota_hours']) ? (int) $data['quota_hours'] : null,
        ];
    }
}
