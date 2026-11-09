<?php

namespace App\Http\Controllers\Borrower;

use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'category_id' => $request->integer('category_id') ?: null,
        ];

        $assets = Asset::query()
            ->with(['category', 'photos'])
            ->where('organization_id', $organizationId)
            ->whereIn('status', [
                AssetStatus::Available,
                AssetStatus::Reserved,
                AssetStatus::Borrowed,
            ])
            ->when($filters['search'], function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'], fn ($query, int $categoryId) => $query->where('category_id', $categoryId))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();

        return view('borrower.catalog.index', compact('assets', 'categories', 'filters'));
    }

    public function show(Request $request, Asset $asset): View
    {
        abort_unless($asset->organization_id === $request->user()->organization_id, 404);

        abort_unless(in_array($asset->status, [
            AssetStatus::Available,
            AssetStatus::Reserved,
            AssetStatus::Borrowed,
        ], true), 404);

        $asset->load(['category', 'photos']);

        $isAvailable = $asset->status === AssetStatus::Available;

        $availabilityDays = collect(range(0, 13))->map(fn (int $offset) => [
            'date' => now()->addDays($offset),
            'available' => $isAvailable,
        ]);

        return view('borrower.catalog.show', compact('asset', 'isAvailable', 'availabilityDays'));
    }
}
