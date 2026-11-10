@extends('layouts.admin')

@section('title', __('ui.nav.profile'))

@section('content')
    <div class="ldr-profile-page">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.profile.title') }}</h1>
                <p class="ldr-muted">{{ __('ui.profile.subtitle') }}</p>
            </div>
        </div>

        <section class="ldr-card ldr-profile-hero">
            <span class="ldr-profile-hero__avatar" aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>

            <div class="ldr-profile-hero__body">
                <span class="ldr-eyebrow">{{ __('ui.profile.account') }}</span>
                <h2>{{ $user->name }}</h2>
                <p class="ldr-muted">{{ $user->email }}</p>

                <div class="ldr-profile-hero__badges">
                    <span class="ldr-badge ldr-badge--primary">{{ $user->role?->name }}</span>
                    @if ($user->status === 'suspended')
                        <span class="ldr-badge ldr-badge--danger">{{ __('ui.user.suspended') }}</span>
                    @else
                        <span class="ldr-badge ldr-badge--success">{{ __('ui.user.active') }}</span>
                    @endif
                </div>
            </div>

            <dl class="ldr-profile-facts">
                <div>
                    <dt>{{ __('ui.user.role') }}</dt>
                    <dd>{{ $user->role?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.auth.organization') }}</dt>
                    <dd>{{ $user->organization?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.user.phone') }}</dt>
                    <dd>{{ $user->phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.profile.joined') }}</dt>
                    <dd>{{ $user->created_at?->copy()->locale('id')->translatedFormat('d F Y') ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="ldr-profile-form ldr-profile-form--split">
            @csrf
            @method('PUT')

            <div class="ldr-profile-column">
                <section class="ldr-card ldr-form-section">
                    <h2>{{ __('ui.profile.identity') }}</h2>
                    <p class="ldr-form-hint">{{ __('ui.profile.identity_hint') }}</p>

                    <div class="ldr-field">
                        <label class="ldr-label" for="name">{{ __('ui.user.name') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                        <input id="name" class="ldr-input" type="text" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                        @error('name') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="phone">{{ __('ui.user.phone') }}</label>
                        <input id="phone" class="ldr-input" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                        @error('phone') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="email">{{ __('ui.auth.email') }}</label>
                        <input id="email" class="ldr-input" type="email" value="{{ $user->email }}" readonly>
                        <p class="ldr-form-hint">{{ __('ui.profile.email_locked') }}</p>
                    </div>
                </section>

                <section class="ldr-card ldr-form-section">
                    <h2>{{ __('ui.profile.change_password') }}</h2>
                    <p class="ldr-form-hint">{{ __('ui.profile.change_password_hint') }}</p>

                    <div class="ldr-field">
                        <label class="ldr-label" for="current_password">{{ __('ui.profile.current_password') }}</label>
                        <input id="current_password" class="ldr-input" type="password" name="current_password" autocomplete="current-password">
                        @error('current_password') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field-row">
                        <div class="ldr-field">
                            <label class="ldr-label" for="password">{{ __('ui.profile.new_password') }}</label>
                            <input id="password" class="ldr-input" type="password" name="password" autocomplete="new-password">
                            @error('password') <p class="ldr-field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="ldr-field">
                            <label class="ldr-label" for="password_confirmation">{{ __('ui.user.password_confirmation') }}</label>
                            <input id="password_confirmation" class="ldr-input" type="password" name="password_confirmation" autocomplete="new-password">
                        </div>
                    </div>
                </section>
            </div>

            <aside class="ldr-profile-column">
                <x-notification-preferences :types="$notificationTypes" :user="$user" />

                <section class="ldr-card ldr-profile-note">
                    <span class="ldr-profile-note__icon"><x-icon name="shield-check" :size="18" /></span>
                    <div>
                        <strong>{{ __('ui.profile.security_title') }}</strong>
                        <p class="ldr-muted">{{ __('ui.profile.security_hint') }}</p>
                    </div>
                </section>
            </aside>

            <div class="ldr-form-actions ldr-profile-actions">
                <button type="submit" class="ldr-btn ldr-btn--primary">
                    <x-icon name="check-circle" :size="16" />
                    {{ __('ui.common.save') }}
                </button>
                <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.dashboard') }}">{{ __('ui.common.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
