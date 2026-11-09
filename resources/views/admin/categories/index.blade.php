@extends('layouts.admin')

@section('title', __('ui.nav.categories'))

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.categories') }}</h1>
            <p class="ldr-muted">{{ __('ui.category.assets_count', ['count' => $categories->sum('assets_count')]) }}</p>
        </div>

        <a class="ldr-btn ldr-btn--primary" href="{{ route('admin.categories.create') }}">
            <x-icon name="layers" :size="18" />
            {{ __('ui.category.add') }}
        </a>
    </div>

    @if ($categories->isEmpty())
        <x-empty-state
            icon="layers"
            :title="__('ui.category.empty_title')"
            :description="__('ui.category.empty_description')"
        >
            <a class="ldr-btn ldr-btn--primary" href="{{ route('admin.categories.create') }}">
                {{ __('ui.category.add') }}
            </a>
        </x-empty-state>
    @else
        <div class="ldr-card ldr-tree-card">
            <ul class="ldr-tree">
                @foreach ($categories as $category)
                    <li class="ldr-tree__item">
                        <div class="ldr-tree__row">
                            @if ($category->children->isNotEmpty())
                                <button type="button"
                                        class="ldr-tree__toggle"
                                        data-tree-toggle
                                        aria-expanded="true"
                                        aria-label="{{ $category->name }}">
                                    <x-icon name="chevron-down" :size="16" />
                                </button>
                            @else
                                <span class="ldr-tree__toggle-spacer" aria-hidden="true"></span>
                            @endif

                            <span class="ldr-tree__name">{{ $category->name }}</span>
                            <span class="ldr-tree__slug">{{ $category->slug }}</span>
                            <span class="ldr-badge ldr-badge--primary">
                                {{ __('ui.category.assets_count', ['count' => $category->assets_count]) }}
                            </span>

                            <div class="ldr-tree__actions">
                                <a class="ldr-icon-btn"
                                   href="{{ route('admin.categories.edit', $category) }}"
                                   title="{{ __('ui.common.edit') }}"
                                   aria-label="{{ __('ui.common.edit') }} {{ $category->name }}">
                                    <x-icon name="clipboard-list" :size="18" />
                                </a>

                                @if ($canDelete[$category->id] ?? false)
                                    <button type="button"
                                            class="ldr-icon-btn ldr-icon-btn--danger"
                                            data-confirm-open="delete-category-{{ $category->id }}"
                                            title="{{ __('ui.common.delete') }}"
                                            aria-label="{{ __('ui.common.delete') }} {{ $category->name }}">
                                        <x-icon name="x" :size="18" />
                                    </button>
                                @else
                                    <button type="button"
                                            class="ldr-icon-btn"
                                            disabled
                                            title="{{ __('messages.category.delete_blocked') }}">
                                        <x-icon name="x" :size="18" />
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if ($category->children->isNotEmpty())
                            <ul class="ldr-tree__children">
                                @foreach ($category->children as $child)
                                    <li class="ldr-tree__item">
                                        <div class="ldr-tree__row">
                                            <span class="ldr-tree__toggle-spacer" aria-hidden="true"></span>
                                            <span class="ldr-tree__name">{{ $child->name }}</span>
                                            <span class="ldr-tree__slug">{{ $child->slug }}</span>
                                            <span class="ldr-badge ldr-badge--primary">
                                                {{ __('ui.category.assets_count', ['count' => $child->assets_count]) }}
                                            </span>

                                            <div class="ldr-tree__actions">
                                                <a class="ldr-icon-btn"
                                                   href="{{ route('admin.categories.edit', $child) }}"
                                                   title="{{ __('ui.common.edit') }}"
                                                   aria-label="{{ __('ui.common.edit') }} {{ $child->name }}">
                                                    <x-icon name="clipboard-list" :size="18" />
                                                </a>

                                                @if ($canDelete[$child->id] ?? false)
                                                    <button type="button"
                                                            class="ldr-icon-btn ldr-icon-btn--danger"
                                                            data-confirm-open="delete-category-{{ $child->id }}"
                                                            title="{{ __('ui.common.delete') }}"
                                                            aria-label="{{ __('ui.common.delete') }} {{ $child->name }}">
                                                        <x-icon name="x" :size="18" />
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="ldr-icon-btn"
                                                            disabled
                                                            title="{{ __('messages.category.delete_blocked') }}">
                                                        <x-icon name="x" :size="18" />
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        @foreach ($categories as $category)
            @if ($canDelete[$category->id] ?? false)
                <x-confirm-modal
                    :id="'delete-category-'.$category->id"
                    :title="__('ui.category.delete_title')"
                    :message="__('ui.category.delete_message', ['name' => $category->name])"
                    :action="route('admin.categories.destroy', $category)"
                    method="DELETE"
                    variant="danger"
                />
            @endif

            @foreach ($category->children as $child)
                @if ($canDelete[$child->id] ?? false)
                    <x-confirm-modal
                        :id="'delete-category-'.$child->id"
                        :title="__('ui.category.delete_title')"
                        :message="__('ui.category.delete_message', ['name' => $child->name])"
                        :action="route('admin.categories.destroy', $child)"
                        method="DELETE"
                        variant="danger"
                    />
                @endif
            @endforeach
        @endforeach
    @endif
@endsection
