@extends('layouts.borrower')

@section('title', __('ui.nav.notifications'))
@section('nav', 'notifications')

@section('content')
    <div class="ldr-notifications-page">
        <div class="ldr-notifications-main">
            <div class="ldr-page-head">
                <div>
                    <h1>{{ __('ui.nav.notifications') }}</h1>
                    <p class="ldr-muted">{{ __('ui.notification.unread', ['count' => $unreadCount]) }}</p>
                </div>

                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('borrower.notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="ldr-btn ldr-btn--secondary">
                            <x-icon name="check-circle" :size="18" />
                            {{ __('ui.notification.mark_all') }}
                        </button>
                    </form>
                @endif
            </div>

            @if ($categories->isNotEmpty())
                <nav class="ldr-notification-categories" aria-label="{{ __('ui.nav.notifications') }}">
                    <a href="{{ route('borrower.notifications.index') }}" class="ldr-chip {{ $category === null ? 'is-active' : '' }}">
                        {{ __('ui.common.all') }}
                    </a>
                    @foreach ($categories as $value => $label)
                        <a href="{{ route('borrower.notifications.index', ['category' => $value]) }}" class="ldr-chip {{ $category === $value ? 'is-active' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            @endif

            <div class="ldr-notification-list">
                @if (collect($groups)->every(fn ($items) => $items->isEmpty()))
                    <x-empty-state
                        icon="bell"
                        :title="__('ui.notification.empty_title')"
                        :description="__('ui.notification.empty_description')"
                    />
                @else
                    @foreach ($groups as $groupLabel => $items)
                        @continue($items->isEmpty())

                        <h2 class="ldr-notification-group">{{ __('ui.notification.' . $groupLabel) }}</h2>

                        @foreach ($items as $notification)
                            <x-borrower.notification-item
                                :notification="$notification"
                                :category="$notification->data['category'] ?? null"
                                :action-url="$notification->data['action_url'] ?? null"
                                :action-label="$notification->data['action_label'] ?? null"
                            />
                        @endforeach
                    @endforeach
                @endif
            </div>
        </div>

        <aside class="ldr-notifications-sidebar" aria-label="{{ __('ui.notification.sidebar_summary') }}">
            <section class="ldr-aside-card">
                <h2 class="ldr-aside-card__title">{{ __('ui.notification.sidebar_summary') }}</h2>

                <dl class="ldr-aside-stats">
                    <div class="ldr-aside-stat">
                        <dt>{{ __('ui.borrower.active_loan') }}</dt>
                        <dd>{{ $activeLoans }}</dd>
                    </div>
                    <div class="ldr-aside-stat">
                        <dt>{{ __('ui.profile.compliance_score') }}</dt>
                        <dd>
                            {{ $compliance['score'] }}
                            <span class="ldr-badge ldr-badge--success">{{ $compliance['tier'] }}</span>
                        </dd>
                    </div>
                </dl>

                <p class="ldr-aside-card__note">
                    <x-icon name="shield-check" :size="15" />
                    {{ __('ui.notification.account_good') }}
                </p>
            </section>

            <section class="ldr-aside-card">
                <h2 class="ldr-aside-card__title">{{ __('ui.notification.sidebar_documents') }}</h2>

                <ul class="ldr-aside-links">
                    <li>
                        <a href="{{ route('borrower.help') }}">
                            <x-icon name="book-open" :size="16" />
                            <span>{{ __('ui.notification.sop_link') }}</span>
                            <x-icon name="chevron-right" :size="16" />
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('borrower.penalties.index') }}">
                            <x-icon name="clipboard-list" :size="16" />
                            <span>{{ __('ui.notification.penalty_link') }}</span>
                            <x-icon name="chevron-right" :size="16" />
                        </a>
                    </li>
                </ul>
            </section>

            <section class="ldr-aside-card">
                <h2 class="ldr-aside-card__title">{{ __('ui.notification.sidebar_contact') }}</h2>

                <ul class="ldr-aside-links">
                    <li>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $operatorPhone) }}">
                            <x-icon name="phone" :size="16" />
                            <span>{{ $operatorPhone }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $operatorWhatsApp) }}" target="_blank" rel="noopener">
                            <x-icon name="message-circle" :size="16" />
                            <span>{{ $operatorWhatsApp }}</span>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>
    </div>
@endsection
