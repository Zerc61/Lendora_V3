@props(['name', 'label', 'value' => '', 'rows' => 4, 'placeholder' => '', 'required' => false])

<div class="ldr-field">
    @if ($label)
        <label class="ldr-label" for="{{ $name }}">{{ $label }}
            @if ($required)
                <span class="ldr-label__required" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <textarea id="{{ $name }}"
        name="{{ $name }}"
        class="ldr-textarea @error($name) ldr-input--error @enderror"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif>{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="ldr-field__error">{{ $message }}</p>
    @enderror
</div>