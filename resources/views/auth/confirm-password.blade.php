<x-guest-layout :title="__('ui.auth.confirm_title')">
    <h1 class="ldr-auth__title">{{ __('ui.auth.confirm_title') }}</h1>
    <p class="ldr-auth__subtitle">{{ __('ui.auth.confirm_description') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="ldr-auth__form">
        @csrf

        <div class="ldr-field">
            <label class="ldr-label" for="password">{{ __('ui.auth.password') }}</label>
            <input id="password"
                   class="ldr-input @error('password') ldr-input--error @enderror"
                   type="password"
                   name="password"
                   required
                   autofocus
                   autocomplete="current-password">
            @error('password')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block">
            {{ __('ui.auth.submit_confirm') }}
        </button>
    </form>
</x-guest-layout>
