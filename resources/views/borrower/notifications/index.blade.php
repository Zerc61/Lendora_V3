@extends('layouts.borrower')

@section('title', __('ui.nav.notifications'))
@section('nav', 'notifications')

@section('content')
    <h1>{{ __('ui.nav.notifications') }}</h1>

    <p>Belum dibaca: {{ $unreadCount }}</p>

    <form method="POST" action="{{ route('borrower.notifications.read-all') }}">
        @csrf
        <button type="submit">Tandai semua dibaca</button>
    </form>

    @foreach (['today' => 'Hari ini', 'earlier' => 'Sebelumnya'] as $key => $label)
        <h2>{{ $label }}</h2>

        <ul>
            @forelse ($groups[$key] as $notification)
                <li>
                    {{ $notification->data['message'] ?? 'Notifikasi' }}
                    <span>{{ $notification->created_at?->format('d M Y H:i') }}</span>

                    @if ($notification->read_at === null)
                        <form method="POST" action="{{ route('borrower.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit">Tandai dibaca</button>
                        </form>
                    @endif
                </li>
            @empty
                <li>Tidak ada notifikasi.</li>
            @endforelse
        </ul>
    @endforeach
@endsection
