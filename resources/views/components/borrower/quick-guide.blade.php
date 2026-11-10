@props([])

<section class="ldr-quick-guide" aria-labelledby="guide-title">
    <h2 id="guide-title">{{ __('ui.borrower.quick_guide_title') }}</h2>
    <p class="ldr-quick-guide__subtitle">{{ __('ui.borrower.quick_guide_subtitle') }}</p>

    <ol class="ldr-quick-guide__steps">
        <li class="ldr-quick-guide__step">
            <span class="ldr-quick-guide__number">1</span>
            <div class="ldr-quick-guide__content">
                <h3>{{ __('ui.borrower.guide_step_1_title') }}</h3>
                <p>{{ __('ui.borrower.guide_step_1_desc') }}</p>
            </div>
        </li>

        <li class="ldr-quick-guide__step">
            <span class="ldr-quick-guide__number">2</span>
            <div class="ldr-quick-guide__content">
                <h3>{{ __('ui.borrower.guide_step_2_title') }}</h3>
                <p>{{ __('ui.borrower.guide_step_2_desc') }}</p>
            </div>
        </li>

        <li class="ldr-quick-guide__step">
            <span class="ldr-quick-guide__number">3</span>
            <div class="ldr-quick-guide__content">
                <h3>{{ __('ui.borrower.guide_step_3_title') }}</h3>
                <p>{{ __('ui.borrower.guide_step_3_desc') }}</p>
            </div>
        </li>

        <li class="ldr-quick-guide__step">
            <span class="ldr-quick-guide__number">4</span>
            <div class="ldr-quick-guide__content">
                <h3>{{ __('ui.borrower.guide_step_4_title') }}</h3>
                <p>{{ __('ui.borrower.guide_step_4_desc') }}</p>
            </div>
        </li>
    </ol>

    <a href="{{ route('borrower.help') }}" class="ldr-quick-guide__link">
        <x-icon name="help-circle" :size="16" />
        {{ __('ui.borrower.guide_more') }}
    </a>
</section>