<?php

namespace App\Services;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Exceptions\InvalidMaintenanceTransitionException;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function create(
        Asset $asset,
        User $reporter,
        string $description,
        ?User $technician = null,
    ): Maintenance {
        if ($reporter->organization_id !== $asset->organization_id) {
            throw new AuthorizationException(__('messages.booking.wrong_organization'));
        }

        if (! in_array($reporter->role?->slug, ['operator', 'organization_admin'], true)) {
            throw new AuthorizationException(__('messages.maintenance.not_allowed'));
        }

        return DB::transaction(function () use ($asset, $reporter, $description, $technician): Maintenance {
            $maintenance = Maintenance::create([
                'asset_id' => $asset->id,
                'reported_by' => $reporter->id,
                'assigned_to' => $technician?->id,
                'status' => MaintenanceStatus::Scheduled,
                'description' => $description,
            ]);

            $asset->update(['status' => AssetStatus::Maintenance]);

            $this->logger->log(
                'maintenance.created',
                $maintenance,
                "Tugas perawatan {$asset->code} dibuat.",
                $reporter,
                ['asset_id' => $asset->id],
            );

            return $maintenance->fresh(['asset', 'assignedTo', 'reportedBy']);
        });
    }

    public function assign(Maintenance $maintenance, User $actor, ?User $technician): Maintenance
    {
        $this->assertAdmin($maintenance, $actor);

        if ($maintenance->status === MaintenanceStatus::Done || $maintenance->status === MaintenanceStatus::Cancelled) {
            throw InvalidMaintenanceTransitionException::for($maintenance, $maintenance->status);
        }

        if ($technician !== null && ! $technician->hasRole('technician')) {
            throw new AuthorizationException(__('messages.maintenance.technician_required'));
        }

        $maintenance->update(['assigned_to' => $technician?->id]);

        return $maintenance->fresh(['asset', 'assignedTo', 'reportedBy']);
    }

    public function start(Maintenance $maintenance, User $technician): Maintenance
    {
        $this->assertTechnician($maintenance, $technician);

        if ($maintenance->status !== MaintenanceStatus::Scheduled) {
            throw InvalidMaintenanceTransitionException::for($maintenance, MaintenanceStatus::InProgress);
        }

        $maintenance->update([
            'status' => MaintenanceStatus::InProgress,
            'started_at' => now(),
        ]);

        $this->logger->log(
            'maintenance.started',
            $maintenance,
            "Perawatan {$maintenance->asset->code} mulai dikerjakan.",
            $technician,
        );

        return $maintenance->fresh(['asset', 'assignedTo', 'reportedBy']);
    }

    public function finish(
        Maintenance $maintenance,
        User $technician,
        ?string $resultNotes,
        ?int $cost,
        bool $conditionGood,
    ): Maintenance {
        $this->assertTechnician($maintenance, $technician);

        if ($maintenance->status !== MaintenanceStatus::InProgress) {
            throw InvalidMaintenanceTransitionException::for($maintenance, MaintenanceStatus::Done);
        }

        return DB::transaction(function () use ($maintenance, $technician, $resultNotes, $cost, $conditionGood): Maintenance {
            $maintenance->update([
                'status' => MaintenanceStatus::Done,
                'finished_at' => now(),
                'cost' => $cost,
                'result_notes' => $resultNotes,
            ]);

            if ($conditionGood) {
                $maintenance->asset->update([
                    'status' => AssetStatus::Available,
                    'condition_status' => AssetCondition::Good,
                ]);
            }

            $this->logger->log(
                'maintenance.done',
                $maintenance,
                "Perawatan {$maintenance->asset->code} selesai.",
                $technician,
                ['condition_good' => $conditionGood, 'cost' => $cost],
            );

            return $maintenance->fresh(['asset', 'assignedTo', 'reportedBy']);
        });
    }

    public function cancel(Maintenance $maintenance, User $admin): Maintenance
    {
        $this->assertAdmin($maintenance, $admin);

        if (! in_array($maintenance->status, [MaintenanceStatus::Scheduled, MaintenanceStatus::InProgress], true)) {
            throw InvalidMaintenanceTransitionException::for($maintenance, MaintenanceStatus::Cancelled);
        }

        $maintenance->update(['status' => MaintenanceStatus::Cancelled]);

        $this->logger->log(
            'maintenance.cancelled',
            $maintenance,
            "Perawatan {$maintenance->asset->code} dibatalkan.",
            $admin,
        );

        return $maintenance->fresh(['asset', 'assignedTo', 'reportedBy']);
    }

    private function assertAdmin(Maintenance $maintenance, User $actor): void
    {
        if ($actor->organization_id !== $maintenance->asset->organization_id || ! $actor->hasRole('organization_admin')) {
            throw new AuthorizationException(__('messages.maintenance.not_allowed'));
        }
    }

    private function assertTechnician(Maintenance $maintenance, User $technician): void
    {
        if ($technician->organization_id !== $maintenance->asset->organization_id
            || ! $technician->hasRole('technician')
            || $maintenance->assigned_to !== $technician->id
        ) {
            throw new AuthorizationException(__('messages.maintenance.not_assigned'));
        }
    }
}
