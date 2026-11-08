@extends('layouts.admin')

@section('title', __('ui.nav.dashboard'))

@section('content')
    <h1>{{ __('ui.admin.welcome', ['name' => $user->name, 'role' => $user->role->name]) }}</h1>

    <dl>
        @foreach ($kpis as $key => $value)
            <dt>{{ $key }}</dt>
            <dd>{{ $value }}</dd>
        @endforeach
    </dl>

    @if ($role === 'organization_admin' && $recentActivity->isNotEmpty())
        <h2>Log aktivitas terbaru</h2>
        <ul>
            @foreach ($recentActivity as $log)
                <li>{{ $log->created_at?->format('d M Y H:i') }} · {{ $log->description }}</li>
            @endforeach
        </ul>
    @endif

    @if ($role === 'admin_prodi' && $specialQueue->isNotEmpty())
        <h2>Antrean persetujuan khusus</h2>
        <ul>
            @foreach ($specialQueue as $booking)
                <li>
                    {{ $booking->code }} · {{ $booking->borrower?->name }} ·
                    {{ $booking->durationInDays() }} hari
                </li>
            @endforeach
        </ul>
    @endif

    @if ($role === 'technician' && $tasks->isNotEmpty())
        <h2>Tugas saya</h2>
        <ul>
            @foreach ($tasks as $task)
                <li>{{ $task->asset?->code }} · {{ $task->status->value }}</li>
            @endforeach
        </ul>
    @endif
@endsection
