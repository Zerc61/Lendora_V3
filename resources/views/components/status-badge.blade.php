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
    ];

    $prefix = match ($type) {
        'booking' => 'ui.booking_status.',
        'item' => 'ui.item_status.',
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
