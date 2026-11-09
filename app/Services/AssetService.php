<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetPhoto;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AssetService
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function generateCode(Organization $organization, Category $category): string
    {
        $prefix = $organization->code.'-'.$this->categoryCode($category).'-';

        return DB::transaction(function () use ($prefix): string {
            for ($attempt = 0; $attempt < 20; $attempt++) {
                $last = Asset::withTrashed()
                    ->where('code', 'like', $prefix.'%')
                    ->orderByDesc('code')
                    ->value('code');

                $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
                $code = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);

                if (! Asset::withTrashed()->where('code', $code)->exists()) {
                    return $code;
                }
            }

            throw new RuntimeException('Gagal membuat kode aset unik.');
        });
    }

    public function create(
        Organization $organization,
        Category $category,
        array $data,
        array $photos = [],
    ): Asset {
        return DB::transaction(function () use ($organization, $category, $data, $photos): Asset {
            for ($attempt = 1; $attempt <= 5; $attempt++) {
                try {
                    $asset = Asset::create([
                        ...$data,
                        'organization_id' => $organization->id,
                        'category_id' => $category->id,
                        'code' => $data['code'] ?? $this->generateCode($organization, $category),
                        'status' => AssetStatus::Available,
                    ]);

                    break;
                } catch (QueryException $exception) {
                    if ($attempt === 5 || ! empty($data['code'])) {
                        throw $exception;
                    }
                }
            }

            $this->savePhotos($asset, $photos);

            return $asset->fresh(['category', 'photos']);
        });
    }

    public function update(Asset $asset, array $data, array $photos = []): Asset
    {
        return DB::transaction(function () use ($asset, $data, $photos): Asset {
            $asset->update($data);
            $this->savePhotos($asset, $photos);

            return $asset->fresh(['category', 'photos']);
        });
    }

    public function delete(Asset $asset): void
    {
        $asset->delete();
    }

    public function retire(Asset $asset, User $actor): Asset
    {
        if (! $actor->hasRole('organization_admin')) {
            throw new AuthorizationException('Hanya Organization Admin yang dapat menonaktifkan aset.');
        }

        $previous = $asset->status;

        $asset->update(['status' => AssetStatus::Retired]);

        $this->logger->log(
            'asset.status_changed',
            $asset,
            "Status aset {$asset->code} diubah menjadi retired.",
            $actor,
            ['from' => $previous->value, 'to' => AssetStatus::Retired->value],
        );

        return $asset->refresh();
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function savePhotos(Asset $asset, array $files): void
    {
        if ($files === []) {
            return;
        }

        $hasPrimary = $asset->photos()->where('is_primary', true)->exists();
        $sortOrder = (int) $asset->photos()->max('sort_order');

        foreach (array_values($files) as $index => $file) {
            $path = $file->store("assets/{$asset->code}", 'public');

            AssetPhoto::create([
                'asset_id' => $asset->id,
                'path' => $path,
                'is_primary' => ! $hasPrimary && $index === 0,
                'sort_order' => $sortOrder + $index + 1,
            ]);
        }
    }

    private function categoryCode(Category $category): string
    {
        $root = $category;

        while ($root->parent) {
            $root = $root->parent;
        }

        $known = [
            'kamera mirrorless' => 'CAM',
            'lensa' => 'LEN',
            'tripod' => 'TRI',
            'lampu' => 'LMP',
        ];

        $name = Str::lower($root->name);

        if (isset($known[$name])) {
            return $known[$name];
        }

        $letters = preg_replace('/[^A-Za-z]/', '', Str::ascii($root->name));

        return Str::upper(Str::substr($letters ?: 'GEN', 0, 3));
    }
}
