<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $role = $user->role->slug;
        $organizationId = $user->organization_id;
        $term = trim((string) $request->query('q'));

        $results = collect();
        $searched = mb_strlen($term) >= 2;

        if ($searched) {
            $results = $this->results($role, $organizationId, $term);
        }

        return view('admin.search.index', [
            'term' => $term,
            'searched' => $searched,
            'results' => $results,
        ]);
    }

    /**
     * @return Collection<string, Collection<int, mixed>>
     */
    private function results(string $role, ?int $organizationId, string $term): Collection
    {
        $like = "%{$term}%";
        $results = collect();

        if (in_array($role, ['operator', 'organization_admin', 'admin_prodi', 'technician'], true)) {
            $results->put('assets', Asset::query()
                ->where('organization_id', $organizationId)
                ->where(fn (Builder $query) => $query
                    ->where('code', 'like', $like)
                    ->orWhere('name', 'like', $like))
                ->orderBy('code')
                ->limit(5)
                ->get());
        }

        if (in_array($role, ['operator', 'organization_admin', 'admin_prodi'], true)) {
            $results->put('bookings', Booking::query()
                ->where('organization_id', $organizationId)
                ->where(fn (Builder $query) => $query
                    ->where('code', 'like', $like)
                    ->orWhereHas('borrower', fn (Builder $query) => $query->where('name', 'like', $like)))
                ->latest('id')
                ->limit(5)
                ->get());
        }

        if ($role === 'organization_admin') {
            $results->put('users', User::query()
                ->with('role')
                ->where('organization_id', $organizationId)
                ->where(fn (Builder $query) => $query
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like))
                ->orderBy('name')
                ->limit(5)
                ->get());
        }

        if (in_array($role, ['organization_admin', 'admin_prodi'], true)) {
            $results->put('categories', Category::query()
                ->where('organization_id', $organizationId)
                ->where('name', 'like', $like)
                ->orderBy('name')
                ->limit(5)
                ->get());
        }

        return $results;
    }
}
