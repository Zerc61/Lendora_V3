@props(['icon' => 'package', 'title', 'description' => null])

<div class="ldr-empty-state">
    <span class="ldr-empty-state__icon">
        <x-icon :name="$icon" :size="24" />
    </span>

    <h2>{{ $title }}</h2>

    @if ($description)
        <p>{{ $description }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="ldr-empty-state__action">
            {{ $slot }}
        </div>
    @endif
</div>
