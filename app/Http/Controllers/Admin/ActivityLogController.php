<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\SpreadsheetService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function __construct(private readonly SpreadsheetService $spreadsheet) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $organizationId = $request->user()->organization_id;

        $logs = $this->query($organizationId, $filters)
            ->with('user')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $actions = ActivityLog::query()
            ->whereHas('user', fn (Builder $query) => $query->where('organization_id', $organizationId))
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.activity-logs.index', compact('logs', 'filters', 'actions'));
    }

    public function export(Request $request): StreamedResponse|BinaryFileResponse
    {
        $filters = $this->filters($request);
        $logs = $this->query($request->user()->organization_id, $filters)
            ->with('user')
            ->latest('id')
            ->get();

        $headings = ['Waktu', 'Pengguna', 'Aksi', 'Deskripsi', 'Alamat IP'];
        $rows = $logs->map(fn (ActivityLog $log): array => [
            $log->created_at?->format('Y-m-d H:i:s'),
            $log->user?->name,
            $log->action,
            $log->description,
            $log->ip_address,
        ]);
        $base = 'log-aktivitas-'.now()->format('Ymd-His');

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
        }, $base.'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{search: ?string, action: ?string, date_from: ?string, date_to: ?string}
     */
    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search')) ?: null,
            'action' => trim((string) $request->query('action')) ?: null,
            'date_from' => $request->date('date_from')?->toDateString(),
            'date_to' => $request->date('date_to')?->toDateString(),
        ];
    }

    /**
     * @param  array{search: ?string, action: ?string, date_from: ?string, date_to: ?string}  $filters
     */
    private function query(?int $organizationId, array $filters): Builder
    {
        return ActivityLog::query()
            ->whereHas('user', fn (Builder $query) => $query->where('organization_id', $organizationId))
            ->when($filters['search'], function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['action'], fn (Builder $query, string $action) => $query->where('action', $action))
            ->when($filters['date_from'], fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'], fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date));
    }
}
