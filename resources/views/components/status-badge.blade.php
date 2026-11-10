@props(['status' => null, 'variant' => null, 'type' => 'asset'])

@php
    $variants = [
        'asset' => [
            'available' => 'success',
            'reserved' => 'violet',
            'borrowed' => 'info',
            'maintenance' => 'warning',
            'retired' => 'neutral',
        ],
        'booking' => [
            'pending' => 'warning',
            'approved' => 'info',
            'active' => 'success',
            'returned' => 'neutral',
            'rejected' => 'danger',
            'cancelled' => 'neutral',
        ],
        'item' => [
            'pending' => 'warning',
            'approved' => 'info',
            'picked_up' => 'violet',
            'returned' => 'success',
            'damaged' => 'danger',
            'lost' => 'danger',
        ],
        'maintenance' => [
            'scheduled' => 'warning',
            'in_progress' => 'info',
            'done' => 'success',
            'cancelled' => 'neutral',
        ],
        'penalty' => [
            'waiting_payment' => 'warning',
            'partial' => 'info',
            'installment_requested' => 'violet',
            'paid' => 'success',
            'waived' => 'neutral',
            'overdue' => 'danger',
        ],
        'payment' => [
            'pending' => 'warning',
            'waiting_verification' => 'info',
            'verified' => 'success',
            'rejected' => 'danger',
        ],
        'inspection' => [
            'ok' => 'success',
            'needs_repair' => 'warning',
            'damaged' => 'danger',
        ],
    ];

    $prefix = match ($type) {
        'booking' => 'ui.booking_status.',
        'item' => 'ui.item_status.',
        'maintenance' => 'ui.maintenance_status.',
        'penalty' => 'ui.penalty.status.',
        'payment' => 'ui.payment.status.',
        'inspection' => 'ui.inspection_result.',
        default => 'ui.status.',
    };

    $resolvedVariant = $variant ?? $variants[$type][$status] ?? 'neutral';
    $label = $status ? __($prefix.$status) : null;
@endphp

<span {{ $attributes->merge(['class' => 'ldr-badge ldr-badge--'.$resolvedVariant]) }}>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        {{ $label }}
    @endif
</span>
