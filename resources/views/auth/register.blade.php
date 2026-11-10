<x-guest-layout :title="__('ui.auth.register_title')">
    <header class="ldr-auth__intro">
        <h1 class="ldr-auth__title">{{ __('ui.auth.register_title') }}</h1>
        <p class="ldr-auth__subtitle">{{ __('ui.auth.register_subtitle') }}</p>
    </header>

    <form method="POST" action="{{ route('register') }}" class="ldr-auth__form">
        @csrf

        <div class="ldr-field">
            <label class="ldr-label" for="name">
                {{ __('ui.auth.name') }}
                <span class="ldr-label__required" aria-hidden="true">*</span>
            </label>

            <div class="ldr-input-group">
                <x-icon name="user" :size="18" />
                <input id="name"
                       class="ldr-input @error('name') ldr-input--error @enderror"
                       type="text"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="{{ __('ui.auth.name') }}"
                       required
                       autofocus
                       autocomplete="name">
            </div>

            @error('name')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="email">
                {{ __('ui.auth.email_label') }}
                <span class="ldr-label__required" aria-hidden="true">*</span>
            </label>

            <div class="ldr-input-group">
                <x-icon name="mail" :size="18" />
                <input id="email"
                       class="ldr-input @error('email') ldr-input--error @enderror"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="{{ __('ui.auth.email_placeholder') }}"
                       required
                       autocomplete="username">
            </div>

            @error('email')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="password">
                {{ __('ui.auth.password') }}
                <span class="ldr-label__required" aria-hidden="true">*</span>
            </label>

            <div class="ldr-input-group ldr-input-group--action">
                <x-icon name="lock" :size="18" />
                <input id="password"
                       class="ldr-input @error('password') ldr-input--error @enderror"
                       type="password"
                       name="password"
                       placeholder="{{ __('ui.auth.password_placeholder') }}"
                       required
                       autocomplete="new-password">

                <button type="button"
                        class="ldr-input-action"
                        data-password-toggle
                        aria-controls="password"
                        aria-pressed="false"
                        aria-label="{{ __('ui.auth.show_password') }}"
                        data-label-show="{{ __('ui.auth.show_password') }}"
                        data-label-hide="{{ __('ui.auth.hide_password') }}">
                    <x-icon name="eye" :size="18" data-icon-show />
                    <x-icon name="eye-off" :size="18" data-icon-hide hidden />
                </button>
            </div>

            @error('password')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="password_confirmation">
                {{ __('ui.auth.password_confirmation') }}
                <span class="ldr-label__required" aria-hidden="true">*</span>
            </label>

            <div class="ldr-input-group ldr-input-group--action">
                <x-icon name="lock" :size="18" />
                <input id="password_confirmation"
                       class="ldr-input"
                       type="password"
                       name="password_confirmation"
                       placeholder="{{ __('ui.auth.password_confirmation') }}"
                       required
                       autocomplete="new-password">

                <button type="button"
                        class="ldr-input-action"
                        data-password-toggle
                        aria-controls="password_confirmation"
                        aria-pressed="false"
                        aria-label="{{ __('ui.auth.show_password') }}"
                        data-label-show="{{ __('ui.auth.show_password') }}"
                        data-label-hide="{{ __('ui.auth.hide_password') }}">
                    <x-icon name="eye" :size="18" data-icon-show />
                    <x-icon name="eye-off" :size="18" data-icon-hide hidden />
                </button>
            </div>
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="nim">
                {{ __('ui.auth.nim') }}
                <span class="ldr-label__required" aria-hidden="true">*</span>
            </label>

            <div class="ldr-input-group">
                <x-icon name="id" :size="18" />
                <input id="nim"
                       class="ldr-input @error('nim') ldr-input--error @enderror"
                       type="text"
                       name="nim"
                       value="{{ old('nim') }}"
                       placeholder="{{ __('ui.auth.nim_placeholder') }}"
                       required
                       autocomplete="nim">
            </div>

            @error('nim')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="study_program">
                {{ __('ui.auth.study_program') }}
                <span class="ldr-label__required" aria-hidden="true">*</span>
            </label>

            <div class="ldr-input-group">
                <x-icon name="graduation-cap" :size="18" />
                <input id="study_program"
                       class="ldr-input @error('study_program') ldr-input--error @enderror"
                       type="text"
                       name="study_program"
                       value="{{ old('study_program') }}"
                       placeholder="{{ __('ui.auth.study_program_placeholder') }}"
                       required>
            </div>

            @error('study_program')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="phone">
                {{ __('ui.auth.phone') }}
            </label>

            <div class="ldr-input-group">
                <x-icon name="phone" :size="18" />
                <input id="phone"
                       class="ldr-input @error('phone') ldr-input--error @enderror"
                       type="tel"
                       name="phone"
                       value="{{ old('phone') }}"
                       placeholder="{{ __('ui.auth.phone_placeholder') }}"
                       autocomplete="tel">
            </div>

            @error('phone')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="organization_id">
                {{ __('ui.auth.organization') }}
                <span class="ldr-label__required" aria-hidden="true">*</span>
            </label>

            <div class="ldr-input-group">
                <select id="organization_id"
                        class="ldr-select @error('organization_id') ldr-input--error @enderror"
                        name="organization_id"
                        required>
                    <option value="">{{ __('ui.auth.organization_placeholder') }}</option>
                    @foreach ($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            @error('organization_id')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field ldr-field--checkbox">
            <input id="whatsapp_opt_in"
                   name="whatsapp_opt_in"
                   type="checkbox"
                   value="1"
                   {{ old('whatsapp_opt_in') ? 'checked' : '' }}>
            <label for="whatsapp_opt_in">
                {{ __('ui.auth.whatsapp_opt_in') }}
            </label>
        </div>

        <div class="ldr-field ldr-field--checkbox">
            <input id="terms"
                   name="terms"
                   type="checkbox"
                   value="1"
                   required>
            <label for="terms">
                {{ __('ui.auth.terms') }}
                <a href="{{ route('borrower.help') }}">{{ __('ui.auth.terms_link') }}</a>
            </label>
        </div>

        <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block">
            <span>{{ __('ui.auth.submit_register') }}</span>
            <x-icon name="arrow-right" :size="18" />
        </button>
    </form>

    <p class="ldr-auth__switch">
        {{ __('ui.auth.have_account') }}
        <a href="{{ route('login') }}">{{ __('ui.auth.login_link') }}</a>
    </p>

    <div class="ldr-auth__callout" role="note">
        <span class="ldr-auth__callout-icon" aria-hidden="true">
            <x-icon name="shield-check" :size="20" />
        </span>
        <div>
            <strong>{{ __('ui.auth.verified_title') }}</strong>
            <p>{{ __('ui.auth.verified_body') }}</p>
        </div>
    </div>
</x-guest-layout>
