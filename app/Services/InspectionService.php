<?php

namespace App\Services;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\InspectionResult;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\BookingItem;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AssetDamageNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class InspectionService
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function record(
        Asset $asset,
        User $inspector,
        ?BookingItem $bookingItem,
        string $result,
        ?string $notes = null,
    ): Inspection {
        if ($inspector->organization_id !== $asset->organization_id) {
            throw new AuthorizationException(__('messages.booking.wrong_organization'));
        }

        if (! in_array($inspector->role?->slug, ['operator', 'organization_admin', 'technician'], true)) {
            throw new AuthorizationException(__('messages.inspection.not_allowed'));
        }

        if ($bookingItem !== null && $bookingItem->asset_id !== $asset->id) {
            throw new AuthorizationException(__('messages.inspection.item_mismatch'));
        }

        $inspectionResult = InspectionResult::from($result);

        return DB::transaction(function () use ($asset, $inspector, $bookingItem, $inspectionResult, $notes): Inspection {
            $asset = Asset::query()->whereKey($asset->id)->lockForUpdate()->firstOrFail();

            $inspection = Inspection::create([
                'booking_item_id' => $bookingItem?->id,
                'asset_id' => $asset->id,
                'inspector_id' => $inspector->id,
                'result' => $inspectionResult,
                'notes' => $notes,
                'checked_at' => now(),
            ]);

            $maintenance = null;

            if ($inspectionResult === InspectionResult::Ok) {
                if ($asset->status === AssetStatus::Borrowed) {
                    $asset->update([
                        'status' => AssetStatus::Available,
                        'condition_status' => AssetCondition::Good,
                    ]);
                }
            } else {
                $maintenance = Maintenance::create([
                    'asset_id' => $asset->id,
                    'reported_by' => $inspector->id,
                    'status' => MaintenanceStatus::Scheduled,
                    'description' => __('messages.inspection.maintenance_description', [
                        'asset' => "{$asset->code} {$asset->name}",
                        'notes' => $notes ?: $inspectionResult->value,
                    ]),
                ]);

                $asset->update([
                    'status' => AssetStatus::Maintenance,
                    'condition_status' => $inspectionResult === InspectionResult::Damaged
                        ? AssetCondition::Damaged
                        : AssetCondition::MinorDamage,
                ]);

                $this->logger->log(
                    'maintenance.created',
                    $maintenance,
                    "Tugas perawatan {$asset->code} dibuat dari inspeksi.",
                    $inspector,
                    ['inspection_id' => $inspection->id],
                );

                $this->notifyDamage($inspection, $maintenance);
            }

            $this->logger->log(
                'inspection.recorded',
                $inspection,
                "Inspeksi {$asset->code} dicatat dengan hasil {$inspectionResult->value}.",
                $inspector,
                ['asset_id' => $asset->id, 'result' => $inspectionResult->value],
            );

            return $inspection->fresh(['asset', 'inspector', 'bookingItem']);
        });
    }

    private function notifyDamage(Inspection $inspection, Maintenance $maintenance): void
    {
        $asset = $inspection->asset;

        $recipients = User::query()
            ->where('organization_id', $asset->organization_id)
            ->whereIn('role_id', Role::query()
                ->whereIn('slug', ['operator', 'organization_admin'])
                ->pluck('id'))
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new AssetDamageNotification($inspection, $maintenance));
        }
    }
}
