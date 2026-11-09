@props(['message' => null, 'title' => null])

@if ($message)
    <div class="ldr-conflict-banner" role="alert">
        <span class="ldr-conflict-banner__icon" aria-hidden="true">
            <x-icon name="alert-circle" :size="20" />
        </span>

        <div>
            <p class="ldr-conflict-banner__title">{{ $title ?? __('ui.booking.conflict_title') }}</p>
            <p class="ldr-conflict-banner__message">{{ $message }}</p>
        </div>
    </div>
@endif
