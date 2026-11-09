<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinishMaintenanceRequest;
use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\UpdateMaintenanceRequest;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\Role;
use App\Models\User;
use App\Services\MaintenanceService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function __construct(private readonly MaintenanceService $maintenances) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'status' => \App\Enums\MaintenanceStatus::tryFrom((string) $request->query('status'))?->value,
        ];

        $maintenances = Maintenance::query()
            ->with(['asset.photos', 'assignedTo', 'reportedBy'])
            ->whereHas('asset', fn ($query) => $query->where('organization_id', $user->organization_id))
            ->when($user->hasRole('technician'), fn ($query) => $query->where('assigned_to', $user->id))
            ->when($filters['search'], function ($query, string $search): void {
                $query->whereHas('asset', function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'], fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summaryBase = Maintenance::query()
            ->whereHas('asset', fn ($query) => $query->where('organization_id', $user->organization_id))
            ->when($user->hasRole('technician'), fn ($query) => $query->where('assigned_to', $user->id));

        $statusCounts = (clone $summaryBase)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $summary = [
            'scheduled' => (int) ($statusCounts[\App\Enums\MaintenanceStatus::Scheduled->value] ?? 0),
            'in_progress' => (int) ($statusCounts[\App\Enums\MaintenanceStatus::InProgress->value] ?? 0),
            'done' => (int) ($statusCounts[\App\Enums\MaintenanceStatus::Done->value] ?? 0),
            'cost' => (int) (clone $summaryBase)->sum('cost'),
        ];

        return view('admin.maintenances.index', [
            'maintenances' => $maintenances,
            'role' => $user->role->slug,
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }

    public function create(Request $request): View
    {
        $assets = Asset::query()
            ->where('organization_id', $request->user()->organization_id)
            ->orderBy('code')
            ->get();

        return view('admin.maintenances.create', [
            'assets' => $assets,
            'technicians' => $this->technicians($request),
        ]);
    }

    public function store(StoreMaintenanceRequest $request): RedirectResponse
    {
        $asset = Asset::query()
            ->where('organization_id', $request->user()->organization_id)
            ->findOrFail($request->integer('asset_id'));

        $technician = $request->filled('assigned_to')
            ? User::query()
                ->where('organization_id', $request->user()->organization_id)
                ->findOrFail($request->integer('assigned_to'))
            : null;

        $this->maintenances->create(
            $asset,
            $request->user(),
            (string) $request->input('description'),
            $technician,
        );

        return redirect()
            ->route('admin.maintenances.index')
            ->with('status', __('messages.maintenance.created'));
    }

    public function edit(Request $request, Maintenance $maintenance): View
    {
        $this->assertOrganization($request, $maintenance);

        return view('admin.maintenances.edit', [
            'maintenance' => $maintenance->load(['asset', 'assignedTo', 'reportedBy']),
            'technicians' => $this->technicians($request),
        ]);
    }

    public function update(UpdateMaintenanceRequest $request, Maintenance $maintenance): RedirectResponse
    {
        $this->assertOrganization($request, $maintenance);

        $technician = $request->filled('assigned_to')
            ? User::query()
                ->where('organization_id', $request->user()->organization_id)
                ->findOrFail($request->integer('assigned_to'))
            : null;

        $this->maintenances->assign($maintenance, $request->user(), $technician);

        return redirect()
            ->route('admin.maintenances.index')
            ->with('status', __('messages.maintenance.assigned'));
    }

    public function destroy(Request $request, Maintenance $maintenance): RedirectResponse
    {
        $this->assertOrganization($request, $maintenance);

        $this->maintenances->cancel($maintenance, $request->user());

        return redirect()
            ->route('admin.maintenances.index')
            ->with('status', __('messages.maintenance.cancelled'));
    }

    public function start(Request $request, Maintenance $maintenance): RedirectResponse
    {
        $this->assertOrganization($request, $maintenance);

        $this->maintenances->start($maintenance, $request->user());

        return redirect()
            ->route('admin.maintenances.index')
            ->with('status', __('messages.maintenance.started'));
    }

    public function finishForm(Request $request, Maintenance $maintenance): View
    {
        $this->assertOrganization($request, $maintenance);

        return view('admin.maintenances.finish', [
            'maintenance' => $maintenance->load(['asset.photos', 'assignedTo', 'reportedBy']),
        ]);
    }

    public function finish(FinishMaintenanceRequest $request, Maintenance $maintenance): RedirectResponse
    {
        $this->assertOrganization($request, $maintenance);

        $this->maintenances->finish(
            $maintenance,
            $request->user(),
            $request->input('result_notes'),
            $request->filled('cost') ? $request->integer('cost') : null,
            $request->boolean('condition_good'),
        );

        return redirect()
            ->route('admin.maintenances.index')
            ->with('status', __('messages.maintenance.finished'));
    }

    private function assertOrganization(Request $request, Maintenance $maintenance): void
    {
        abort_unless($maintenance->asset->organization_id === $request->user()->organization_id, 404);
    }

    private function technicians(Request $request): Collection
    {
        $technicianRole = Role::where('slug', 'technician')->first();

        return User::query()
            ->where('organization_id', $request->user()->organization_id)
            ->where('role_id', $technicianRole?->id)
            ->orderBy('name')
            ->get();
    }
}
