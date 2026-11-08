<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'parent_id',
        'name',
        'slug',
        'description',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * @return array<int, array{id: int, name: string, depth: int}>
     */
    public static function selectOptions(int $organizationId, ?int $excludeId = null): array
    {
        $categories = self::where('organization_id', $organizationId)
            ->orderBy('name')
            ->get()
            ->groupBy(fn (self $category) => $category->parent_id ?? 0);

        $options = [];

        $walk = function (int $parentId, int $depth) use (&$walk, &$options, $categories, $excludeId): void {
            foreach ($categories->get($parentId, collect()) as $category) {
                if ($category->id === $excludeId) {
                    continue;
                }

                $options[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'depth' => $depth,
                ];

                $walk($category->id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $options;
    }
}
