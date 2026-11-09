<?php

namespace App\Http\Controllers\Borrower;

use App\Enums\AssetAccessLevel;
use App\Enums\AssetGrade;
use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function index(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'category_id' => $request->integer('category_id') ?: null,
            'start_date' => $request->query('start_date') ? Carbon::parse($request->query('start_date')) : null,
            'end_date' => $request->query('end_date') ? Carbon::parse($request->query('end_date')) : null,
            'availability' => $request->query('availability'),
            'brand' => $request->query('brand'),
            'location' => $request->query('location'),
            'grade' => $request->query('grade'),
            'access_level' => $request->query('access_level'),
            'sort' => $request->query('sort', 'availability'),
        ];

        $query = Asset::query()
            ->with(['category', 'photos'])
            ->where('organization_id', $organizationId)
            ->whereNotIn('status', [AssetStatus::Maintenance, AssetStatus::Retired]);

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('brand', 'like', "%{$filters['search']}%")
                    ->orWhere('model', 'like', "%{$filters['search']}%")
                    ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }

        if ($filters['brand']) {
            $query->where('brand', $filters['brand']);
        }

        if ($filters['location']) {
            $query->where('location', $filters['location']);
        }

        if ($filters['grade']) {
            $query->where('grade', $filters['grade']);
        }

        if ($filters['access_level']) {
            $query->where('access_level', $filters['access_level']);
        }

        if ($filters['availability'] === 'available_now') {
            $query->where('status', AssetStatus::Available);
        } elseif ($filters['availability'] === 'booked') {
            $query->whereIn('status', [AssetStatus::Reserved, AssetStatus::Borrowed]);
        }

        if ($filters['start_date'] && $filters['end_date']) {
            $availableIds = $this->availability
                ->availabilityMapFor(
                    Asset::where('organization_id', $organizationId)->get(),
                    $filters['start_date'],
                    $filters['start_date']->diffInDays($filters['end_date']) + 1
                )
                ->filter(fn ($status) => $status === 'available')
                ->keys()
                ->toArray();

            $query->whereIn('id', $availableIds);
        }

        $sortMap = [
            'availability' => fn ($q) => $q->orderByRaw("CASE status WHEN 'available' THEN 0 WHEN 'reserved' THEN 1 WHEN 'borrowed' THEN 2 ELSE 3 END"),
            'name' => fn ($q) => $q->orderBy('name'),
            'popular' => fn ($q) => $q->orderByDesc('id'),
            'newest' => fn ($q) => $q->latest(),
        ];

        ($sortMap[$filters['sort']] ?? $sortMap['availability'])($query);

        $assets = $query->paginate(12)->withQueryString();

        $categories = Category::where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();

        $brands = Asset::where('organization_id', $organizationId)
            ->whereNotNull('brand')
            ->distinct('brand')
            ->pluck('brand')
            ->filter()
            ->sort()
            ->values();

        $locations = Asset::where('organization_id', $organizationId)
            ->whereNotNull('location')
            ->distinct('location')
            ->pluck('location')
            ->filter()
            ->sort()
            ->values();

        $statusCounts = Asset::query()
            ->where('organization_id', $organizationId)
            ->whereNotIn('status', [AssetStatus::Maintenance, AssetStatus::Retired])
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $facets = [
            'categories' => $categories->mapWithKeys(fn ($c) => [$c->id => Asset::where('category_id', $c->id)->count()]),
            'brands' => $brands->mapWithKeys(fn ($b) => [$b => Asset::where('brand', $b)->count()]),
            'locations' => $locations->mapWithKeys(fn ($l) => [$l => Asset::where('location', $l)->count()]),
            'grades' => collect(AssetGrade::cases())->mapWithKeys(fn ($g) => [$g->value => Asset::where('grade', $g->value)->count()]),
            'access_levels' => collect(AssetAccessLevel::cases())->mapWithKeys(fn ($a) => [$a->value => Asset::where('access_level', $a->value)->count()]),
        ];

        $availabilityByAsset = $this->availability->availabilityMapFor(
            $assets->getCollection(),
            now(),
            14
        );

        return view('borrower.catalog.index', [
            'assets' => $assets,
            'categories' => $categories,
            'filters' => $filters,
            'statusCounts' => [
                'available' => (int) ($statusCounts[AssetStatus::Available->value] ?? 0),
                'reserved' => (int) ($statusCounts[AssetStatus::Reserved->value] ?? 0),
                'borrowed' => (int) ($statusCounts[AssetStatus::Borrowed->value] ?? 0),
            ],
            'facets' => $facets,
            'availabilityByAsset' => $availabilityByAsset,
            'activeFilters' => array_filter($filters),
        ]);
    }

    public function show(Request $request, Asset $asset): View
    {
        abort_unless($asset->organization_id === $request->user()->organization_id, 404);

        abort_unless(in_array($asset->status, [
            AssetStatus::Available,
            AssetStatus::Reserved,
            AssetStatus::Borrowed,
        ], true), 404);

        $asset->load(['category', 'photos', 'components', 'qcChecks' => fn ($q) => $q->latest()->limit(3), 'documents', 'blocks' => fn ($q) => $q->where('ends_at', '>=', now())]);

        $availabilityDays = $this->availability->calendarFor($asset, now(), 30);

        $isAvailable = $asset->status === AssetStatus::Available;

        $compatibleAddons = Asset::where('organization_id', $asset->organization_id)
            ->where('status', AssetStatus::Available)
            ->where('id', '!=', $asset->id)
            ->where('category_id', '!=', $asset->category_id)
            ->limit(5)
            ->get();

        $favorite = $request->user()->favorites()->where('asset_id', $asset->id)->exists();

        $rating = [
            'average' => $asset->rating_average ?? 0,
            'count' => $asset->rating_count ?? 0,
        ];

        return view('borrower.catalog.show', compact('asset', 'availabilityDays', 'isAvailable', 'compatibleAddons', 'favorite', 'rating'));
    }
}