<x-guest-layout :title="__('ui.auth.login_title')">
    <header class="ldr-auth__intro">
        <h1 class="ldr-auth__title">{{ __('ui.auth.login_heading') }}</h1>
        <p class="ldr-auth__subtitle">{{ __('ui.auth.login_subtitle') }}</p>
    </header>

    @if (session('status'))
        <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="ldr-auth__form">
        @csrf

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
                       autofocus
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
                       autocomplete="current-password">

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

        <div class="ldr-auth__meta">
            <label class="ldr-checkbox" for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                <span>{{ __('ui.auth.remember') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">{{ __('ui.auth.forgot_link') }}</a>
            @endif
        </div>

        <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block">
            <span>{{ __('ui.auth.submit_login') }}</span>
            <x-icon name="arrow-right" :size="18" />
        </button>
    </form>

    <p class="ldr-auth__switch">
        {{ __('ui.auth.no_account') }}
        <a href="{{ route('register') }}">{{ __('ui.auth.register_link') }}</a>
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
