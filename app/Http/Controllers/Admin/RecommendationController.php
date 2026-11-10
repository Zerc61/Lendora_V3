<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SemesterReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    public function __construct(private readonly SemesterReportService $semester) {}

    public function index(Request $request): View
    {
        $report = $this->semester->for($request->user());

        return view('admin/recommendations/index', [
            'procurement' => $report['procurement'],
            'rab_total' => $report['rab_total'],
            'semester' => $report['semester'],
            'rows' => $report['rows'],
            'kpis' => $report['kpis'],
        ]);
    }
}
