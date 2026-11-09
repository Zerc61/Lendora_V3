@extends('layouts.borrower')

@section('title', __('ui.nav.home'))
@section('nav', 'home')

@section('content')
    <h1 class="ldr-display">{{ __('ui.borrower.greeting', ['name' => $user->name]) }}</h1>

    @if ($announcement !== '')
        <p role="note">{{ $announcement }}</p>
    @endif

    @if ($activeBooking !== null)
        <section>
            <h2>Peminjaman aktif · {{ $activeBooking->code }}</h2>
            <p>
                Jatuh tempo:
                {{ $activeBooking->due_at?->locale('id')->translatedFormat('d M Y H:i') ?? '-' }}
                @if ($dueInfo !== null)
                    ({{ $dueInfo['is_overdue'] ? 'terlambat' : $dueInfo['days_left'].' hari lagi' }})
                @endif
            </p>
        </section>
    @else
        <x-empty-state
            icon="package"
            :title="__('ui.borrower.no_active_title')"
            :description="__('ui.borrower.no_active_description')"
        />
    @endif

    <h2>Rekomendasi</h2>
    <ul>
        @foreach ($recommendations as $asset)
            <li>{{ $asset->code }} · {{ $asset->name }}</li>
        @endforeach
    </ul>
@endsection
