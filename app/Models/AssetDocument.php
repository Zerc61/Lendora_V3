<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDocument extends Model
{
    protected $fillable = [
        'asset_id',
        'title',
        'type',
        'path',
        'mime_type',
        'size',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'size' => 'integer',
            'is_public' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
