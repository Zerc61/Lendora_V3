<x-guest-layout :title="__('ui.auth.login_title')" :split="true">
    <div class="ldr-login">

        <div class="ldr-login__card">

            {{-- ===== PANEL VISUAL (foto kamera + brand) ===== --}}
            <aside class="ldr-login__visual">
                <img class="ldr-login__photo"
                     src="{{ asset('images/onboarding-hero.jpg') }}"
                     alt="{{ __('ui.auth.visual_alt') }}"
                     loading="eager">

                <div class="ldr-login__brand">
                    <div>
                        <img class="ldr-login__logo"
                             src="{{ asset('images/lendora-logo-gelap.svg') }}"
                             alt="{{ config('app.name') }}"
                             width="180"
                             height="51">
                        <p class="ldr-login__tagline">{{ __('ui.auth.brand_tagline') }}</p>
                    </div>
                </div>
            </aside>

            {{-- ===== PANEL FORM ===== --}}
            <section class="ldr-login__panel">

                <header class="ldr-login__intro">
                    <h1 class="ldr-login__heading">{{ __('ui.auth.login_heading') }}</h1>
                    <p class="ldr-login__lead">{{ __('ui.auth.login_subtitle') }}</p>
                </header>

                @if (session('status'))
                    <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="ldr-login__form">
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

                    <div class="ldr-login__meta">
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

                <p class="ldr-login__register">
                    {{ __('ui.auth.no_account') }}
                    <a href="{{ route('register') }}">{{ __('ui.auth.register_link') }}</a>
                </p>

                <div class="ldr-login__callout" role="note">
                    <span class="ldr-login__callout-icon" aria-hidden="true">
                        <x-icon name="shield-check" :size="20" />
                    </span>
                    <div>
                        <strong>{{ __('ui.auth.verified_title') }}</strong>
                        <p>{{ __('ui.auth.verified_body') }}</p>
                    </div>
                </div>

            </section>
        </div>

        {{-- ===== BARIS DI BAWAH KARTU (di atas gradient) ===== --}}
        <div class="ldr-login__below">
            <div class="ldr-login__links">
                <span>
                    <x-icon name="book-open" :size="16" />
                    {{ __('ui.auth.rules_link') }}
                </span>
                <span aria-hidden="true">·</span>
                <span>
                    <x-icon name="help-circle" :size="16" />
                    {{ __('ui.auth.help_link') }}
                </span>
            </div>

            <p class="ldr-login__version">{{ __('ui.auth.version') }}</p>
        </div>

    </div>
</x-guest-layout>