@props(['steps', 'currentStep', 'compact' => false])

@php
    $stepOrder = ['pending', 'approved', 'active', 'returned', 'completed'];
    $currentIndex = array_search($currentStep->value ?? $currentStep, $stepOrder);
    if ($currentIndex === false) {
        $currentIndex = -1;
    }
@endphp

<div class="ldr-booking-stepper {{ $compact ? 'ldr-booking-stepper--compact' : '' }}" aria-label="{{ __('ui.booking.stepper_label') }}">
    <div class="ldr-booking-stepper__track" role="list">
        @foreach ($steps as $index => $step)
            @php
                $isCompleted = $index < $currentIndex;
                $isCurrent = $index === $currentIndex;
                $isFuture = $index > $currentIndex;
            @endphp

            <div class="ldr-booking-stepper__step {{ $isCompleted ? 'is-completed' : '' }} {{ $isCurrent ? 'is-current' : '' }} {{ $isFuture ? 'is-future' : '' }}" role="listitem">
                <div class="ldr-booking-stepper__circle">
                    @if ($isCompleted)
                        <x-icon name="check" :size="14" />
                    @elseif ($isCurrent)
                        @if ($currentStep === 'active')
                            <x-icon name="key" :size="14" />
                        @else
                            {{ $index + 1 }}
                        @endif
                    @else
                        {{ $index + 1 }}
                    @endif
                </div>

                @if (! $compact)
                    <span class="ldr-booking-stepper__label">{{ $step['label'] }}</span>
                    <span class="ldr-booking-stepper__time">
                        @if (isset($step['time']))
                            {{ $step['time'] }}
                        @elseif ($isFuture)
                            {{ $step['scheduled'] ?? '—' }}
                        @endif
                    </span>
                @endif
            </div>

            @if ($index < count($steps) - 1 && ! $compact)
                <div class="ldr-booking-stepper__connector {{ $isCompleted ? 'is-completed' : '' }}"></div>
            @endif
        @endforeach
    </div>

    @if ($compact)
        <div class="ldr-booking-stepper__progress">
            <span>{{ __('ui.booking.stepper_progress', ['current' => $currentIndex + 1, 'total' => count($steps)]) }}</span>
        </div>
    @endif
</div>