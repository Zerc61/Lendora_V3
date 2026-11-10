<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StudentComplianceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private readonly StudentComplianceService $students) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'tier' => $request->query('tier') ?: null,
            'status' => in_array($request->query('status'), ['active', 'suspended'], true)
                ? $request->query('status')
                : null,
            'sort' => in_array($request->query('sort'), ['lowest', 'highest', 'name'], true)
                ? $request->query('sort')
                : 'lowest',
        ];

        return view('admin.students.index', $this->students->for($request->user(), $filters));
    }
}
