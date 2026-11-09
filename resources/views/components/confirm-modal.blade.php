@props([
    'id',
    'title',
    'message',
    'action',
    'method' => 'POST',
    'confirmLabel' => null,
    'variant' => 'danger',
])

<x-modal :id="$id" :title="$title" :described-by="$id.'-message'" data-confirm-modal>
    <p class="ldr-modal__message" id="{{ $id }}-message">{{ $message }}</p>

    <form method="POST" action="{{ $action }}" class="ldr-modal__actions">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif

        <button type="button" class="ldr-btn ldr-btn--secondary" data-confirm-close>
            {{ __('ui.common.cancel') }}
        </button>
        <button type="submit" class="ldr-btn ldr-btn--{{ $variant }}">
            {{ $confirmLabel ?? __('ui.common.delete') }}
        </button>
    </form>
</x-modal>
