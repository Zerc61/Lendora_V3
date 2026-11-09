<?php

namespace App\Services;

use App\Models\User;

class BorrowerScoreService
{
    public function summary(User $user): array
    {
        return [
            'score' => $user->compliance_score ?? 100,
            'tier' => $this->getTier($user->compliance_score ?? 100),
            'on_time_returns' => $user->on_time_returns ?? 0,
            'damages' => $user->damages_count ?? 0,
            'violations' => $user->violations_count ?? 0,
        ];
    }

    private function getTier(int $score): string
    {
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B+';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        return 'D';
    }
}