@props(['notification', 'category', 'actionUrl', 'actionLabel'])

<article class="ldr-notification-item {{ $notification->read_at === null ? 'ldr-notification-item--unread' : '' }}">
    <div class="ldr-notification-item__icon">
        @switch($category)
            @case('booking_received')
                <x-icon name="clipboard-list" :size="20" />
                @break
            @case('booking_decision')
                <x-icon name="check-circle" :size="20" />
                @break
            @case('loan_due_reminder')
                <x-icon name="clock" :size="20" />
                @break
            @case('asset_damage')
                <x-icon name="alert-circle" :size="20" />
                @break
            @default
                <x-icon name="bell" :size="20" />
        @endswitch
    </div>

    <div class="ldr-notification-item__content">
        <div class="ldr-notification-item__header">
            <span class="ldr-notification-item__title">{{ $notification->data['title'] ?? __('ui.notification.fallback') }}</span>
            @if ($notification->read_at === null)
                <span class="ldr-badge ldr-badge--primary ldr-badge--small">{{ __('ui.notification.unread_badge') }}</span>
            @endif
        </div>

        <p class="ldr-notification-item__body">{{ $notification->data['message'] ?? __('ui.notification.fallback') }}</p>

        <div class="ldr-notification-item__meta">
            <span>{{ $notification->created_at->copy()->locale('id')->translatedFormat('d M Y H:i') }}</span>
        </div>
    </div>

    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="ldr-notification-item__action">
            {{ $actionLabel }}
            <x-icon name="arrow-right" :size="14" />
        </a>
    @endif
</article>