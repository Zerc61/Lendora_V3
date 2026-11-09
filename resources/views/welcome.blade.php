<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F97316">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('ui.onboarding.title') }} — {{ config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/lendora-icon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
    <link rel="stylesheet" href="{{ asset('css/onboarding.css') }}">
</head>
<body class="ldr-onboarding">

@php($slides = trans('ui.onboarding.slides'))

<main class="ldr-onboarding__shell"
      data-onboarding
      data-login-url="{{ route('login') }}">

    <header class="ldr-onboarding__header">
        <a href="{{ url('/') }}" class="ldr-onboarding__brand" aria-label="{{ config('app.name') }}">
            <img class="ldr-onboarding__logo"
                 src="{{ asset('images/lendora-logo.svg') }}"
                 alt="{{ config('app.name') }}"
                 width="120"
                 height="34">
            <span class="ldr-onboarding__brand-badge">{{ __('ui.onboarding.brand_badge') }}</span>
        </a>

        <a href="{{ route('login') }}" class="ldr-onboarding__skip">{{ __('ui.onboarding.skip') }}</a>
    </header>

    <div class="ldr-onboarding__body">

        <section class="ldr-onboarding__visual" aria-label="{{ __('ui.onboarding.title') }}">
            <div class="ldr-onboarding__slides">
                @foreach ($slides as $slide)
                    <article class="ldr-onboarding__slide ldr-onboarding__slide--{{ $loop->iteration }}"
                             data-slide="{{ $loop->index }}"
                             @if (! $loop->first) hidden @endif>
                        <div class="ldr-onboarding__hero-frame">
                            <img class="ldr-onboarding__photo ldr-onboarding__photo--{{ $loop->iteration }}"
                                 src="{{ asset('images/onboarding-hero.jpg') }}"
                                 alt="{{ __('ui.onboarding.hero_alt') }}"
                                 loading="{{ $loop->first ? 'eager' : 'lazy' }}">

                            <div class="ldr-onboarding__badges">
                                <span class="ldr-onboarding__pill">
                                    <x-icon :name="$slide['badge_primary_icon']" :size="14" />
                                    {{ $slide['badge_primary'] }}
                                </span>
                                <span class="ldr-onboarding__pill ldr-onboarding__pill--secondary">
                                    <x-icon :name="$slide['badge_secondary_icon']" :size="14" />
                                    {{ $slide['badge_secondary'] }}
                                </span>
                            </div>

                            <div class="ldr-onboarding__hero-foot">
                                <span class="ldr-onboarding__safe">
                                    <span class="ldr-onboarding__dot-live" aria-hidden="true"></span>
                                    {{ $slide['pill'] }}
                                </span>
                                <span class="ldr-onboarding__lab">{{ __('ui.onboarding.lab') }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="ldr-onboarding__dots" aria-label="{{ __('ui.onboarding.title') }}">
                @foreach ($slides as $slide)
                    <button type="button"
                            class="ldr-onboarding__dot @if ($loop->first) is-active @endif"
                            data-go-to="{{ $loop->index }}"
                            aria-label="{{ __('ui.onboarding.step_label', ['current' => $loop->iteration, 'total' => $loop->count]) }}"
                            @if ($loop->first) aria-current="step" @endif></button>
                @endforeach
            </div>
        </section>

        <section class="ldr-onboarding__content">

            <div class="ldr-onboarding__panels">
                @foreach ($slides as $slide)
                    <article class="ldr-onboarding__panel @if ($loop->first) is-active @endif"
                             data-panel="{{ $loop->index }}"
                             @if (! $loop->first) hidden @endif>
                        <h1 class="ldr-onboarding__heading"
                            id="onboarding-heading-{{ $loop->iteration }}"
                            tabindex="-1">
                            {{ $slide['heading'] }}
                        </h1>
                        <p class="ldr-onboarding__lead">{{ $slide['lead'] }}</p>

                        <ul class="ldr-onboarding__features">
                            @foreach ($slide['features'] as $feature)
                                <li @class([
                                    'ldr-onboarding__feature',
                                    'ldr-onboarding__feature--'.$feature['tone'] => $feature['tone'] !== 'default',
                                ])>
                                    <span class="ldr-onboarding__feature-icon" aria-hidden="true">
                                        <x-icon :name="$feature['icon']" :size="20" />
                                    </span>
                                    <span class="ldr-onboarding__feature-text">
                                        <strong>{{ $feature['title'] }}</strong>
                                        <span>{{ $feature['body'] }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>

            <footer class="ldr-onboarding__footer">
                <button type="button"
                        class="ldr-btn ldr-btn--primary ldr-btn--block ldr-onboarding__cta"
                        data-onboarding-next
                        data-label-next="{{ __('ui.onboarding.next') }}"
                        data-label-finish="{{ __('ui.onboarding.finish') }}">
                    <span data-next-label>{{ __('ui.onboarding.next') }}</span>
                    <x-icon name="arrow-right" :size="18" />
                </button>

                <p class="ldr-onboarding__login">
                    {{ __('ui.onboarding.account_question') }}
                    <a href="{{ route('login') }}">{{ __('ui.onboarding.login') }}</a>
                </p>

                <p class="ldr-onboarding__footnote">
                    <x-icon name="graduation-cap" :size="14" />
                    {{ __('ui.onboarding.footnote') }}
                </p>
            </footer>

        </section>

    </div>

</main>

<script src="{{ asset('js/onboarding.js') }}" defer></script>

</body>
</html>
