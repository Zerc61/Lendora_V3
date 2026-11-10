@extends('layouts.admin')

@section('title', __('ui.approval.title'))

@php
    $roleSlug = auth()->user()->role->slug;
    $canRejectRole = in_array($roleSlug, ['operator', 'organization_admin'], true);
    $standby = collect($cards)->flatMap(fn ($card) => $card['alternatives'])->unique('asset_id');

    $formatRange = function ($booking): string {
        return $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M')
            .' – '.$booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y');
    };
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">W6 · {{ __('ui.approval.title') }}</span>
            <h1>{{ __('ui.approval.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.approval.subtitle') }}</p>
        </div>

        @if ($canRejectRole)
            <div class="ldr-page-head__actions">
                <a class="ldr-btn ldr-btn--primary" href="{{ route('admin.bookings.walk-in') }}">
                    <x-icon name="plus" :size="18" />
                    {{ __('ui.approval.walk_in') }}
                </a>
            </div>
        @endif
    </div>

    @if (session('status'))
        <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="ldr-alert ldr-alert--danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.approval.waiting') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="clipboard-list" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['waiting'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.booking_status.pending') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.approval.conflicts') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['conflicts'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.approval.conflict_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.approval.special') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="shield-check" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['special'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.approval.special_badge') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.approval.approval_rate') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="activity" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['approval_rate'] }}%</span>
            <span class="ldr-kpi__hint">{{ __('ui.approval.approval_rate_hint') }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.bookings.approval') }}" class="ldr-filter-bar" role="search">
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="approval-search">{{ __('ui.approval.search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="approval-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.approval.search') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="approval-category">{{ __('ui.asset.fields.category') }}</label>
            <select id="approval-category" class="ldr-input" name="category_id">
                <option value="">{{ __('ui.approval.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) ($filters['category_id'] ?? 0) === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="approval-urgency">{{ __('ui.approval.urgency') }}</label>
            <select id="approval-urgency" class="ldr-input" name="urgency">
                <option value="">{{ __('ui.approval.all_urgency') }}</option>
                <option value="high" @selected(($filters['urgency'] ?? null) === 'high')>{{ __('ui.approval.urgency_high') }}</option>
                <option value="medium" @selected(($filters['urgency'] ?? null) === 'medium')>{{ __('ui.approval.urgency_medium') }}</option>
            </select>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="approval-sort">{{ __('ui.approval.sort_nearest') }}</label>
            <select id="approval-sort" class="ldr-input" name="sort">
                <option value="nearest" @selected(($filters['sort'] ?? 'nearest') === 'nearest')>{{ __('ui.approval.sort_nearest') }}</option>
                <option value="newest" @selected(($filters['sort'] ?? null) === 'newest')>{{ __('ui.approval.sort_newest') }}</option>
            </select>
        </div>

        <div class="ldr-filter-bar__actions">
            <button class="ldr-btn ldr-btn--secondary ldr-btn--sm" type="submit">{{ __('ui.approval.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.bookings.approval') }}">{{ __('ui.common.reset') }}</a>
        </div>
    </form>

    <div class="ldr-approval-grid">
        <div class="ldr-stack">
            @forelse ($cards as $card)
                @php
                    $booking = $card['booking'];
                    $borrower = $booking->borrower;
                @endphp

                <article class="ldr-apv-card ldr-apv-card--{{ $card['urgency'] }} {{ $card['has_conflict'] ? 'is-conflict' : '' }}">
                    <header class="ldr-apv-card__head">
                        <div>
                            <span class="ldr-mono ldr-apv-card__code">{{ $booking->code }}</span>
                            <x-status-badge type="booking" :status="$booking->status->value" />
                            @if ($card['is_special'])
                                <span class="ldr-badge ldr-badge--info">{{ __('ui.approval.special_badge') }}</span>
                            @endif
                        </div>

                        <span class="ldr-apv-card__submitted">
                            <x-icon name="calendar" :size="14" />
                            {{ __('ui.approval.submitted') }} {{ $booking->created_at->copy()->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                        </span>
                    </header>

                    <div class="ldr-apv-card__body">
                        <div class="ldr-apv-person">
                            <span class="ldr-apv-avatar" aria-hidden="true">
                                {{ mb_substr($borrower?->name ?? '?', 0, 2) }}
                            </span>
                            <div>
                                <strong>{{ $borrower?->name ?? '—' }}</strong>
                                <span class="ldr-apv-person__meta">
                                    @if ($borrower?->nim) NIM {{ $borrower->nim }} @endif
                                    @if ($borrower?->study_program) · {{ $borrower->study_program }} @endif
                                    @if ($borrower?->semester) · {{ __('ui.profile.semester') }} {{ $borrower->semester }} @endif
                                </span>
                                <p class="ldr-apv-person__purpose">{{ $booking->purpose ?: __('ui.approval.no_purpose') }}</p>
                            </div>
                        </div>

                        <div class="ldr-apv-period">
                            <span class="ldr-apv-period__label">{{ __('ui.booking.period') }}</span>
                            <strong>{{ $formatRange($booking) }}</strong>
                            <span class="ldr-apv-period__days">{{ __('ui.booking.duration_days', ['count' => $booking->durationInDays()]) }}</span>
                        </div>
                    </div>

                    <div class="ldr-apv-items">
                        <div class="ldr-apv-items__head">
                            <span>{{ __('ui.approval.equipment_list') }}</span>
                            <span class="ldr-muted">{{ __('ui.booking.items_count', ['count' => $booking->items->count()]) }}</span>
                        </div>

                        @foreach ($booking->items as $item)
                            @php
                                $asset = $item->asset;
                                $photo = $asset?->photos->first();
                            @endphp

                            <div class="ldr-apv-item">
                                <span class="ldr-apv-item__media">
                                    @if ($photo)
                                        <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                             alt="{{ $asset->name }}"
                                             loading="lazy"
                                             width="40"
                                             height="40">
                                    @else
                                        <x-icon name="camera" :size="18" />
                                    @endif
                                </span>

                                <span class="ldr-apv-item__body">
                                    <strong>{{ $asset?->name ?? '—' }}</strong>
                                    <span class="ldr-mono">{{ $asset?->code }}</span>
                                </span>

                                <span class="ldr-apv-item__value">
                                    Rp {{ number_format($asset?->purchase_price ?? 0, 0, ',', '.') }}
                                </span>

                                <span class="ldr-badge {{ $asset?->status?->value === 'available' ? 'ldr-badge--success' : 'ldr-badge--warning' }}">
                                    {{ $asset?->status ? __('ui.status.'.$asset->status->value) : '—' }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    @if ($card['conflict'])
                        <div class="ldr-apv-conflict" role="alert">
                            <div>
                                <strong>{{ __('ui.approval.conflict_title') }}</strong>
                                <p>{{ $card['conflict'] }}</p>
                            </div>

                            <form method="POST" action="{{ route('admin.bookings.suggest', $booking) }}">
                                @csrf
                                <button class="ldr-btn ldr-btn--secondary ldr-btn--sm" type="submit">
                                    {{ __('ui.approval.suggest') }}
                                </button>
                            </form>
                        </div>

                        @if ($card['alternatives'] !== [])
                            <div class="ldr-apv-alternatives">
                                <span class="ldr-apv-alternatives__label">{{ __('ui.approval.alternatives') }}</span>

                                @foreach ($card['alternatives'] as $alternative)
                                    <form method="POST" action="{{ route('admin.bookings.reassign', $booking) }}" class="ldr-apv-alternative">
                                        @csrf
                                        <input type="hidden" name="booking_item_id" value="{{ $alternative['booking_item_id'] }}">
                                        <input type="hidden" name="asset_id" value="{{ $alternative['asset_id'] }}">
                                        <span class="ldr-mono">{{ $alternative['code'] }}</span>
                                        <span>{{ $alternative['name'] }}</span>
                                        <button class="ldr-btn ldr-btn--ghost ldr-btn--sm" type="submit">
                                            {{ __('ui.approval.reassign') }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="ldr-apv-ok">
                            <x-icon name="check-circle" :size="16" />
                            {{ __('ui.approval.slot_ok') }}
                        </p>
                    @endif

                    @if ($card['block_reason'])
                        <div class="ldr-apv-block" role="status">
                            <x-icon name="shield-check" :size="16" />
                            <span>{{ $card['block_reason'] }}</span>
                        </div>
                    @endif

                    <footer class="ldr-apv-card__foot">
                        <div class="ldr-apv-total">
                            <span class="ldr-muted">{{ __('ui.approval.total_value') }}</span>
                            <strong>Rp {{ number_format($card['value'], 0, ',', '.') }}</strong>
                            <span class="ldr-apv-total__note">{{ __('ui.approval.limit_note') }}</span>
                        </div>

                        <div class="ldr-apv-actions">
                            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.bookings.show', $booking) }}">
                                {{ __('ui.approval.detail') }}
                            </a>

                            @if ($canRejectRole)
                                <button class="ldr-btn ldr-btn--danger ldr-btn--sm"
                                        type="button"
                                        data-modal-open="reject-approval-{{ $booking->id }}">
                                    <x-icon name="x" :size="16" />
                                    {{ __('ui.approval.reject') }}
                                </button>
                            @endif

                            @if ($card['can_decide'])
                                <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                                    @csrf
                                    <button class="ldr-btn ldr-btn--primary ldr-btn--sm" type="submit">
                                        <x-icon name="check-circle" :size="16" />
                                        {{ __('ui.approval.approve') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </footer>
                </article>
            @empty
                <x-empty-state
                    icon="clipboard-list"
                    :title="__('ui.approval.empty')"
                    :description="__('ui.approval.empty_description')"
                />
            @endforelse
        </div>

        <aside class="ldr-approval-side">
            <section class="ldr-card">
                <h2>{{ __('ui.approval.policy_title') }}</h2>

                <ol class="ldr-policy-list">
                    <li>
                        <strong>{{ __('ui.approval.policy_duration_title') }}</strong>
                        <p>{{ __('ui.approval.policy_duration', ['days' => $policy['max_days']]) }}</p>
                    </li>
                    <li>
                        <strong>{{ __('ui.approval.policy_limit_title') }}</strong>
                        <p>{{ __('ui.approval.policy_limit', ['amount' => number_format($policy['operator_limit'], 0, ',', '.')]) }}</p>
                    </li>
                    <li>
                        <strong>{{ __('ui.approval.policy_conflict_title') }}</strong>
                        <p>{{ __('ui.approval.policy_conflict') }}</p>
                    </li>
                </ol>
            </section>

            <section class="ldr-card">
                <h2>{{ __('ui.approval.standby_title') }}</h2>

                @if ($standby->isEmpty())
                    <p class="ldr-muted">{{ __('ui.approval.standby_empty') }}</p>
                @else
                    <div class="ldr-standby-list">
                        @foreach ($standby as $unit)
                            <div class="ldr-standby">
                                <x-icon name="package" :size="18" />
                                <div>
                                    <strong>{{ $unit['name'] }}</strong>
                                    <span class="ldr-mono">{{ $unit['code'] }}</span>
                                </div>
                                <span class="ldr-badge ldr-badge--success">{{ __('ui.status.available') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>
    </div>

    @if ($canRejectRole)
        @foreach ($cards as $card)
            <x-modal :id="'reject-approval-'.$card['booking']->id" :title="__('ui.booking.reject_title')">
                <p class="ldr-modal__message">
                    {{ $card['booking']->code }} · {{ $card['booking']->borrower?->name }}
                </p>

                <form method="POST" action="{{ route('admin.bookings.reject', $card['booking']) }}" class="ldr-reject-form">
                    @csrf

                    <div class="ldr-field">
                        <label class="ldr-label" for="approval-reason-{{ $card['booking']->id }}">
                            {{ __('ui.booking.reason') }} <span class="ldr-label__required" aria-hidden="true">*</span>
                        </label>
                        <textarea id="approval-reason-{{ $card['booking']->id }}"
                                  class="ldr-input"
                                  name="rejection_reason"
                                  minlength="5"
                                  maxlength="255"
                                  required
                                  placeholder="{{ __('ui.booking.reject_placeholder') }}"></textarea>
                        <p class="ldr-form-hint">{{ __('ui.booking.reject_help') }}</p>
                    </div>

                    <div class="ldr-modal__actions">
                        <button type="button" class="ldr-btn ldr-btn--secondary" data-modal-close>
                            {{ __('ui.common.cancel') }}
                        </button>
                        <button type="submit" class="ldr-btn ldr-btn--danger">
                            {{ __('ui.booking.reject_confirm') }}
                        </button>
                    </div>
                </form>
            </x-modal>
        @endforeach
    @endif
@endsection
