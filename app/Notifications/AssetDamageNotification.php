<?php

namespace App\Notifications;

use App\Models\Inspection;
use App\Models\Maintenance;
use Illuminate\Notifications\Notification;

class AssetDamageNotification extends Notification
{
    public function __construct(
        public readonly Inspection $inspection,
        public readonly Maintenance $maintenance,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $asset = $this->inspection->asset;

        return [
            'inspection_id' => $this->inspection->id,
            'maintenance_id' => $this->maintenance->id,
            'asset_id' => $asset?->id,
            'asset_code' => $asset?->code,
            'result' => $this->inspection->result->value,
            'message' => __('messages.inspection.notification_damage', [
                'asset' => "{$asset?->code} {$asset?->name}",
            ]),
        ];
    }
}
