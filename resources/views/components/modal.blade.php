@props(['id', 'title', 'describedBy' => null])

<div {{ $attributes->merge(['id' => $id, 'class' => 'ldr-modal', 'data-modal' => true]) }} hidden>
    <div class="ldr-modal__backdrop" data-modal-close></div>

    <div class="ldr-modal__panel"
         role="dialog"
         aria-modal="true"
         aria-labelledby="{{ $id }}-title"
         @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
        <div class="ldr-modal__header">
            <h2 class="ldr-modal__title" id="{{ $id }}-title">{{ $title }}</h2>

            <button type="button" class="ldr-modal__close" data-modal-close aria-label="{{ __('ui.common.close') }}">
                <x-icon name="x" :size="18" />
            </button>
        </div>

        <div class="ldr-modal__body">
            {{ $slot }}
        </div>
    </div>
</div>
