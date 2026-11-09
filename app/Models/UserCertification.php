<?php

namespace App\Models;

use App\Enums\CertificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCertification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'issuer',
        'certificate_number',
        'issued_at',
        'expires_at',
        'file_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'status' => CertificationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
