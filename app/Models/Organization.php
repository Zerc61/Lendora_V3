<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
