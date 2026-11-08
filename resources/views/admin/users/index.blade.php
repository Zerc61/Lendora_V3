@extends('layouts.admin')

@section('title', __('ui.nav.users'))

@section('content')
    <h1>{{ __('ui.nav.users') }}</h1>

    <a href="{{ route('admin.users.create') }}">Tambah pengguna</a>

    <form method="GET" action="{{ route('admin.users.index') }}">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama atau surel">
        <select name="role_id">
            <option value="">Semua peran</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(($filters['role_id'] ?? null) === $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
        <button type="submit">Filter</button>
    </form>

    <ul>
        @forelse ($users as $user)
            <li>
                {{ $user->name }} · {{ $user->email }} · {{ $user->role?->name }} · {{ $user->status }}
                <a href="{{ route('admin.users.edit', $user) }}">Ubah</a>

                @if ($user->status === 'suspended')
                    <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                        @csrf
                        <button type="submit">Aktifkan</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                        @csrf
                        <button type="submit">Tangguhkan</button>
                    </form>
                @endif
            </li>
        @empty
            <li>Belum ada pengguna.</li>
        @endforelse
    </ul>

    {{ $users->links() }}
@endsection
