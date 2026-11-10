@extends('layouts.borrower')

@section('title', __('ui.nav.profile'))
@section('nav', 'profile')

@section('content')
    @php
        $activeItem = $activeBooking?->items->first();
        $activeAsset = $activeItem?->asset;
        $activePhoto = $activeAsset?->photos->first();
    @endphp

    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.profile.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.profile.subtitle') }}</p>
        </div>
    </div>

    <div class="ldr-profile-layout">
        <aside class="ldr-profile-side">
            <section class="ldr-card ldr-profile-identity">
                <span class="ldr-profile-identity__avatar" aria-hidden="true">
                    {{ mb_substr($user->name, 0, 1) }}
                </span>

                <h2>{{ $user->name }}</h2>

                <p class="ldr-profile-identity__meta">
                    @if ($user->nim)
                        NIM {{ $user->nim }}
                    @endif
                    @if ($user->study_program)
                        · {{ $user->study_program }}
                    @endif
                    @if ($user->semester)
                        · Semester {{ $user->semester }}
                    @endif
                </p>

                <div class="ldr-profile-identity__badges">
                    <span class="ldr-badge ldr-badge--primary">{{ $user->role?->name }}</span>

                    @if ($user->academic_verified_at)
                        <span class="ldr-badge ldr-badge--success">
                            <x-icon name="shield-check" :size="14" />
                            {{ __('ui.profile.verified') }}
                        </span>
                    @endif
                </div>

                @if ($user->lab_member_id || $user->lab_tier)
                    <div class="ldr-profile-pass">
                        <span>
                            <x-icon name="graduation-cap" :size="16" />
                            {{ __('ui.profile.lab_id') }}: {{ $user->lab_member_id ?: '—' }}
                        </span>
                        <strong>{{ $user->lab_tier ? __('ui.profile.tier', ['tier' => $user->lab_tier]) : '' }}</strong>
                    </div>
                @endif
            </section>

            <section class="ldr-card ldr-profile-summary">
                <div class="ldr-profile-summary__head">
                    <h2>{{ __('ui.profile.loan_summary') }}</h2>
                    <span class="ldr-muted">{{ __('ui.profile.total_records', ['count' => $counts['total']]) }}</span>
                </div>

                <div class="ldr-profile-summary__grid">
                    <a class="ldr-profile-stat is-info" href="{{ route('borrower.bookings.index', ['status' => 'active']) }}">
                        <strong>{{ $counts['active'] }}</strong>
                        <span>{{ __('ui.booking_status.active') }}</span>
                    </a>
                    <a class="ldr-profile-stat is-warning" href="{{ route('borrower.bookings.index', ['status' => 'pending']) }}">
                        <strong>{{ $counts['pending'] }}</strong>
                        <span>{{ __('ui.booking_status.pending') }}</span>
                    </a>
                    <a class="ldr-profile-stat is-success" href="{{ route('borrower.bookings.index', ['status' => 'returned']) }}">
                        <strong>{{ $counts['returned'] }}</strong>
                        <span>{{ __('ui.booking_status.returned') }}</span>
                    </a>
                </div>

                @if ($activeBooking)
                    <a class="ldr-profile-teaser" href="{{ route('borrower.bookings.show', $activeBooking) }}">
                        <span class="ldr-profile-teaser__media">
                            @if ($activePhoto)
                                <img src="{{ Illuminate\Support\Facades\Storage::url($activePhoto->path) }}"
                                     alt="{{ $activeAsset?->name }}"
                                     loading="lazy"
                                     width="48"
                                     height="48">
                            @else
                                <x-icon name="camera" :size="20" />
                            @endif
                        </span>
                        <span class="ldr-profile-teaser__body">
                            <strong>{{ $activeAsset?->name ?? __('ui.booking.assets') }}</strong>
                            <span>
                                @if ($activeBooking->due_at)
                                    {{ __('ui.profile.due_soon', ['time' => $activeBooking->due_at->locale('id')->translatedFormat('d M, H:i')]) }}
                                @else
                                    {{ $activeBooking->code }}
                                @endif
                            </span>
                        </span>
                        <x-icon name="chevron-right" :size="18" />
                    </a>
                @endif
            </section>

            <section class="ldr-card ldr-profile-tip">
                <span class="ldr-profile-tip__icon" aria-hidden="true">
                    <x-icon name="help-circle" :size="20" />
                </span>
                <div>
                    <h2>{{ __('ui.profile.tip_title') }}</h2>
                    <p>{{ __('ui.profile.tip_body') }}</p>
                </div>
            </section>

            <x-borrower.compliance-card :score="$compliance['score'] ?? $user->compliance_score" :tier="$compliance['tier'] ?? null" />
            <x-borrower.quota-card :total="$quota['total_hours'] ?? 25" :used="$quota['used_hours'] ?? 0" :remaining="$quota['remaining_hours'] ?? '—'" />
            <x-borrower.sanction-card :total="$sanctions['total_penalties'] ?? 0" :amount="$sanctions['total_amount'] ?? 0" />
        </aside>

        <form method="POST" action="{{ route('borrower.profile.update') }}" class="ldr-profile-form">
            @csrf
            @method('PUT')

            <section class="ldr-card ldr-form-section">
                <div class="ldr-form-section__head">
                    <span class="ldr-form-section__icon">
                        <x-icon name="user" :size="20" />
                    </span>
                    <div>
                        <h2>{{ __('ui.profile.identity') }}</h2>
                        <p class="ldr-form-hint">{{ __('ui.profile.identity_hint') }}</p>
                    </div>
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="name">{{ __('ui.user.name') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                    <input id="name" class="ldr-input" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @error('name') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="email">{{ __('ui.auth.email') }}</label>
                    <input id="email" class="ldr-input" type="email" value="{{ $user->email }}" readonly disabled>
                    <p class="ldr-form-hint">{{ __('ui.profile.email_locked') }}</p>
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="phone">{{ __('ui.user.phone') }}</label>
                    <input id="phone" class="ldr-input" type="text" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                    @error('phone') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                @if ($user->nim || $user->study_program)
                    <dl class="ldr-profile-readonly">
                        @if ($user->nim)
                            <div>
                                <dt>{{ __('ui.profile.nim') }}</dt>
                                <dd>{{ $user->nim }}</dd>
                            </div>
                        @endif
                        @if ($user->study_program)
                            <div>
                                <dt>{{ __('ui.profile.study_program') }}</dt>
                                <dd>{{ $user->study_program }}</dd>
                            </div>
                        @endif
                        @if ($user->cohort_year)
                            <div>
                                <dt>{{ __('ui.profile.cohort_year') }}</dt>
                                <dd>{{ $user->cohort_year }}</dd>
                            </div>
                        @endif
                        @if ($user->study_concentration)
                            <div>
                                <dt>{{ __('ui.profile.concentration') }}</dt>
                                <dd>{{ $user->study_concentration }}</dd>
                            </div>
                        @endif
                        @if ($user->dpa_name)
                            <div>
                                <dt>{{ __('ui.profile.dpa') }}</dt>
                                <dd>{{ $user->dpa_name }}</dd>
                            </div>
                        @endif
                        @if ($user->dpa_nidn)
                            <div>
                                <dt>{{ __('ui.profile.dpa_nidn') }}</dt>
                                <dd>{{ $user->dpa_nidn }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                <div class="ldr-form-actions">
                    <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.save') }}</button>
                </div>
            </section>

            <x-notification-preferences :types="$notificationTypes" :user="$user" />

            <section class="ldr-card ldr-form-section">
                <div class="ldr-form-section__head">
                    <span class="ldr-form-section__icon">
                        <x-icon name="lock" :size="20" />
                    </span>
                    <div>
                        <h2>{{ __('ui.profile.change_password') }}</h2>
                        <p class="ldr-form-hint">{{ __('ui.profile.change_password_hint') }}</p>
                    </div>
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="current_password">{{ __('ui.profile.current_password') }}</label>
                    <input id="current_password" class="ldr-input" type="password" name="current_password" autocomplete="current-password">
                    @error('current_password') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="password">{{ __('ui.profile.new_password') }}</label>
                    <input id="password" class="ldr-input" type="password" name="password" autocomplete="new-password">
                    @error('password') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="password_confirmation">{{ __('ui.user.password_confirmation') }}</label>
                    <input id="password_confirmation" class="ldr-input" type="password" name="password_confirmation" autocomplete="new-password">
                </div>

                <div class="ldr-form-actions">
                    <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.profile.save_password') }}</button>
                </div>
            </section>
        </form>

        <section class="ldr-card ldr-profile-logout">
            <div>
                <h2>{{ __('ui.profile.logout_title') }}</h2>
                <p class="ldr-muted">{{ __('ui.profile.logout_hint') }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ldr-btn ldr-btn--danger">
                    <x-icon name="log-out" :size="18" />
                    {{ __('ui.auth.logout') }}
                </button>
            </form>
        </section>
    </div>
@endsection
