<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenaltyItem extends Model
{
    protected $fillable = [
        'penalty_id',
        'description',
        'part_code',
        'quantity',
        'unit_cost',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function penalty(): BelongsTo
    {
        return $this->belongsTo(Penalty::class);
    }
}
