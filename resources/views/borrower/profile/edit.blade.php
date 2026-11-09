@extends('layouts.borrower')

@section('title', __('ui.nav.profile'))
@section('nav', 'profile')

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.profile') }}</h1>
            <p class="ldr-muted">{{ __('ui.profile.subtitle') }}</p>
        </div>
    </div>

    <section class="ldr-card ldr-profile-card">
        <span class="ldr-profile-card__avatar" aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>
        <div>
            <h2>{{ $user->name }}</h2>
            <p class="ldr-muted">{{ $user->email }}</p>
            <span class="ldr-badge ldr-badge--primary">{{ $user->role?->name }}</span>
        </div>
    </section>

    <form method="POST" action="{{ route('borrower.profile.update') }}" class="ldr-profile-form">
        @csrf
        @method('PUT')

        <section class="ldr-card ldr-form-section">
            <h2>{{ __('ui.profile.identity') }}</h2>

            <div class="ldr-field">
                <label class="ldr-label" for="name">{{ __('ui.user.name') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                <input id="name" class="ldr-input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="phone">{{ __('ui.user.phone') }}</label>
                <input id="phone" class="ldr-input" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                @error('phone') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>
        </section>

        <x-notification-preferences :types="$notificationTypes" :user="$user" />

        <section class="ldr-card ldr-form-section">
            <h2>{{ __('ui.profile.change_password') }}</h2>
            <p class="ldr-form-hint">{{ __('ui.profile.change_password_hint') }}</p>

            <div class="ldr-field">
                <label class="ldr-label" for="current_password">{{ __('ui.profile.current_password') }}</label>
                <input id="current_password" class="ldr-input" type="password" name="current_password">
                @error('current_password') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="password">{{ __('ui.profile.new_password') }}</label>
                <input id="password" class="ldr-input" type="password" name="password">
                @error('password') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="password_confirmation">{{ __('ui.user.password_confirmation') }}</label>
                <input id="password_confirmation" class="ldr-input" type="password" name="password_confirmation">
            </div>

            <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block">{{ __('ui.common.save') }}</button>
        </section>
    </form>
@endsection
