<x-guest-layout :title="__('ui.auth.forgot_title')">
    <h1 class="ldr-auth__title">{{ __('ui.auth.forgot_title') }}</h1>
    <p class="ldr-auth__subtitle">{{ __('ui.auth.forgot_description') }}</p>

    @if (session('status'))
        <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
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

        <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block">
            {{ __('ui.auth.submit_forgot') }}
        </button>
    </form>

    <p class="ldr-auth__footer">
        <a href="{{ route('login') }}">{{ __('ui.auth.login_link') }}</a>
    </p>
</x-guest-layout>
