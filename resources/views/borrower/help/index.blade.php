@extends('layouts.borrower')

@section('title', __('ui.nav.help'))
@section('nav', 'help')

@section('content')
    <div class="ldr-help-page">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.nav.help') }}</h1>
                <p class="ldr-muted">{{ __('ui.help.subtitle') }}</p>
            </div>
        </div>

        <div class="ldr-help-grid">
            <div class="ldr-help-sop">
                <h2>{{ __('ui.help.sop_title') }}</h2>
                <ol class="ldr-help-sop__steps">
                    <li>
                        <span class="ldr-help-sop__number">1</span>
                        <div>
                            <strong>{{ __('ui.help.sop_1_title') }}</strong>
                            <p>{{ __('ui.help.sop_1_desc') }}</p>
                        </div>
                    </li>
                    <li>
                        <span class="ldr-help-sop__number">2</span>
                        <div>
                            <strong>{{ __('ui.help.sop_2_title') }}</strong>
                            <p>{{ __('ui.help.sop_2_desc') }}</p>
                        </div>
                    </li>
                    <li>
                        <span class="ldr-help-sop__number">3</span>
                        <div>
                            <strong>{{ __('ui.help.sop_3_title') }}</strong>
                            <p>{{ __('ui.help.sop_3_desc') }}</p>
                        </div>
                    </li>
                </ol>
            </div>

            <div class="ldr-help-faq">
                <h2>{{ __('ui.help.faq_title') }}</h2>
                <x-borrower.help-faq-accordion :faqs="$faqs" />
            </div>
        </div>

        <div class="ldr-help-calculator">
            <h2>{{ __('ui.help.calculator_title') }}</h2>
            <x-borrower.penalty-calculator />
        </div>

        <div class="ldr-help-documents">
            <h2>{{ __('ui.help.documents_title') }}</h2>
            <ul class="ldr-help-document-list">
                @foreach ($documents as $document)
                    <li class="ldr-help-document-item">
                        <a href="{{ Storage::url($document->path) }}" class="ldr-help-document-link">
                            <x-icon name="file-text" :size="16" />
                            <span>{{ $document->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="ldr-help-contact">
            <h2>{{ __('ui.help.contact_title') }}</h2>
            <p>{{ __('ui.help.contact_desc') }}</p>
            <p>
                <x-icon name="phone" :size="16" /> {{ $operatorContact ?? 'Ext. 402' }}<br>
                <x-icon name="message-circle" :size="16" /> {{ $operatorWhatsApp }}
            </p>
        </div>
    </div>
@endsection
