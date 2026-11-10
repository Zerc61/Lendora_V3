@props([])

<nav class="ldr-breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
    <ol class="ldr-breadcrumbs__list">
        {{ $slot }}
    </ol>
</nav>