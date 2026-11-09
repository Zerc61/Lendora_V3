@props(['steps'])

<div class="ldr-recovery-steps">
    @foreach ($steps as $num => $step)
        <div class="ldr-recovery-step {{ $loop->first ? 'is-active' : '' }}">
            <span class="ldr-recovery-step__number">{{ $num }}</span>
            <div class="ldr-recovery-step__content">
                <div class="ldr-recovery-step__title">{{ $step['title'] }}</div>
                <div class="ldr-recovery-step__desc">{{ $step['description'] }}</div>
            </div>
        </div>
    @endforeach
</div>