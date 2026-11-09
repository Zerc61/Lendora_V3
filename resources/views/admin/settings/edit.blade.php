@extends('layouts.admin')

@section('title', __('ui.nav.settings'))

@section('content')
    <div class="ldr-form-page">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.nav.settings') }}</h1>
                <p class="ldr-muted">{{ __('ui.setting.index_description') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="ldr-settings-banner">
                <span class="ldr-settings-banner__icon">
                    <x-icon name="check-circle" :size="20" />
                </span>
                <div>
                    <strong>{{ __('ui.setting.active_title') }}</strong>
                    <p>{{ __('ui.setting.active_hint') }}</p>
                </div>
            </div>

            <div class="ldr-form-grid">
                <section class="ldr-card ldr-form-section">
                    <h2>{{ __('ui.setting.organization_section') }}</h2>

                    <div class="ldr-field">
                        <label class="ldr-label" for="organization_name">{{ __('ui.setting.organization_name') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                        <input id="organization_name" class="ldr-input" type="text" name="organization_name" value="{{ old('organization_name', $organization->name) }}" required>
                        @error('organization_name') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="organization_address">{{ __('ui.setting.organization_address') }}</label>
                        <input id="organization_address" class="ldr-input" type="text" name="organization_address" value="{{ old('organization_address', $organization->address) }}">
                        @error('organization_address') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="organization_phone">{{ __('ui.setting.organization_phone') }}</label>
                        <input id="organization_phone" class="ldr-input" type="text" name="organization_phone" value="{{ old('organization_phone', $organization->phone) }}">
                        @error('organization_phone') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="organization_email">{{ __('ui.setting.organization_email') }}</label>
                        <input id="organization_email" class="ldr-input" type="email" name="organization_email" value="{{ old('organization_email', $organization->email) }}">
                        @error('organization_email') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="ldr-card ldr-form-section">
                    <h2>{{ __('ui.setting.policy_section') }}</h2>

                    <div class="ldr-field">
                        <label class="ldr-label" for="max_loan_days">{{ __('ui.setting.max_loan_days') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                        <input id="max_loan_days" class="ldr-input" type="number" name="max_loan_days" min="1" value="{{ old('max_loan_days', $settings['max_loan_days']) }}" required>
                        <p class="ldr-form-hint">{{ __('ui.setting.max_loan_days_hint') }}</p>
                        @error('max_loan_days') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="operator_approval_limit">{{ __('ui.setting.operator_limit') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                        <input id="operator_approval_limit" class="ldr-input" type="number" name="operator_approval_limit" min="0" value="{{ old('operator_approval_limit', $settings['operator_approval_limit']) }}" required>
                        <p class="ldr-form-hint">{{ __('ui.setting.operator_limit_hint') }}</p>
                        @error('operator_approval_limit') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <label class="ldr-checkbox" for="return_reminder_enabled">
                        <input id="return_reminder_enabled"
                               type="checkbox"
                               name="return_reminder_enabled"
                               value="1"
                               @checked((bool) old('return_reminder_enabled', $settings['return_reminder_enabled']))>
                        {{ __('ui.setting.return_reminder') }}
                    </label>

                    <div class="ldr-field">
                        <label class="ldr-label" for="borrower_announcement">{{ __('ui.setting.announcement') }}</label>
                        <textarea id="borrower_announcement" class="ldr-input" name="borrower_announcement" maxlength="500">{{ old('borrower_announcement', $settings['borrower_announcement']) }}</textarea>
                        <p class="ldr-form-hint">{{ __('ui.setting.announcement_hint') }}</p>
                        @error('borrower_announcement') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>
                </section>
            </div>

            <div class="ldr-form-actions">
                <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.save') }}</button>
            </div>
        </form>
    </div>
@endsection
