@props(['announcements'])

@if ($announcements->isNotEmpty())
    <section class="ldr-announcement-list" aria-labelledby="announcement-title">
        <div class="ldr-announcement-list__head">
            <h2 id="announcement-title">{{ __('ui.borrower.announcements') }}</h2>
            <a class="ldr-announcement-list__link" href="{{ route('borrower.help') }}">
                {{ __('ui.common.all') }}
                <x-icon name="chevron-right" :size="16" />
            </a>
        </div>

        <div class="ldr-announcement-list__items">
            @foreach ($announcements as $announcement)
                <article class="ldr-announcement-item">
                    <div class="ldr-announcement-item__icon">
                        @switch($announcement->category)
                            @case('operational')
                                <x-icon name="schedule" :size="20" />
                                @break
                            @case('maintenance')
                                <x-icon name="build" :size="20" />
                                @break
                            @case('policy')
                                <x-icon name="file-text" :size="20" />
                                @break
                            @case('procurement')
                                <x-icon name="shopping-bag" :size="20" />
                                @break
                            @default
                                <x-icon name="campaign" :size="20" />
                        @endswitch
                    </div>

                    <div class="ldr-announcement-item__content">
                        <div class="ldr-announcement-item__header">
                            <h3>{{ $announcement->title }}</h3>
                            @if ($announcement->is_pinned)
                                <span class="ldr-badge ldr-badge--warning">{{ __('ui.announcement.pinned') }}</span>
                            @endif
                        </div>

                        <p>{{ Str::limit($announcement->excerpt ?? $announcement->body, 160) }}</p>

                        <div class="ldr-announcement-item__meta">
                            <span>{{ $announcement->publisher_name ?? __('ui.announcement.publisher') }}</span>
                            <span>·</span>
                            <span>{{ $announcement->published_at->locale('id')->translatedFormat('d M Y') }}</span>
                        </div>

                        @if ($announcement->cta_label && $announcement->cta_url)
                            <a href="{{ $announcement->cta_url }}" class="ldr-announcement-item__cta">
                                {{ $announcement->cta_label }}
                                <x-icon name="arrow-right" :size="14" />
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif