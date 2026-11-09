@extends('layouts.admin')

@section('title', __('ui.nav.notifications'))

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.notifications') }}</h1>
            <p class="ldr-muted">
                {{ __('ui.notification.unread', ['count' => $unreadCount]) }}
            </p>
        </div>

        @if ($unreadCount > 0)
            <div class="ldr-page-head__actions">
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="ldr-btn ldr-btn--secondary">
                        <x-icon name="check-circle" :size="18" />
                        {{ __('ui.notification.mark_all') }}
                    </button>
                </form>
            </div>
        @endif
    </div>

    @foreach (['today' => __('ui.notification.today'), 'earlier' => __('ui.notification.earlier')] as $key => $label)
        <h2 class="ldr-section-title">{{ $label }}</h2>

        @if ($groups[$key]->isEmpty())
            <x-empty-state
                icon="bell"
                :title="__('ui.notification.empty_title')"
                :description="__('ui.notification.empty_description')"
            />
        @else
            <div class="ldr-notice-list">
                @foreach ($groups[$key] as $notification)
                    <article class="ldr-notice {{ $notification->read_at === null ? 'ldr-notice--unread' : '' }}">
                        <span class="ldr-notice__icon" aria-hidden="true">
                            <x-icon name="bell" :size="18" />
                        </span>

                        <div class="ldr-notice__body">
                            <p>{{ $notification->data['message'] ?? __('ui.notification.fallback') }}</p>
                            <span class="ldr-notice__time">
                                {{ $notification->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}
                            </span>
                        </div>

                        @if ($notification->read_at === null)
                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="ldr-btn ldr-btn--ghost ldr-btn--sm">
                                    {{ __('ui.notification.mark_read') }}
                                </button>
                            </form>
                        @else
                            <span class="ldr-badge ldr-badge--neutral">{{ __('ui.notification.read_badge') }}</span>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    @endforeach
@endsection
