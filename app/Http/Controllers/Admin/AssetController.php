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
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::selectOptions($organizationId);

        return view('admin.assets.index', compact('assets', 'categories', 'filters'));
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
