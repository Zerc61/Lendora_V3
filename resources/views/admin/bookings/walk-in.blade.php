@extends('layouts.admin')

@section('title', __('ui.walk_in.title'))

@section('content')
    <div class="ldr-walkin-page">
        <div class="ldr-page-head">
            <div>
                <span class="ldr-eyebrow">{{ __('ui.sections.operator_counter') }}</span>
                <h1>{{ __('ui.walk_in.title') }}</h1>
                <p class="ldr-muted">{{ __('ui.walk_in.subtitle') }}</p>
            </div>

            <div class="ldr-page-head__actions">
                <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.bookings.approval') }}">
                    <x-icon name="clipboard-list" :size="16" />
                    {{ __('ui.nav.approval_queue') }}
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="ldr-alert ldr-alert--danger" role="alert">
                <x-icon name="alert-circle" :size="18" />
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.bookings.walk-in.store') }}" class="ldr-walkin-form">
            @csrf

            <div class="ldr-walkin-layout">
                <div class="ldr-walkin-main">
                    <section class="ldr-card ldr-walkin-section">
                        <header class="ldr-walkin-section__head">
                            <span class="ldr-walkin-section__icon">
                                <x-icon name="user" :size="20" />
                            </span>
                            <div class="ldr-walkin-section__title">
                                <h2>{{ __('ui.walk_in.details') }}</h2>
                                <p>{{ __('ui.walk_in.details_hint') }}</p>
                            </div>
                            <span class="ldr-walkin-section__step">{{ __('ui.walk_in.section_one') }}</span>
                        </header>

                        <div class="ldr-field">
                            <label class="ldr-label" for="walkin-borrower">{{ __('ui.walk_in.borrower') }}</label>
                            <select id="walkin-borrower" class="ldr-input" name="borrower_id" required>
                                <option value="">{{ __('ui.walk_in.choose_borrower') }}</option>
                                @foreach ($borrowers as $borrower)
                                    <option value="{{ $borrower->id }}" @selected((int) old('borrower_id') === $borrower->id)>
                                        {{ $borrower->name }}{{ $borrower->nim ? ' · '.$borrower->nim : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('borrower_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
                        </div>

                        <div class="ldr-field-row">
                            <div class="ldr-field">
                                <label class="ldr-label" for="walkin-start">{{ __('ui.walk_in.start') }}</label>
                                <input id="walkin-start" class="ldr-input" type="date" name="requested_start_date" value="{{ old('requested_start_date', now()->toDateString()) }}" required>
                                @error('requested_start_date') <p class="ldr-field__error">{{ $message }}</p> @enderror
                            </div>

                            <div class="ldr-field">
                                <label class="ldr-label" for="walkin-end">{{ __('ui.walk_in.end') }}</label>
                                <input id="walkin-end" class="ldr-input" type="date" name="requested_end_date" value="{{ old('requested_end_date', now()->addDays(2)->toDateString()) }}" required>
                                @error('requested_end_date') <p class="ldr-field__error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="ldr-field">
                            <label class="ldr-label" for="walkin-purpose">{{ __('ui.walk_in.purpose') }}</label>
                            <input id="walkin-purpose" class="ldr-input" type="text" name="purpose" value="{{ old('purpose') }}" required maxlength="255" placeholder="{{ __('ui.walk_in.purpose_placeholder') }}">
                            @error('purpose') <p class="ldr-field__error">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    <section class="ldr-card ldr-walkin-section">
                        <header class="ldr-walkin-section__head">
                            <span class="ldr-walkin-section__icon">
                                <x-icon name="package" :size="20" />
                            </span>
                            <div class="ldr-walkin-section__title">
                                <h2>{{ __('ui.walk_in.units') }}</h2>
                                <p>{{ __('ui.walk_in.units_hint') }}</p>
                            </div>
                            <span class="ldr-walkin-section__step">{{ __('ui.walk_in.section_two') }}</span>
                        </header>

                        @error('asset_ids') <p class="ldr-field__error">{{ $message }}</p> @enderror

                        @if ($assets->isEmpty())
                            <p class="ldr-dash-empty">{{ __('ui.walk_in.units_empty') }}</p>
                        @else
                            <div class="ldr-walkin-units">
                                @foreach ($assets as $asset)
                                    @php
                                        $photo = $asset->photos->first();
                                    @endphp
                                    <label class="ldr-walkin-unit">
                                        <input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" @checked(in_array($asset->id, old('asset_ids', [])))>

                                        <span class="ldr-walkin-unit__media">
                                            @if ($photo)
                                                <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                                     alt="{{ $asset->name }}"
                                                     loading="lazy"
                                                     width="48"
                                                     height="48">
                                            @else
                                                <x-icon name="camera" :size="20" />
                                            @endif
                                        </span>

                                        <span class="ldr-walkin-unit__body">
                                            <strong>{{ $asset->name }}</strong>
                                            <span class="ldr-walkin-unit__code">{{ $asset->code }}</span>
                                            <span class="ldr-walkin-unit__category">{{ $asset->category?->name }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="ldr-walkin-side">
                    <section class="ldr-card ldr-walkin-summary">
                        <header class="ldr-walkin-summary__head">
                            <span class="ldr-walkin-section__icon">
                                <x-icon name="clipboard-list" :size="20" />
                            </span>
                            <div>
                                <h2>{{ __('ui.walk_in.summary_title') }}</h2>
                                <p>{{ __('ui.walk_in.summary_hint') }}</p>
                            </div>
                        </header>

                        <dl class="ldr-walkin-summary__facts">
                            <div>
                                <dt>{{ __('ui.walk_in.summary_borrowers') }}</dt>
                                <dd>{{ $borrowers->count() }}</dd>
                            </div>
                            <div>
                                <dt>{{ __('ui.walk_in.summary_units') }}</dt>
                                <dd>{{ $assets->count() }}</dd>
                            </div>
                        </dl>

                        <div class="ldr-walkin-summary__block">
                            <h3>{{ __('ui.walk_in.flow_title') }}</h3>
                            <ol class="ldr-walkin-flow">
                                <li>{{ __('ui.walk_in.flow_one') }}</li>
                                <li>{{ __('ui.walk_in.flow_two') }}</li>
                                <li>{{ __('ui.walk_in.flow_three') }}</li>
                            </ol>
                        </div>

                        <div class="ldr-walkin-callout">
                            <x-icon name="shield-check" :size="18" />
                            <div>
                                <strong>{{ __('ui.walk_in.policy_title') }}</strong>
                                <p>{{ __('ui.walk_in.policy_body') }}</p>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>

            <div class="ldr-walkin-footer">
                <p class="ldr-muted">
                    <x-icon name="activity" :size="15" />
                    {{ __('ui.walk_in.footer_note') }}
                </p>

                <div class="ldr-walkin-footer__actions">
                    <button class="ldr-btn ldr-btn--ghost" type="reset">{{ __('ui.walk_in.reset') }}</button>
                    <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.dashboard') }}">{{ __('ui.common.cancel') }}</a>
                    <button class="ldr-btn ldr-btn--primary" type="submit">
                        <x-icon name="check-circle" :size="16" />
                        {{ __('ui.walk_in.submit') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
