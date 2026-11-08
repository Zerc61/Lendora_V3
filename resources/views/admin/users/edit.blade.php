@extends('layouts.admin')

@section('title', __('ui.nav.users'))

@section('content')
    <h1>Ubah pengguna · {{ $user->name }}</h1>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <label for="name">Nama</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>

        <label for="email">Surel</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>

        <label for="phone">Telepon</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">

        <label for="role_id">Peran</label>
        <select id="role_id" name="role_id" required>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>

        <label for="password">Kata sandi baru (opsional)</label>
        <input id="password" type="password" name="password">

        <label for="password_confirmation">Ulangi kata sandi baru</label>
        <input id="password_confirmation" type="password" name="password_confirmation">

        <button type="submit">Simpan</button>
    </form>
@endsection
