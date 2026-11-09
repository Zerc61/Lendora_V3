<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Collection;

class AnnouncementService
{
    public function forBorrower(User $user): Collection
    {
        return Announcement::query()
            ->where('organization_id', $user->organization_id)
            ->where('published_at', '<=', now())
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();
    }
}
