@props(['types', 'user'])

<section class="ldr-card ldr-form-section">
    <h2>{{ __('ui.notification.preferences_title') }}</h2>
    <p class="ldr-form-hint">{{ __('ui.notification.preferences_hint') }}</p>

    @foreach ($types as $key => $label)
        <input type="hidden" name="notifications[{{ $key }}]" value="0">

        <label class="ldr-checkbox" for="notification-{{ $key }}">
            <input id="notification-{{ $key }}"
                   type="checkbox"
                   name="notifications[{{ $key }}]"
                   value="1"
                   @checked(old('notifications.'.$key, $user->wantsNotification($key)))>
            {{ __($label) }}
        </label>
    @endforeach
</section>
