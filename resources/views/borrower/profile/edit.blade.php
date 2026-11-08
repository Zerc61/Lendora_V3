@extends('layouts.borrower')

@section('title', __('ui.nav.profile'))
@section('nav', 'profile')

@section('content')
    <h1>{{ __('ui.nav.profile') }}</h1>

    <p>{{ $user->email }} · {{ $user->role?->name }}</p>

    <form method="POST" action="{{ route('borrower.profile.update') }}">
        @csrf
        @method('PUT')

        <label for="name">Nama</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>

        <label for="phone">Telepon</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">

        <label for="current_password">Kata sandi saat ini</label>
        <input id="current_password" type="password" name="current_password">

        <label for="password">Kata sandi baru</label>
        <input id="password" type="password" name="password">

        <label for="password_confirmation">Ulangi kata sandi baru</label>
        <input id="password_confirmation" type="password" name="password_confirmation">

        <button type="submit">Simpan</button>
    </form>
@endsection
