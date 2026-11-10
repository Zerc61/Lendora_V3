<?php

namespace App\Services;

use App\Models\ShiftNote;
use App\Models\User;
use Illuminate\Support\Collection;

class ShiftService
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly ActivityLogger $logger,
    ) {}

    /**
     * @return array{label: string, start: string, end: string, active: bool}
     */
    public function current(?int $organizationId = null): array
    {
        $label = (string) $this->settings->get('operator_shift_label', 'Pagi (08:00–16:00)', $organizationId);
        $start = (string) $this->settings->get('pickup_open_time', '08:30', $organizationId);
        $end = (string) $this->settings->get('return_close_time', '16:00', $organizationId);

        return [
            'label' => $label,
            'start' => $start,
            'end' => $end,
            'active' => now()->format('H:i') >= $start && now()->format('H:i') <= $end,
        ];
    }

    public function lastNote(?int $organizationId = null): ?ShiftNote
    {
        return ShiftNote::query()
            ->with('user')
            ->where('organization_id', $organizationId ?? auth()->user()?->organization_id)
            ->latest('created_at')
            ->first();
    }

    /**
     * @return Collection<int, ShiftNote>
     */
    public function notes(?int $organizationId = null, int $limit = 10): Collection
    {
        return ShiftNote::query()
            ->with('user')
            ->where('organization_id', $organizationId ?? auth()->user()?->organization_id)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function store(User $user, string $note, ?string $shift = null): ShiftNote
    {
        $note = trim($note);
        $shift ??= $this->current($user->organization_id)['label'];

        $shiftNote = ShiftNote::create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'shift' => $shift,
            'note' => $note,
        ]);

        $this->logger->log('shift.note_added', $shiftNote, "Catatan shift {$shift} ditambahkan.", $user);

        return $shiftNote->load('user');
    }
}
