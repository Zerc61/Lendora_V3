@extends('layouts.kiosk')

@section('title', 'Antrean Mandiri')

@section('bar')
    <span class="ldr-kiosk__shift">{{ $organization->name }}</span>
@endsection

@section('content')
    <div class="ldr-kiosk__head ldr-kiosk__head--center">
        <div>
            <span class="ldr-eyebrow">{{ __('ui.kiosk.queue_portal') }}</span>
            <h1>{{ __('ui.kiosk.queue_title') }}</h1>
            <p class="ldr-muted">{{ __('ui.kiosk.queue_subtitle') }}</p>
        </div>
    </div>

    @if ($ticket)
        <section class="ldr-ticket" role="status" aria-live="polite">
            <span class="ldr-ticket__label">{{ __('ui.kiosk.ticket_label') }}</span>
            <strong class="ldr-ticket__number">{{ $ticket['number'] }}</strong>
            <span class="ldr-ticket__service">
                {{ \App\Enums\QueueServiceType::from($ticket['service_type'])->label() }}
            </span>
            <span class="ldr-ticket__waiting">
                {{ __('ui.kiosk.ticket_waiting', ['count' => $ticket['waiting']]) }}
            </span>
            <span class="ldr-muted">{{ __('ui.kiosk.ticket_hint') }}</span>
        </section>
    @endif

    <div class="ldr-kiosk__grid ldr-kiosk__grid--queue" data-queue-poll="20">
        <section class="ldr-card ldr-kiosk__ticket-form">
            <div class="ldr-card-head ldr-card-head--flush">
                <div>
                    <h2>{{ __('ui.kiosk.take_ticket') }}</h2>
                    <p>{{ __('ui.kiosk.take_ticket_hint') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('kiosk.tickets.store') }}" class="ldr-stack">
                @csrf
                <label class="ldr-field">
                    <span for="kiosk-nim">{{ __('ui.kiosk.nim_label') }}</span>
                    <input id="kiosk-nim" class="ldr-input" type="text" name="nim" maxlength="30" autocomplete="off" placeholder="{{ __('ui.kiosk.nim_placeholder') }}">
                </label>
                @error('nim')
                    <p class="ldr-field-error">{{ $message }}</p>
                @enderror
                @error('service_type')
                    <p class="ldr-field-error">{{ $message }}</p>
                @enderror

                <div class="ldr-service-tiles">
                    @foreach ([\App\Enums\QueueServiceType::Pickup, \App\Enums\QueueServiceType::Return, \App\Enums\QueueServiceType::Consultation] as $type)
                        <button class="ldr-service-tile ldr-service-tile--{{ $type->value }}"
                                type="submit"
                                name="service_type"
                                value="{{ $type->value }}">
                            <span class="ldr-service-tile__icon">
                                <x-icon :name="match ($type) {
                                    \App\Enums\QueueServiceType::Pickup => 'package',
                                    \App\Enums\QueueServiceType::Return => 'arrow-left-right',
                                    default => 'help-circle',
                                }" :size="26" />
                            </span>
                            <strong>{{ $type->label() }}</strong>
                            <span class="ldr-service-tile__hint">{{ __('ui.kiosk.service_prefix', ['prefix' => match ($type) {
                                \App\Enums\QueueServiceType::Pickup => 'A',
                                \App\Enums\QueueServiceType::Return => 'B',
                                default => 'T',
                            }]) }}</span>
                        </button>
                    @endforeach
                </div>
            </form>
        </section>

        <section class="ldr-card ldr-kiosk__monitor">
            <div class="ldr-card-head ldr-card-head--flush">
                <div>
                    <h2>{{ __('ui.kiosk.monitor_title') }}</h2>
                    <p>{{ __('ui.kiosk.monitor_hint') }}</p>
                </div>
                <span class="ldr-op-live">
                    <span class="ldr-op-live__dot" aria-hidden="true"></span>
                    Live
                </span>
            </div>

            @if ($board['counters']->isEmpty())
                <p class="ldr-dash-empty">{{ __('ui.kiosk.monitor_empty') }}</p>
            @else
                <ul class="ldr-monitor">
                    @foreach ($board['counters'] as $counter => $called)
                        <li class="ldr-monitor__item">
                            <span class="ldr-monitor__counter">Loket {{ $counter ?: '—' }}</span>
                            <strong class="ldr-monitor__number">{{ $called->number }}</strong>
                            <span class="ldr-muted">{{ $called->service_type->label() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <dl class="ldr-monitor__stats">
                <div>
                    <dt>{{ __('ui.kiosk.stat_waiting') }}</dt>
                    <dd>{{ $board['waiting_count'] }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.kiosk.stat_done') }}</dt>
                    <dd>{{ $board['done_count'] }}</dd>
                </div>
            </dl>

            @if ($board['waiting']->isNotEmpty())
                <div class="ldr-monitor__next">
                    <span class="ldr-muted">{{ __('ui.kiosk.next_queue') }}</span>
                    <span class="ldr-monitor__next-numbers">
                        {{ $board['waiting']->take(5)->pluck('number')->join(' · ') }}
                    </span>
                </div>
            @endif
        </section>
    </div>
@endsection
