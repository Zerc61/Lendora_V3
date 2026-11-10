@extends('layouts.admin')

@section('title', 'Antrean Loket')

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>Antrean Loket</h1>
            <p class="ldr-muted">
                {{ $shift['label'] }} · {{ $shift['start'] }}–{{ $shift['end'] }} WIB ·
                {{ now()->locale('id')->translatedFormat('d M Y') }}
            </p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.kiosk.index') }}">
                <x-icon name="scan-line" :size="16" />
                Kiosk Scanner
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
    @endif

    <div class="ldr-op-livebar">
        <span class="ldr-op-live">
            <span class="ldr-op-live__dot" aria-hidden="true"></span>
            Antrean berjalan
        </span>
        <span class="ldr-muted">{{ $board['waiting_count'] }} menunggu · {{ $board['done_count'] }} selesai hari ini</span>
        <label class="ldr-checkbox ldr-op-livebar__poll">
            <input type="checkbox" data-queue-poll-toggle data-queue-poll-interval="30" checked>
            <span>Muat ulang otomatis tiap 30 detik</span>
        </label>
    </div>

    <div class="ldr-kiosk__grid ldr-kiosk__grid--queue" data-queue-poll="30">
        <section class="ldr-card ldr-queue-panel">
            <div class="ldr-card-head ldr-card-head--flush">
                <div>
                    <h2>Menunggu ({{ $board['waiting_count'] }})</h2>
                    <p>Tiket yang belum dipanggil, urut waktu terbit.</p>
                </div>
            </div>

            @if ($board['waiting']->isEmpty())
                <p class="ldr-dash-empty">{{ __('messages.queue.empty') }}</p>
            @else
                <ol class="ldr-queue-list">
                    @foreach ($board['waiting'] as $ticket)
                        <li class="ldr-queue-item">
                            <span class="ldr-queue-item__number">{{ $ticket->number }}</span>
                            <div class="ldr-queue-item__body">
                                <strong>{{ $ticket->service_type->label() }}</strong>
                                <span class="ldr-muted">
                                    {{ $ticket->nim ?: '—' }}
                                    @if ($ticket->issued_at)
                                        · {{ $ticket->issued_at->copy()->locale('id')->translatedFormat('H:i') }} WIB
                                    @endif
                                </span>
                            </div>
                            <form method="POST" action="{{ route('admin.kiosk.queue.call') }}">
                                @csrf
                                <input type="hidden" name="counter" value="{{ $ticket->counter }}">
                                <button class="ldr-btn ldr-btn--secondary ldr-btn--sm" type="submit">
                                    Panggil
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ol>
            @endif

            <form method="POST" action="{{ route('admin.kiosk.queue.call') }}" class="ldr-queue-call">
                @csrf
                <label class="ldr-field">
                    <span>Loket</span>
                    <select class="ldr-input" name="counter">
                        <option value="">Semua loket</option>
                        <option value="A">A · Pengambilan</option>
                        <option value="B">B · Pengembalian</option>
                        <option value="T">T · Konsultasi</option>
                    </select>
                </label>
                <button class="ldr-btn ldr-btn--primary" type="submit">
                    <x-icon name="bell" :size="16" />
                    Panggil berikutnya
                </button>
            </form>
        </section>

        <div class="ldr-kiosk__stack">
            <section class="ldr-card ldr-queue-panel">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>Loket aktif</h2>
                        <p>Tiket yang sedang dipanggil atau dilayani.</p>
                    </div>
                </div>

                @if ($board['counters']->isEmpty())
                    <p class="ldr-dash-empty">Belum ada loket aktif.</p>
                @else
                    <ul class="ldr-queue-counters">
                        @foreach ($board['counters'] as $counter => $ticket)
                            <li class="ldr-queue-counter">
                                <span class="ldr-queue-counter__label">Loket {{ $counter ?: '—' }}</span>
                                <strong class="ldr-queue-counter__number">{{ $ticket->number }}</strong>
                                <span class="ldr-muted">{{ $ticket->service_type->label() }}</span>

                                <form method="POST" action="{{ route('admin.kiosk.queue.serve', $ticket) }}">
                                    @csrf
                                    <button class="ldr-btn ldr-btn--primary ldr-btn--sm" type="submit">
                                        Selesaikan
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="ldr-card ldr-queue-panel">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>Catatan shift</h2>
                        <p>Serah terima operasional loket.</p>
                    </div>
                </div>

                @if ($notes->isEmpty())
                    <p class="ldr-dash-empty">Belum ada catatan shift.</p>
                @else
                    <ul class="ldr-shift-notes">
                        @foreach ($notes as $note)
                            <li class="ldr-shift-note">
                                <div class="ldr-shift-note__head">
                                    <strong>{{ $note->shift }}</strong>
                                    <span class="ldr-muted">
                                        {{ $note->user?->name ?? '—' }} ·
                                        {{ $note->created_at?->copy()->locale('id')->translatedFormat('d M H:i') }}
                                    </span>
                                </div>
                                <p>{{ $note->note }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <form method="POST" action="{{ route('admin.shifts.notes.store') }}" class="ldr-stack">
                    @csrf
                    <label class="ldr-field">
                        <span>Catatan serah terima shift</span>
                        <textarea id="shift-note" class="ldr-input" name="note" rows="3" required maxlength="2000">{{ old('note') }}</textarea>
                    </label>
                    @error('note')
                        <p class="ldr-field-error">{{ $message }}</p>
                    @enderror
                    <button class="ldr-btn ldr-btn--primary" type="submit">Simpan catatan</button>
                </form>
            </section>
        </div>
    </div>
@endsection
