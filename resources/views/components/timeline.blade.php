@props(['timeline' => [], 'status' => null])

@php
    $steps = [
        ['status' => 'pending', 'label' => __('ui.booking.timeline_steps.submitted')],
        ['status' => 'approved', 'label' => __('ui.booking.timeline_steps.approved')],
        ['status' => 'active', 'label' => __('ui.booking.timeline_steps.picked_up')],
        ['status' => 'returned', 'label' => __('ui.booking.timeline_steps.returned')],
        ['status' => 'done', 'label' => __('ui.booking.timeline_steps.done')],
    ];

    $indexByStatus = [
        'pending' => 0,
        'approved' => 1,
        'active' => 2,
        'returned' => 3,
    ];

    $events = [];

    foreach ($timeline as $event) {
        $index = $indexByStatus[$event['status']] ?? null;

        if ($index !== null && ! isset($events[$index])) {
            $events[$index] = $event;
        }
    }

    $failed = in_array($status, ['rejected', 'cancelled'], true);
    $failure = null;

    if ($failed) {
        $failure = collect($timeline)->last(fn (array $event) => in_array($event['status'], ['rejected', 'cancelled'], true));
    }

    $lastReached = $events === [] ? 0 : max(array_keys($events));
@endphp

<ol class="ldr-timeline" aria-label="{{ __('ui.booking.timeline') }}">
    @foreach ($steps as $index => $step)
        @php
            $event = $events[$index] ?? null;

            if ($failed) {
                $state = $event ? 'is-done' : 'is-pending';
            } else {
                $state = $event === null ? 'is-pending' : ($index === $lastReached ? 'is-current' : 'is-done');
            }
        @endphp

        <li class="ldr-timeline__step {{ $state }}">
            <span class="ldr-timeline__marker" aria-hidden="true"></span>

            <div class="ldr-timeline__content">
                <p class="ldr-timeline__label">{{ $step['label'] }}</p>

                @if ($event)
                    <p class="ldr-timeline__meta">
                        @if ($event['actor'])
                            {{ $event['actor'] }} ·
                        @endif
                        {{ $event['time']?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}
                    </p>

                    @if ($event['note'])
                        <p class="ldr-timeline__note">{{ $event['note'] }}</p>
                    @endif
                @else
                    <p class="ldr-timeline__meta">—</p>
                @endif
            </div>
        </li>
    @endforeach

    @if ($failed && $failure)
        <li class="ldr-timeline__step is-failed">
            <span class="ldr-timeline__marker" aria-hidden="true"></span>

            <div class="ldr-timeline__content">
                <p class="ldr-timeline__label">
                    {{ $status === 'rejected' ? __('ui.booking_status.rejected') : __('ui.booking_status.cancelled') }}
                </p>
                <p class="ldr-timeline__meta">
                    @if ($failure['actor'])
                        {{ $failure['actor'] }} ·
                    @endif
                    {{ $failure['time']?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}
                </p>

                @if ($failure['note'])
                    <p class="ldr-timeline__note">{{ $failure['note'] }}</p>
                @endif
            </div>
        </li>
    @endif
</ol>
