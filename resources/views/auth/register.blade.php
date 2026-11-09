<x-guest-layout :title="__('ui.auth.register_title')">
    <h1 class="ldr-auth__title">{{ __('ui.auth.register_title') }}</h1>
    <p class="ldr-auth__subtitle">{{ __('ui.auth.register_subtitle') }}</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="ldr-field">
            <label class="ldr-label" for="name">{{ __('ui.auth.name') }}</label>
            <input id="name"
                   class="ldr-input @error('name') ldr-input--error @enderror"
                   type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   autofocus
                   autocomplete="name">
            @error('name')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="email">{{ __('ui.auth.email') }}</label>
            <input id="email"
                   class="ldr-input @error('email') ldr-input--error @enderror"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
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
                   autocomplete="new-password">
            @error('password')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="password_confirmation">{{ __('ui.auth.password_confirmation') }}</label>
            <input id="password_confirmation"
                   class="ldr-input"
                   type="password"
                   name="password_confirmation"
                   required
                   autocomplete="new-password">
        </div>

        <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block">
            {{ __('ui.auth.submit_register') }}
        </button>
    </form>

    <p class="ldr-auth__footer">
        {{ __('ui.auth.have_account') }}
        <a href="{{ route('login') }}">{{ __('ui.auth.login_link') }}</a>
    </p>
</x-guest-layout>
