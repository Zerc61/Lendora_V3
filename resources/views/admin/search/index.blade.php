@extends('layouts.admin')

@section('title', __('ui.search.title'))

@php
    $meta = [
        'assets' => ['label' => __('ui.nav.assets'), 'icon' => 'package'],
        'bookings' => ['label' => __('ui.nav.booking_queue'), 'icon' => 'clipboard-list'],
        'users' => ['label' => __('ui.nav.users'), 'icon' => 'users'],
        'categories' => ['label' => __('ui.nav.categories'), 'icon' => 'layers'],
    ];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.search.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.search.description') }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.search') }}" class="ldr-filter-bar" role="search">
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="global-search">{{ __('ui.search.placeholder') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="global-search"
                   class="ldr-input"
                   type="search"
                   name="q"
                   value="{{ $term }}"
                   minlength="2"
                   placeholder="{{ __('ui.search.placeholder') }}"
                   autofocus>
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">
                {{ __('ui.common.search') }}
            </button>
        </div>
    </form>

    @if (! $searched)
        <x-empty-state
            icon="search"
            :title="__('ui.search.start_title')"
            :description="__('ui.search.start_description')"
        />
    @elseif ($results->flatten()->isEmpty())
        <x-empty-state
            icon="search"
            :title="__('ui.search.empty_title', ['term' => $term])"
            :description="__('ui.search.empty_description')"
        />
    @else
        @foreach ($results as $group => $items)
            @continue($items->isEmpty())

            <h2 class="ldr-section-title">{{ $meta[$group]['label'] }}</h2>

            <div class="ldr-table-card">
                <div class="ldr-table-wrap">
                    <table class="ldr-table">
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    @switch($group)
                                        @case('assets')
                                            <td>
                                                <span class="ldr-table__name">{{ $item->name }}</span>
                                                <span class="ldr-table__sub ldr-table__code">{{ $item->code }}</span>
                                            </td>
                                            <td><x-status-badge :status="$item->status->value" /></td>
                                            <td class="ldr-table__actions">
                                                <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                                   href="{{ route('admin.assets.edit', $item) }}">
                                                    {{ __('ui.common.edit') }}
                                                </a>
                                            </td>
                                            @break

                                        @case('bookings')
                                            <td>
                                                <span class="ldr-table__name ldr-table__code">{{ $item->code }}</span>
                                                <span class="ldr-table__sub">{{ $item->borrower?->name }}</span>
                                            </td>
                                            <td><x-status-badge type="booking" :status="$item->status->value" /></td>
                                            <td class="ldr-table__actions">
                                                <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                                   href="{{ route('admin.bookings.show', $item) }}">
                                                    {{ __('ui.booking.detail_action') }}
                                                </a>
                                            </td>
                                            @break

                                        @case('users')
                                            <td>
                                                <span class="ldr-table__name">{{ $item->name }}</span>
                                                <span class="ldr-table__sub">{{ $item->email }}</span>
                                            </td>
                                            <td><span class="ldr-badge ldr-badge--primary">{{ $item->role?->name }}</span></td>
                                            <td class="ldr-table__actions">
                                                <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                                   href="{{ route('admin.users.edit', $item) }}">
                                                    {{ __('ui.common.edit') }}
                                                </a>
                                            </td>
                                            @break

                                        @case('categories')
                                            <td>
                                                <span class="ldr-table__name">{{ $item->name }}</span>
                                            </td>
                                            <td class="ldr-table__actions">
                                                <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                                   href="{{ route('admin.categories.edit', $item) }}">
                                                    {{ __('ui.common.edit') }}
                                                </a>
                                            </td>
                                            @break
                                    @endswitch
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
@endsection
