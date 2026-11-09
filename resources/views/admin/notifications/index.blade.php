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
            <div class="ldr-table-card">
                <div class="ldr-table-wrap">
                    <table class="ldr-table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.notification.message') }}</th>
                                <th>{{ __('ui.notification.time') }}</th>
                                <th>{{ __('ui.booking.filter_status') }}</th>
                                <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groups[$key] as $notification)
                                <tr>
                                    <td>{{ $notification->data['message'] ?? __('ui.notification.fallback') }}</td>
                                    <td class="ldr-table__num">
                                        {{ $notification->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}
                                    </td>
                                    <td>
                                        @if ($notification->read_at === null)
                                            <span class="ldr-badge ldr-badge--warning">{{ __('ui.notification.unread_badge') }}</span>
                                        @else
                                            <span class="ldr-badge ldr-badge--neutral">{{ __('ui.notification.read_badge') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($notification->read_at === null)
                                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                                @csrf
                                                <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">
                                                    {{ __('ui.notification.mark_read') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="ldr-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endforeach
@endsection
