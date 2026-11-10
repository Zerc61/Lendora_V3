@props(['faqs'])

<div class="ldr-faq-accordion">
    @foreach ($faqs as $faq)
        <div class="ldr-faq-item">
            <button class="ldr-faq-item__button" aria-expanded="false" aria-controls="faq-{{ $loop->index }}">
                {{ $faq->question }}
                <x-icon name="chevron-down" :size="16" />
            </button>
            <div id="faq-{{ $loop->index }}" class="ldr-faq-item__panel" hidden>
                {{ $faq->answer }}
            </div>
        </div>
    @endforeach
</div>