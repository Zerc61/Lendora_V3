@extends('layouts.admin')

@section('title', __('ui.user.edit'))

@section('content')
    <div class="ldr-form-page ldr-form-narrow">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.user.edit') }} · {{ $user->name }}</h1>
                <p class="ldr-muted">{{ $user->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="ldr-card">
            @csrf
            @method('PUT')

            <div class="ldr-form-section">
                <h2>{{ __('ui.user.identity') }}</h2>

                <div class="ldr-field">
                    <label class="ldr-label" for="name">{{ __('ui.user.name') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                    <input id="name" class="ldr-input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="email">{{ __('ui.auth.email') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                    <input id="email" class="ldr-input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="phone">{{ __('ui.user.phone') }}</label>
                    <input id="phone" class="ldr-input" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                    @error('phone') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="role_id">{{ __('ui.user.role') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                    <select id="role_id" class="ldr-input" name="role_id" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="ldr-form-section">
                <h2>{{ __('ui.user.password_section') }}</h2>
                <p class="ldr-form-hint">{{ __('ui.user.password_hint') }}</p>

                <div class="ldr-field-row">
                    <div class="ldr-field">
                        <label class="ldr-label" for="password">{{ __('ui.user.new_password') }}</label>
                        <input id="password" class="ldr-input" type="password" name="password">
                        @error('password') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="password_confirmation">{{ __('ui.user.password_confirmation') }}</label>
                        <input id="password_confirmation" class="ldr-input" type="password" name="password_confirmation">
                    </div>
                </div>
            </div>

            <div class="ldr-form-actions">
                <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.save') }}</button>
                <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.users.index') }}">{{ __('ui.common.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
