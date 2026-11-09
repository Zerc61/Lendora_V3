@props(['components'])

@if ($components->isEmpty())
    @return
@endif

<ul class="ldr-bundle-list">
    @foreach ($components as $component)
        <li class="ldr-bundle-list__item">
            <span class="ldr-bundle-list__name">{{ $component->name }}</span>
            @if ($component->serial_number)
                <span class="ldr-bundle-list__serial">{{ $component->serial_number }}</span>
            @endif
            <span class="ldr-bundle-list__qty">{{ __('ui.asset.bundle_qty', ['count' => $component->quantity]) }}</span>
            <span class="ldr-badge ldr-badge--condition-{{ $component->condition_status->value }}">
                {{ __('ui.condition_status.' . $component->condition_status->value) }}
            </span>
        </li>
    @endforeach
</ul>