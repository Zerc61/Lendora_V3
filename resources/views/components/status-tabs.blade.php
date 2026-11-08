@props(['route', 'current' => null, 'counts' => [], 'query' => [], 'statuses' => null])

@php
    $tabs = collect($statuses ?? ['pending', 'approved', 'active', 'returned', 'rejected', 'cancelled'])
        ->map(fn (string $status) => [
            'value' => $status,
            'label' => __('ui.booking_status.'.$status),
            'count' => (int) ($counts[$status] ?? 0),
        ]);

    $allCount = $tabs->sum('count');
@endphp

<nav class="ldr-status-tabs" aria-label="{{ __('ui.booking.filter_status') }}">
    <a class="ldr-status-tabs__tab {{ $current === null ? 'is-active' : '' }}"
       href="{{ route($route, array_filter($query, fn ($value) => $value !== null && $value !== '')) }}"
       @if ($current === null) aria-current="page" @endif>
        {{ __('ui.common.all') }}
        <span class="ldr-status-tabs__count">{{ $allCount }}</span>
    </a>

    @foreach ($tabs as $tab)
        <a class="ldr-status-tabs__tab {{ $current === $tab['value'] ? 'is-active' : '' }}"
           href="{{ route($route, array_filter([...$query, 'status' => $tab['value']], fn ($value) => $value !== null && $value !== '')) }}"
           @if ($current === $tab['value']) aria-current="page" @endif>
            {{ $tab['label'] }}
            <span class="ldr-status-tabs__count">{{ $tab['count'] }}</span>
        </a>
    @endforeach
</nav>
