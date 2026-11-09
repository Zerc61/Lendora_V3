@extends('layouts.borrower')

@section('title', __('ui.profile.security_title'))
@section('nav', 'profile')

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.profile.security_title') }}</h1>
            <p class="ldr-muted">{{ __('ui.profile.security_hint') }}</p>
        </div>
        <a href="{{ route('borrower.profile.edit') }}" class="ldr-btn ldr-btn--ghost">
            {{ __('ui.common.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('borrower.two-factor.update') }}" class="ldr-profile-form">
        @csrf
        @method('PUT')

        <section class="ldr-card ldr-form-section">
            <div class="ldr-form-section__head">
                <span class="ldr-form-section__icon">
                    <x-icon name="shield-check" :size="20" />
                </span>
                <div>
                    <h2>{{ __('ui.profile.security_title') }}</h2>
                    <p class="ldr-form-hint">{{ __('ui.profile.otp_hint') }}</p>
                </div>
            </div>

            <label class="ldr-checkbox">
                <input type="checkbox" name="email_otp_enabled" value="1" @checked($user->email_otp_enabled)>
                <span>{{ __('ui.profile.otp_label') }}</span>
            </label>
        </section>

        <div class="ldr-form-actions">
            <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.save') }}</button>
        </div>
    </form>
@endsection
