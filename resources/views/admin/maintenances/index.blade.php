@extends('layouts.admin')

@section('title', __('ui.nav.maintenances'))

@section('content')
    <h1>{{ __('ui.nav.maintenances') }}</h1>

    @if (in_array($role, ['operator', 'organization_admin'], true))
        <a href="{{ route('admin.maintenances.create') }}">Buat tugas perawatan</a>
    @endif

    <ul>
        @forelse ($maintenances as $maintenance)
            <li>
                {{ $maintenance->asset?->code }} ·
                {{ $maintenance->status->value }} ·
                {{ $maintenance->assignedTo?->name ?? 'Belum ditugaskan' }}

                @if ($role === 'organization_admin')
                    <a href="{{ route('admin.maintenances.edit', $maintenance) }}">Ubah</a>
                @endif

                @if ($role === 'technician' && $maintenance->status === \App\Enums\MaintenanceStatus::Scheduled)
                    <form method="POST" action="{{ route('admin.maintenances.start', $maintenance) }}">
                        @csrf
                        <button type="submit">Mulai</button>
                    </form>
                @endif

                @if ($role === 'technician' && $maintenance->status === \App\Enums\MaintenanceStatus::InProgress)
                    <a href="{{ route('admin.maintenances.finish', $maintenance) }}">Selesaikan</a>
                @endif
            </li>
        @empty
            <li>Belum ada tugas perawatan.</li>
        @endforelse
    </ul>

    {{ $maintenances->links() }}
@endsection
