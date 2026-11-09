@props(['days'])

<div class="ldr-availability-calendar" role="list" aria-label="{{ __('ui.catalog.availability_30') }}">
    @foreach ($days as $day)
        <div class="ldr-availability__day {{ $day['status'] === 'available' ? 'is-available' : 'is-unavailable' }}"
             role="listitem"
             title="{{ $day['label'] ?? __('ui.catalog.availability_available') }}">
            <span class="ldr-availability__weekday">{{ $day['date']->translatedFormat('D') }}</span>
            <span class="ldr-availability__date">{{ $day['date']->format('j') }}</span>
        </div>
    @endforeach
</div>