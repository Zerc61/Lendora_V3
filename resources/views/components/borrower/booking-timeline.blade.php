@props(['steps', 'currentStep', 'compact' => false])

@php
    $stepOrder = ['pending', 'approved', 'active', 'returned', 'completed'];
    $currentIndex = array_search($currentStep->value ?? $currentStep, $stepOrder);
    if ($currentIndex === false) {
        $currentIndex = -1;
    }
@endphp

<div class="ldr-booking-timeline {{ $compact ? 'ldr-booking-timeline--compact' : '' }}" role="list" aria-label="{{ __('ui.booking.timeline_label') }}">
    @foreach ($steps as $index => $step)
        @php
            $isCompleted = $index < $currentIndex;
            $isCurrent = $index === $currentIndex;
            $isFuture = $index > $currentIndex;
        @endphp

        <div class="ldr-booking-timeline__step {{ $isCompleted ? 'is-completed' : '' }} {{ $isCurrent ? 'is-current' : '' }} {{ $isFuture ? 'is-future' : '' }}" role="listitem">
            <div class="ldr-booking-timeline__circle">
                @if ($isCompleted)
                    <x-icon name="check" :size="14" />
                @elseif ($isCurrent)
                    <x-icon name="key" :size="14" />
                @else
                    {{ $index + 1 }}
                @endif
            </div>

            @if (! $compact)
                <h3 class="ldr-booking-timeline__title">{{ $step['label'] }}</h3>
                <p class="ldr-booking-timeline__time">
                    @if (isset($step['time']))
                        {{ $step['time'] }}
                    @elseif ($isFuture)
                        {{ $step['scheduled'] ?? '—' }}
                    @endif
                </p>
            @endif
        </div>

        @if ($index < count($steps) - 1 && ! $compact)
            <div class="ldr-booking-timeline__line {{ $isCompleted ? 'is-completed' : '' }}"></div>
        @endif
    @endforeach
</div>