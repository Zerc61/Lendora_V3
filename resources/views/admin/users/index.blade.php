@extends('layouts.admin')

@section('title', __('ui.nav.users'))

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.users') }}</h1>
            <p class="ldr-muted">{{ __('ui.user.index_description') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--primary" href="{{ route('admin.users.create') }}">
                <x-icon name="users" :size="18" />
                {{ __('ui.user.add') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="ldr-filter-bar" role="search">
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="user-search">{{ __('ui.user.filter_search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="user-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.user.filter_search') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="user-role">{{ __('ui.user.role') }}</label>
            <select id="user-role" class="ldr-input" name="role_id">
                <option value="">{{ __('ui.user.all_roles') }}</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected(($filters['role_id'] ?? null) === $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.users.index') }}">
                {{ __('ui.common.reset') }}
            </a>
        </div>
    </form>

    @if ($users->isEmpty())
        <x-empty-state
            icon="users"
            :title="__('ui.user.empty_title')"
            :description="__('ui.user.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.user.name') }}</th>
                            <th>{{ __('ui.auth.email') }}</th>
                            <th>{{ __('ui.user.role') }}</th>
                            <th>{{ __('ui.user.phone') }}</th>
                            <th>{{ __('ui.booking.filter_status') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <span class="ldr-table__name">{{ $user->name }}</span>
                                    @if ($user->id === auth()->id())
                                        <span class="ldr-table__sub">{{ __('ui.user.you') }}</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td><span class="ldr-badge ldr-badge--primary">{{ $user->role?->name }}</span></td>
                                <td>{{ $user->phone ?: '—' }}</td>
                                <td>
                                    @if ($user->status === 'suspended')
                                        <span class="ldr-badge ldr-badge--danger">{{ __('ui.user.suspended') }}</span>
                                    @else
                                        <span class="ldr-badge ldr-badge--success">{{ __('ui.user.active') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ldr-table__actions ldr-table__actions--text">
                                        <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                           href="{{ route('admin.users.edit', $user) }}">
                                            {{ __('ui.common.edit') }}
                                        </a>

                                        @if ($user->id !== auth()->id())
                                            @if ($user->status === 'suspended')
                                                <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">
                                                        {{ __('ui.user.activate') }}
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="ldr-btn ldr-btn--danger ldr-btn--sm">
                                                        {{ __('ui.user.suspend') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$users" />
        </div>
    @endif
@endsection
