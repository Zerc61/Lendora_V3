<?php

namespace App\Models;

use App\Enums\AnnouncementCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'publisher_name',
        'published_at',
        'is_pinned',
        'attachment_path',
        'cta_label',
        'cta_url',
    ];

    protected function casts(): array
    {
        return [
            'category' => AnnouncementCategory::class,
            'published_at' => 'datetime',
            'is_pinned' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
