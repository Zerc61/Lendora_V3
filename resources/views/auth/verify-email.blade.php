<x-guest-layout :title="__('ui.auth.verify_title')">
    <h1 class="ldr-auth__title">{{ __('ui.auth.verify_title') }}</h1>
    <p class="ldr-auth__subtitle">{{ __('ui.auth.verify_description') }}</p>

    @if (session('status') === 'verification-link-sent')
        <div class="ldr-alert ldr-alert--success" role="status">{{ __('ui.auth.verify_sent') }}</div>
    @endif

    <div class="ldr-auth__meta">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="ldr-btn ldr-btn--primary">
                {{ __('ui.auth.verify_resend') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="ldr-btn ldr-btn--ghost">
                {{ __('ui.auth.logout') }}
            </button>
        </form>
    </div>
</x-guest-layout>
