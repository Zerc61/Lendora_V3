@extends('layouts.admin')

@section('title', __('ui.nav.users'))

@section('content')
    <h1>Tambah pengguna</h1>

    <p>Organisasi: {{ $organization?->name }}</p>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <label for="name">Nama</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required>

        <label for="email">Surel</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="phone">Telepon</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone') }}">

        <label for="role_id">Peran</label>
        <select id="role_id" name="role_id" required>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
            @endforeach
        </select>

        <label for="password">Kata sandi</label>
        <input id="password" type="password" name="password" required>

        <label for="password_confirmation">Ulangi kata sandi</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button type="submit">Simpan</button>
    </form>
@endsection
