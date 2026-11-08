@extends('layouts.admin')

@section('title', __('ui.nav.settings'))

@section('content')
    <h1>{{ __('ui.nav.settings') }}</h1>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <label for="organization_name">Nama organisasi</label>
        <input id="organization_name" type="text" name="organization_name" value="{{ old('organization_name', $organization->name) }}" required>

        <label for="organization_address">Alamat</label>
        <input id="organization_address" type="text" name="organization_address" value="{{ old('organization_address', $organization->address) }}">

        <label for="organization_phone">Telepon</label>
        <input id="organization_phone" type="text" name="organization_phone" value="{{ old('organization_phone', $organization->phone) }}">

        <label for="organization_email">Surel</label>
        <input id="organization_email" type="email" name="organization_email" value="{{ old('organization_email', $organization->email) }}">

        <label for="max_loan_days">Maksimal hari pinjam</label>
        <input id="max_loan_days" type="number" name="max_loan_days" min="1" value="{{ old('max_loan_days', $settings['max_loan_days']) }}" required>

        <label for="operator_approval_limit">Batas nilai persetujuan operator (Rp)</label>
        <input id="operator_approval_limit" type="number" name="operator_approval_limit" min="0" value="{{ old('operator_approval_limit', $settings['operator_approval_limit']) }}" required>

        <label for="return_reminder_enabled">
            <input id="return_reminder_enabled" type="checkbox" name="return_reminder_enabled" value="1" @checked((bool) $settings['return_reminder_enabled'])>
            Aktifkan pengingat H-1
        </label>

        <label for="borrower_announcement">Pengumuman peminjam</label>
        <textarea id="borrower_announcement" name="borrower_announcement">{{ old('borrower_announcement', $settings['borrower_announcement']) }}</textarea>

        <button type="submit">Simpan</button>
    </form>
@endsection
