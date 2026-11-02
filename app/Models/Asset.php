<?php

namespace App\Models;

use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Allowed condition values. Kept as strings so a value change needs no migration.
     *
     * @var list<string>
     */
    public const CONDITIONS = ['good', 'minor_damage', 'major_damage'];

    /**
     * Allowed status values, taken from the project guide.
     *
     * @var list<string>
     */
    public const STATUSES = ['available', 'reserved', 'borrowed', 'maintenance'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'asset_category_id',
        'code',
        'name',
        'serial_number',
        'condition',
        'status',
        'description',
    ];

    /**
     * Category this asset belongs to.
     *
     * @return BelongsTo<AssetCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }
}
