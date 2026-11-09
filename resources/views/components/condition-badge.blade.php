@props(['condition' => null, 'variant' => null])

@php
    $variants = [
        'good' => 'success',
        'minor_damage' => 'warning',
        'fair' => 'warning',
        'damaged' => 'danger',
        'lost' => 'danger',
    ];

    $resolvedVariant = $variant ?? $variants[$condition] ?? 'neutral';
    $label = $condition ? __('ui.condition.'.$condition) : null;
@endphp

<span {{ $attributes->merge(['class' => 'ldr-badge ldr-badge--'.$resolvedVariant]) }}>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        {{ $label }}
    @endif
</span>
