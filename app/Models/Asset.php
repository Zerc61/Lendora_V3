<?php

namespace App\Models;

use App\Enums\AssetCondition;
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
        'status',
        'condition_status',
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
            'purchase_date' => 'date',
            'purchase_price' => 'integer',
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
}
