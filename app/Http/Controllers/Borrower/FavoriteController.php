<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(Request $request, Asset $asset): JsonResponse|RedirectResponse
    {
        $existing = AssetFavorite::where('user_id', $request->user()->id)
            ->where('asset_id', $asset->id)
            ->first();

        if ($existing !== null) {
            $existing->delete();
            $favorited = false;
        } else {
            AssetFavorite::create([
                'user_id' => $request->user()->id,
                'asset_id' => $asset->id,
            ]);
            $favorited = true;
        }

        if ($request->expectsJson()) {
            return response()->json(['favorited' => $favorited]);
        }

        return back()->with('status', $favorited
            ? __('messages.favorite.added')
            : __('messages.favorite.removed'));
    }

    public function destroy(Request $request, Asset $asset): JsonResponse|RedirectResponse
    {
        AssetFavorite::where('user_id', $request->user()->id)
            ->where('asset_id', $asset->id)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['favorited' => false]);
        }

        return back()->with('status', __('messages.favorite.removed'));
    }
}
