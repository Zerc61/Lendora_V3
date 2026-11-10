<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Services\PdfService;
use App\Services\ReportAnalyticsService;
use App\Services\SemesterReportService;
use App\Services\SpreadsheetService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportAnalyticsService $analytics,
        private readonly SemesterReportService $semester,
        private readonly PdfService $pdf,
        private readonly SpreadsheetService $spreadsheet,
    ) {}

    public function index(Request $request): View
    {
        [$from, $to] = $this->period($request);

        return view('admin.reports.index', $this->analytics->build(
            $request->user()->organization_id,
            $from,
            $to,
            $request->integer('category_id') ?: null,
        ));
    }

    public function semester(Request $request): View
    {
        return view('admin.reports.semester', $this->semester->for($request->user()));
    }

    public function semesterExport(Request $request): StreamedResponse|BinaryFileResponse
    {
        $report = $this->semester->for($request->user());
        $base = 'laporan-semester-'.$report['semester']['term'].'-'.str_replace('/', '-', $report['semester']['year']);
        $headings = ['Kategori', 'Unit', 'Jam permintaan', 'Jam kapasitas', 'Pemanfaatan (%)', 'Pengajuan', 'Ditolak', 'Rasio penolakan (%)', 'Status'];
        $rows = $report['rows']->map(fn (array $row): array => [
            $row['category']->name,
            $row['units'],
            $row['demand_hours'],
            $row['capacity_hours'],
            $row['utilization'],
            $row['requests'],
            $row['rejected'],
            $row['rejection_rate'],
            $row['status'],
        ])->values();

        if ($request->query('format') === 'xlsx') {
            return $this->spreadsheet->download($base.'.xlsx', $headings, $rows);
        }

        return response()->streamDownload(function () use ($headings, $rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headings);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $base.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function semesterPdf(Request $request): Response
    {
        $report = $this->semester->for($request->user());
        $filename = 'laporan-semester-'.$report['semester']['term'].'-'.str_replace('/', '-', $report['semester']['year']).'.pdf';

        return $this->pdf->download('admin/reports/semester', $report, $filename, 'a4');
    }

    public function pdf(Request $request): Response
    {
        [$from, $to] = $this->period($request);
        $data = $this->analytics->build(
            $request->user()->organization_id,
            $from,
            $to,
            $request->integer('category_id') ?: null,
        );

        return $this->pdf->download('admin/reports/print', $data, 'laporan-'.$from->format('Ymd').'-'.$to->format('Ymd').'.pdf');
    }

    public function print(Request $request): View
    {
        [$from, $to] = $this->period($request);

        return view('admin.reports.print', $this->analytics->build(
            $request->user()->organization_id,
            $from,
            $to,
            $request->integer('category_id') ?: null,
        ));
    }

    public function export(Request $request): StreamedResponse|BinaryFileResponse
    {
        [$from, $to] = $this->period($request);
        $categoryId = $request->integer('category_id') ?: null;
        $organizationId = $request->user()->organization_id;

        $bookings = Booking::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('requested_start_date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('status', [
                BookingStatus::Approved->value,
                BookingStatus::Active->value,
                BookingStatus::Returned->value,
            ])
            ->when($categoryId, fn (Builder $query, int $id) => $query->whereHas(
                'items.asset',
                fn (Builder $query) => $query->where('category_id', $id),
            ))
            ->with(['borrower', 'items.asset'])
            ->orderBy('requested_start_date')
            ->get();

        $base = "laporan-peminjaman-{$from->format('Ymd')}-{$to->format('Ymd')}";
        $headings = ['Kode', 'Peminjam', 'Mulai', 'Selesai', 'Durasi (hari)', 'Status', 'Unit'];
        $rows = $bookings->map(fn (Booking $booking): array => [
            $booking->code,
            $booking->borrower?->name,
            $booking->requested_start_date->toDateString(),
            $booking->requested_end_date->toDateString(),
            $booking->durationInDays(),
            $booking->status->value,
            $booking->items->map(fn (BookingItem $item) => $item->asset?->code)->filter()->implode('; '),
        ]);

        if ($request->query('format') === 'xlsx') {
            return $this->spreadsheet->download($base.'.xlsx', $headings, $rows);
        }

        return response()->streamDownload(function () use ($headings, $rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headings);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $base.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function period(Request $request): array
    {
        $from = $this->parseDate($request->query('date_from')) ?? now()->startOfMonth();
        $to = $this->parseDate($request->query('date_to')) ?? now()->endOfMonth();

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->endOfDay()];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
