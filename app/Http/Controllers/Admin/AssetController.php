<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\Category;
use App\Services\AssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetController extends Controller
{
    public function __construct(private readonly AssetService $assets) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Asset::class);

        $organizationId = $request->user()->organization_id;

        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'category_id' => $request->integer('category_id') ?: null,
            'status' => AssetStatus::tryFrom((string) $request->query('status'))?->value,
            'condition_status' => AssetCondition::tryFrom((string) $request->query('condition_status'))?->value,
            'location' => trim((string) $request->query('location')) ?: null,
        ];

        $assets = Asset::query()
            ->with(['category', 'photos'])
            ->where('organization_id', $organizationId)
            ->when($filters['search'], function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'], fn ($query, int $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['status'], fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['condition_status'], fn ($query, string $condition) => $query->where('condition_status', $condition))
            ->when($filters['location'], fn ($query, string $location) => $query->where('location', $location))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::selectOptions($organizationId);

        $statusCounts = Asset::query()
            ->where('organization_id', $organizationId)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $summary = [
            'total' => (int) $statusCounts->sum(),
            'available' => (int) ($statusCounts[AssetStatus::Available->value] ?? 0),
            'borrowed' => (int) ($statusCounts[AssetStatus::Borrowed->value] ?? 0),
            'maintenance' => (int) ($statusCounts[AssetStatus::Maintenance->value] ?? 0),
        ];

        $locations = Asset::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        return view('admin.assets.index', compact('assets', 'categories', 'filters', 'summary', 'locations'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Asset::class);

        $organizationId = $request->user()->organization_id;
        $assets = Asset::query()
            ->with('category')
            ->where('organization_id', $organizationId)
            ->when($request->integer('category_id') ?: null, fn ($query, int $categoryId) => $query->where('category_id', $categoryId))
            ->when(AssetStatus::tryFrom((string) $request->query('status'))?->value, fn ($query, string $status) => $query->where('status', $status))
            ->when(trim((string) $request->query('condition_status')) ?: null, fn ($query, string $condition) => $query->where('condition_status', $condition))
            ->when(trim((string) $request->query('location')) ?: null, fn ($query, string $location) => $query->where('location', $location))
            ->orderBy('code')
            ->get();

        return response()->streamDownload(function () use ($assets): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Kode', 'Nama', 'Kategori', 'Status', 'Kondisi', 'Lokasi', 'Serial']);

            foreach ($assets as $asset) {
                fputcsv($handle, [
                    $asset->code,
                    $asset->name,
                    $asset->category?->name,
                    $asset->status->value,
                    $asset->condition_status?->value,
                    $asset->location,
                    $asset->serial_number,
                ]);
            }

            fclose($handle);
        }, 'daftar-aset-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Asset::class);

        $categories = Category::selectOptions($request->user()->organization_id);

        return view('admin.assets.create', compact('categories'));
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $organization = $request->user()->organization;
        $category = Category::where('organization_id', $organization->id)
            ->findOrFail($request->integer('category_id'));

        $this->assets->create(
            $organization,
            $category,
            $request->safe()->except('photos'),
            $request->file('photos', []),
        );

        return redirect()
            ->route('admin.assets.index')
            ->with('status', __('messages.asset.created'));
    }

    public function edit(Request $request, Asset $asset): View
    {
        $this->authorize('update', $asset);

        abort_unless($asset->organization_id === $request->user()->organization_id, 404);

        $asset->load(['category', 'photos']);
        $categories = Category::selectOptions($request->user()->organization_id);

        return view('admin.assets.edit', compact('asset', 'categories'));
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $category = Category::where('organization_id', $request->user()->organization_id)
            ->findOrFail($request->integer('category_id'));

        $this->assets->update(
            $asset,
            $request->safe()->except('photos'),
            $request->file('photos', []),
        );

        return redirect()
            ->route('admin.assets.index')
            ->with('status', __('messages.asset.updated'));
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);

        $this->assets->delete($asset);

        return redirect()
            ->route('admin.assets.index')
            ->with('status', __('messages.asset.deleted'));
    }

    public function retire(Request $request, Asset $asset): RedirectResponse
    {
        $this->authorize('retire', $asset);

        $this->assets->retire($asset, $request->user());

        return redirect()
            ->route('admin.assets.index')
            ->with('status', __('messages.asset.retired'));
    }
}
