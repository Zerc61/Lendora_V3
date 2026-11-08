@extends('layouts.admin')

@section('title', __('ui.nav.assets'))

@php
    $canManage = auth()->user()->hasRole('organization_admin');

    $selects = [
        [
            'name' => 'category_id',
            'label' => __('ui.asset.fields.category'),
            'options' => collect($categories)->map(fn (array $category) => [
                'value' => $category['id'],
                'label' => str_repeat('— ', $category['depth']).$category['name'],
            ])->all(),
        ],
        [
            'name' => 'status',
            'label' => __('ui.asset.fields.status'),
            'options' => collect(App\Enums\AssetStatus::cases())->map(fn ($status) => [
                'value' => $status->value,
                'label' => __('ui.status.'.$status->value),
            ])->all(),
        ],
        [
            'name' => 'condition_status',
            'label' => __('ui.asset.fields.condition_status'),
            'options' => collect(App\Enums\AssetCondition::cases())->map(fn ($condition) => [
                'value' => $condition->value,
                'label' => __('ui.condition.'.$condition->value),
            ])->all(),
        ],
    ];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.assets') }}</h1>
            <p class="ldr-muted">{{ $assets->total() }} unit terdaftar</p>
        </div>

        @if ($canManage)
            <a class="ldr-btn ldr-btn--primary" href="{{ route('admin.assets.create') }}">
                <x-icon name="package" :size="18" />
                {{ __('ui.asset.add') }}
            </a>
        @endif
    </div>

    <x-filter-bar
        :action="route('admin.assets.index')"
        :filters="$filters"
        :selects="$selects"
        :search-placeholder="__('ui.asset.search_placeholder')"
    />

    @if ($assets->isEmpty())
        <x-empty-state
            icon="package"
            :title="__('ui.asset.empty_title')"
            :description="__('ui.asset.empty_description')"
        >
            <a class="ldr-btn ldr-btn--secondary" href="{{ route('admin.assets.index') }}">
                {{ __('ui.common.reset') }}
            </a>
        </x-empty-state>
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Kondisi</th>
                            <th>Lokasi</th>
                            @if ($canManage)
                                <th class="ldr-table__actions-head">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            @php
                                $photo = $asset->photos->first();
                            @endphp
                            <tr>
                                <td>
                                    <span class="ldr-thumb">
                                        @if ($photo)
                                            <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                                 alt="{{ $asset->name }}"
                                                 loading="lazy"
                                                 width="48"
                                                 height="48">
                                        @else
                                            <x-icon name="camera" :size="20" />
                                        @endif
                                    </span>
                                </td>
                                <td class="ldr-table__code">{{ $asset->code }}</td>
                                <td>
                                    <span class="ldr-table__name">{{ $asset->name }}</span>
                                    <span class="ldr-table__sub">
                                        {{ collect([$asset->brand, $asset->model])->filter()->join(' · ') ?: '—' }}
                                    </span>
                                </td>
                                <td>{{ $asset->category->name }}</td>
                                <td><x-status-badge :status="$asset->status->value" /></td>
                                <td><x-condition-badge :condition="$asset->condition_status->value" /></td>
                                <td>{{ $asset->location ?: '—' }}</td>

                                @if ($canManage)
                                    <td>
                                        <div class="ldr-table__actions">
                                            <a class="ldr-icon-btn"
                                               href="{{ route('admin.assets.edit', $asset) }}"
                                               title="{{ __('ui.common.edit') }}"
                                               aria-label="{{ __('ui.common.edit') }} {{ $asset->code }}">
                                                <x-icon name="clipboard-list" :size="18" />
                                            </a>

                                            @if ($asset->status !== App\Enums\AssetStatus::Retired)
                                                <button type="button"
                                                        class="ldr-icon-btn ldr-icon-btn--danger"
                                                        data-confirm-open="retire-asset-{{ $asset->id }}"
                                                        title="{{ __('ui.asset.retire') }}"
                                                        aria-label="{{ __('ui.asset.retire') }} {{ $asset->code }}">
                                                    <x-icon name="x" :size="18" />
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$assets" />
        </div>
    @endif

    @if ($canManage)
        @foreach ($assets as $asset)
            @if ($asset->status !== App\Enums\AssetStatus::Retired)
                <x-confirm-modal
                    :id="'retire-asset-'.$asset->id"
                    :title="__('ui.asset.retire_title')"
                    :message="__('ui.asset.retire_message', ['code' => $asset->code])"
                    :action="route('admin.assets.retire', $asset)"
                    :confirm-label="__('ui.asset.retire')"
                    variant="danger"
                />
            @endif
        @endforeach
    @endif
@endsection
