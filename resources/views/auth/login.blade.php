<x-guest-layout :title="__('ui.auth.login_title')">
    <h1 class="ldr-auth__title">{{ __('ui.auth.login_title') }}</h1>
    <p class="ldr-auth__subtitle">{{ __('ui.auth.login_subtitle') }}</p>

    @if (session('status'))
        <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="ldr-field">
            <label class="ldr-label" for="email">{{ __('ui.auth.email') }}</label>
            <input id="email"
                   class="ldr-input @error('email') ldr-input--error @enderror"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username">
            @error('email')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="password">{{ __('ui.auth.password') }}</label>
            <input id="password"
                   class="ldr-input @error('password') ldr-input--error @enderror"
                   type="password"
                   name="password"
                   required
                   autocomplete="current-password">
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
            {{ __('ui.auth.submit_login') }}
        </button>
    </form>

    <p class="ldr-auth__footer">
        {{ __('ui.auth.no_account') }}
        <a href="{{ route('register') }}">{{ __('ui.auth.register_link') }}</a>
    </p>
</x-guest-layout>
