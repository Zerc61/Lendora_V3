@extends('layouts.admin')

@section('title', __('ui.nav.handovers'))

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.handovers.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.handover.back_to_list') }}
    </a>

    <div class="ldr-page-head">
        <div>
            <h1>{{ $type->label() }} · <span class="ldr-mono">{{ $booking->code }}</span></h1>
            <p class="ldr-muted">
                {{ $booking->borrower?->name }} ·
                {{ $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M Y') }}
                –
                {{ $booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y') }}
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.bookings.handover.store', $booking) }}" class="ldr-form-page">
        @csrf
        <input type="hidden" name="type" value="{{ $type->value }}">

        <section class="ldr-card">
            <h2>{{ __('ui.booking.assets') }}</h2>

            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.nav.assets') }}</th>
                            <th>{{ __('ui.handover.condition') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>
                                    <span class="ldr-table__name">{{ $item->asset?->name }}</span>
                                    <span class="ldr-table__sub ldr-table__code">{{ $item->asset?->code }}</span>
                                </td>
                                <td style="min-width: 200px;">
                                    <label class="ldr-sr-only" for="condition-{{ $item->id }}">{{ __('ui.handover.condition') }} {{ $item->asset?->code }}</label>
                                    <select id="condition-{{ $item->id }}"
                                            class="ldr-input"
                                            name="conditions[{{ $item->id }}]"
                                            required>
                                        @foreach (\App\Enums\AssetCondition::cases() as $condition)
                                            <option value="{{ $condition->value }}" @selected(old('conditions.'.$item->id) === $condition->value)>
                                                {{ __('ui.condition.'.$condition->value) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('conditions.'.$item->id) <p class="ldr-field__error">{{ $message }}</p> @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ldr-card">
            <div class="ldr-field">
                <label class="ldr-label" for="notes">{{ __('ui.booking.notes') }}</label>
                <textarea id="notes" class="ldr-input" name="notes" maxlength="255">{{ old('notes') }}</textarea>
                @error('notes') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-form-actions">
                <button type="submit" class="ldr-btn ldr-btn--primary">
                    {{ $type === App\Enums\HandoverType::Pickup ? __('ui.handover.submit_pickup') : __('ui.handover.submit_return') }}
                </button>
                <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.handovers.index') }}">{{ __('ui.common.cancel') }}</a>
            </div>
        </section>
    </form>
@endsection
