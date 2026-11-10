<?php

namespace App\Models;

use App\Enums\AssetAccessLevel;
use App\Enums\AssetCondition;
use App\Enums\AssetGrade;
use App\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'category_id',
        'code',
        'name',
        'brand',
        'model',
        'serial_number',
        'description',
        'specifications',
        'status',
        'condition_status',
        'grade',
        'access_level',
        'condition_score',
        'is_featured',
        'manual_path',
        'rating_average',
        'rating_count',
        'purchase_date',
        'purchase_price',
        'location',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssetStatus::class,
            'condition_status' => AssetCondition::class,
            'grade' => AssetGrade::class,
            'access_level' => AssetAccessLevel::class,
            'condition_score' => 'integer',
            'is_featured' => 'boolean',
            'rating_average' => 'decimal:2',
            'rating_count' => 'integer',
            'purchase_date' => 'date',
            'purchase_price' => 'integer',
            'specifications' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AssetPhoto::class)->orderBy('sort_order');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class)->latest('checked_at');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class)->latest();
    }

    public function components(): HasMany
    {
        return $this->hasMany(AssetComponent::class)->orderBy('sort_order');
    }

    public function qcChecks(): HasMany
    {
        return $this->hasMany(AssetQcCheck::class)->latest('checked_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(AssetBlock::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(AssetFavorite::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AssetReview::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }
}
