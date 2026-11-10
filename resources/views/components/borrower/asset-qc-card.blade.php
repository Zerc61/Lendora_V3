@props(['checks'])

@if ($checks->isEmpty())
    @return
@endif

<div class="ldr-qc-list">
    @foreach ($checks as $check)
        <article class="ldr-qc-list__item">
            <div class="ldr-qc-list__head">
                <span class="ldr-qc-list__date">{{ $check->checked_at->format('d M Y') }}</span>
                <x-status-badge :status="strtolower($check->result)" class="ldr-qc-list__badge" type="inspection" />
            </div>
            @if ($check->technician)
                <p class="ldr-qc-list__tech">{{ $check->technician->name }}</p>
            @endif
            @if ($check->notes)
                <p class="ldr-qc-list__notes">{{ $check->notes }}</p>
            @endif
        </article>
    @endforeach
</div>