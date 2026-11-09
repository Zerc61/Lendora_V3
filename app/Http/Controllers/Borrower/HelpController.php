<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Faq;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $settings = app(SettingService::class);

        $faqs = Faq::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $documents = Document::where('organization_id', $user->organization_id)
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();

        return view('borrower.help.index', [
            'faqs' => $faqs,
            'documents' => $documents,
            'sopVersion' => $settings->get('sop_version', 'V2.4'),
            'operatorContact' => $settings->get('operator_contact_phone', 'Ext. 402'),
            'operatorWhatsApp' => $settings->get('operator_contact_whatsapp', '+62 812-3456-7890'),
        ]);
    }
}
