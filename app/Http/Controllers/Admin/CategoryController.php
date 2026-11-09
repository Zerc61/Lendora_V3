<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        $categories = Category::with(['children' => fn ($query) => $query->withCount('assets')])
            ->withCount('assets')
            ->where('organization_id', $organizationId)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $canDelete = Category::where('organization_id', $organizationId)
            ->withCount([
                'children' => fn ($query) => $query->withTrashed(),
                'assets' => fn ($query) => $query->withTrashed(),
            ])
            ->get()
            ->mapWithKeys(fn (Category $category) => [
                $category->id => $category->children_count === 0 && $category->assets_count === 0,
            ])
            ->all();

        $summary = [
            'parents' => $categories->count(),
            'children' => $categories->sum(fn (Category $category) => $category->children->count()),
            'assets' => $categories->sum(fn (Category $category) => $category->assets_count),
        ];

        return view('admin.categories.index', compact('categories', 'canDelete', 'summary'));
    }

    public function create(Request $request): View
    {
        $categories = Category::selectOptions($request->user()->organization_id);

        return view('admin.categories.create', compact('categories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('messages.category.created'));
    }

    public function edit(Request $request, Category $category): View
    {
        abort_unless($category->organization_id === $request->user()->organization_id, 404);

        $categories = Category::selectOptions($request->user()->organization_id, $category->id);

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        abort_unless($category->organization_id === $request->user()->organization_id, 404);

        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? $category->slug,
            $category->id,
        );

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('messages.category.updated'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeOrganization($category);

        if ($category->children()->withTrashed()->exists() || $category->assets()->withTrashed()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', __('messages.category.delete_blocked'));
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('messages.category.deleted'));
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'kategori';
        $slug = $base;
        $suffix = 2;

        while (Category::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function authorizeOrganization(Category $category): void
    {
        abort_unless($category->organization_id === request()->user()->organization_id, 404);
    }
}
