<?php

namespace App\Services;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Exceptions\InvalidMaintenanceTransitionException;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenancePart;
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
        array $options = [],
    ): Maintenance {
        if ($reporter->organization_id !== $asset->organization_id) {
            throw new AuthorizationException(__('messages.booking.wrong_organization'));
        }

        if (! in_array($reporter->role?->slug, ['operator', 'organization_admin'], true)) {
            throw new AuthorizationException(__('messages.maintenance.not_allowed'));
        }

        return DB::transaction(function () use ($asset, $reporter, $description, $technician, $options): Maintenance {
            $maintenance = Maintenance::create([
                'asset_id' => $asset->id,
                'reported_by' => $reporter->id,
                'assigned_to' => $technician?->id,
                'vendor' => $options['vendor'] ?? null,
                'status' => MaintenanceStatus::Scheduled,
                'priority' => MaintenancePriority::tryFrom((string) ($options['priority'] ?? '')) ?? MaintenancePriority::Normal,
                'description' => $description,
                'due_at' => $options['due_at'] ?? null,
            ]);

            $maintenance->update([
                'code' => sprintf('MTC-%d-%04d', now()->year, $maintenance->id),
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

    public function addPart(Maintenance $maintenance, User $actor, array $data): MaintenancePart
    {
        $this->assertStaff($maintenance, $actor);

        if (in_array($maintenance->status, [MaintenanceStatus::Done, MaintenanceStatus::Cancelled], true)) {
            throw InvalidMaintenanceTransitionException::for($maintenance, $maintenance->status);
        }

        return DB::transaction(function () use ($maintenance, $actor, $data): MaintenancePart {
            $part = $maintenance->parts()->create([
                'name' => $data['name'],
                'part_code' => $data['part_code'] ?? null,
                'quantity' => max(1, (int) ($data['quantity'] ?? 1)),
                'unit_cost' => max(0, (int) ($data['unit_cost'] ?? 0)),
            ]);

            $maintenance->load('parts');
            $maintenance->update(['cost' => $maintenance->totalCost()]);

            $this->logger->log(
                'maintenance.part_added',
                $maintenance,
                "Suku cadang {$part->name} ditambahkan ke tiket {$maintenance->code}.",
                $actor,
                ['part_id' => $part->id, 'subtotal' => $part->subtotal()],
            );

            return $part;
        });
    }

    public function recordProgress(Maintenance $maintenance, User $actor, string $label, bool $done = true): Maintenance
    {
        $this->assertStaff($maintenance, $actor);

        $progress = $maintenance->progress ?? [];
        $progress[] = [
            'label' => $label,
            'done' => $done,
            'at' => now()->toDateTimeString(),
            'by' => $actor->id,
        ];

        $maintenance->update(['progress' => $progress]);

        $this->logger->log(
            'maintenance.progress_updated',
            $maintenance,
            "Progres tiket {$maintenance->code}: {$label}.",
            $actor,
        );

        return $maintenance->fresh(['asset', 'assignedTo', 'reportedBy', 'parts']);
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

    private function assertStaff(Maintenance $maintenance, User $actor): void
    {
        if ($actor->organization_id !== $maintenance->asset->organization_id
            || ! in_array($actor->role?->slug, ['operator', 'organization_admin', 'technician'], true)
        ) {
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
